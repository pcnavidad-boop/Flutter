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
        'created_by',
        'slug',
    ];

    protected $casts = [
        'base_price'  => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            $room->slug = self::uniqueSlug($room->name);
        });

        static::updating(function ($room) {
            if ($room->isDirty('name')) {
                $room->slug = self::uniqueSlug($room->name);
            }
        });
    }

    private static function uniqueSlug(string $name): string
    {
        return Str::slug($name . '-' . Str::random(6));
    }

    public function getRouteKeyName()
    {
        return request()->is('admin/*') ? 'id' : 'slug';
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
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
    public function getFormattedBasePriceAttribute()
    {
        return number_format($this->base_price, 2);
    }

    public function getFormattedPriceTypeAttribute()
    {
        return strtolower(str_replace('_', ' ', $this->price_type));
    }

    public function getPriceLabelAttribute()
    {
        return "₱{$this->formatted_base_price} {$this->formatted_price_type}";
    }

    public function getIsFunctionRoomAttribute()
    {
        return $this->room_type === 'function';
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'available'   => 'success',
            'maintenance' => 'warning',
            default        => 'secondary',
        };
    }

    protected $appends = [
        'formatted_base_price',
        'formatted_price_type',
        'price_label',
        'is_function_room',
        'status_badge',
    ];
}
