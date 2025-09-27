<?php

namespace App\Notifications;

use App\Models\Commission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionEarned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Commission $commission
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
            ->subject('🎉 Commission Earned!')
            ->greeting("Congratulations {$notifiable->name}!")
            ->line('You have earned a new commission!')
            ->line("**Amount:** $" . number_format($this->commission->amount, 2))
            ->line("**Application:** {$this->commission->application->application_number}")
            ->line("**Student:** {$this->commission->application->student->name}")
            ->line("**Program:** {$this->commission->application->program->name}")
            ->line("**University:** {$this->commission->application->program->university->name}")
            ->line('This commission will be included in your next payout.')
            ->action('View Commission Details', route('filament.agent.resources.commissions.index'))
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
            'commission_id' => $this->commission->id,
            'amount' => $this->commission->amount,
            'application_id' => $this->commission->application->id,
            'application_number' => $this->commission->application->application_number,
            'student_name' => $this->commission->application->student->name,
            'program_name' => $this->commission->application->program->name,
            'university_name' => $this->commission->application->program->university->name,
            'action_url' => route('filament.agent.resources.commissions.index'),
        ];
    }
}
