<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\StudentResource;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;
    
    protected static ?string $title = 'Student Details';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // Single fullwidth tabs card
                Tabs::make('Student Details')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('Student Overview')
                            ->schema([
                                Section::make('Basic Information')
                                    ->schema([
                                        Placeholder::make('name')
                                            ->label('Full Name')
                                            ->content(fn ($record) => $record->name),
                                        Placeholder::make('email')
                                            ->label('Email Address')
                                            ->content(fn ($record) => $record->email),
                                        Placeholder::make('phone')
                                            ->label('Phone Number')
                                            ->content(fn ($record) => $record->phone ?: 'Not provided'),
                                    ])
                                    ->columns(3),
                                    
                                Section::make('Personal Details')
                                    ->schema([
                                        Placeholder::make('nationality')
                                            ->label('Nationality')
                                            ->content(fn ($record) => $record->nationality ?: 'Not provided'),
                                        Placeholder::make('date_of_birth')
                                            ->label('Date of Birth')
                                            ->content(fn ($record) => $record->date_of_birth ? $record->date_of_birth->format('M j, Y') : 'Not provided'),
                                        Placeholder::make('gender')
                                            ->label('Gender')
                                            ->content(fn ($record) => $record->gender ? ucfirst($record->gender) : 'Not provided'),
                                        Placeholder::make('country_of_residence')
                                            ->label('Country of Residence')
                                            ->content(fn ($record) => $record->country_of_residence ?: 'Not provided'),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('System Information')
                                    ->schema([
                                        Placeholder::make('created_at')
                                            ->label('Added to System')
                                            ->content(fn ($record) => $record->created_at->format('M j, Y g:i A')),
                                        Placeholder::make('updated_at')
                                            ->label('Last Updated')
                                            ->content(fn ($record) => $record->updated_at->format('M j, Y g:i A')),
                                        Placeholder::make('student_status')
                                            ->label('Student Status')
                                            ->content(function ($record) {
                                                $color = 'success';
                                                $label = 'Active';
                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tab::make('Applications')
                            ->schema([
                                Section::make('Application History')
                                    ->schema([
                                        Placeholder::make('applications_list')
                                            ->label('Applications')
                                            ->content(function ($record) {
                                                $applications = $record->applications()->with(['program.university'])->orderBy('created_at', 'desc')->get();
                                                
                                                if ($applications->isEmpty()) {
                                                    return '<div class="text-gray-500 italic text-center py-8">No applications submitted yet.</div>';
                                                }
                                                
                                                $html = '<div class="space-y-4">';
                                                foreach ($applications as $application) {
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'submitted' => 'info', 
                                                        'under_review' => 'warning',
                                                        'additional_documents_required' => 'danger',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'enrolled' => 'success',
                                                        'cancelled' => 'gray'
                                                    ];
                                                    $color = $statusColors[$application->status] ?? 'gray';
                                                    $statusLabel = ucfirst(str_replace('_', ' ', $application->status));
                                                    $submittedDate = $application->created_at->format('M j, Y');
                                                    $commission = $application->commission_amount ? '$' . number_format($application->commission_amount, 2) : 'N/A';
                                                    
                                                    $html .= '<div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">';
                                                    $html .= '<div class="flex items-center justify-between mb-3">';
                                                    $html .= '<div class="flex-1">';
                                                    $html .= '<h4 class="font-medium text-gray-900">' . e($application->program->name) . '</h4>';
                                                    $html .= '<p class="text-sm text-gray-600">' . e($application->program->university->name) . '</p>';
                                                    $html .= '</div>';
                                                    $html .= '<span class="fi-badge fi-color-' . $color . ' fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">' . $statusLabel . '</span>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="grid grid-cols-3 gap-4 text-sm">';
                                                    $html .= '<div><span class="text-gray-500">Application #:</span><br><span class="font-medium">' . e($application->application_number) . '</span></div>';
                                                    $html .= '<div><span class="text-gray-500">Submitted:</span><br><span class="font-medium">' . $submittedDate . '</span></div>';
                                                    $html .= '<div><span class="text-gray-500">Commission:</span><br><span class="font-medium text-green-600">' . $commission . '</span></div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';
                                                
                                                return $html;
                                            })
                                            ->html(),
                                    ]),
                                    
                                Section::make('Application Summary')
                                    ->schema([
                                        Placeholder::make('total_applications')
                                            ->label('Total Applications')
                                            ->content(fn ($record) => $record->applications()->count() . ' application' . ($record->applications()->count() !== 1 ? 's' : '')),
                                        Placeholder::make('approved_applications')
                                            ->label('Approved Applications')
                                            ->content(function ($record) {
                                                $count = $record->applications()->where('status', 'approved')->count();
                                                return $count . ' approved';
                                            }),
                                        Placeholder::make('pending_applications')
                                            ->label('Pending Applications')
                                            ->content(function ($record) {
                                                $count = $record->applications()->whereIn('status', ['pending', 'submitted', 'under_review'])->count();
                                                return $count . ' pending';
                                            }),
                                        Placeholder::make('total_commission_earned')
                                            ->label('Total Commission Earned')
                                            ->content(function ($record) {
                                                $total = $record->applications()->where('status', 'approved')->sum('commission_amount');
                                                return '$' . number_format($total, 2);
                                            }),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Documents')
                            ->schema([
                                Section::make('Uploaded Documents')
                                    ->schema([
                                        Placeholder::make('documents_list')
                                            ->label('Documents')
                                            ->content(function ($record) {
                                                $documents = $record->documents()->with('uploadedBy')->orderBy('created_at', 'desc')->get();
                                                
                                                if ($documents->isEmpty()) {
                                                    return '<div class="text-gray-500 italic text-center py-8">No documents uploaded yet.</div>';
                                                }
                                                
                                                $html = '<div class="space-y-3">';
                                                foreach ($documents as $document) {
                                                    $uploadedBy = $document->uploadedBy->name ?? 'Unknown';
                                                    $uploadDate = $document->created_at->format('M j, Y g:i A');
                                                    $fileSize = $document->formatted_file_size;
                                                    $downloadUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($document->file_path);
                                                    
                                                    $documentTypes = [
                                                        'passport' => 'Passport',
                                                        'certificate' => 'Certificate',
                                                        'transcript' => 'Transcript',
                                                        'photo' => 'Photo',
                                                        'other' => 'Other'
                                                    ];
                                                    $typeName = $documentTypes[$document->type] ?? 'Document';
                                                    
                                                    $html .= '<div class="border border-gray-200 rounded-lg p-4 bg-gray-50">';
                                                    $html .= '<div class="flex items-center justify-between">';
                                                    $html .= '<div class="flex-1">';
                                                    $html .= '<h4 class="font-medium text-gray-900">' . e($document->name) . '</h4>';
                                                    $html .= '<p class="text-sm text-gray-500">Type: ' . e($typeName) . ' | Uploaded by ' . e($uploadedBy) . ' on ' . $uploadDate . '</p>';
                                                    $html .= '<p class="text-xs text-gray-400">File size: ' . $fileSize . '</p>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="ml-4">';
                                                    $html .= '<a href="' . $downloadUrl . '" target="_blank" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">';
                                                    $html .= '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                    $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                                                    $html .= '</svg>';
                                                    $html .= 'Download';
                                                    $html .= '</a>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';
                                                
                                                return $html;
                                            })
                                            ->html(),
                                    ]),
                                    
                                Section::make('Document Summary')
                                    ->schema([
                                        Placeholder::make('total_documents')
                                            ->label('Total Documents')
                                            ->content(fn ($record) => $record->documents()->count() . ' document' . ($record->documents()->count() !== 1 ? 's' : '')),
                                        Placeholder::make('document_types')
                                            ->label('Document Types')
                                            ->content(function ($record) {
                                                $types = $record->documents()->distinct('type')->pluck('type');
                                                return $types->isEmpty() ? 'No documents' : $types->count() . ' different types';
                                            }),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Tab::make('Agent Information')
                            ->schema([
                                Section::make('Your Agent Details')
                                    ->schema([
                                        Placeholder::make('agent_name')
                                            ->label('Agent Name')
                                            ->content(fn ($record) => $record->agent->name ?? 'Not assigned'),
                                        Placeholder::make('agent_email')
                                            ->label('Agent Email')
                                            ->content(fn ($record) => $record->agent->email ?? 'Not assigned'),
                                        Placeholder::make('agent_role')
                                            ->label('Agent Role')
                                            ->content(fn ($record) => $record->agent ? ucfirst(str_replace('_', ' ', $record->agent->role)) : 'Not assigned'),
                                        Placeholder::make('agent_status')
                                            ->label('Account Status')
                                            ->content(function ($record) {
                                                if (!$record->agent) {
                                                    return '<span class="fi-badge fi-color-gray fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">Not Assigned</span>';
                                                }
                                                $color = $record->agent->is_active ? 'success' : 'danger';
                                                $label = $record->agent->is_active ? 'Active' : 'Inactive';
                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('Agent Performance with This Student')
                                    ->schema([
                                        Placeholder::make('student_applications')
                                            ->label('Applications for This Student')
                                            ->content(fn ($record) => $record->applications()->count() . ' application' . ($record->applications()->count() !== 1 ? 's' : '')),
                                        Placeholder::make('student_success_rate')
                                            ->label('Success Rate for This Student')
                                            ->content(function ($record) {
                                                $total = $record->applications()->count();
                                                $approved = $record->applications()->where('status', 'approved')->count();
                                                if ($total === 0) return '0% (No applications)';
                                                $rate = round(($approved / $total) * 100);
                                                return $rate . '% (' . $approved . '/' . $total . ' approved)';
                                            }),
                                        Placeholder::make('total_commission_this_student')
                                            ->label('Total Commission from This Student')
                                            ->content(function ($record) {
                                                $total = $record->applications()->where('status', 'approved')->sum('commission_amount');
                                                return '$' . number_format($total, 2);
                                            }),
                                        Placeholder::make('relationship_duration')
                                            ->label('Student Relationship Duration')
                                            ->content(function ($record) {
                                                $duration = $record->created_at->diffForHumans(null, true);
                                                return $duration . ' (since ' . $record->created_at->format('M j, Y') . ')';
                                            }),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
