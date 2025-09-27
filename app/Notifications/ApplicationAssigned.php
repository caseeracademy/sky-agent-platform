<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Application $application
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
            ->subject("New Application Assigned - {$this->application->application_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new application has been assigned to you for review.')
            ->line("**Application:** {$this->application->application_number}")
            ->line("**Student:** {$this->application->student->name}")
            ->line("**Agent:** {$this->application->agent->name}")
            ->line("**Program:** {$this->application->program->name}")
            ->line("**University:** {$this->application->program->university->name}")
            ->line("**Status:** {$this->getStatusMessage()}")
            ->line('Please review this application at your earliest convenience.')
            ->action('Review Application', route('filament.admin.resources.applications.view', $this->application))
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
            'application_id' => $this->application->id,
            'application_number' => $this->application->application_number,
            'student_name' => $this->application->student->name,
            'agent_name' => $this->application->agent->name,
            'program_name' => $this->application->program->name,
            'university_name' => $this->application->program->university->name,
            'status' => $this->application->status,
            'status_message' => $this->getStatusMessage(),
            'action_url' => route('filament.admin.resources.applications.view', $this->application),
        ];
    }

    /**
     * Get the user-friendly status message.
     */
    private function getStatusMessage(): string
    {
        return match ($this->application->status) {
            'pending' => 'Pending Review',
            'submitted' => 'Submitted for Review',
            'under_review' => 'Under Review',
            'additional_documents_required' => 'Additional Documents Required',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'enrolled' => 'Enrolled',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->application->status)),
        };
    }
}
