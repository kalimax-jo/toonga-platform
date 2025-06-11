<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FlightBooking;
use App\Models\Miles;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get user statistics
        $totalOrders = FlightBooking::where('user_id', $user->id)->count();
        
        // Safe miles calculation (in case table is empty)
        $earnedMiles = 0;
        $redeemedMiles = 0;
        $milesEarnedThisYear = 0;
        $milesExpiring = 0;

        try {
            $earnedMiles = Miles::where('user_id', $user->id)
                ->where('type', 'earned')
                ->sum('amount') ?: 0;
                
            $redeemedMiles = Miles::where('user_id', $user->id)
                ->where('type', 'redeemed')
                ->sum('amount') ?: 0;

            $milesEarnedThisYear = Miles::where('user_id', $user->id)
                ->where('type', 'earned')
                ->whereYear('created_at', now()->year)
                ->sum('amount') ?: 0;

            $milesExpiring = Miles::where('user_id', $user->id)
                ->where('type', 'earned')
                ->where('expires_at', '<=', now()->addMonths(3))
                ->where('expires_at', '>', now())
                ->sum('amount') ?: 0;
        } catch (\Exception $e) {
            // If miles table doesn't exist or has issues, use defaults
            \Log::info('Miles calculation failed, using defaults: ' . $e->getMessage());
        }
            
        $availableMiles = $earnedMiles - $redeemedMiles;
        
        $activeBookings = FlightBooking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();

        // Get recent orders
        $recentOrders = FlightBooking::where('user_id', $user->id)
            ->with(['segments'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $dashboardData = [
            'user' => $user,
            'stats' => [
                'totalOrders' => $totalOrders,
                'availableMiles' => $availableMiles,
                'cartItems' => $this->getCartItemsCount($user->id),
                'activeBookings' => $activeBookings
            ],
            'recentOrders' => $recentOrders,
            'milesData' => [
                'available' => $availableMiles,
                'earned' => $milesEarnedThisYear,
                'expiring' => $milesExpiring,
                'tier' => $this->getUserTier($user->id),
                'tierProgress' => $this->getTierProgress($user->id)
            ]
        ];

        return Inertia::render('Dashboard/Index', $dashboardData);
    }

    public function cart()
    {
        $user = auth()->user();
        
        // Get all unpaid items for the cart
        $cartItems = $this->getCartItems($user->id);
        
        return Inertia::render('Dashboard/Cart', [
            'user' => $user,
            'cartItems' => $cartItems,
            'stats' => [
                'totalOrders' => FlightBooking::where('user_id', $user->id)->count(),
                'availableMiles' => $this->getAvailableMiles($user->id),
                'cartItems' => count($cartItems),
                'activeBookings' => FlightBooking::where('user_id', $user->id)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->count()
            ]
        ]);
    }

    public function orders()
    {
        $user = auth()->user();
        
        $orders = FlightBooking::where('user_id', $user->id)
            ->with(['segments', 'passengers'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Dashboard/Orders', [
            'user' => $user,
            'orders' => $orders,
            'stats' => [
                'totalOrders' => FlightBooking::where('user_id', $user->id)->count(),
                'availableMiles' => $this->getAvailableMiles($user->id),
                'cartItems' => $this->getCartItemsCount($user->id),
                'activeBookings' => FlightBooking::where('user_id', $user->id)
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->count()
            ]
        ]);
    }

    public function miles()
    {
        $user = auth()->user();
        
        $earnedMiles = 0;
        $redeemedMiles = 0;
        $milesHistory = collect(); // Empty collection

        try {
            $earnedMiles = Miles::where('user_id', $user->id)
                ->where('type', 'earned')
                ->sum('amount') ?: 0;
                
            $redeemedMiles = Miles::where('user_id', $user->id)
                ->where('type', 'redeemed')
                ->sum('amount') ?: 0;

            $milesHistory = Miles::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } catch (\Exception $e) {
            \Log::info('Miles page calculation failed: ' . $e->getMessage());
            $milesHistory = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1, ['path' => request()->url()]
            );
        }

        return Inertia::render('Dashboard/Miles', [
            'user' => $user,
            'milesData' => [
                'available' => $earnedMiles - $redeemedMiles,
                'earned' => $earnedMiles,
                'expiring' => 0,
                'tier' => $this->getUserTier($user->id),
                'tierProgress' => $this->getTierProgress($user->id),
                'history' => $milesHistory
            ]
        ]);
    }

    public function wishlist()
    {
        return Inertia::render('Dashboard/Wishlist', [
            'user' => auth()->user(),
            'wishlistItems' => []
        ]);
    }

    public function profile()
    {
        return Inertia::render('Dashboard/Profile', [
            'user' => auth()->user()
        ]);
    }



    public function clearCart()
{
    $user = auth()->user();
    
    // Cancel all unpaid flight bookings
    FlightBooking::where('user_id', $user->id)
        ->whereIn('status', ['pending', 'draft', 'unpaid'])
        ->update(['status' => 'cancelled']);
    
    // TODO: Clear other unpaid items
    
    return redirect()->back()->with('success', 'Cart cleared successfully');
}
    /**
     * Get all unpaid items that should appear in the cart
     */
    private function getCartItems($userId)
    {
        $cartItems = [];
        
        try {
            // Get unpaid flight bookings
            $unpaidFlights = FlightBooking::where('user_id', $userId)
                ->whereIn('status', ['pending', 'draft', 'unpaid'])
                ->with(['segments', 'passengers'])
                ->get();

            foreach ($unpaidFlights as $booking) {
                $cartItems[] = [
                    'id' => 'flight_' . $booking->id,
                    'booking_id' => $booking->id,
                    'type' => 'flight',
                    'title' => $this->getFlightTitle($booking),
                    'description' => $this->getFlightDescription($booking),
                    'price' => $booking->total_amount,
                    'currency' => $booking->currency_used ?: 'RWF',
                    'quantity' => 1,
                    'image' => null,
                    'details' => [
                        'departure' => $booking->segments->first()?->departure_airport ?? 'N/A',
                        'arrival' => $booking->segments->first()?->arrival_airport ?? 'N/A',
                        'departure_time' => $booking->segments->first()?->departure_time ?? null,
                        'arrival_time' => $booking->segments->first()?->arrival_time ?? null,
                        'passengers' => $booking->passengers->count(),
                        'booking_reference' => $booking->booking_reference,
                        'airline' => $booking->segments->first()?->airline_name ?? 'Unknown',
                        'flight_number' => $booking->segments->first()?->flight_number ?? 'N/A'
                    ],
                    'created_at' => $booking->created_at,
                    'expires_at' => $booking->created_at->addHours(24), // Booking expires in 24 hours
                    'is_expired' => $booking->created_at->addHours(24)->isPast()
                ];
            }

            // TODO: Add other unpaid items here (hotels, products, etc.)
            // Example structure for other items:
            /*
            $unpaidHotels = HotelBooking::where('user_id', $userId)
                ->whereIn('status', ['pending', 'unpaid'])
                ->get();
            
            foreach ($unpaidHotels as $hotel) {
                $cartItems[] = [
                    'id' => 'hotel_' . $hotel->id,
                    'type' => 'hotel',
                    'title' => $hotel->hotel_name,
                    'price' => $hotel->total_amount,
                    'currency' => $hotel->currency,
                    // ... other hotel details
                ];
            }
            */

        } catch (\Exception $e) {
            \Log::error('Error fetching cart items: ' . $e->getMessage());
        }

        // Sort by creation date (newest first)
        usort($cartItems, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return $cartItems;
    }

    /**
     * Get count of cart items
     */
    private function getCartItemsCount($userId)
    {
        try {
            $count = FlightBooking::where('user_id', $userId)
                ->whereIn('status', ['pending', 'draft', 'unpaid'])
                ->count();
            
            // TODO: Add counts for other item types
            // $count += HotelBooking::where('user_id', $userId)->whereIn('status', ['pending', 'unpaid'])->count();
            // $count += ProductOrder::where('user_id', $userId)->whereIn('status', ['pending', 'unpaid'])->count();
            
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Helper to get available miles
     */
    private function getAvailableMiles($userId)
    {
        try {
            $earned = Miles::where('user_id', $userId)->where('type', 'earned')->sum('amount') ?: 0;
            $redeemed = Miles::where('user_id', $userId)->where('type', 'redeemed')->sum('amount') ?: 0;
            return $earned - $redeemed;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Generate flight title for cart display
     */
    private function getFlightTitle($booking)
    {
        $firstSegment = $booking->segments->first();
        if (!$firstSegment) return "Flight Booking #{$booking->id}";
        
        return "{$firstSegment->departure_airport} → {$firstSegment->arrival_airport}";
    }

    /**
     * Generate flight description for cart display
     */
    private function getFlightDescription($booking)
    {
        $details = [];
        
        if ($booking->segments->count() > 0) {
            $firstSegment = $booking->segments->first();
            if ($firstSegment->departure_time) {
                $details[] = $firstSegment->departure_time->format('M j, Y');
            }
            if ($firstSegment->airline_name) {
                $details[] = $firstSegment->airline_name;
            }
        }
        
        $passengerCount = $booking->passengers->count();
        if ($passengerCount > 0) {
            $details[] = $passengerCount . ' passenger' . ($passengerCount > 1 ? 's' : '');
        }
        
        return implode(' • ', $details);
    }

    private function getUserTier($userId)
    {
        try {
            $totalMiles = Miles::where('user_id', $userId)
                ->where('type', 'earned')
                ->sum('amount') ?: 0;

            if ($totalMiles >= 10000) return 'Platinum';
            if ($totalMiles >= 5000) return 'Gold';
            if ($totalMiles >= 2000) return 'Silver';
            return 'Bronze';
        } catch (\Exception $e) {
            return 'Bronze';
        }
    }

    private function getTierProgress($userId)
    {
        try {
            $totalMiles = Miles::where('user_id', $userId)
                ->where('type', 'earned')
                ->sum('amount') ?: 0;

            $tiers = [
                'Bronze' => 0,
                'Silver' => 2000,
                'Gold' => 5000,
                'Platinum' => 10000
            ];
            
            $currentTier = $this->getUserTier($userId);
            
            if ($currentTier === 'Platinum') return 100;
            
            $currentTierMiles = $tiers[$currentTier];
            $nextTierMiles = collect($tiers)->first(function($miles) use ($totalMiles) {
                return $miles > $totalMiles;
            });

            if (!$nextTierMiles) return 100;
            
            return round((($totalMiles - $currentTierMiles) / ($nextTierMiles - $currentTierMiles)) * 100);
        } catch (\Exception $e) {
            return 0;
        }
    }
}