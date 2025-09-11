<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <x-heroicon-o-cpu-chip class="w-5 h-5 text-white" />
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Ollama Status
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    @if(app(\App\Services\OllamaService::class)->isAvailable())
                                        <span class="text-green-600">✅ Connected</span>
                                    @else
                                        <span class="text-red-600">❌ Disconnected</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <x-heroicon-o-bell class="w-5 h-5 text-white" />
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    AI Notifications
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    @if(config('ollama.notifications.enabled'))
                                        <span class="text-green-600">✅ Enabled</span>
                                    @else
                                        <span class="text-gray-600">❌ Disabled</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                                <x-heroicon-o-chart-bar class="w-5 h-5 text-white" />
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                    Today's AI Notifications
                                </dt>
                                <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                    {{ \App\Models\UserNotification::whereDate('created_at', today())->count() }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        {{ $this->form }}

        <!-- Information Section -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        About AI Notifications
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <ul class="list-disc list-inside space-y-1">
                            <li>AI notifications are generated using Ollama with the configured model</li>
                            <li>Notifications are personalized based on participant goals and progress</li>
                            <li>The system automatically prevents duplicate notifications within an hour</li>
                            <li>You can test the connection and generate sample notifications using the buttons above</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Commands -->
        <div class="bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-800 rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-4">
                Quick Commands
            </h3>
            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <p><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">php artisan notifications:generate-ai</code> - Generate AI notifications manually</p>
                <p><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">php artisan notifications:generate-ai --test</code> - Test Ollama connection and show statistics</p>
                <p><code class="bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">php artisan queue:work</code> - Process scheduled notification jobs</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
