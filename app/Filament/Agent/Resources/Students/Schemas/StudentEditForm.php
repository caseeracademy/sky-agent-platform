<?php

namespace App\Filament\Agent\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentEditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('agent_id')
                    ->default(auth()->id())
                    ->required(),

                // Section 1: Basic Information
                Section::make('Basic Information')
                    ->description('Student personal details and contact information')
                    ->schema([
                        // Row 1: Name fields (First Name, Surname)
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., John')
                            ->columnSpan(1),
                        TextInput::make('last_name')
                            ->label('Surname')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Smith')
                            ->columnSpan(1),
                        Hidden::make('middle_name')
                            ->default(null)
                            ->columnSpan(1),

                        // Row 2: Profile Image, Country, Gender
                        FileUpload::make('profile_image')
                            ->label('Profile Picture')
                            ->image()
                            ->disk('public')
                            ->directory('student-profiles')
                            ->visibility('public')
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(2048) // 2MB
                            ->helperText('Upload student photo (Max 2MB)')
                            ->columnSpan(1),
                        Select::make('country_of_residence')
                            ->label('Country of Residence')
                            ->required()
                            ->searchable()
                            ->options(config('countries.countries'))
                            ->placeholder('Select country')
                            ->columnSpan(1),
                        Select::make('gender')
                            ->label('Gender')
                            ->required()
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                                'prefer_not_to_say' => 'Prefer not to say',
                            ])
                            ->placeholder('Select gender')
                            ->columnSpan(1),

                        // Row 3: Contact fields
                        TextInput::make('mothers_name')
                            ->label("Mother's Name")
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Jane Smith'),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., john.smith@email.com'),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('e.g., +1 (555) 123-4567'),

                        // Row 4: Identity fields
                        TextInput::make('passport_number')
                            ->label('Passport Number')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., A1234567')
                            ->unique(ignoreRecord: true),
                        Select::make('nationality')
                            ->label('Nationality')
                            ->required()
                            ->searchable()
                            ->options(array_combine(config('countries.nationalities'), config('countries.nationalities')))
                            ->placeholder('Select nationality'),
                        DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now()->subYears(16))
                            ->placeholder('Select date of birth')
                            ->displayFormat('Y-m-d')
                            ->helperText('Student must be at least 16 years old'),
                    ])
                    ->columns(3),

                // Section 2: Current Application
                Section::make('Current Application')
                    ->description('View or manage student application')
                    ->schema([
                        Placeholder::make('current_application')
                            ->label('')
                            ->content(function ($record) {
                                if (! $record) {
                                    return '';
                                }

                                $application = $record->applications()->first();

                                if (! $application) {
                                    return new \Illuminate\Support\HtmlString('
                                        <div style="padding: 20px; background: #f3f4f6; border: 2px dashed #d1d5db; border-radius: 8px; text-align: center;">
                                            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                                                📝 No application created yet. 
                                                <br><br>
                                                Go to the student details page to create an application.
                                            </p>
                                        </div>
                                    ');
                                }

                                $universityName = $application->program->university->name ?? 'N/A';
                                $programName = $application->program->name ?? 'N/A';
                                $degreeName = $application->program->degree->name ?? 'N/A';
                                $status = ucfirst(str_replace('_', ' ', $application->status));
                                $appNumber = $application->application_number;

                                $statusColors = [
                                    'needs_review' => '#f59e0b',
                                    'submitted' => '#3b82f6',
                                    'additional_documents_needed' => '#ef4444',
                                    'applied' => '#3b82f6',
                                    'offer_received' => '#10b981',
                                    'payment_approval' => '#3b82f6',
                                    'ready_for_approval' => '#3b82f6',
                                    'approved' => '#10b981',
                                    'rejected' => '#ef4444',
                                ];
                                $statusColor = $statusColors[$application->status] ?? '#6b7280';

                                return new \Illuminate\Support\HtmlString("
                                    <div style='background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #3b82f6; border-radius: 12px; padding: 20px;'>
                                        <div style='display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;'>
                                            <div>
                                                <h3 style='font-size: 18px; font-weight: 700; color: #1e40af; margin: 0 0 4px 0;'>🎓 {$universityName}</h3>
                                                <p style='font-size: 14px; color: #3b82f6; margin: 0;'>{$programName} ({$degreeName})</p>
                                            </div>
                                            <div style='padding: 6px 12px; background: {$statusColor}; color: white; border-radius: 6px; font-size: 12px; font-weight: 600;'>
                                                {$status}
                                            </div>
                                        </div>
                                        <div style='background: white; border-radius: 8px; padding: 12px; margin-top: 12px;'>
                                            <p style='margin: 0; font-size: 13px; color: #6b7280;'>
                                                <strong>Application #:</strong> {$appNumber}
                                            </p>
                                        </div>
                                    </div>
                                ");
                            }),
                    ])
                    ->collapsible(),
            ]);
    }
}
