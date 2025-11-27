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
        'number_of_guests',
        'start_date',
        'end_date',
        'total_price',
        'remarks',
        'type',
        'booking_status',
        'payment_status',
        'status_change_reason',
        'created_by',
    ];

    protected $casts = [
        'total_price'  => 'decimal:2',
        'booking_date' => 'date',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    // Auto-generate reference + booking_date
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->reference) {
                $booking->reference = 'RB-' . strtoupper(Str::random(8));
            }
            $booking->booking_date = now();
        });
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

    // Scopes and Calculated Attributes
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
        return max(0, $this->total_price - $this->totalPaymentsCompleted());
    }

    // Accessors
    public function getPeriodAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('M d, Y') . " - " . $this->end_date->format('M d, Y');
        }
        return null;
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

    // Notification Routing
    public function routeNotificationForMail(): string
    {
        return $this->guest_email;
    }

    // Appended attributes
    protected $appends = [
        'period',
        'schedule_display',
        'is_function_booking',
        'is_stay_booking',
        'remaining_balance',
    ];
}
