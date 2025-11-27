<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'method',
        'status',
        'user_id',
        'channel',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date'   => 'date',
    ];

    // Auto-generate reference, date, channel
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {

            // Random unique reference
            do {
                $ref = 'PAY-' . strtoupper(Str::random(10));
            } while (Payment::where('reference', $ref)->exists());

            $payment->reference = $ref;

            // System-generated date
            if (!$payment->date) {
                $payment->date = now()->toDateString();
            }

            // Default channel
            if (!$payment->channel) {
                $payment->channel = 'offline';
            }
        });
    }

    // Polymorphic Relationship
    public function payable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
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

    public function getPaymentLabelAttribute()
    {
        return "{$this->formatted_amount} ({$this->method})";
    }

    protected $appends = [
        'formatted_amount',
        'formatted_date',
        'payment_label',
    ];
}
