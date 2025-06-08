<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'payment_method',
        'payment_provider',
        'transaction_id',
        'external_reference',
        'amount_eur',
        'amount_local',
        'currency',
        'exchange_rate',
        'status',
        'payment_date',
        'failure_reason',
        'provider_response'
    ];

    protected $casts = [
        'amount_eur' => 'decimal:2',
        'amount_local' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'payment_date' => 'datetime',
        'provider_response' => 'array'
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(FlightBooking::class, 'booking_id');
    }
}