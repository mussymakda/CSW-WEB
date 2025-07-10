@if($getState())
    @php $data = $getState(); @endphp
    
    <div class="space-y-6">
        <!-- Test Progress -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Test Progress</h4>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $data['tests_passed'] ?? 0 }} / {{ $data['total_tests'] ?? 0 }} tests passed
                </span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 ease-out shadow-sm" 
                     style="width: {{ $data['test_progress'] ?? 0 }}%"></div>
            </div>
            
            <div class="flex justify-between items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $data['test_progress'] ?? 0 }}% completed</span>
                @if(isset($data['tests_taken']) && $data['tests_taken'] > 0)
                    <span>{{ round(($data['tests_passed'] / $data['tests_taken']) * 100, 1) }}% pass rate</span>
                @endif
            </div>
        </div>

        <!-- Time Progress -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Time Progress</h4>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $data['time_progress'] ?? 0 }}% elapsed
                </span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500 ease-out shadow-sm" 
                     style="width: {{ $data['time_progress'] ?? 0 }}%"></div>
            </div>
            
            <div class="flex justify-between items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Course duration elapsed</span>
                <span>Time-based progress</span>
            </div>
        </div>

        <!-- Overall Progress -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Overall Progress</h4>
                </div>
                <span class="text-sm font-semibold {{ ($data['overall_progress'] ?? 0) >= 80 ? 'text-green-600 dark:text-green-400' : (($data['overall_progress'] ?? 0) >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                    {{ $data['overall_progress'] ?? 0 }}%
                </span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                @php
                    $overallProgress = $data['overall_progress'] ?? 0;
                    $colorClass = $overallProgress >= 80 ? 'from-green-500 to-green-600' : 
                                  ($overallProgress >= 60 ? 'from-yellow-500 to-yellow-600' : 'from-red-500 to-red-600');
                @endphp
                <div class="bg-gradient-to-r {{ $colorClass }} h-4 rounded-full transition-all duration-700 ease-out shadow-md" 
                     style="width: {{ $overallProgress }}%"></div>
            </div>
            
            <div class="flex justify-between items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                <span>Weighted: 70% tests + 30% time</span>
                @if(isset($data['average_score']) && $data['average_score'])
                    <span>Avg Score: {{ number_format($data['average_score'], 1) }}%</span>
                @endif
            </div>
        </div>

        <!-- Progress Summary -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $data['tests_passed'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Tests Passed</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $data['tests_taken'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Tests Taken</div>
                </div>
            </div>
            
            @if(isset($data['average_score']) && $data['average_score'])
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600 text-center">
                    <div class="text-lg font-semibold text-purple-600 dark:text-purple-400">
                        {{ number_format($data['average_score'], 1) }}%
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Average Test Score</div>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p>No active course progress to display</p>
    </div>
@endif
