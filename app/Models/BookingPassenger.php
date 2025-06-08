<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPassenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'passenger_type',
        'title',
        'first_name',
        'last_name',
        'date_of_birth',
        'nationality',
        'passport_number',
        'passport_expiry',
        'passport_country',
        'email',
        'phone',
        'meal_preference',
        'special_assistance',
        'frequent_flyer_number',
        'seat_number'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'passport_expiry' => 'date'
    ];

    public $timestamps = false;

    // Relationships
    public function booking()
    {
        return $this->belongsTo(FlightBooking::class, 'booking_id');
    }
}