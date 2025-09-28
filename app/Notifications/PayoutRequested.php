<?php

namespace App\Notifications;

use App\Models\Payout;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutRequested extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Payout $payout
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
            ->subject("💰 Payout Request - {$this->payout->agent->name}")
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new payout request has been submitted and requires your review.')
            ->line("**Agent:** {$this->payout->agent->name}")
            ->line("**Amount:** $" . number_format($this->payout->amount, 2))
            ->line("**Request Date:** {$this->payout->created_at->format('M j, Y \a\t g:i A')}")
            ->line("**Status:** Pending Review")
            ->line('Please review this payout request and approve or reject it.')
            ->action('Review Payout Request', route('filament.admin.resources.payouts.view', $this->payout))
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
            'payout_id' => $this->payout->id,
            'agent_id' => $this->payout->agent_id,
            'agent_name' => $this->payout->agent->name,
            'amount' => $this->payout->amount,
            'status' => $this->payout->status,
            'requested_at' => $this->payout->created_at,
            'action_url' => route('filament.admin.resources.payouts.view', $this->payout),
        ];
    }
}