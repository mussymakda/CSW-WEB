<?php

namespace App\Filament\Pages;

use App\Services\AINotificationService;
use App\Services\OllamaService;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class AIConfiguration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string $view = 'filament.pages.a-i-configuration';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $title = 'AI Settings';

    protected static ?string $navigationLabel = 'AI Settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'notification_lookahead' => config('ollama.notifications.lookahead_hours', 24),
            'batch_size' => config('ollama.notifications.batch_size', 10),
        ]);
    }

    protected function getOllamaService(): OllamaService
    {
        return app(OllamaService::class);
    }

    protected function getAINotificationService(): AINotificationService
    {
        return app(AINotificationService::class);
    }

    protected function getServiceStatus(): string
    {
        try {
            $ollamaService = $this->getOllamaService();
            $isAvailable = $ollamaService->isAvailable();

            return $isAvailable ? '🟢 Online' : '🔴 Offline';
        } catch (\Exception $e) {
            return '🟡 Unknown - '.$e->getMessage();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('AI Service Status')
                    ->description('Monitor and control the AI notification system')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('service_status')
                                ->label('Service Status')
                                ->default($this->getServiceStatus())
                                ->disabled()
                                ->suffixIcon('heroicon-o-signal'),

                            TextInput::make('current_model')
                                ->label('Current Model')
                                ->default('llama3.2:3b (Optimized)')
                                ->disabled()
                                ->suffixIcon('heroicon-o-cpu-chip'),

                            TextInput::make('batch_size')
                                ->label('Batch Size')
                                ->numeric()
                                ->default(10)
                                ->helperText('Number of notifications per batch'),
                        ]),
                    ]),

                Section::make('AI Notifications')
                    ->description('AI notifications automatically generate based on participant schedules')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('notification_lookahead')
                                ->label('Notification Lookahead (hours)')
                                ->numeric()
                                ->default(24)
                                ->helperText('Generate notifications for tasks within this many hours'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_connection')
                ->label('Test Connection')
                ->icon('heroicon-o-wifi')
                ->color('info')
                ->action('testConnection'),

            Action::make('reconnect_ollama')
                ->label('Reconnect Ollama')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action('reconnectOllama'),

            Action::make('generate_batch')
                ->label('Generate Notifications Now')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->action('generateNotificationBatch'),

            Action::make('view_stats')
                ->label('View Statistics')
                ->icon('heroicon-o-chart-bar')
                ->color('gray')
                ->action('viewStatistics'),
        ];
    }

    public function testConnection(): void
    {
        try {
            $ollamaService = $this->getOllamaService();

            $results = $ollamaService->test();

            if ($results['connection'] && $results['model_available'] && $results['test_generation']) {
                Notification::make()
                    ->title('Connection Successful!')
                    ->body('Ollama is working correctly with model: '.config('ollama.model'))
                    ->success()
                    ->send();
            } else {
                $error = $results['error'] ?? 'Unknown connection issue';
                Notification::make()
                    ->title('Connection Failed')
                    ->body($error)
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Test Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function reconnectOllama(): void
    {
        try {
            // Reset the Ollama service connection
            $ollamaService = $this->getOllamaService();
            $ollamaService->reconnect();

            // Test the connection
            $results = $ollamaService->test();

            if ($results['connection'] && $results['model_available']) {
                Notification::make()
                    ->title('Reconnection Successful!')
                    ->body('Ollama has been reconnected and is working properly')
                    ->success()
                    ->send();
            } else {
                $error = $results['error'] ?? 'Reconnection failed';
                Notification::make()
                    ->title('Reconnection Failed')
                    ->body($error)
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Reconnection Error')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function generateNotificationBatch(): void
    {
        try {
            $aiNotificationService = $this->getAINotificationService();

            $results = $aiNotificationService->generateScheduledNotifications();

            $message = "Generated: {$results['generated']} notifications";
            if (! empty($results['errors'])) {
                $message .= "\nErrors: ".count($results['errors']);
            }

            Notification::make()
                ->title('Batch Generation Complete')
                ->body($message)
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Batch Generation Failed')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function viewStatistics(): void
    {
        try {
            $aiNotificationService = $this->getAINotificationService();

            $stats = $aiNotificationService->getStatistics();

            $message = "Today's AI Notifications: {$stats['total_ai_notifications']}\n";
            $message .= "Participants Notified: {$stats['participants_notified_today']}\n";
            $message .= 'Ollama Status: '.($stats['ollama_status']['available'] ? 'Available' : 'Unavailable');

            Notification::make()
                ->title('AI Notification Statistics')
                ->body($message)
                ->info()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Statistics Error')
                ->body('Error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'AI Settings';
    }
}
