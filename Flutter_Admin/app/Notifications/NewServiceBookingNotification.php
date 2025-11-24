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
            ->line('Reference: ' . $this->booking->reference)
            ->line('Guest Name: ' . $this->booking->guest_name)
            ->line('Service: ' . optional($this->booking->service)->name)
            ->line('Appointment Date: ' . optional($this->booking->appointment_date)->format('M d, Y'))
            ->line('Time: ' . $this->formatTimeRange())
            ->action('View Booking', url('/admin/service-bookings?reference=' . $this->booking->reference))
            ->line('Please check the booking as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'           => 'New Service Booking',
            'booking_id'      => $this->booking->id,
            'reference'       => $this->booking->reference,
            'guest_name'      => $this->booking->guest_name,
            'service_name'    => optional($this->booking->service)->name,
            'appointment_date'=> optional($this->booking->appointment_date)->format('Y-m-d'),
            'start_time'      => $this->booking->start_time,
            'end_time'        => $this->booking->end_time,
        ];
    }

    private function formatTimeRange()
    {
        if (!$this->booking->start_time || !$this->booking->end_time) {
            return 'N/A';
        }

        return $this->booking->start_time . ' - ' . $this->booking->end_time;
    }
}
