<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'room_number',
        'room_type',
        'price_type',
        'base_price',
        'number_of_beds',
        'capacity',
        'status',
        'description',
        'image',
        'is_archived',
        'user_id',
        'slug',
    ];

    protected function casts(): array
    {
        return [
            'base_price'   => 'decimal:2',
            'is_archived'  => 'boolean',
        ];
    }

    // Auto-slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (!$room->slug) {
                $room->slug = Str::slug($room->name . '-' . uniqid());
            }
        });
    }

    // Route by slug instead of ID
    public function getRouteKeyName()
    {
        return 'slug';
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
        return $query->where('status', 'available');
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
