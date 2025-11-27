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
        'channel',
        'status',
        'processed_by',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    // Auto-generate reference, paid_at, and default channel
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {

            do {
                $ref = 'PAY-' . strtoupper(Str::random(10));
            } while (Payment::where('reference', $ref)->exists());

            $payment->reference = $ref;

            if (!$payment->paid_at) {
                $payment->paid_at = now();
            }

            if (!$payment->channel) {
                $payment->channel = 'offline';
            }
        });
    }

    // Relationships
    public function payable()
    {
        return $this->morphTo();
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedDateAttribute()
    {
        return $this->paid_at ? $this->paid_at->format('M d, Y') : null;
    }

    public function getPaymentLabelAttribute()
    {
        return "{$this->formatted_amount} ({$this->method})";
    }

    // Appended attributes
    protected $appends = [
        'formatted_amount',
        'formatted_date',
        'payment_label',
    ];
}
