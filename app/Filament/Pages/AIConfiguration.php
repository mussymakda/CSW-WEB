<?php

namespace App\Filament\Pages;

use App\Services\OllamaService;
use App\Services\AINotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;

class AIConfiguration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string $view = 'filament.pages.a-i-configuration';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $title = 'AI Settings';

    protected static ?string $navigationLabel = 'AI Settings';

    public ?array $data = [];

    protected ?OllamaService $ollamaService = null;
    protected ?AINotificationService $aiNotificationService = null;

    public function mount(): void
    {
        $this->ollamaService = app(OllamaService::class);
        $this->aiNotificationService = app(AINotificationService::class);
        
        $this->form->fill([
            'ollama_enabled' => config('ollama.enabled'),
            'ollama_host' => config('ollama.host'),
            'ollama_model' => config('ollama.model'),
            'notifications_enabled' => config('ollama.notifications.enabled'),
            'notification_interval' => config('ollama.notifications.interval'),
            'batch_size' => config('ollama.notifications.batch_size'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ollama Configuration')
                    ->description('Configure the Ollama LLM service for AI-powered features')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('ollama_enabled')
                                ->label('Enable Ollama')
                                ->helperText('Turn on/off Ollama integration'),
                            
                            TextInput::make('ollama_host')
                                ->label('Ollama Host')
                                ->placeholder('http://localhost:11434')
                                ->helperText('URL where Ollama is running'),
                        ]),
                        
                        Grid::make(2)->schema([
                            Select::make('ollama_model')
                                ->label('Model')
                                ->options($this->getAvailableModels())
                                ->helperText('Select the AI model to use'),
                            
                            TextInput::make('batch_size')
                                ->label('Batch Size')
                                ->numeric()
                                ->default(10)
                                ->helperText('Number of notifications to generate per batch'),
                        ]),
                    ]),

                Section::make('AI Notifications')
                    ->description('Configure automatic AI-generated notifications')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('notifications_enabled')
                                ->label('Enable AI Notifications')
                                ->helperText('Automatically generate personalized notifications'),
                            
                            Select::make('notification_interval')
                                ->label('Generation Interval')
                                ->options([
                                    'hourly' => 'Every Hour',
                                    'daily' => 'Daily',
                                    'weekly' => 'Weekly',
                                ])
                                ->helperText('How often to generate new notifications'),
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
                
            Action::make('generate_test')
                ->label('Generate Test Notification')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->action('generateTestNotification'),
                
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
            $ollamaService = $this->ollamaService ?? app(OllamaService::class);
            
            $results = $ollamaService->test();
            
            if ($results['connection'] && $results['model_available'] && $results['test_generation']) {
                Notification::make()
                    ->title('Connection Successful!')
                    ->body('Ollama is working correctly with model: ' . config('ollama.model'))
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
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function generateTestNotification(): void
    {
        try {
            $participant = \App\Models\Participant::with('goal')->first();
            
            if (!$participant) {
                Notification::make()
                    ->title('No Participants Found')
                    ->body('Please create a participant first to test notifications')
                    ->warning()
                    ->send();
                return;
            }

            $aiNotificationService = $this->aiNotificationService ?? app(AINotificationService::class);

            $notification = $aiNotificationService->generateParticipantNotification($participant);
            
            if ($notification) {
                Notification::make()
                    ->title('Test Notification Generated!')
                    ->body("Generated for {$participant->name}: {$notification->notification_text}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Generation Failed')
                    ->body('Could not generate test notification')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Test Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function viewStatistics(): void
    {
        try {
            $aiNotificationService = $this->aiNotificationService ?? app(AINotificationService::class);
            
            $stats = $aiNotificationService->getStatistics();
            
            $message = "Today's AI Notifications: {$stats['total_ai_notifications']}\n";
            $message .= "Participants Notified: {$stats['participants_notified_today']}\n";
            $message .= "Ollama Status: " . ($stats['ollama_status']['available'] ? 'Available' : 'Unavailable');
            
            Notification::make()
                ->title('AI Notification Statistics')
                ->body($message)
                ->info()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Statistics Error')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getAvailableModels(): array
    {
        try {
            $ollamaService = $this->ollamaService ?? app(OllamaService::class);
            
            $models = $ollamaService->getAvailableModels();
            
            if (empty($models)) {
                return [config('ollama.model') => config('ollama.model') . ' (default)'];
            }
            
            return array_combine($models, $models);
        } catch (\Exception $e) {
            return [config('ollama.model') => config('ollama.model') . ' (default)'];
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'AI Settings';
    }
}
