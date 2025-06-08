<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'segment_type',
        'segment_order',
        'flight_number',
        'aircraft_code',
        'operating_airline',
        'departure_airport',
        'departure_city',
        'departure_time',
        'departure_terminal',
        'arrival_airport',
        'arrival_city',
        'arrival_time',
        'arrival_terminal',
        'flight_duration',
        'layover_duration',
        'cabin_class',
        'booking_class'
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'flight_duration' => 'integer',
        'layover_duration' => 'integer',
        'segment_order' => 'integer'
    ];

    public $timestamps = false;

    // Relationships
    public function booking()
    {
        return $this->belongsTo(FlightBooking::class, 'booking_id');
    }
}