<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\FlightBooking;
use App\Models\BookingPassenger;
use App\Models\FlightSegment;
use App\Models\Miles;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function createPending(Request $request)
    {
        $validated = $request->validate([
            'flight' => 'required|array',
            'passenger' => 'required|array',
            'travelers' => 'required|integer|min:1',
            'total' => 'required|string',
            'currency' => 'required|string|size:3',
            'exchange_rate' => 'required|numeric',
            'miles_earned' => 'required|integer',
            'searchCriteria' => 'required|array'
        ]);

        try {
            DB::beginTransaction();

            // Generate unique booking reference
            $bookingReference = $this->generateBookingReference();

            // Extract price from total string (e.g., "€355.05" -> 355.05)
$totalPrice = (float) preg_replace('/[^\d.]/', '', $validated['total']);
$basePriceEur = $totalPrice;
if ($validated['currency'] !== 'EUR') {
    $basePriceEur = $totalPrice / $validated['exchange_rate'];
}

// Get vendor commission percentage
$vendor = \App\Models\User::where('carrier_code', $validated['flight']['carrier'])
    ->where('role', 'vendor')
    ->where('is_approved', true)
    ->first();

$commissionPercentage = 10; // Default 10%
if ($vendor && isset($vendor->commission_percentage)) {
    $commissionPercentage = $vendor->commission_percentage;
}

// Calculate split amounts
$platformCommission = $totalPrice * ($commissionPercentage / 100);
$vendorAmount = $totalPrice - $platformCommission;

            // Create main booking record
            $booking = FlightBooking::create([
                'booking_reference' => $bookingReference,
                'user_id' => Auth::id(),
                
                // Flight Details
                'airline_code' => $validated['flight']['carrier'],
                'airline_name' => $this->getAirlineName($validated['flight']['carrier']),
                'trip_type' => $validated['searchCriteria']['tripType'] === 'round' ? 'round' : 'oneway',
                'flight_class' => $validated['searchCriteria']['flightClass'] ?: 'ECONOMY',
                
                // Route
                'origin_code' => $validated['searchCriteria']['origin'],
                'origin_city' => $this->getAirportCity($validated['searchCriteria']['origin']),
                'destination_code' => $validated['searchCriteria']['destination'],
                'destination_city' => $this->getAirportCity($validated['searchCriteria']['destination']),
                
                // Passenger Info
                'total_passengers' => $validated['travelers'],
                'lead_passenger_name' => $validated['passenger']['firstName'] . ' ' . $validated['passenger']['lastName'],
                'lead_passenger_email' => $validated['passenger']['email'],
                'lead_passenger_phone' => $validated['passenger']['phone'],
                
                // Pricing
                'base_price_eur' => $basePriceEur,
                'total_price_eur' => $basePriceEur,
                'currency_used' => $validated['currency'],
                'total_price_local' => $totalPrice,
                'exchange_rate' => $validated['exchange_rate'],
                
                // Miles
                'miles_earned' => $validated['miles_earned'],
                
                // Status (pending payment)
                'status' => 'pending',
                'payment_status' => 'pending',
                
                // Dates
                'departure_date' => $validated['searchCriteria']['departureDate'],
                'return_date' => $validated['searchCriteria']['returnDate'],
                'booking_date' => now(),
                'booking_source' => 'website',
                // Commission & Split Payment
                'vendor_amount' => $vendorAmount,
                'platform_commission' => $platformCommission,
                'commission_percentage' => $commissionPercentage,
            ]);

            // Create passenger record
            BookingPassenger::create([
                'booking_id' => $booking->id,
                'passenger_type' => 'adult',
                'first_name' => $validated['passenger']['firstName'],
                'last_name' => $validated['passenger']['lastName'],
                'date_of_birth' => $validated['passenger']['dateOfBirth'],
                'nationality' => $validated['passenger']['nationality'],
                'passport_number' => $validated['passenger']['passportNumber'],
                'email' => $validated['passenger']['email'],
                'phone' => $validated['passenger']['phone']
            ]);

            // Create flight segments from itineraries
            foreach ($validated['flight']['itineraries'] as $index => $itinerary) {
                $segmentType = $index === 0 ? 'outbound' : 'return';
                
                foreach ($itinerary['segments'] as $segIndex => $segment) {
                    FlightSegment::create([
                        'booking_id' => $booking->id,
                        'segment_type' => $segmentType,
                        'segment_order' => $segIndex + 1,
                        'flight_number' => ($segment['carrierCode'] ?? '') . ($segment['number'] ?? ''),
                        'aircraft_code' => $segment['aircraft']['code'] ?? null,
                        'operating_airline' => $segment['carrierCode'] ?? $validated['flight']['carrier'],
                        
                        'departure_airport' => $segment['departure']['iataCode'],
                        'departure_city' => $this->getAirportCity($segment['departure']['iataCode']),
                        'departure_time' => $segment['departure']['at'],
                        'departure_terminal' => $segment['departure']['terminal'] ?? null,
                        
                        'arrival_airport' => $segment['arrival']['iataCode'],
                        'arrival_city' => $this->getAirportCity($segment['arrival']['iataCode']),
                        'arrival_time' => $segment['arrival']['at'],
                        'arrival_terminal' => $segment['arrival']['terminal'] ?? null,
                        
                        'flight_duration' => $this->parseDuration($segment['duration'] ?? 'PT0M'),
                        'cabin_class' => $segment['cabin'] ?? 'ECONOMY',
                        'booking_class' => $segment['bookingCode'] ?? 'Y'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'booking_reference' => $bookingReference,
                'booking_id' => $booking->id,
                'message' => 'Booking created successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking'
            ], 500);
        }
    }

    private function generateBookingReference()
    {
        do {
            $reference = 'TGA-' . strtoupper(Str::random(6));
        } while (FlightBooking::where('booking_reference', $reference)->exists());
        
        return $reference;
    }

    private function getAirlineName($code)
    {
        $airlines = [
            'WB' => 'RwandAir',
            'QR' => 'Qatar Airways',
            'KQ' => 'Kenya Airways',
            'ET' => 'Ethiopian Airlines',
            'TK' => 'Turkish Airlines'
        ];
        return $airlines[$code] ?? $code;
    }

    private function getAirportCity($code)
    {
        $airports = [
            'KGL' => 'Kigali',
            'NBO' => 'Nairobi',
            'ADD' => 'Addis Ababa',
            'DOH' => 'Doha',
            'DXB' => 'Dubai'
        ];
        return $airports[$code] ?? $code;
    }

private function parseDuration($isoDuration)
{
    preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $isoDuration, $matches);
    $hours = isset($matches[1]) ? (int)$matches[1] : 0;
    $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
    return ($hours * 60) + $minutes;
}

    public function success($bookingReference)
    {
        $booking = FlightBooking::where('booking_reference', $bookingReference)
            ->where('user_id', auth()->id())
            ->with(['product.vendor', 'passengers', 'segments'])
            ->firstOrFail();

        return Inertia::render('Booking/Success', [
            'booking' => $booking
        ]);
    }

    public function failed()
    {
        return Inertia::render('Booking/Failed');
    }
}