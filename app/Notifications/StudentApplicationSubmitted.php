<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentApplicationSubmitted extends Notification implements ShouldQueue
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
            ->subject("📋 New Application Submitted - {$this->application->application_number}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new application has been submitted by your student.')
            ->line("**Application:** {$this->application->application_number}")
            ->line("**Student:** {$this->application->student->name}")
            ->line("**Program:** {$this->application->program->name}")
            ->line("**University:** {$this->application->program->university->name}")
            ->line("**Commission Potential:** $" . number_format($this->application->program->agent_commission, 2))
            ->line("**Submitted:** {$this->application->created_at->format('M j, Y \a\t g:i A')}")
            ->line("**Status:** {$this->getStatusMessage()}")
            ->line('You can track the progress of this application in your dashboard.')
            ->action('View Application', route('filament.agent.resources.applications.view', $this->application))
            ->line('Keep up the great work!');
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
            'commission_amount' => $this->application->program->agent_commission,
            'status' => $this->application->status,
            'status_message' => $this->getStatusMessage(),
            'submitted_at' => $this->application->created_at,
            'action_url' => route('filament.agent.resources.applications.view', $this->application),
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