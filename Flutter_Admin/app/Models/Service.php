<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'service_description',
        'service_capacity',
        'service_image',
        'price_type',
        'base_price',
        'service_start_time',
        'service_end_time',
        'service_availability_status',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
        ];
    }
}
