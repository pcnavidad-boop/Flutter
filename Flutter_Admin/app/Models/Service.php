<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
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
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
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
        return $this->hasMany(ServiceBooking::class, 'service_id');
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
