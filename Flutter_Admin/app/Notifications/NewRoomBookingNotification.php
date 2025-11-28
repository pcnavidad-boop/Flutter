<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRoomBookingNotification extends Notification
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
        $room = $this->booking->room;

        return (new MailMessage)
            ->subject('New Room Booking Submitted')
            ->greeting('Hello Admin,')
            ->line('A new room booking has been created.')
            ->line("**Reference:** {$this->booking->reference}")
            ->line("**Guest:** {$this->booking->guest_name}")
            ->line("**Room:** {$room->name}")
            ->line("**Dates:** {$this->booking->start_date->format('M d, Y')} – {$this->booking->end_date->format('M d, Y')}")
            ->line("**Guests:** {$this->booking->number_of_guests}")
            ->action('Open Bookings', route('room_booking.index_page'))
            ->line('Thank you.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'     => 'New Room Booking',
            'reference' => $this->booking->reference,
        ];
    }
}
