<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Payout $payout,
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
            ->subject("💳 Payout Status Update - $" . number_format($this->payout->amount, 2))
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your payout request status has been updated.")
            ->line("**Amount:** $" . number_format($this->payout->amount, 2))
            ->line("**Status:** {$statusMessage}")
            ->line("**Updated:** {$this->payout->updated_at->format('M j, Y \a\t g:i A')}")
            ->when($this->newStatus === 'paid', function ($mail) {
                return $mail->line('🎉 Congratulations! Your payout has been processed and should appear in your account within 1-3 business days.');
            })
            ->when($this->newStatus === 'rejected', function ($mail) {
                return $mail->line('❌ Your payout request was not approved. Please contact support for more information.');
            })
            ->when($this->newStatus === 'pending', function ($mail) {
                return $mail->line('⏳ Your payout request is being reviewed and will be processed soon.');
            })
            ->action('View Payout Details', route('filament.agent.resources.payouts.view', $this->payout))
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
            'payout_id' => $this->payout->id,
            'amount' => $this->payout->amount,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'status_message' => $this->getStatusMessage(),
            'updated_at' => $this->payout->updated_at,
            'action_url' => route('filament.agent.resources.payouts.view', $this->payout),
        ];
    }

    /**
     * Get the user-friendly status message.
     */
    private function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $this->newStatus)),
        };
    }

    /**
     * Get the status color for styling.
     */
    private function getStatusColor(): string
    {
        return match ($this->newStatus) {
            'paid' => 'success',
            'rejected' => 'danger',
            'approved' => 'success',
            'pending' => 'warning',
            default => 'info',
        };
    }
}