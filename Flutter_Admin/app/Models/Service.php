<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type',
        'description',
        'capacity',
        'image',
        'price_type',
        'base_price',
        'start_time',
        'end_time',
        'status',
        'is_archived',
        'user_id',
        'slug',
    ];

    protected $casts = [
        'base_price'  => 'decimal:2',
        'is_archived' => 'boolean',
        'start_time'  => 'string',
        'end_time'    => 'string',
    ];

    // Auto-generate & update slugs
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            $service->slug = Str::slug($service->name . '-' . uniqid());
        });

        static::updating(function ($service) {
            if ($service->isDirty('name')) {
                $service->slug = Str::slug($service->name . '-' . uniqid());
            }
        });
    }

    // Use slug for route model binding
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
        return $this->hasMany(ServiceBooking::class, 'service_id');
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

    // Accessors 
    public function getFormattedPriceAttribute()
    {
        return number_format($this->base_price, 2);
    }

    public function getScheduleAttribute()
    {
        return $this->start_time && $this->end_time
            ? substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5)
            : null;
    }

    public function getPriceLabelAttribute()
    {
        return match ($this->price_type) {
            'per_hour'    => "{$this->formatted_price} / hour",
            'per_day'     => "{$this->formatted_price} / day",
            'per_person'  => "{$this->formatted_price} / person",
            default        => "{$this->formatted_price}",
        };
    }

    // Appended virtual fields
    protected $appends = [
        'formatted_price',
        'schedule',
        'price_label',
    ];
}
