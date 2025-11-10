<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'guest_name',
        'guest_email',
        'guest_contact',
        'service_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'quantity',
        'total_price',
        'remarks',
        'type',
        'booking_date',
        'booking_status',
        'payment_status',
        'status_change_reason',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'date' => 'date',
            'booking_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'service_booking_id');
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
        return $this->total_price ? number_format($this->total_price, 2) : '0.00';
    }

    public function getServiceScheduleAttribute()
    {
        $date = $this->date ? $this->date->format('M d, Y') : 'No date set';
        if ($this->start_time && $this->end_time) {
            return "{$date} ({$this->start_time} - {$this->end_time})";
        }
        return $date;
    }
}
