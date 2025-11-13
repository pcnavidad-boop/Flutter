<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomBooking extends Model
{
    
    use HasFactory;

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
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'event_date' => 'date',
            'start_time' => 'time',
            'end_time' => 'time',
            'booking_date' => 'date',
        ];
    }

    // Relationships

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'room_booking_id');
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
}
