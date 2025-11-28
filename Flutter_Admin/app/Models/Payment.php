<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'amount',
        'method',
        'channel',
        'status',
        'processed_by',
        'paid_at',
        'payable_id',
        'payable_type',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {

            // Auto-generate ref unless Stripe sent one
            if (!$payment->reference) {
                do {
                    $ref = 'PAY-' . strtoupper(Str::random(10));
                } while (Payment::where('reference', $ref)->exists());

                $payment->reference = $ref;
            }

            if (!$payment->paid_at) {
                $payment->paid_at = now();
            }

            if (!$payment->channel) {
                $payment->channel = 'offline';
            }
        });
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

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

    protected $appends = [
        'formatted_amount',
        'formatted_date',
        'payment_label',
    ];
}
