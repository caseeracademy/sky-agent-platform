<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Models\SystemSettings as SystemSettingsModel;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class SystemSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.system-settings';

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SystemSetup;

    protected static ?int $navigationSort = 1;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Company Information')
                        ->description('Configure your company details that appear throughout the system')
                        ->schema([
                            TextInput::make('company_name')
                                ->label('Company Name')
                                ->required()
                                ->maxLength(255)
                                ->default('Sky Blue Consulting'),

                            TextInput::make('company_email')
                                ->label('Company Email')
                                ->email()
                                ->maxLength(255),

                            TextInput::make('company_phone')
                                ->label('Company Phone')
                                ->tel()
                                ->maxLength(255),

                            Textarea::make('company_address')
                                ->label('Company Address')
                                ->rows(3)
                                ->maxLength(500),

                            FileUpload::make('company_logo_path')
                                ->label('Company Logo')
                                ->image()
                                ->maxSize(2048)
                                ->directory('company')
                                ->visibility('public')
                                ->imagePreviewHeight('100')
                                ->helperText('Upload your company logo (max 2MB, recommended: 200x200px)'),
                        ])
                        ->columns(2),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save Settings')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();

        if (! $record) {
            $record = new SystemSettingsModel;
        }

        $record->fill($data);
        $record->save();

        if ($record->wasRecentlyCreated) {
            $this->form->record($record)->saveRelationships();
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public function getRecord(): ?SystemSettingsModel
    {
        return SystemSettingsModel::getSettings();
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    public function getTitle(): string
    {
        return 'System Settings';
    }

    public function getHeading(): string
    {
        return 'System Settings';
    }
}
