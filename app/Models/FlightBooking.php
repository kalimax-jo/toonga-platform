<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'user_id',
        'product_id',              // Add this
        'airline_code',
        'airline_name',
        'trip_type',
        'flight_class',
        'origin_code',
        'origin_city',
        'destination_code',
        'destination_city',
        'total_passengers',
        'lead_passenger_name',
        'lead_passenger_email',
        'lead_passenger_phone',
        'base_price_eur',
        'taxes_fees_eur',
        'total_price_eur',
        'currency_used',
        'total_price_local',
        'exchange_rate',
        'miles_earned',
        'vendor_amount',         
        'platform_commission',   
        'commission_percentage',  
        'split_payment_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'departure_date',
        'return_date',
        'booking_date',
        'payment_date',
        'special_requests',
        'booking_source'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'booking_date' => 'datetime',
        'payment_date' => 'datetime',
        'base_price_eur' => 'decimal:2',
        'taxes_fees_eur' => 'decimal:2',
        'total_price_eur' => 'decimal:2',
        'total_price_local' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'miles_earned' => 'integer',
        'total_passengers' => 'integer',
        'vendor_amount' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function passengers()
    {
        return $this->hasMany(BookingPassenger::class, 'booking_id');
    }

    public function segments()
    {
        return $this->hasMany(FlightSegment::class, 'booking_id');
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class, 'booking_id');
    }

    public function miles()
    {
        return $this->hasMany(Miles::class, 'booking_id');
    }
}