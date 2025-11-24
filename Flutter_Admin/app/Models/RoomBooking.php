<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;

class RoomBooking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'guest_name',
        'guest_email',
        'guest_contact',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'event_date',
        'start_time',
        'end_time',
        'room_id',
        'user_id',
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
        'total_price'    => 'decimal:2',
        'check_in_date'  => 'date',
        'check_out_date' => 'date',
        'event_date'     => 'date',
        'start_time'     => 'datetime:H:i',
        'end_time'       => 'datetime:H:i',
        'booking_date'   => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->reference) {
                $booking->reference = 'RB-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
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
        return $query->whereNotIn('booking_status', ['checked_out', 'cancelled']);
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

    public function getStayPeriodAttribute()
    {
        if ($this->check_in_date && $this->check_out_date) {
            return $this->check_in_date->format('M d, Y') . ' - ' . $this->check_out_date->format('M d, Y');
        }

        if ($this->event_date) {
            return $this->event_date->format('M d, Y');
        }

        return null;
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

