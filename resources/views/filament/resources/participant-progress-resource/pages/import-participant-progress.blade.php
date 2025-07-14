<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Import Participant Progress Data
        </x-slot>

        <x-slot name="description">
            Upload an Excel file containing participant progress data. The system will automatically create new participants and update existing ones with their progress information.
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Participants</h4>
                                <ul class="space-y-1 text-sm">
                                    <li class="text-green-600 dark:text-green-400">
                                        ✓ {{ $importResults['participants_created'] }} new participants created
                                    </li>
                                    <li class="text-blue-600 dark:text-blue-400">
                                        ↻ {{ $importResults['participants_updated'] }} existing participants updated
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Progress Records</h4>
                                <ul class="space-y-1 text-sm">
                                    <li class="text-green-600 dark:text-green-400">
                                        ✓ {{ $importResults['progress_records_created'] }} new progress records created
                                    </li>
                                    <li class="text-blue-600 dark:text-blue-400">
                                        ↻ {{ $importResults['progress_records_updated'] }} existing records updated
                                    </li>
                                    <li class="text-gray-600 dark:text-gray-400">
                                        ➤ {{ $importResults['skipped'] }} records skipped
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @if(count($importResults['errors']) > 0)
                            <div>
                                <h4 class="font-medium text-red-600 dark:text-red-400 mb-2">Errors</h4>
                                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                                    <ul class="space-y-1 text-sm text-red-600 dark:text-red-400">
                                        @foreach($importResults['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-filament::section>
            @endif

            <x-filament::section>
                <x-slot name="heading">
                    Expected Excel Format
                </x-slot>

                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Your Excel file should contain the following columns (case-sensitive):
                    </p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <th class="px-3 py-2 text-left font-medium text-gray-900 dark:text-gray-100">Required Columns</th>
                                    <th class="px-3 py-2 text-left font-medium text-gray-900 dark:text-gray-100">Optional Columns</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                        • Student Number<br>
                                        • Student Name<br>
                                        • Program Description
                                    </td>
                                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                                        • Student Personal Email<br>
                                        • Student Phone Number<br>
                                        • Student Location<br>
                                        • Enrollment Date<br>
                                        • Graduation Date<br>
                                        • Student Status<br>
                                        • Exams Completed %<br>
                                        • Total Exams<br>
                                        • Exams Taken<br>
                                        • Exams Needed<br>
                                        • Last Exam Date<br>
                                        • Client Name<br>
                                        • Location Name
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        <p><strong>Notes:</strong></p>
                        <ul class="list-disc list-inside space-y-1 mt-2">
                            <li>Dates can be in formats: MM/DD/YYYY, MM-DD-YYYY, DD/MM/YYYY, DD-MM-YYYY, or YYYY-MM-DD</li>
                            <li>Percentages can include or exclude the % symbol</li>
                            <li>Student Status will be mapped: Canceled/Cancelled → Dropped, Graduated → Completed</li>
                            <li>The system will automatically create courses and batches if they don't exist</li>
                        </ul>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </x-filament::section>
</x-filament-panels::page>
