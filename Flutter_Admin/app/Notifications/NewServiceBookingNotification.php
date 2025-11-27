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
            ->subject('New Service Booking')
            ->greeting('Hello Admin,')
            ->line('A new service booking has been submitted.')
            ->line("Reference: {$this->booking->reference}")
            ->line("Guest Name: {$this->booking->guest_name}")
            ->line("Service: {$this->booking->service->name}")
            ->line("Appointment Date: " . $this->booking->appointment_date->format('M d, Y'))
            ->line("Time: " . $this->formatTimeRange())
            ->action('View Booking List', route('service_booking.index_page'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'     => 'New Service Booking',
            'reference' => $this->booking->reference,
        ];
    }

    private function formatTimeRange(): string
    {
        if (!$this->booking->start_time || !$this->booking->end_time) {
            return 'N/A';
        }

        return date('h:i A', strtotime($this->booking->start_time))
             . ' – '
             . date('h:i A', strtotime($this->booking->end_time));
    }
}
