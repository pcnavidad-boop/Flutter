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

    protected $casts = [
        'base_price'  => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    // Automatically generate & update slugs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            $room->slug = Str::slug($room->name . '-' . uniqid());
        });

        static::updating(function ($room) {
            if ($room->isDirty('name')) {
                $room->slug = Str::slug($room->name . '-' . uniqid());
            }
        });
    }

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

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    public function setRoomNumberAttribute($value)
    {
        $this->attributes['room_number'] = strtoupper($value);
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return number_format($this->base_price, 2);
    }

    public function getPriceLabelAttribute()
    {
        return match ($this->price_type) {
            'per_night' => $this->formatted_price . ' / night',
            'per_event_per_day' => $this->formatted_price . ' / event',
            default     => $this->formatted_price,
        };
    }

    public function getIsFunctionRoomAttribute()
    {
        return $this->room_type === 'function';
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'available'   => 'success',
            'occupied'    => 'danger',
            'maintenance' => 'warning',
            default        => 'secondary',
        };
    }

    // Appended virtual fields
    protected $appends = [
        'formatted_price',
        'price_label',
        'is_function_room',
        'status_badge',
    ];
}
