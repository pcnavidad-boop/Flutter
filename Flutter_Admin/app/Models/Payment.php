<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_id',
        'payable_type',
        'user_id',
        'amount',
        'date',
        'method',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    // Polymorphic Relationship
    public function payable()
    {
        return $this->morphTo();
    }

    // User who recorded the payment
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'Refunded');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('M d, Y') : null;
    }
}
