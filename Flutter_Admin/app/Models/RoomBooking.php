<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use App\Services\BookingCalculator;

class RoomBooking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'reference',
        'guest_name',
        'guest_email',
        'guest_contact',
        'number_of_guests',
        'start_date',
        'end_date',
        'booking_date',
        'total_price',
        'remarks',
        'type',
        'booking_status',
        'payment_status',
        'status_change_reason',
        'created_by',
        'room_id',
    ];

    protected $casts = [
        'total_price'  => 'decimal:2',
        'booking_date' => 'date',
        'start_date'   => 'date',
        'end_date'     => 'date',
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
            $ref = 'RB-' . strtoupper(Str::random(8));
        } while (self::where('reference', $ref)->exists());

        return $ref;
    }

    // Relationships
    public function room()
    {
        return $this->belongsTo(Room::class);
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
    public function getPeriodAttribute()
    {
        if (!$this->start_date || !$this->end_date) return null;
        return $this->start_date->format('M d, Y') . ' - ' . $this->end_date->format('M d, Y');
    }

    public function getScheduleDisplayAttribute()
    {
        return $this->period;
    }

    public function getIsFunctionBookingAttribute()
    {
        return $this->room && $this->room->room_type === 'function';
    }

    public function getIsStayBookingAttribute()
    {
        return $this->room && $this->room->room_type !== 'function';
    }

    public function routeNotificationForMail(): string
    {
        return $this->guest_email;
    }

    protected $appends = [
        'period',
        'schedule_display',
        'is_function_booking',
        'is_stay_booking',
        'remaining_balance',
    ];
}
