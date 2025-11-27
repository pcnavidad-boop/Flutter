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

    // Auto-generate reference + booking_date
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (!$booking->reference) {
                $booking->reference = 'SB-' . strtoupper(Str::random(8));
            }
            $booking->booking_date = now();
        });
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
    public function getFormattedPriceAttribute()
    {
        return number_format($this->total_price ?? 0, 2);
    }

    public function getServiceScheduleAttribute()
    {
        if (!$this->appointment_date) {
            return 'No date selected';
        }

        $date = $this->appointment_date->format('M d, Y');

        if ($this->start_time && $this->end_time) {
            return "{$date} ({$this->start_time} - {$this->end_time})";
        }

        return $date;
    }

    // Notification Routing
    public function routeNotificationForMail(): string
    {
        return $this->guest_email;
    }

    // Appended attributes
    protected $appends = [
        'formatted_price',
        'service_schedule',
        'remaining_balance',
    ];
}
