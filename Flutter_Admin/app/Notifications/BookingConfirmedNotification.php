<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingConfirmedNotification extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Booking is Confirmed!')
            ->greeting("Hello {$this->booking->guest_name},")
            ->line("Your booking has been successfully confirmed.")
            ->line("**Reference:** {$this->booking->reference}")
            ->line('We look forward to serving you.')
            ->line('Thank you for choosing our hotel.');
    }
}
