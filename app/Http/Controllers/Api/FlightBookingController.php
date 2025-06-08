<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\FlightBooking;
use App\Models\Product;
use App\Models\User;

class FlightBookingController extends Controller
{
    /**
     * Create a new flight booking
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'flight' => 'required|array',
            'flight.carrier' => 'required|string',
            'flight.price' => 'required|string',
            'passenger' => 'required|array',
            'passenger.firstName' => 'required|string|max:255',
            'passenger.lastName' => 'required|string|max:255', 
            'passenger.email' => 'required|email|max:255',
            'passenger.phone' => 'required|string|max:20',
            'passenger.dateOfBirth' => 'required|date|before:today',
            'passenger.nationality' => 'required|string|max:255',
            'passenger.passportNumber' => 'nullable|string|max:50',
            'travelers' => 'required|integer|min:1|max:9',
            'total' => 'required|string',
            'searchCriteria' => 'required|array',
            'searchCriteria.origin' => 'required|string|size:3',
            'searchCriteria.destination' => 'required|string|size:3',
            'searchCriteria.departureDate' => 'required|date|after_or_equal:today',
            'searchCriteria.returnDate' => 'nullable|date|after:searchCriteria.departureDate',
            'searchCriteria.tripType' => 'required|in:oneway,round',
            'searchCriteria.flightClass' => 'nullable|in:ECONOMY,BUSINESS,FIRST'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            // Generate unique flight booking reference
            $bookingRef = $this->generateBookingReference();

            // Extract and validate amounts
            $totalAmount = $this->extractAmount($validated['total']);
            $currency = $this->extractCurrency($validated['total']);

            if ($totalAmount <= 0) {
                throw new \Exception('Invalid total amount');
            }

            // Create flight booking record
            $booking = FlightBooking::create([
                'user_id' => $user->id,
                'booking_reference' => $bookingRef,
                'status' => 'pending',
                'flight_data' => $validated['flight'],
                'passenger_data' => $validated['passenger'],
                'search_criteria' => $validated['searchCriteria'],
                'travelers_count' => $validated['travelers'],
                'total_amount' => $totalAmount,
                'currency' => $currency,
            ]);

            // Prepare flight data for Flutterwave payment
            $flightForPayment = array_merge($validated['flight'], [
                'booking_id' => $booking->id,
                'booking_reference' => $bookingRef,
                'booking_type' => 'flight',
                'originalPrice' => $totalAmount,
                'convertedPrice' => $totalAmount,
                'currency' => $currency === 'USD' ? 'EUR' : $currency, // Convert USD to EUR for consistency
                'product_id' => $this->getFlightProductId(),
                'vendor_subaccount_id' => $validated['flight']['vendor_subaccount_id'] ?? null
            ]);

            // Store in session for Flutterwave payment
            session([
                'cart' => [
                    'flight' => $flightForPayment,
                    'passenger' => $validated['passenger'],
                    'booking_reference' => $bookingRef,
                    'booking_type' => 'flight',
                    'booking_id' => $booking->id
                ]
            ]);

            DB::commit();

            Log::info('Flight booking created successfully', [
                'booking_id' => $booking->id,
                'booking_reference' => $bookingRef,
                'user_id' => $user->id,
                'amount' => $totalAmount,
                'currency' => $currency
            ]);

            return response()->json([
                'success' => true,
                'booking_id' => $booking->id,
                'booking_reference' => $bookingRef,
                'booking_type' => 'flight',
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'message' => 'Flight booking created successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Flight booking creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create flight booking',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your booking'
            ], 500);
        }
    }

    /**
     * Get a specific flight booking
     */
    public function show($id)
    {
        try {
            $booking = FlightBooking::where('user_id', auth()->id())
                                   ->where('id', $id)
                                   ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'error' => 'Flight booking not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'booking' => $booking->toApiArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving flight booking', [
                'booking_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve booking'
            ], 500);
        }
    }

    /**
     * Update flight booking status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_reference' => 'nullable|string|max:255',
            'payment_status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $booking = FlightBooking::findOrFail($id);
            
            // Check authorization (admin or booking owner)
            if (!auth()->user()->hasRole('admin') && $booking->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized to update this booking'
                ], 403);
            }

            $validated = $validator->validated();
            
            $updateData = [
                'status' => $validated['status'],
                'payment_reference' => $validated['payment_reference'] ?? $booking->payment_reference,
                'payment_status' => $validated['payment_status'] ?? $booking->payment_status,
                'notes' => $validated['notes'] ?? $booking->notes
            ];

            // Set confirmed_at timestamp when status changes to confirmed
            if ($validated['status'] === 'confirmed' && !$booking->confirmed_at) {
                $updateData['confirmed_at'] = now();
            }

            $booking->update($updateData);

            Log::info('Flight booking status updated', [
                'booking_id' => $booking->id,
                'old_status' => $booking->getOriginal('status'),
                'new_status' => $validated['status'],
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'booking' => $booking->fresh()->toApiArray(),
                'message' => 'Booking status updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating flight booking status', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update booking status'
            ], 500);
        }
    }

    /**
     * Get user's flight bookings
     */
    public function getUserFlightBookings(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 10);
            $status = $request->get('status');
            $search = $request->get('search');

            $query = FlightBooking::where('user_id', $user->id);

            // Filter by status if provided
            if ($status && in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
                $query->where('status', $status);
            }

            // Search by booking reference or passenger name
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('booking_reference', 'like', '%' . $search . '%')
                      ->orWhereRaw("JSON_EXTRACT(passenger_data, '$.firstName') LIKE ?", ['%' . $search . '%'])
                      ->orWhereRaw("JSON_EXTRACT(passenger_data, '$.lastName') LIKE ?", ['%' . $search . '%']);
                });
            }

            $bookings = $query->orderBy('created_at', 'desc')
                             ->paginate($perPage);

            return response()->json([
                'success' => true,
                'bookings' => $bookings->getCollection()->map(function($booking) {
                    return $booking->toApiArray();
                }),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving user flight bookings', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve bookings'
            ], 500);
        }
    }

    /**
     * Cancel a flight booking
     */
    public function cancel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $booking = FlightBooking::where('user_id', auth()->id())
                                   ->where('id', $id)
                                   ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'error' => 'Flight booking not found'
                ], 404);
            }

            if (!$booking->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This booking cannot be cancelled'
                ], 400);
            }

            $reason = $request->get('reason', 'Cancelled by user');
            $booking->markAsCancelled($reason);

            Log::info('Flight booking cancelled', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'reason' => $reason,
                'cancelled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'booking' => $booking->fresh()->toApiArray(),
                'message' => 'Booking cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling flight booking', [
                'booking_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel booking'
            ], 500);
        }
    }

    /**
     * Get booking statistics for admin
     */
    public function getBookingStats()
    {
        try {
            // Check if user is admin
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }

            $stats = [
                'total_bookings' => FlightBooking::count(),
                'pending_bookings' => FlightBooking::pending()->count(),
                'confirmed_bookings' => FlightBooking::confirmed()->count(),
                'cancelled_bookings' => FlightBooking::where('status', 'cancelled')->count(),
                'total_revenue' => FlightBooking::confirmed()->sum('total_amount'),
                'bookings_this_month' => FlightBooking::whereMonth('created_at', now()->month)
                                                    ->whereYear('created_at', now()->year)
                                                    ->count(),
                'revenue_this_month' => FlightBooking::confirmed()
                                                    ->whereMonth('created_at', now()->month)
                                                    ->whereYear('created_at', now()->year)
                                                    ->sum('total_amount')
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving booking statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve statistics'
            ], 500);
        }
    }

    /**
     * Generate unique booking reference
     */
    private function generateBookingReference()
    {
        do {
            $reference = 'FLT-' . strtoupper(Str::random(8));
        } while (FlightBooking::where('booking_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Extract numeric amount from string
     */
    private function extractAmount($totalString)
    {
        // Remove all non-numeric characters except decimal point
        $cleaned = preg_replace('/[^\d.]/', '', $totalString);
        $amount = floatval($cleaned);
        
        // Ensure amount is positive
        return max(0, $amount);
    }

    /**
     * Extract currency from string
     */
    private function extractCurrency($totalString)
    {
        if (strpos($totalString, '$') !== false) return 'USD';
        if (strpos($totalString, '€') !== false || stripos($totalString, 'EUR') !== false) return 'EUR';
        if (stripos($totalString, 'RWF') !== false) return 'RWF';
        
        return 'USD'; // default currency
    }

    /**
     * Get or create flight product for commission calculation
     */
    private function getFlightProductId()
    {
        try {
            // Try to find existing flight product
            $product = Product::where('name', 'Flight Booking')
                             ->where('is_active', true)
                             ->first();
            
            if (!$product) {
                // Create default flight product if it doesn't exist
                $product = Product::create([
                    'name' => 'Flight Booking',
                    'description' => 'Flight booking service with commission',
                    'price' => 0, // Dynamic pricing
                    'category_id' => 1, // Adjust based on your flight category
                    'vendor_id' => 1, // Adjust based on your setup
                    'commission_percentage' => 10, // Default 10% commission
                    'is_active' => true
                ]);

                Log::info('Created default flight product', ['product_id' => $product->id]);
            }

            return $product->id;

        } catch (\Exception $e) {
            Log::error('Error getting flight product ID', ['error' => $e->getMessage()]);
            
            // Return default ID if creation fails
            return 1;
        }
    }
}