<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;

class ServiceBooking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'guest_name',
        'guest_email',
        'guest_contact',
        'service_id',
        'user_id',
        'appointment_date',
        'start_time',
        'end_time',
        'number_of_guests',
        'total_price',
        'remarks',
        'reference',
        'type',
        'booking_date',
        'booking_status',
        'payment_status',
        'status_change_reason',
    ];

    protected $casts = [
        'total_price'      => 'decimal:2',
        'appointment_date' => 'date',
        'booking_date'     => 'date',
        'start_time'       => 'datetime:H:i',
        'end_time'         => 'datetime:H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->reference) {
                $booking->reference = 'SB-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', 'confirmed');
    }

    public function scopeDownpayment($query)
    {
        return $query->where('payment_status', 'downpayment');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('booking_status', ['completed', 'cancelled']);
    }

    public function totalPaymentsCompleted()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function totalPaymentsRefunded()
    {
        return $this->payments()->where('status', 'refunded')->sum('amount');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->total_price ?? 0, 2);
    }

    public function getServiceScheduleAttribute()
    {
        if (!$this->appointment_date) {
            return 'No date set';
        }

        $date = $this->appointment_date->format('M d, Y');

        if ($this->start_time && $this->end_time) {
            return "{$date} ({$this->start_time} - {$this->end_time})";
        }

        return $date;
    }

    public function getCalculatedTotalAttribute()
    {
        return \App\Services\BookingCalculator::computeTotal($this);
    }

    public function getRemainingBalanceAttribute()
    {
        return \App\Services\BookingCalculator::remainingBalance($this);
    }

    // Notification routing for mail
    public function routeNotificationForMail(): string
    {
        return $this->guest_email;
    }
}
