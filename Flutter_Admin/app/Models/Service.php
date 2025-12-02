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
        'location',
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
        'created_by',
        'slug',
    ];

    protected $casts = [
        'base_price'  => 'decimal:2',
        'is_archived' => 'boolean',
        'start_time'  => 'string',
        'end_time'    => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            $service->slug = self::uniqueSlug($service->name);
        });

        static::updating(function ($service) {
            if ($service->isDirty('name')) {
                $service->slug = self::uniqueSlug($service->name);
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

    /* RELATIONSHIPS */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings()
    {
        return $this->hasMany(ServiceBooking::class);
    }

    /* SCOPES */
    public function scopeAvailable($q)
    {
        return $q->where('status', 'available');
    }

    public function scopeActive($q)
    {
        return $q->where('is_archived', false);
    }

    /* MUTATORS */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    /* ACCESSORS */
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

    public function getScheduleAttribute()
    {
        if (!$this->start_time || !$this->end_time) return null;
        return "{$this->start_time} - {$this->end_time}";
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
        'schedule',
        'status_badge',
    ];
}
