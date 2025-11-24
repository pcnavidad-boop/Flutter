<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class GuestPaymentReceivedNotification extends Notification
{
    use Queueable;

    protected $booking;
    protected $amount;

    public function __construct($booking, float $amount)
    {
        $this->booking = $booking;
        $this->amount = $amount;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Remaining via BookingCalculator
        $remaining = \App\Services\BookingCalculator::remainingBalance($this->booking);

        $mail = (new MailMessage)
            ->subject('Payment Received for Your Booking')
            ->greeting('Hello ' . $this->booking->guest_name . ',')
            ->line('We have successfully received your payment.')
            ->line('Booking Reference: ' . $this->booking->reference)
            ->line('Amount Paid: ₱' . number_format($this->amount, 2));

        if ($remaining > 0) {
            $mail->line('Remaining Balance: ₱' . number_format($remaining, 2))
                ->action('Pay Remaining Balance', url('/hotel/pay/remaining/' . $this->booking->reference))
                ->line('You may complete your payment anytime before your arrival.');
        } else {
            $mail->line('Your booking is now fully paid! 🎉')
                ->line('We look forward to serving you soon.');
        }

        return $mail->line('Thank you for choosing our hotel.');
    }
}
