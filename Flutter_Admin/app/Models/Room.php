<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'room_number',
        'type',
        'price_type',
        'base_price',
        'is_time_based',
        'number_of_beds',
        'capacity',
        'status',
        'description',
        'image',
        'is_archived',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_time_based' => 'boolean',
            'is_archived' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class, 'room_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    // Accessor
    public function getFormattedPriceAttribute()
    {
        return number_format($this->base_price, 2);
    }
}
