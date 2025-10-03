<?php

namespace App\Filament\Agent\Resources\Scholarships\Pages;

use App\Filament\Agent\Resources\Scholarships\ScholarshipResource;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Program;
use App\Models\ScholarshipCommission;
use App\Models\Student;
use App\Models\StudentDocument;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class ConvertScholarship extends Page implements HasForms
{
    use InteractsWithForms;
    use WithFileUploads;

    protected static string $resource = ScholarshipResource::class;

    protected static ?string $title = 'Convert Scholarship to Application';

    public $data = [];

    public $scholarship = null;

    // Livewire form properties
    public $first_name = '';

    public $last_name = '';

    public $middle_name = '';

    public $email = '';

    public $phone_number = '';

    public $mothers_name = '';

    public $passport_number = '';

    public $nationality = '';

    public $country_of_residence = '';

    public $gender = '';

    public $date_of_birth = '';

    public $profile_image = [];

    public $passport_file = [];

    public $diploma_file = [];

    public $transcript_file = [];

    public $program_id = '';

    public $scholarship_id = '';

    public $university_id = '';

    public $degree_id = '';

    public function mount($record): void
    {
        // Extract actual scholarship commission ID from display ID
        // Display IDs are like "completed_1" or "progress_1_3"
        $scholarshipId = null;

        if (str_starts_with($record, 'completed_')) {
            $scholarshipId = (int) str_replace('completed_', '', $record);
        } else {
            // If it's a progress ID or direct ID, show error
            Notification::make()
                ->title('Scholarship Not Ready')
                ->body('This scholarship has not been earned yet and cannot be converted.')
                ->warning()
                ->send();

            $this->redirect(ScholarshipResource::getUrl('index'));

            return;
        }

        // Get the scholarship commission
        $this->scholarship = ScholarshipCommission::where('id', $scholarshipId)
            ->where('agent_id', auth()->id())
            ->where('status', 'earned')
            ->with(['university', 'degree'])
            ->first();

        if (! $this->scholarship) {
            Notification::make()
                ->title('Scholarship Not Available')
                ->body('This scholarship is not available for conversion or does not belong to you.')
                ->danger()
                ->send();

            $this->redirect(ScholarshipResource::getUrl('index'));

            return;
        }

        // Initialize properties with scholarship data
        $this->scholarship_id = $this->scholarship->id;
        $this->university_id = $this->scholarship->university_id;
        $this->degree_id = $this->scholarship->degree_id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('scholarship_id'),
                Hidden::make('university_id'),
                Hidden::make('degree_id'),

                // Section 1: Basic Information
                Section::make('Student Information')
                    ->description('Enter student personal details')
                    ->schema([
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
                            ->options($this->getCountryOptions())
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
                            ->unique(Student::class, 'email')
                            ->placeholder('e.g., john.smith@email.com'),
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('e.g., +1 (555) 123-4567'),

                        TextInput::make('passport_number')
                            ->label('Passport Number')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., A1234567')
                            ->unique(Student::class, 'passport_number'),
                        Select::make('nationality')
                            ->label('Nationality')
                            ->required()
                            ->options($this->getNationalityOptions())
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

                // Section 3: Program Selection (filtered by scholarship)
                Section::make('Program Selection')
                    ->description('Select program at '.($this->scholarship->university->name ?? 'university'))
                    ->schema([
                        Placeholder::make('locked_university')
                            ->label('University (Locked by Scholarship)')
                            ->content($this->scholarship->university->name ?? 'N/A'),
                        Placeholder::make('locked_degree')
                            ->label('Degree Level (Locked by Scholarship)')
                            ->content($this->scholarship->degree->name ?? 'N/A'),
                        Select::make('program_id')
                            ->label('Program')
                            ->required()
                            ->options(Program::where('university_id', $this->scholarship->university_id)
                                ->where('degree_id', $this->scholarship->degree_id)
                                ->pluck('name', 'id'))
                            ->placeholder('Select program')
                            ->searchable()
                            ->preload()
                            ->helperText('Only programs matching your scholarship are shown'),
                    ])
                    ->columns(3),
            ]);
    }

    public function submit(): void
    {
        // Validate the form
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'mothers_name' => 'required|string|max:255',
            'passport_number' => 'required|string|max:255|unique:students,passport_number',
            'nationality' => 'required|string',
            'country_of_residence' => 'required|string',
            'gender' => 'required|string',
            'passport_file' => 'required',
            'diploma_file' => 'required',
            'transcript_file' => 'required',
            'program_id' => 'required|exists:programs,id',
        ]);

        try {
            // Store profile image if uploaded
            $profileImagePath = null;
            if (is_array($this->profile_image) && isset($this->profile_image[0])) {
                $profileImagePath = $this->profile_image[0];
            } elseif (is_string($this->profile_image) && ! empty($this->profile_image)) {
                $profileImagePath = $this->profile_image;
            }

            // Create the student
            $student = Student::create([
                'agent_id' => auth()->id(),
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'middle_name' => $this->middle_name ?: null,
                'name' => $this->first_name.' '.$this->last_name,
                'email' => $this->email,
                'phone_number' => $this->phone_number ?: null,
                'date_of_birth' => $this->date_of_birth ?: null,
                'gender' => $this->gender,
                'nationality' => $this->nationality,
                'country_of_residence' => $this->country_of_residence,
                'passport_number' => $this->passport_number,
                'mothers_name' => $this->mothers_name,
                'profile_image' => $profileImagePath,
            ]);

            // Create student documents
            $this->createStudentDocuments($student);

            // Create FREE application (still needs admin review)
            $application = Application::create([
                'application_number' => Application::generateApplicationNumber(),
                'student_id' => $student->id,
                'program_id' => $this->program_id,
                'agent_id' => auth()->id(),
                'status' => 'needs_review',
                'commission_type' => 'scholarship',
                'commission_amount' => 0,
                'needs_review' => true,
                'submitted_at' => now(),
            ]);

            // Create application documents
            $this->createApplicationDocuments($application);

            // Update scholarship status to 'used'
            $this->scholarship->update([
                'status' => 'used',
                'used_at' => now(),
                'application_id' => $application->id,
            ]);

            Notification::make()
                ->title('Scholarship Converted Successfully!')
                ->body("Application {$application->application_number} created for {$student->name} - FREE (using scholarship)")
                ->success()
                ->send();

            // Redirect to the application view
            $this->redirect(\App\Filament\Agent\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $application->id]));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Converting Scholarship')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function createStudentDocuments($student): void
    {
        $documentTypes = [
            'passport_file' => ['file' => $this->passport_file, 'type' => 'passport'],
            'diploma_file' => ['file' => $this->diploma_file, 'type' => 'diploma'],
            'transcript_file' => ['file' => $this->transcript_file, 'type' => 'transcript'],
        ];

        foreach ($documentTypes as $fileField => $documentData) {
            $file = $documentData['file'];
            $documentType = $documentData['type'];

            // Handle both array and string file paths
            $filePath = null;
            if (is_array($file) && isset($file[0])) {
                $filePath = $file[0];
            } elseif (is_string($file) && ! empty($file)) {
                $filePath = $file;
            }

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                $fileInfo = pathinfo($filePath);

                StudentDocument::create([
                    'student_id' => $student->id,
                    'uploaded_by' => auth()->id(),
                    'name' => ucfirst($documentType).' Document',
                    'type' => $documentType,
                    'file_path' => $filePath,
                    'file_name' => $fileInfo['basename'],
                    'mime_type' => Storage::disk('public')->mimeType($filePath),
                    'file_size' => Storage::disk('public')->size($filePath),
                ]);
            }
        }
    }

    protected function createApplicationDocuments($application): void
    {
        $documentTypes = [
            'passport_file' => ['file' => $this->passport_file, 'title' => 'Passport Document'],
            'diploma_file' => ['file' => $this->diploma_file, 'title' => 'Diploma Document'],
            'transcript_file' => ['file' => $this->transcript_file, 'title' => 'Transcript Document'],
        ];

        foreach ($documentTypes as $fileField => $documentData) {
            $file = $documentData['file'];
            $title = $documentData['title'];

            // Handle both array and string file paths
            $filePath = null;
            if (is_array($file) && isset($file[0])) {
                $filePath = $file[0];
            } elseif (is_string($file) && ! empty($file)) {
                $filePath = $file;
            }

            if ($filePath && Storage::disk('public')->exists($filePath)) {
                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'uploaded_by_user_id' => auth()->id(),
                    'title' => $title,
                    'original_filename' => basename($filePath),
                    'disk' => 'public',
                    'path' => $filePath,
                    'file_size' => Storage::disk('public')->size($filePath),
                    'mime_type' => Storage::disk('public')->mimeType($filePath),
                ]);
            }
        }
    }

    protected function getCountryOptions(): array
    {
        return [
            'Afghanistan' => 'Afghanistan', 'Albania' => 'Albania', 'Algeria' => 'Algeria',
            'Argentina' => 'Argentina', 'Australia' => 'Australia', 'Austria' => 'Austria',
            'Bangladesh' => 'Bangladesh', 'Belgium' => 'Belgium', 'Brazil' => 'Brazil',
            'Canada' => 'Canada', 'Chile' => 'Chile', 'China' => 'China',
            'Colombia' => 'Colombia', 'Croatia' => 'Croatia', 'Denmark' => 'Denmark',
            'Egypt' => 'Egypt', 'Finland' => 'Finland', 'France' => 'France',
            'Germany' => 'Germany', 'Greece' => 'Greece', 'India' => 'India',
            'Indonesia' => 'Indonesia', 'Ireland' => 'Ireland', 'Italy' => 'Italy',
            'Japan' => 'Japan', 'Malaysia' => 'Malaysia', 'Mexico' => 'Mexico',
            'Netherlands' => 'Netherlands', 'New Zealand' => 'New Zealand', 'Nigeria' => 'Nigeria',
            'Norway' => 'Norway', 'Pakistan' => 'Pakistan', 'Philippines' => 'Philippines',
            'Poland' => 'Poland', 'Portugal' => 'Portugal', 'Russia' => 'Russia',
            'Saudi Arabia' => 'Saudi Arabia', 'Singapore' => 'Singapore', 'South Africa' => 'South Africa',
            'South Korea' => 'South Korea', 'Spain' => 'Spain', 'Sweden' => 'Sweden',
            'Switzerland' => 'Switzerland', 'Thailand' => 'Thailand', 'Turkey' => 'Turkey',
            'Ukraine' => 'Ukraine', 'United Arab Emirates' => 'United Arab Emirates',
            'United Kingdom' => 'United Kingdom', 'United States' => 'United States',
            'Vietnam' => 'Vietnam', 'Other' => 'Other',
        ];
    }

    protected function getNationalityOptions(): array
    {
        return [
            'Afghan' => 'Afghan', 'Albanian' => 'Albanian', 'Algerian' => 'Algerian',
            'American' => 'American', 'Argentine' => 'Argentine', 'Australian' => 'Australian',
            'Austrian' => 'Austrian', 'Bangladeshi' => 'Bangladeshi', 'Belgian' => 'Belgian',
            'Brazilian' => 'Brazilian', 'British' => 'British', 'Canadian' => 'Canadian',
            'Chilean' => 'Chilean', 'Chinese' => 'Chinese', 'Colombian' => 'Colombian',
            'Croatian' => 'Croatian', 'Czech' => 'Czech', 'Danish' => 'Danish',
            'Dutch' => 'Dutch', 'Egyptian' => 'Egyptian', 'Finnish' => 'Finnish',
            'French' => 'French', 'German' => 'German', 'Greek' => 'Greek',
            'Indian' => 'Indian', 'Indonesian' => 'Indonesian', 'Irish' => 'Irish',
            'Israeli' => 'Israeli', 'Italian' => 'Italian', 'Japanese' => 'Japanese',
            'Korean' => 'Korean', 'Malaysian' => 'Malaysian', 'Mexican' => 'Mexican',
            'Nigerian' => 'Nigerian', 'Norwegian' => 'Norwegian', 'Pakistani' => 'Pakistani',
            'Philippine' => 'Philippine', 'Polish' => 'Polish', 'Portuguese' => 'Portuguese',
            'Russian' => 'Russian', 'Saudi' => 'Saudi', 'Singaporean' => 'Singaporean',
            'South African' => 'South African', 'Spanish' => 'Spanish', 'Swedish' => 'Swedish',
            'Swiss' => 'Swiss', 'Thai' => 'Thai', 'Turkish' => 'Turkish',
            'Ukrainian' => 'Ukrainian', 'Vietnamese' => 'Vietnamese', 'Other' => 'Other',
        ];
    }

    public function getHeading(): string
    {
        return 'Convert Scholarship to FREE Application';
    }

    public function getSubheading(): ?string
    {
        if ($this->scholarship) {
            return "Using scholarship: {$this->scholarship->commission_number} - {$this->scholarship->university->name} ({$this->scholarship->degree->name})";
        }

        return null;
    }

    protected function getViewData(): array
    {
        return [
            'scholarship' => $this->scholarship,
        ];
    }

    public function getView(): string
    {
        return 'filament.agent.pages.convert-scholarship';
    }
}
