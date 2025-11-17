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
        return (new MailMessage)
            ->subject('New Room Booking Received')
            ->greeting('Hello Admin,')
            ->line('A new room booking has been submitted.')
            ->line('Reference: ' . $this->booking->reference)
            ->line('Guest Name: ' . $this->booking->guest_name)
            ->line('Room: ' . optional($this->booking->room)->room_number)
            ->line('Check-in Date: ' . optional($this->booking->check_in_date)->format('M d, Y'))
            ->line('Check-out Date: ' . optional($this->booking->check_out_date)->format('M d, Y'))
            ->action('View Booking', url('/room-bookings/' . $this->booking->id))
            ->line('Thank you for using the system.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'          => 'New Room Booking',
            'booking_id'     => $this->booking->id,
            'reference'      => $this->booking->reference,
            'guest_name'     => $this->booking->guest_name,
            'room_number'    => optional($this->booking->room)->room_number,
            'check_in_date'  => optional($this->booking->check_in_date)->format('Y-m-d'),
            'check_out_date' => optional($this->booking->check_out_date)->format('Y-m-d'),
        ];
    }
}
