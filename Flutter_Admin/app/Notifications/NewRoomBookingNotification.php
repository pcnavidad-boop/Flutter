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

        $mail = (new MailMessage)
            ->subject('New Room Booking')
            ->greeting('Hello Admin,')
            ->line('A new room booking has been submitted.')
            ->line("Reference: {$this->booking->reference}")
            ->line("Guest Name: {$this->booking->guest_name}")
            ->line("Room: {$room->name}");

        if ($this->booking->check_in_date) {
            $mail->line("Check-in: " . $this->booking->check_in_date->format('M d, Y'))
                 ->line("Check-out: " . $this->booking->check_out_date->format('M d, Y'));
        } else {
            $mail->line(
                "Event Dates: " .
                $this->booking->event_start_date->format('M d, Y') .
                " – " .
                $this->booking->event_end_date->format('M d, Y')
            );
        }

        return $mail->action('View Booking List', route('room_booking.index_page'))
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
