<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Miles extends Model
{
   use HasFactory;

   protected $fillable = [
       'user_id',
       'booking_id',
       'amount',
       'type',
       'description',
       'payment_value',
       'source',
       'reference',
       'earned_at',
       'expires_at'
   ];

   protected $casts = [
       'amount' => 'decimal:2',
       'payment_value' => 'decimal:2',
       'earned_at' => 'datetime',
       'expires_at' => 'datetime'
   ];

   // Relationships
   public function user()
   {
       return $this->belongsTo(User::class);
   }

   public function booking()
   {
       return $this->belongsTo(FlightBooking::class, 'booking_id');
   }

   // Scopes
   public function scopeEarned($query)
   {
       return $query->where('type', 'earned');
   }

   public function scopeRedeemed($query)
   {
       return $query->where('type', 'redeemed');
   }

   public function scopeExpired($query)
   {
       return $query->where('type', 'expired');
   }

   public function scopeActive($query)
   {
       return $query->where('type', 'earned')
                   ->where(function($q) {
                       $q->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                   });
   }

   // Helper methods
   public function isExpired()
   {
       return $this->expires_at && $this->expires_at->isPast();
   }

   public function isExpiringSoon($months = 3)
   {
       return $this->expires_at && 
              $this->expires_at->isBetween(now(), now()->addMonths($months));
   }
}