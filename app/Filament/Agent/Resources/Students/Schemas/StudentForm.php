<?php

namespace App\Filament\Agent\Resources\Students\Schemas;

use App\Models\Program;
use App\Models\University;
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
                Hidden::make('agent_id')
                    ->default(auth()->id())
                    ->required(),

                // Section 1: Basic Information
                Section::make('Basic Information')
                    ->description('Student personal details and contact information')
                    ->schema([
                        // Row 1: Name fields (First Name, Surname) - fills 2 of 3 columns
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
                            ->options([
                                'Afghanistan' => 'Afghanistan',
                                'Albania' => 'Albania',
                                'Algeria' => 'Algeria',
                                'Argentina' => 'Argentina',
                                'Australia' => 'Australia',
                                'Austria' => 'Austria',
                                'Bangladesh' => 'Bangladesh',
                                'Belgium' => 'Belgium',
                                'Brazil' => 'Brazil',
                                'Canada' => 'Canada',
                                'Chile' => 'Chile',
                                'China' => 'China',
                                'Colombia' => 'Colombia',
                                'Croatia' => 'Croatia',
                                'Czech Republic' => 'Czech Republic',
                                'Denmark' => 'Denmark',
                                'Egypt' => 'Egypt',
                                'Finland' => 'Finland',
                                'France' => 'France',
                                'Germany' => 'Germany',
                                'Greece' => 'Greece',
                                'India' => 'India',
                                'Indonesia' => 'Indonesia',
                                'Ireland' => 'Ireland',
                                'Israel' => 'Israel',
                                'Italy' => 'Italy',
                                'Japan' => 'Japan',
                                'Malaysia' => 'Malaysia',
                                'Mexico' => 'Mexico',
                                'Netherlands' => 'Netherlands',
                                'New Zealand' => 'New Zealand',
                                'Nigeria' => 'Nigeria',
                                'Norway' => 'Norway',
                                'Pakistan' => 'Pakistan',
                                'Peru' => 'Peru',
                                'Philippines' => 'Philippines',
                                'Poland' => 'Poland',
                                'Portugal' => 'Portugal',
                                'Romania' => 'Romania',
                                'Russia' => 'Russia',
                                'Saudi Arabia' => 'Saudi Arabia',
                                'Singapore' => 'Singapore',
                                'South Africa' => 'South Africa',
                                'South Korea' => 'South Korea',
                                'Spain' => 'Spain',
                                'Sweden' => 'Sweden',
                                'Switzerland' => 'Switzerland',
                                'Thailand' => 'Thailand',
                                'Turkey' => 'Turkey',
                                'Ukraine' => 'Ukraine',
                                'United Arab Emirates' => 'United Arab Emirates',
                                'United Kingdom' => 'United Kingdom',
                                'United States' => 'United States',
                                'Vietnam' => 'Vietnam',
                                'Other' => 'Other',
                            ])
                            ->placeholder('Select country')
                            ->searchable()
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
                            ->options([
                                'Afghan' => 'Afghan',
                                'Albanian' => 'Albanian',
                                'Algerian' => 'Algerian',
                                'American' => 'American',
                                'Argentine' => 'Argentine',
                                'Australian' => 'Australian',
                                'Austrian' => 'Austrian',
                                'Bangladeshi' => 'Bangladeshi',
                                'Belgian' => 'Belgian',
                                'Brazilian' => 'Brazilian',
                                'British' => 'British',
                                'Bulgarian' => 'Bulgarian',
                                'Canadian' => 'Canadian',
                                'Chilean' => 'Chilean',
                                'Chinese' => 'Chinese',
                                'Colombian' => 'Colombian',
                                'Croatian' => 'Croatian',
                                'Czech' => 'Czech',
                                'Danish' => 'Danish',
                                'Dutch' => 'Dutch',
                                'Egyptian' => 'Egyptian',
                                'Finnish' => 'Finnish',
                                'French' => 'French',
                                'German' => 'German',
                                'Greek' => 'Greek',
                                'Hungarian' => 'Hungarian',
                                'Indian' => 'Indian',
                                'Indonesian' => 'Indonesian',
                                'Irish' => 'Irish',
                                'Israeli' => 'Israeli',
                                'Italian' => 'Italian',
                                'Japanese' => 'Japanese',
                                'Korean' => 'Korean',
                                'Malaysian' => 'Malaysian',
                                'Mexican' => 'Mexican',
                                'Nigerian' => 'Nigerian',
                                'Norwegian' => 'Norwegian',
                                'Pakistani' => 'Pakistani',
                                'Peruvian' => 'Peruvian',
                                'Philippine' => 'Philippine',
                                'Polish' => 'Polish',
                                'Portuguese' => 'Portuguese',
                                'Romanian' => 'Romanian',
                                'Russian' => 'Russian',
                                'Saudi' => 'Saudi',
                                'Singaporean' => 'Singaporean',
                                'South African' => 'South African',
                                'Spanish' => 'Spanish',
                                'Swedish' => 'Swedish',
                                'Swiss' => 'Swiss',
                                'Thai' => 'Thai',
                                'Turkish' => 'Turkish',
                                'Ukrainian' => 'Ukrainian',
                                'Vietnamese' => 'Vietnamese',
                                'Other' => 'Other',
                            ])
                            ->placeholder('Select nationality')
                            ->searchable(),
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
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('student-documents/passports')
                            ->helperText('Upload passport copy (PDF, JPG, PNG - Max 10MB)'),
                        FileUpload::make('diploma_file')
                            ->label('Diploma')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('student-documents/diplomas')
                            ->helperText('Upload diploma certificate (PDF, JPG, PNG - Max 10MB)'),
                        FileUpload::make('transcript_file')
                            ->label('Transcript')
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('student-documents/transcripts')
                            ->helperText('Upload academic transcript (PDF, JPG, PNG - Max 10MB)'),
                    ])
                    ->columns(1),

                // Section 3: Add to Application
                Section::make('Add to Application')
                    ->description('Create application for this student (optional)')
                    ->schema([
                        Select::make('university_id')
                            ->label('University')
                            ->options(University::all()->pluck('name', 'id'))
                            ->placeholder('Select university (optional)')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('program_id', null)),
                        Select::make('program_id')
                            ->label('Program')
                            ->options(function (callable $get) {
                                $universityId = $get('university_id');
                                if (! $universityId) {
                                    return [];
                                }

                                return Program::where('university_id', $universityId)
                                    ->pluck('name', 'id');
                            })
                            ->placeholder('Select program (optional)')
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => ! $get('university_id')),
                    ])
                    ->columns(1),
            ]);
    }
}
