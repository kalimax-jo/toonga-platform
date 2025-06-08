<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'nationality',
        'password',
        'role',
        'is_approved',
        'carrier_code',
        'category_type_id',
        'subaccount_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function flightBookings()
    {
        return $this->hasMany(FlightBooking::class);
    }

    public function miles()
    {
        return $this->hasMany(Miles::class);
    }
}