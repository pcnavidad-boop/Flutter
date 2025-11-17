<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'total_price'      => 'decimal:2',
            'appointment_date' => 'date',
            'booking_date'     => 'date',
            'start_time'       => 'time',
            'end_time'         => 'time',
        ];
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

    // ❗ Polymorphic payment
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', 'Confirmed');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'Unpaid');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('booking_status', ['Cancelled', 'Declined']);
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
}
