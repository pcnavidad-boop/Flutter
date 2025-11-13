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
        return ['mail', 'database'];   // Send email + in-app notification
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Room Booking Received')
            ->greeting('Hello Admin,')
            ->line('A new room booking has been submitted.')
            ->line('Guest Name: ' . $this->booking->guest_name)
            ->line('Room: ' . $this->booking->room->room_number)
            ->line('Check-in Date: ' . $this->booking->check_in_date)
            ->line('Check-out Date: ' . $this->booking->check_out_date)
            ->action('View Booking', url('/room_bookings/' . $this->booking->id))
            ->line('Thank you for using the system.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Room Booking',
            'booking_id' => $this->booking->id,
            'guest_name' => $this->booking->guest_name,
            'room_number' => $this->booking->room->room_number,
            'check_in_date' => $this->booking->check_in_date,
            'check_out_date' => $this->booking->check_out_date,
        ];
    }
}
