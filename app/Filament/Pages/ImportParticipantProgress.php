<?php

namespace App\Filament\Pages;

use App\Models\Participant;
use App\Models\Course;
use App\Models\ParticipantCourseProgress;
use App\Models\Goal;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportParticipantProgress extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string $view = 'filament.pages.import-participant-progress';
    protected static ?string $navigationGroup = 'Course Management';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Import Progress Data';
    
    public ?array $data = [];
    public ?array $importResults = null;
    
    // Cache for performance optimization
    private ?Goal $defaultGoal = null;
    private array $courseCache = [];
    private array $participantCache = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('csv_file')
                    ->label('Upload CSV File')
                    ->disk('local')
                    ->directory('imports')
                    ->acceptedFileTypes(['text/csv', 'application/csv', '.csv', 'text/plain'])
                    ->maxSize(10240) // 10MB
                    ->required()
                    ->live()
                    ->helperText('Upload a CSV file with participant progress data. Required columns: Student Number, Student Name, Exams Completed %, Total Exams, Exams Taken, Exams Needed, Enrollment Date')
                    ->validationMessages([
                        'required' => 'Please select a CSV file to upload.',
                    ]),
                
                Toggle::make('update_existing')
                    ->label('Update existing records')
                    ->helperText('If enabled, existing participants and progress records will be updated with new data')
                    ->default(true),
            ])
            ->statePath('data');
    }

    public function import()
    {
        try {
            // Increase execution time for large imports
            set_time_limit(300); // 5 minutes
            ini_set('memory_limit', '512M');
            
            // Validate the form first
            $this->form->validate();
            
            $data = $this->form->getState();
            
            if (!isset($data['csv_file']) || !$data['csv_file']) {
                Notification::make()
                    ->title('No file selected')
                    ->body('Please upload a CSV file before importing.')
                    ->danger()
                    ->send();
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Validation Error')
                ->body('Please check your form inputs and try again.')
                ->danger()
                ->send();
            throw $e;
        }

        try {
            $filePath = Storage::disk('local')->path($data['csv_file']);
            
            $results = [
                'total_rows' => 0,
                'participants_created' => 0,
                'participants_updated' => 0,
                'progress_records_created' => 0,
                'progress_records_updated' => 0,
                'errors' => [],
                'skipped' => 0
            ];

            // Read CSV file with better error handling
            $rows = [];
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                // Read header row
                $headers = fgetcsv($handle, 1000, ",");
                
                if (!$headers) {
                    throw new \Exception("Could not read CSV headers. Please check your file format.");
                }
                
                // Clean headers (remove BOM, trim whitespace)
                $headers = array_map(function($header) {
                    return trim(str_replace("\xEF\xBB\xBF", '', $header));
                }, $headers);
                
                $rowNumber = 1; // Start from 1 (header is row 0)
                
                while (($data_row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rowNumber++;
                    
                    // Skip empty rows
                    if (empty(array_filter($data_row))) {
                        continue;
                    }
                    
                    // Ensure we have the same number of columns
                    if (count($data_row) !== count($headers)) {
                        $results['errors'][] = "Row $rowNumber: Column count mismatch. Expected " . count($headers) . " columns, got " . count($data_row);
                        continue;
                    }
                    
                    // Combine headers with data
                    $row = array_combine($headers, $data_row);
                    
                    if ($row === false) {
                        $results['errors'][] = "Row $rowNumber: Could not parse row data";
                        continue;
                    }
                    
                    $rows[] = $row;
                }
                fclose($handle);
            } else {
                throw new \Exception("Could not open CSV file for reading.");
            }
            
            $results['total_rows'] = count($rows);

            // Process rows in batches with database transactions for better performance
            $batchSize = 50; // Process 50 rows at a time
            $chunks = array_chunk($rows, $batchSize);
            $totalChunks = count($chunks);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                DB::transaction(function () use ($chunk, $data, &$results, $chunkIndex, $batchSize) {
                    foreach ($chunk as $index => $row) {
                        $actualRowNumber = ($chunkIndex * $batchSize) + $index + 1;
                        try {
                            $this->processRow($row, $data['update_existing'], $results, $actualRowNumber);
                        } catch (\Exception $e) {
                            $results['errors'][] = "Row $actualRowNumber: " . $e->getMessage();
                            Log::error("Import error on row $actualRowNumber", [
                                'error' => $e->getMessage(),
                                'row_data' => $row
                            ]);
                        }
                    }
                });
                
                // Show progress for large imports
                if ($totalChunks > 10 && ($chunkIndex + 1) % 5 === 0) {
                    $progress = round((($chunkIndex + 1) / $totalChunks) * 100);
                    Log::info("Import progress: $progress% complete ({$results['participants_created']} participants created, {$results['progress_records_created']} progress records created)");
                }
                
                // Free memory after each batch
                if ($chunkIndex % 10 === 0) {
                    gc_collect_cycles();
                }
            }

            $this->importResults = $results;

            Notification::make()
                ->title('Import completed')
                ->body($this->getImportSummary($results))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function processRow(array $row, bool $updateExisting, array &$results, int $rowNumber)
    {
        // Debug: Log the row data (remove this in production)
        Log::debug("Processing row $rowNumber", ['row_data' => $row]);
        
        // Map CSV columns to our data structure with better trimming
        $studentNumber = trim($row['Student Number'] ?? '');
        $studentName = trim($row['Student Name'] ?? '');
        $studentEmail = $row['Student Personal Email'] ?? null;
        $studentPhone = $row['Student Phone Number'] ?? null;
        $studentLocation = $row['Student Location'] ?? null;
        $studentGender = $row['Student Gender'] ?? null;
        $studentDob = $row['Student Date of Birth'] ?? $row['DOB'] ?? null;
        $studentWeight = $row['Student Weight'] ?? $row['Weight'] ?? null;
        $studentHeight = $row['Student Height'] ?? $row['Height'] ?? null;
        $acedsNumber = $row['ACEDS Number'] ?? $row['Student ID'] ?? null;
        $clientName = $row['Client Name'] ?? null;
        
        // Course/Program Data
        $enrollmentDate = $row['Enrollment Date'] ?? null;
        $graduationDate = $row['Graduation Date'] ?? null;
        $programDescription = $row['Program Description'] ?? null;
        $studentStatus = $row['Student Status'] ?? null;
        
        // Progress Data - Your 5 key fields
        $examsCompletedPercent = $row['Exams Completed %'] ?? null;
        $totalExams = $row['Total Exams'] ?? null;
        $examsTaken = $row['Exams Taken'] ?? null;
        $examsNeeded = $row['Exams Needed'] ?? null;

        // More robust checking for required fields
        if (empty($studentNumber) || empty($studentName)) {
            $debugInfo = [
                'student_number' => $studentNumber,
                'student_name' => $studentName,
                'available_keys' => array_keys($row)
            ];
            Log::debug("Missing required data for row $rowNumber", $debugInfo);
            
            $results['errors'][] = "Row $rowNumber: Missing required data (Student Number: '$studentNumber' or Student Name: '$studentName'). Available columns: " . implode(', ', array_keys($row));
            return;
        }

        // Find or create participant with caching
        $participant = $this->participantCache[$studentNumber] ?? null;
        if (!$participant) {
            $participant = Participant::where('student_number', $studentNumber)->first();
            if ($participant) {
                $this->participantCache[$studentNumber] = $participant;
            }
        }
        
        $participantData = [
            'student_number' => $studentNumber,
            'name' => $studentName,
            'email' => $studentEmail,
            'phone' => $studentPhone,
            'location' => $studentLocation,
            'client_name' => $clientName,
            'password' => $this->generateDefaultPassword($studentNumber, $studentName),
            'updated_at' => now(),
        ];

        // Add optional fields if they exist
        if ($studentGender) {
            $participantData['gender'] = strtolower($studentGender);
        }
        if ($studentDob) {
            $participantData['dob'] = $this->parseDate($studentDob);
        }
        if ($studentWeight && is_numeric($studentWeight)) {
            $participantData['weight'] = (float)$studentWeight;
        }
        if ($studentHeight && is_numeric($studentHeight)) {
            $participantData['height'] = (float)$studentHeight;
        }
        if ($acedsNumber) {
            $participantData['aceds_no'] = $acedsNumber;
        }
        
        if (!$participant) {
            $participantData['created_at'] = now();
            $participantData['goal_id'] = $this->getDefaultGoalId();
            
            $participant = Participant::create($participantData);
            $this->participantCache[$studentNumber] = $participant; // Cache the new participant
            $results['participants_created']++;
        } else if ($updateExisting) {
            // Remove password from update data (don't change existing passwords)
            $updateData = $participantData;
            unset($updateData['password']);
            $participant->update($updateData);
            $results['participants_updated']++;
        }

        // Find or create course with caching
        $course = null;
        if ($programDescription) {
            $course = $this->courseCache[$programDescription] ?? null;
            if (!$course) {
                $course = Course::where('name', $programDescription)->first();
                if (!$course) {
                    $course = Course::create([
                        'name' => $programDescription,
                        'description' => $programDescription,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $this->courseCache[$programDescription] = $course;
            }
        }

        // Create progress record directly linked to participant (simplified - no course/batch dependency)
        $progressRecord = ParticipantCourseProgress::where('participant_id', $participant->id)
            ->first();

        $progressPercentage = $this->parsePercentage($examsCompletedPercent);
        $status = $this->mapStatus($studentStatus);
        
        // Parse enrollment date with fallback
        $enrollmentDateParsed = $this->parseDate($enrollmentDate);
        if (!$enrollmentDateParsed) {
            // If no enrollment date provided, use today or estimate based on progress
            $enrollmentDateParsed = Carbon::now()->subDays(30); // Default to 30 days ago
        }

        $progressData = [
            'participant_id' => $participant->id,
            'course_batch_id' => null, // We're not using course batches anymore
            'enrollment_date' => $enrollmentDateParsed,                 // 1. Enrollment Date (with fallback)
            'progress_percentage' => $progressPercentage,               // 2. Exams Completed %
            'total_exams' => (int)($totalExams ?? 0),                  // 3. Total Exams
            'exams_taken' => (int)($examsTaken ?? 0),                  // 4. Exams Taken  
            'exams_needed' => (int)($examsNeeded ?? 0),                // 5. Exams Needed
            'status' => $status,
            'started_at' => $enrollmentDateParsed,
            'completed_at' => $status === 'completed' ? $this->parseDate($graduationDate) : null,
            'grade' => $this->calculateGrade($progressPercentage),
            'notes' => "Program: {$programDescription} | Imported from CSV on " . date('Y-m-d H:i:s'),
            'updated_at' => now(),
        ];

            if (!$progressRecord) {
                $progressData['created_at'] = now();
                ParticipantCourseProgress::create($progressData);
                $results['progress_records_created']++;
            } else if ($updateExisting) {
                $progressRecord->update($progressData);
                $results['progress_records_updated']++;
            } else {
                $results['skipped']++;
            }

        return $results;
    }

    private function getDefaultGoalId()
    {
        if (!$this->defaultGoal) {
            $this->defaultGoal = Goal::where('name', 'Course Completion')->first();
            
            if (!$this->defaultGoal) {
                $this->defaultGoal = Goal::create([
                    'name' => 'Course Completion',
                    'description' => 'Default goal for imported participants',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        return $this->defaultGoal->id;
    }

    private function parseDate($dateString)
    {
        if (!$dateString || trim($dateString) === '' || strtolower(trim($dateString)) === 'null') {
            return null;
        }

        try {
            $dateString = trim($dateString);
            
            // Try to parse with native PHP DateTime first (doesn't need intl)
            // This handles most common formats automatically
            $phpDate = \DateTime::createFromFormat('m/d/Y', $dateString) ?: 
                      \DateTime::createFromFormat('n/j/Y', $dateString) ?: 
                      \DateTime::createFromFormat('m-d-Y', $dateString) ?: 
                      \DateTime::createFromFormat('d/m/Y', $dateString) ?: 
                      \DateTime::createFromFormat('Y-m-d', $dateString) ?: 
                      \DateTime::createFromFormat('m/d/y', $dateString);
            
            if ($phpDate !== false) {
                // Convert to Carbon for Laravel compatibility
                return Carbon::instance($phpDate);
            }
            
            // Try strtotime as fallback (handles many formats)
            $timestamp = strtotime($dateString);
            if ($timestamp !== false) {
                return Carbon::createFromTimestamp($timestamp);
            }
            
            // Last resort: try Carbon parse (might work without intl for simple formats)
            return Carbon::parse($dateString);
            
        } catch (\Exception $e) {
            // Log the problematic date for debugging
            Log::warning("Could not parse date: {$dateString}");
            return null;
        }
    }

    private function parsePercentage($percentString)
    {
        if (!$percentString) return 0;
        
        $cleaned = str_replace(['%', ' '], '', $percentString);
        return (float)$cleaned;
    }

    private function mapStatus($status)
    {
        if (!$status) return 'enrolled';
        
        $status = strtolower(trim($status));
        
        $statusMap = [
            'canceled' => 'dropped',
            'cancelled' => 'dropped',
            'active' => 'active',
            'completed' => 'completed',
            'graduated' => 'completed',
            'enrolled' => 'enrolled',
            'paused' => 'paused',
            'suspended' => 'paused',
        ];

        return $statusMap[$status] ?? 'enrolled';
    }

    private function calculateGrade($progressPercentage)
    {
        if ($progressPercentage >= 90) return 95;
        if ($progressPercentage >= 80) return 85;
        if ($progressPercentage >= 70) return 75;
        if ($progressPercentage >= 60) return 65;
        return max(50, $progressPercentage);
    }

    private function getImportSummary(array $results): string
    {
        return sprintf(
            "Processed %d rows\nParticipants: %d created, %d updated\nProgress Records: %d created, %d updated\nSkipped: %d\nErrors: %d",
            $results['total_rows'],
            $results['participants_created'],
            $results['participants_updated'],
            $results['progress_records_created'],
            $results['progress_records_updated'],
            $results['skipped'],
            count($results['errors'])
        );
    }

    private function generateDefaultPassword($studentNumber, $studentName)
    {
        // Generate a default password using student number and first name
        $firstName = explode(' ', trim($studentName))[0];
        $defaultPassword = $firstName . $studentNumber;
        
        // Ensure minimum length and add some complexity
        if (strlen($defaultPassword) < 8) {
            $defaultPassword .= '2024!';
        }
        
        return $defaultPassword;
    }
}
