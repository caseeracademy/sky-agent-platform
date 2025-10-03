<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Degree;
use App\Models\Program;
use App\Models\University;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Section 1: Basic Information
                Section::make('Basic Information')
                    ->description('Student personal details and contact information')
                    ->schema([
                        // Row 1: Agent, First Name, Last Name
                        Select::make('agent_id')
                            ->label('Assign to Agent')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(User::whereIn('role', ['agent_owner', 'agent_staff'])
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->helperText('Select agent')
                            ->columnSpan(1),
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
                            ->default(null),

                        // Row 2: Profile Image, Country, Gender
                        FileUpload::make('profile_image')
                            ->label('Profile Picture')
                            ->image()
                            ->disk('public')
                            ->directory('student-profiles')
                            ->visibility('public')
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(2048)
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

                        // Row 3: Mother's name and contact fields
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

                // Section 2: Document Uploads
                Section::make('Document Uploads')
                    ->description('Upload required supporting documents')
                    ->schema([
                        FileUpload::make('passport_file')
                            ->label('Passport File')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('student-documents/passports')
                            ->helperText('Upload passport copy (PDF, JPG, PNG - Max 10MB)'),
                        FileUpload::make('diploma_file')
                            ->label('Diploma')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('student-documents/diplomas')
                            ->helperText('Upload diploma certificate (PDF, JPG, PNG - Max 10MB)'),
                        FileUpload::make('transcript_file')
                            ->label('Transcript')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240)
                            ->disk('public')
                            ->directory('student-documents/transcripts')
                            ->helperText('Upload academic transcript (PDF, JPG, PNG - Max 10MB)'),
                    ])
                    ->columns(1),

                // Section 3: Add to Application
                Section::make('Add to Application')
                    ->description('Create application for this student (optional - you can also add later)')
                    ->schema([
                        Select::make('university_id')
                            ->label('University')
                            ->options(University::all()->pluck('name', 'id'))
                            ->placeholder('Select university (optional)')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('degree_id', null);
                                $set('program_id', null);
                            }),
                        Select::make('degree_id')
                            ->label('Degree Type')
                            ->options(function (callable $get) {
                                $universityId = $get('university_id');
                                if (! $universityId) {
                                    return [];
                                }

                                return Degree::whereHas('programs', function ($query) use ($universityId) {
                                    $query->where('university_id', $universityId);
                                })->pluck('name', 'id');
                            })
                            ->placeholder('Select degree type')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->disabled(fn (callable $get) => ! $get('university_id'))
                            ->afterStateUpdated(fn (callable $set) => $set('program_id', null)),
                        Select::make('program_id')
                            ->label('Program')
                            ->options(function (callable $get) {
                                $universityId = $get('university_id');
                                $degreeId = $get('degree_id');

                                if (! $universityId || ! $degreeId) {
                                    return [];
                                }

                                return Program::where('university_id', $universityId)
                                    ->where('degree_id', $degreeId)
                                    ->pluck('name', 'id');
                            })
                            ->placeholder('Select program')
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => ! $get('university_id') || ! $get('degree_id')),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
