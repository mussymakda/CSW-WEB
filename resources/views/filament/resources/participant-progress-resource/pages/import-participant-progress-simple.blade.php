<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Import Participant Progress Data
            </x-slot>

            <x-slot name="description">
                Upload a CSV file containing participant and progress data.
                <br><br>
                <strong>Participant Data:</strong> Student Number, Name, Email, Phone, Location, Gender, DOB, Weight, Height, ACEDS Number, Client Name
                <br><br>
                <strong>Progress Tracking (5 key fields):</strong> Exams Completed %, Total Exams, Exams Taken, Exams Needed, Enrollment Date
                <br><br>
                <em>Note: Please save your Excel file as CSV format before uploading.</em>
            </x-slot>

            <form wire:submit.prevent="import" class="space-y-6">
                <div>
                    <label for="excelFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        CSV File *
                    </label>
                    <input 
                        type="file" 
                        wire:model="excelFile" 
                        id="excelFile"
                        accept=".csv,.txt"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    >
                    @error('excelFile') 
                        <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="updateExisting" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Update existing participants</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">If checked, existing participants will be updated with new data. If unchecked, duplicates will be skipped.</p>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Import Notes (Optional)
                    </label>
                    <textarea 
                        wire:model="notes" 
                        id="notes"
                        rows="3"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Optional notes about this import batch"
                    ></textarea>
                </div>

                <div class="flex justify-between items-center">
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
        </x-filament::section>

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
                                Progress Records Processed
                            </div>
                        </div>

                        <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ count($importResults['errors']) }}
                            </div>
                            <div class="text-sm text-red-600 dark:text-red-400">
                                Errors
                            </div>
                        </div>
                    </div>

                    @if(count($importResults['errors']) > 0)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 rounded-lg p-4">
                            <h4 class="font-semibold text-red-700 dark:text-red-400 mb-2">Import Errors:</h4>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($importResults['errors'] as $error)
                                    <li class="text-sm text-red-600 dark:text-red-400">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Summary:</h4>
                        <pre class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $this->getImportSummary($importResults) }}</pre>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
