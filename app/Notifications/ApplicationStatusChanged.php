<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Application $application,
        public string $oldStatus,
        public string $newStatus
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
        $statusMessage = $this->getStatusMessage();
        $statusColor = $this->getStatusColor();
        
        return (new MailMessage)
            ->subject("Application Status Update - {$this->application->application_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your application status has been updated.")
            ->line("**Application:** {$this->application->application_number}")
            ->line("**Student:** {$this->application->student->name}")
            ->line("**Program:** {$this->application->program->name}")
            ->line("**University:** {$this->application->program->university->name}")
            ->line("**Status:** {$statusMessage}")
            ->when($this->newStatus === 'approved', function ($mail) {
                return $mail->line('🎉 Congratulations! Your application has been approved.');
            })
            ->when($this->newStatus === 'rejected', function ($mail) {
                return $mail->line('We regret to inform you that your application was not approved this time.');
            })
            ->when($this->newStatus === 'additional_documents_required', function ($mail) {
                return $mail->line('📋 Additional documents are required to process your application.');
            })
            ->action('View Application', route('filament.agent.resources.applications.view', $this->application))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'student_name' => $this->application->student->name,
            'program_name' => $this->application->program->name,
            'university_name' => $this->application->program->university->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'status_message' => $this->getStatusMessage(),
            'action_url' => route('filament.agent.resources.applications.view', $this->application),
        ];
    }

    /**
     * Get the user-friendly status message.
     */
    private function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            'pending' => 'Pending Review',
            'submitted' => 'Submitted for Review',
            'under_review' => 'Under Review',
            'additional_documents_required' => 'Additional Documents Required',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'enrolled' => 'Enrolled',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->newStatus)),
        };
    }

    /**
     * Get the status color for styling.
     */
    private function getStatusColor(): string
    {
        return match ($this->newStatus) {
            'approved', 'enrolled' => 'success',
            'rejected', 'cancelled' => 'danger',
            'under_review', 'additional_documents_required' => 'warning',
            default => 'info',
        };
    }
}
