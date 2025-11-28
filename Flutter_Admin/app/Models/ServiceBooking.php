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
        'total_price',
        'remarks',
        'type',
        'booking_status',
        'payment_status',
        'status_change_reason',
        'created_by',
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

    public function getServiceScheduleAttribute()
    {
        if (!$this->appointment_date) return 'No date selected';

        $date = $this->appointment_date->format('M d, Y');

        if ($this->start_time && $this->end_time) {
            return "{$date} ({$this->start_time->format('H:i')} - {$this->end_time->format('H:i')})";
        }

        return $date;
    }

    public function routeNotificationForMail(): string
    {
        return $this->guest_email;
    }

    protected $appends = [
        'formatted_price',
        'service_schedule',
        'remaining_balance',
    ];
}
