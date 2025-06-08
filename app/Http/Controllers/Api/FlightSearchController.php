<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class FlightSearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'origin' => 'required|string|size:3',
                'destination' => 'required|string|size:3',
                'departureDate' => 'required|date',
                'returnDate' => 'nullable|date',
                'travelers' => 'required|integer|min:1',
                'tripType' => 'required|in:round,oneway',
                'flightClass' => 'nullable|string'
            ]);

            $apiKey = env('AMADEUS_API_KEY');
            $apiSecret = env('AMADEUS_API_SECRET');

            if (!$apiKey || !$apiSecret) {
                Log::error('Amadeus API credentials missing');
                return response()->json(['error' => 'API configuration error'], 500);
            }

            // 🔐 Get access token from Amadeus
            $tokenResponse = Http::asForm()->post('https://test.api.amadeus.com/v1/security/oauth2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Amadeus token request failed', ['response' => $tokenResponse->body()]);
                return response()->json(['error' => 'Unable to authenticate with Amadeus API'], 500);
            }

            $token = $tokenResponse->json()['access_token'] ?? null;

            if (!$token) {
                Log::error('No access token received from Amadeus');
                return response()->json(['error' => 'Authentication failed'], 500);
            }

            // 🧭 Prepare query parameters
            $params = [
                'originLocationCode' => strtoupper($validated['origin']),
                'destinationLocationCode' => strtoupper($validated['destination']),
                'departureDate' => $validated['departureDate'],
                'adults' => $validated['travelers'],
                'travelClass' => $validated['flightClass'] ?? 'ECONOMY',
                'max' => 20,
            ];

            if ($validated['tripType'] === 'round' && !empty($validated['returnDate'])) {
                $params['returnDate'] = $validated['returnDate'];
            }

            Log::info('Searching flights with params', $params);

            // ✈️ Search flights
            $flightResponse = Http::withToken($token)
                ->timeout(30)
                ->get('https://test.api.amadeus.com/v2/shopping/flight-offers', $params);

            if (!$flightResponse->successful()) {
                Log::error('Amadeus flight search failed', [
                    'status' => $flightResponse->status(),
                    'body' => $flightResponse->body()
                ]);
                return response()->json(['error' => 'Flight search failed'], 500);
            }

            $results = $flightResponse->json()['data'] ?? [];
            
            Log::info('Raw flight results count', ['count' => count($results)]);

            if (empty($results)) {
                return response()->json([]);
            }

            // ✅ Get approved airline vendors (removed category_type_id filter for now)
            $approvedCarriers = User::where('role', 'vendor')
                ->where('is_approved', true)
                ->whereNotNull('carrier_code')
                ->pluck('carrier_code')
                ->filter()
                ->toArray();

            Log::info('Approved carriers', ['carriers' => $approvedCarriers]);

            // 🧹 Format results - show all flights if no approved carriers, otherwise filter
            $filtered = collect($results)
                ->filter(function ($flight) use ($approvedCarriers) {
                    // If no approved carriers exist, show all flights for testing
                    if (empty($approvedCarriers)) {
                        return true;
                    }
                    
                    $airline = $flight['validatingAirlineCodes'][0] ?? null;
                    return in_array($airline, $approvedCarriers);
                })
                ->map(function ($flight) {
                    return [
                        'carrier' => $flight['validatingAirlineCodes'][0] ?? 'Unknown',
                        'price' => '€' . $flight['price']['total'],
                        'currency' => $flight['price']['currency'],
                        'class' => $flight['travelerPricings'][0]['fareDetailsBySegment'][0]['cabin'] ?? 'ECONOMY',
                        'tripType' => count($flight['itineraries']) === 2 ? 'Round Trip' : 'One Way',
                        'itineraries' => $flight['itineraries'],
                        'bookingClass' => $flight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'] ?? 'N/A',
                        'fareType' => $flight['travelerPricings'][0]['fareDetailsBySegment'][0]['fareBasis'] ?? 'Standard',
                        'includedCheckedBags' => $flight['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags']['quantity'] ?? 1,
                        'seatsRemaining' => $flight['numberOfBookableSeats'] ?? null,
                        'lastTicketingDate' => $flight['lastTicketingDate'] ?? null,
                        'priceBreakdown' => [
                            'baseFare' => $flight['price']['base'] ?? null,
                            'taxes' => implode(' + ', array_column($flight['price']['fees'] ?? [], 'amount')),
                            'total' => $flight['price']['total']
                        ]
                    ];
                })
                ->values();

            Log::info('Filtered flight results count', ['count' => $filtered->count()]);

            return response()->json($filtered);

        } catch (\Exception $e) {
            Log::error('Flight search exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'An error occurred while searching flights'], 500);
        }
    }
}