<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use App\Services\BookingCalculator;

class ServiceBooking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'reference',
        'guest_name',
        'guest_email',
        'guest_contact',
        'number_of_guests',
        'appointment_date',
        'start_time',
        'end_time',
        'booking_date',
        'total_price',
        'remarks',
        'type',
        'booking_status',
        'payment_status',
        'created_by',
        'service_id',
    ];

    protected $casts = [
        'total_price'      => 'decimal:2',
        'booking_date'     => 'date',
        'appointment_date' => 'date',
        'start_time'       => 'string',
        'end_time'         => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($b) {
            $b->reference = self::uniqueReference();
            $b->booking_date = now();
        });
    }

    private static function uniqueReference(): string
    {
        do {
            $ref = 'SB-' . strtoupper(Str::random(8));
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    // Payment calculations
    public function totalPaymentsCompleted()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function totalPaymentsRefunded()
    {
        return $this->payments()->where('status', 'refunded')->sum('amount');
    }

    public function getRemainingBalanceAttribute()
    {
        return BookingCalculator::remainingBalance($this);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->total_price ?? 0, 2);
    }

    /**
     * Get formatted time slot (e.g., "14:00 - 15:00")
     */
    public function getTimeSlotAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }

    public function getBookingStatusBadgeAttribute()
    {
        return [
            'pending'     => 'bg-warning text-dark',
            'confirmed'   => 'bg-success',
            'completed'   => 'bg-info text-dark',
            'cancelled'   => 'bg-secondary',
        ][$this->booking_status] ?? 'bg-light text-dark';
    }

    public function getBookingStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->booking_status));
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return [
            'unpaid'      => 'bg-secondary',
            'downpayment' => 'bg-info text-dark',
            'fully_paid'  => 'bg-success',
            'refunded'    => 'bg-danger',
        ][$this->payment_status] ?? 'bg-light text-dark';
    }

    public function getPaymentStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->payment_status));
    }

    public function routeNotificationForMail(): string
    {
        return $this->guest_email;
    }

    protected $appends = [
        'formatted_price',
        'remaining_balance',
        'time_slot',
        'booking_status_badge',
        'booking_status_label',
        'payment_status_badge',
        'payment_status_label',
    ];
}