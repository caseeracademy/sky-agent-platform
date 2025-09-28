<?php

namespace App\Notifications;

use App\Models\StudentDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentUploaded extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public StudentDocument $document
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("📄 New Document Uploaded - {$this->document->student->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new document has been uploaded and may require your review.')
            ->line("**Student:** {$this->document->student->name}")
            ->line("**Document Type:** {$this->getDocumentTypeName()}")
            ->line("**File Name:** {$this->document->file_name}")
            ->line("**Uploaded By:** {$this->document->student->agent->name}")
            ->line("**Upload Date:** {$this->document->created_at->format('M j, Y \a\t g:i A')}")
            ->line("**Status:** {$this->getStatusMessage()}")
            ->line('Please review this document when convenient.')
            ->action('Review Document', route('filament.admin.resources.student-documents.view', $this->document))
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'student_id' => $this->document->student_id,
            'student_name' => $this->document->student->name,
            'agent_name' => $this->document->student->agent->name,
            'document_type' => $this->document->document_type,
            'document_type_name' => $this->getDocumentTypeName(),
            'file_name' => $this->document->file_name,
            'status' => $this->document->status,
            'status_message' => $this->getStatusMessage(),
            'uploaded_at' => $this->document->created_at,
            'action_url' => route('filament.admin.resources.student-documents.view', $this->document),
        ];
    }

    /**
     * Get the user-friendly document type name.
     */
    private function getDocumentTypeName(): string
    {
        return match ($this->document->document_type) {
            'passport' => 'Passport',
            'transcript' => 'Academic Transcript',
            'diploma' => 'Diploma/Certificate',
            'english_proficiency' => 'English Proficiency Test',
            'recommendation_letter' => 'Recommendation Letter',
            'personal_statement' => 'Personal Statement',
            'cv' => 'CV/Resume',
            'financial_document' => 'Financial Document',
            'other' => 'Other Document',
            default => ucfirst(str_replace('_', ' ', $this->document->document_type)),
        };
    }

    /**
     * Get the user-friendly status message.
     */
    private function getStatusMessage(): string
    {
        return match ($this->document->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'needs_revision' => 'Needs Revision',
            default => ucfirst(str_replace('_', ' ', $this->document->status)),
        };
    }
}