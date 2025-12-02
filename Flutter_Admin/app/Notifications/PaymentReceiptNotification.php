<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\BookingCalculator;

class PaymentReceiptNotification extends Notification
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
        $remaining = BookingCalculator::remainingBalance($this->booking);

        $mail = (new MailMessage)
            ->subject('Payment Receipt')
            ->greeting("Hello {$this->booking->guest_name},")
            ->line("We have received your payment.")
            ->line("**Booking Reference:** {$this->booking->reference}")
            ->line("**Amount Paid:** ₱" . number_format($this->amount, 2));

        if ($remaining > 0) {
            $mail->line("**Remaining Balance:** ₱" . number_format($remaining, 2))
                 ->action('Pay Remaining Balance', url("/payment/payRemaining/{$this->booking->reference}"))
                 ->line('You may complete your payment anytime.');
        } else {
            $mail->line("🎉 Your booking is now fully paid!");
        }

        return $mail->line('Thank you for choosing our hotel.');
    }
}
