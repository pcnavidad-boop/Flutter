<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewServiceBookingNotification extends Notification
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
            ->subject('New Service Booking Received')
            ->greeting('Hello Admin,')
            ->line('A new service booking has been submitted.')
            ->line('Guest Name: ' . $this->booking->guest_name)
            ->line('Service: ' . $this->booking->service->name)
            ->line('Date: ' . $this->booking->date)
            ->line('Time: ' . $this->booking->start_time . ' - ' . $this->booking->end_time)
            ->action('View Booking', url('/service_bookings/' . $this->booking->id))
            ->line('Please check the booking as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Service Booking',
            'booking_id' => $this->booking->id,
            'guest_name' => $this->booking->guest_name,
            'service_name' => $this->booking->service->name,
            'date' => $this->booking->date,
            'start_time' => $this->booking->start_time,
            'end_time' => $this->booking->end_time,
        ];
    }
}
