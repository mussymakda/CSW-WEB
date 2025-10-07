<?php

namespace App\Filament\Resources\ParticipantProgressResource\Pages;

use App\Filament\Resources\ParticipantProgressResource;
use App\Models\Course;
use App\Models\CourseBatch;
use App\Models\Goal;
use App\Models\Participant;
use App\Models\ParticipantCourseProgress;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\WithFileUploads;

class ImportParticipantProgressSimple extends Page
{
    use WithFileUploads;

    protected static string $resource = ParticipantProgressResource::class;

    protected static string $view = 'filament.resources.participant-progress-resource.pages.import-participant-progress-simple';

    protected static ?string $title = 'Import Participant Progress (Simple)';

    public $excelFile;

    public $updateExisting = true;

    public $notes = '';

    public $importResults = null;

    public function import()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:csv,txt|max:10240', // Only CSV for now to avoid ZipArchive issues
        ]);

        try {
            $filePath = $this->excelFile->getRealPath();

            $results = [
                'total_rows' => 0,
                'participants_created' => 0,
                'participants_updated' => 0,
                'progress_records_created' => 0,
                'progress_records_updated' => 0,
                'errors' => [],
                'skipped' => 0,
            ];

            // Use native PHP CSV reading to avoid ZipArchive dependency
            $rows = [];
            if (($handle = fopen($filePath, 'r')) !== false) {
                $headers = fgetcsv($handle, 1000, ',');
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $row = array_combine($headers, $data);
                    $rows[] = $row;
                }
                fclose($handle);
            }

            $results['total_rows'] = count($rows);

            foreach ($rows as $index => $row) {
                try {
                    $this->processRow($row, $this->updateExisting, $results, $index + 1);
                } catch (\Exception $e) {
                    $results['errors'][] = 'Row '.($index + 1).': '.$e->getMessage();
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
        // Map Excel columns to our data structure
        // Participant Data
        $studentNumber = $row['Student Number'] ?? null;
        $studentName = $row['Student Name'] ?? null;
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
        $locationName = $row['Location Name'] ?? null;

        // Progress Data - Matching the 5 key fields you specified
        $examsCompletedPercent = $row['Exams Completed %'] ?? null;  // Progress percentage
        $totalExams = $row['Total Exams'] ?? null;                   // Total number of exams
        $examsTaken = $row['Exams Taken'] ?? null;                   // Number completed
        $examsNeeded = $row['Exams Needed'] ?? null;                 // Remaining exams
        $lastExamDate = $row['Last Exam Date'] ?? null;              // Last exam taken
        // Enrollment Date is already captured above

        if (! $studentNumber || ! $studentName) {
            $results['errors'][] = "Row $rowNumber: Missing required data (Student Number or Student Name)";

            return;
        }

        // Find or create participant with all available fields
        $participant = Participant::where('student_number', $studentNumber)->first();

        $participantData = [
            'student_number' => $studentNumber,
            'name' => $studentName,
            'email' => $studentEmail,
            'phone' => $studentPhone,
            'location' => $studentLocation,
            'client_name' => $clientName,
            'updated_at' => now(),
        ];

        // Add optional fields if they exist in the Excel
        if ($studentGender) {
            $participantData['gender'] = strtolower($studentGender);
        }
        if ($studentDob) {
            $participantData['dob'] = $this->parseDate($studentDob);
        }
        if ($studentWeight && is_numeric($studentWeight)) {
            $participantData['weight'] = (float) $studentWeight;
        }
        if ($studentHeight && is_numeric($studentHeight)) {
            $participantData['height'] = (float) $studentHeight;
        }
        if ($acedsNumber) {
            $participantData['aceds_no'] = $acedsNumber;
        }

        if (! $participant) {
            $participantData['created_at'] = now();
            $participantData['goal_id'] = $this->getDefaultGoalId(); // Add default goal
            $participant = Participant::create($participantData);
            $results['participants_created']++;
        } elseif ($updateExisting) {
            $participant->update($participantData);
            $results['participants_updated']++;
        }

        // Find or create course
        $course = Course::where('name', $programDescription)->first();
        if (! $course && $programDescription) {
            $course = Course::create([
                'name' => $programDescription,
                'description' => $programDescription,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Find or create course batch
        $courseBatch = null;
        if ($course && $locationName) {
            $batchName = $locationName.' - '.$programDescription;
            $courseBatch = CourseBatch::where('course_id', $course->id)
                ->where('batch_name', $batchName)
                ->first();

            if (! $courseBatch) {
                $courseBatch = CourseBatch::create([
                    'course_id' => $course->id,
                    'batch_name' => $batchName,
                    'start_date' => $this->parseDate($enrollmentDate),
                    'end_date' => $this->parseDate($graduationDate),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create or update progress record - Focus on the 5 key progress fields
        if ($courseBatch) {
            $progressRecord = ParticipantCourseProgress::where('participant_id', $participant->id)
                ->where('course_batch_id', $courseBatch->id)
                ->first();

            $progressPercentage = $this->parsePercentage($examsCompletedPercent);
            $status = $this->mapStatus($studentStatus);

            // Core progress data - focused on your 5 key fields
            $progressData = [
                'participant_id' => $participant->id,
                'course_batch_id' => $courseBatch->id,
                'enrollment_date' => $this->parseDate($enrollmentDate),     // 1. Enrollment Date
                'progress_percentage' => $progressPercentage,               // 2. Exams Completed %
                'total_exams' => (int) ($totalExams ?? 0),                  // 3. Total Exams
                'exams_taken' => (int) ($examsTaken ?? 0),                  // 4. Exams Taken
                'exams_needed' => (int) ($examsNeeded ?? 0),                // 5. Exams Needed
                'status' => $status,
                'started_at' => $this->parseDate($enrollmentDate),
                'completed_at' => $status === 'completed' ? $this->parseDate($graduationDate) : null,
                'grade' => $this->calculateGrade($progressPercentage),
                'last_exam_date' => $this->parseDate($lastExamDate),
                'notes' => 'Imported from Excel on '.date('Y-m-d H:i:s'),
                'updated_at' => now(),
            ];

            if (! $progressRecord) {
                $progressData['created_at'] = now();
                ParticipantCourseProgress::create($progressData);
                $results['progress_records_created']++;
            } elseif ($updateExisting) {
                $progressRecord->update($progressData);
                $results['progress_records_updated']++;
            } else {
                $results['skipped']++;
            }
        }
    }

    private function parseDate($dateString)
    {
        if (! $dateString || trim($dateString) === '') {
            return null;
        }

        try {
            $formats = ['m/d/Y', 'm-d-Y', 'd/m/Y', 'd-m-Y', 'Y-m-d'];

            foreach ($formats as $format) {
                $date = Carbon::createFromFormat($format, trim($dateString));
                if ($date !== false) {
                    return $date;
                }
            }

            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parsePercentage($percentString)
    {
        if (! $percentString) {
            return 0;
        }

        $cleaned = str_replace(['%', ' '], '', $percentString);

        return (float) $cleaned;
    }

    private function mapStatus($status)
    {
        if (! $status) {
            return 'enrolled';
        }

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
        if ($progressPercentage >= 90) {
            return 95;
        }
        if ($progressPercentage >= 80) {
            return 85;
        }
        if ($progressPercentage >= 70) {
            return 75;
        }
        if ($progressPercentage >= 60) {
            return 65;
        }

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

    private function getDefaultGoalId()
    {
        // Find or create a default goal for imports
        $defaultGoal = Goal::where('name', 'Course Completion')->first();

        if (! $defaultGoal) {
            $defaultGoal = Goal::create([
                'name' => 'Course Completion',
                'description' => 'Default goal for imported participants',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $defaultGoal->id;
    }
}
