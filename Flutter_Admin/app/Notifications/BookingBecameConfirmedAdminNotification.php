<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingBecameConfirmedAdminNotification extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->booking instanceof \App\Models\RoomBooking
            ? 'Room Booking'
            : 'Service Booking';

        return (new MailMessage)
            ->subject("New Confirmed {$type}")
            ->greeting('Hello Admin,')
            ->line("A {$type} has just been confirmed.")
            ->line("**Reference:** {$this->booking->reference}")
            ->line("**Guest:** {$this->booking->guest_name}")
            ->action('View Booking', url("/admin/".($type === 'Room Booking' ? 'room-bookings' : 'service-bookings')."?ref={$this->booking->reference}"))
            ->line('Thank you.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'     => 'Booking Confirmed',
            'reference' => $this->booking->reference,
        ];
    }
}
