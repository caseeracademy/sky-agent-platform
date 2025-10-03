<?php

namespace App\Filament\Agent\Pages;

use App\Enums\AgentNavigationGroup;
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
class ProfileSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament.agent.pages.profile-settings';

    protected static string|\UnitEnum|null $navigationGroup = AgentNavigationGroup::Dashboard;

    protected static ?int $navigationSort = 99;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'address' => $user->address,
            'bio' => $user->bio,
            'avatar_path' => $user->avatar_path,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Account Information')
                        ->description('View your account details')
                        ->schema([
                            TextInput::make('name')
                                ->label('Full Name')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('email')
                                ->label('Email Address')
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(2),

                    Section::make('Profile Information')
                        ->description('Update your profile details')
                        ->schema([
                            TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->tel()
                                ->maxLength(255)
                                ->helperText('Your contact phone number'),

                            Textarea::make('address')
                                ->label('Address')
                                ->rows(3)
                                ->maxLength(500)
                                ->helperText('Your physical address'),

                            Textarea::make('bio')
                                ->label('Bio')
                                ->rows(4)
                                ->maxLength(1000)
                                ->helperText('Tell us about yourself (max 1000 characters)')
                                ->columnSpanFull(),

                            FileUpload::make('avatar_path')
                                ->label('Profile Picture')
                                ->image()
                                ->maxSize(2048)
                                ->directory('avatars')
                                ->visibility('public')
                                ->imagePreviewHeight('150')
                                ->helperText('Upload your profile picture (max 2MB, recommended: square image)')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save Profile')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = auth()->user();
        $data = $this->form->getState();

        // Remove readonly fields
        unset($data['name'], $data['email']);

        $user->update($data);

        Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Profile Settings';
    }

    public function getHeading(): string
    {
        return 'Profile Settings';
    }
}
