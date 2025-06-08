<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'description',
        'price',
        'commission',
        'is_approved',
        'images',
        'video',
        'additional_data'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'images' => 'array',
        'additional_data' => 'array',
        'commission' => 'integer',
        'price' => 'integer'
    ];

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function flightBookings()
    {
        return $this->hasMany(FlightBooking::class);
    }

    // Helper method to get carrier code
    public function getCarrierCodeAttribute()
    {
        return $this->additional_data['carrier_code'] ?? null;
    }
}