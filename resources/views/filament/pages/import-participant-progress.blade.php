<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Import Participant Progress Data
        </x-slot>

        <x-slot name="description">
            Upload a CSV file containing participant progress data. The system will automatically create new participants and update existing ones with their progress information.
        </x-slot>

        <div class="space-y-6">
            <form wire:submit.prevent="import">
                {{ $this->form }}

                <div class="flex justify-between items-center mt-6">
                    <x-filament::button 
                        type="submit" 
                        color="primary"
                        wire:loading.attr="disabled"
                        wire:target="import"
                    >
                        <x-heroicon-m-arrow-up-tray class="w-4 h-4 mr-2" />
                        <span wire:loading.remove wire:target="import">Import Data</span>
                        <span wire:loading wire:target="import">Processing...</span>
                    </x-filament::button>
                </div>
            </form>

            @if($importResults)
                <x-filament::section>
                    <x-slot name="heading">
                        Import Results
                    </x-slot>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $importResults['total_rows'] }}
                                </div>
                                <div class="text-sm text-blue-600 dark:text-blue-400">
                                    Total Rows Processed
                                </div>
                            </div>

                            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $importResults['participants_created'] + $importResults['participants_updated'] }}
                                </div>
                                <div class="text-sm text-green-600 dark:text-green-400">
                                    Participants Processed
                                </div>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ $importResults['progress_records_created'] + $importResults['progress_records_updated'] }}
                                </div>
                                <div class="text-sm text-purple-600 dark:text-purple-400">
                                    Progress Records
                                </div>
                            </div>

                            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                    {{ count($importResults['errors']) }}
                                </div>
                                <div class="text-sm text-yellow-600 dark:text-yellow-400">
                                    Errors
                                </div>
                            </div>
                        </div>

                        @if(count($importResults['errors']) > 0)
                            <div class="mt-6">
                                <h4 class="font-semibold text-red-600 dark:text-red-400 mb-2">Errors:</h4>
                                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg max-h-60 overflow-y-auto">
                                    <ul class="list-disc list-inside space-y-1">
                                        @foreach($importResults['errors'] as $error)
                                            <li class="text-sm text-red-600 dark:text-red-400">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg">
                            <h4 class="font-semibold mb-2 text-gray-900 dark:text-gray-100">Summary:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="text-gray-700 dark:text-gray-300">
                                    <strong class="text-gray-900 dark:text-gray-100">Participants:</strong>
                                    <br>• {{ $importResults['participants_created'] }} created
                                    <br>• {{ $importResults['participants_updated'] }} updated
                                </div>
                                <div class="text-gray-700 dark:text-gray-300">
                                    <strong class="text-gray-900 dark:text-gray-100">Progress Records:</strong>
                                    <br>• {{ $importResults['progress_records_created'] }} created
                                    <br>• {{ $importResults['progress_records_updated'] }} updated
                                    <br>• {{ $importResults['skipped'] }} skipped
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @endif
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            CSV Format Requirements
        </x-slot>

        <div class="space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Your CSV file should contain the following columns (column names must match exactly):
            </p>

            <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg mb-4">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Note:</strong> Passwords will be automatically generated for new participants using their first name and student number (e.g., "John55543942"). Participants can change their password after logging in.
                </p>
            </div>
            
            <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg mb-4">
                <p class="text-sm text-orange-800 dark:text-orange-200">
                    <strong>Enrollment Date:</strong> If the "Enrollment Date" field is empty or invalid, the system will automatically set it to 30 days ago as a fallback. Supports formats like MM/DD/YYYY, M/D/YYYY, MM-DD-YYYY, and YYYY-MM-DD.
                </p>
            </div>
            
            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg mb-4">
                <p class="text-sm text-green-800 dark:text-green-200">
                    <strong>Email Addresses:</strong> Multiple participants can share the same email address (useful for family accounts or institutional emails). Each participant is uniquely identified by their Student Number.
                </p>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg mb-4">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    <strong>Large Files:</strong> Processing may take several minutes for files with hundreds of rows. The system processes data in batches of 50 rows for optimal performance.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold mb-2">Required Participant Fields:</h4>
                    <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400">
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Student Number</code></li>
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Student Name</code></li>
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Program Description</code></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold mb-2">Required Progress Fields:</h4>
                    <ul class="text-sm space-y-1 text-gray-600 dark:text-gray-400">
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Enrollment Date</code></li>
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Exams Completed %</code></li>
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Total Exams</code></li>
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Exams Taken</code></li>
                        <li>• <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded">Exams Needed</code></li>
                    </ul>
                </div>
            </div>

            <div>
                <h4 class="font-semibold mb-2">Optional Fields:</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Student Personal Email</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Student Phone Number</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Student Location</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Student Gender</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Student Date of Birth</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Client Name</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Student Status</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded text-xs">Graduation Date</code>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
