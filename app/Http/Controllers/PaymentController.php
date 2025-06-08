<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use App\Models\FlightBooking;
use App\Models\BookingPayment;
use App\Models\Miles;
use App\Models\User;
use App\Helpers\CurrencyHelper;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request, $bookingReference)
    {
        $booking = FlightBooking::where('booking_reference', $bookingReference)
            ->where('user_id', auth()->id())
            ->with(['product.vendor', 'passengers', 'segments'])
            ->firstOrFail();

        if ($booking->payment_status === 'paid') {
            return redirect()->route('bookings.success', $booking->booking_reference);
        }

        // Create payment record
        $payment = BookingPayment::create([
            'booking_id' => $booking->id,
            'payment_method' => 'flutterwave',
            'payment_provider' => 'flutterwave',
            'amount_eur' => $booking->total_price_eur,
            'amount_local' => $booking->total_price_local,
            'currency' => $booking->currency_used,
            'exchange_rate' => $booking->exchange_rate,
            'status' => 'pending'
        ]);

        // Get vendor for split payment - with safe null checking
        $vendor = null;
        $commissionPercentage = $booking->commission_percentage ?? 10;

        // Try to get vendor from product relationship first
        if ($booking->product && $booking->product->vendor) {
            $vendor = $booking->product->vendor;
            Log::info('Vendor found via product relationship', [
                'vendor' => $vendor->name,
                'booking' => $booking->booking_reference
            ]);
        } else {
            // Fallback: find vendor by airline code
            $vendor = User::where('carrier_code', $booking->airline_code)
                ->where('role', 'vendor')
                ->where('is_approved', true)
                ->first();
            
            Log::warning('Using fallback vendor lookup', [
                'booking_reference' => $booking->booking_reference,
                'airline_code' => $booking->airline_code,
                'vendor_found' => $vendor ? $vendor->name : 'None'
            ]);
        }

        // Prepare split payment configuration based on commission percentage
        $splitConfig = [];
        if ($vendor && $vendor->subaccount_id) {
            $splitConfig = [
                [
                    'id' => config('services.flutterwave.subaccount_main'),
                    'transaction_charge_type' => 'percentage',
                    'transaction_charge' => $commissionPercentage
                ],
                [
                    'id' => $vendor->subaccount_id,
                    'transaction_charge_type' => 'percentage',
                    'transaction_charge' => 100 - $commissionPercentage
                ]
            ];
            
            Log::info('Split payment configured', [
                'vendor' => $vendor->name,
                'subaccount' => $vendor->subaccount_id,
                'commission' => $commissionPercentage,
                'vendor_percentage' => (100 - $commissionPercentage)
            ]);
        } else {
            Log::warning('No split payment - proceeding with full payment to platform', [
                'vendor_exists' => $vendor ? 'Yes' : 'No',
                'subaccount_exists' => $vendor && isset($vendor->subaccount_id) ? 'Yes' : 'No',
                'booking' => $booking->booking_reference
            ]);
        }

        // Convert to RWF using live exchange rates when necessary
        $amountRWF = $booking->total_price_local;
        $currency = 'RWF';

        if ($booking->currency_used !== 'RWF') {
            try {
                $converted = CurrencyHelper::convert(
                    $booking->total_price_local,
                    $booking->currency_used,
                    'RWF'
                );

                if ($converted !== null) {
                    $amountRWF = round($converted, 2);
                }
            } catch (\Exception $e) {
                Log::error('Currency conversion failed', [
                    'booking' => $booking->booking_reference,
                    'message' => $e->getMessage(),
                ]);
                // fallback to stored local amount without conversion
                $amountRWF = $booking->total_price_local;
            }

        // Convert any non-RWF currency using stored exchange rate
        if ($currency !== 'RWF') {
            $amountRWF = $booking->total_price_local * $booking->exchange_rate;
            $currency = 'RWF';

        }

        // Prepare Flutterwave payment data
        $paymentData = [
            'tx_ref' => $booking->booking_reference . '-' . time(),
            'amount' => $amountRWF,
            'currency' => $currency,
            'payment_options' => 'card,mobilemoney,ussd,banktransfer',
            'customer' => [
                'email' => $booking->lead_passenger_email,
                'phone_number' => $booking->lead_passenger_phone,
                'name' => $booking->lead_passenger_name
            ],
            'customizations' => [
                'title' => 'Flight Booking - ' . $booking->booking_reference,
                'description' => "Flight from {$booking->origin_city} to {$booking->destination_city}",
                'logo' => url('/images/logo.png')
            ],
            'redirect_url' => route('payment.callback'),
            'meta' => [
                'booking_reference' => $booking->booking_reference,
                'payment_id' => $payment->id
            ]
        ];

        // Only add subaccounts if split payment is configured
        if (!empty($splitConfig)) {
            $paymentData['subaccounts'] = $splitConfig;
        }

        Log::info('Sending payment request to Flutterwave', [
            'booking' => $booking->booking_reference,
            'amount' => $amountRWF,
            'currency' => $currency,
            'has_split' => !empty($splitConfig)
        ]);

        // Make request to Flutterwave

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
                'Content-Type' => 'application/json'
            ])->post(config('services.flutterwave.base_url') . '/v3/payments', $paymentData);
        } catch (ConnectionException $e) {
            Log::error('Connection error while initiating payment', [
                'booking' => $booking->booking_reference,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Unable to connect to payment gateway. Please try again later.');
        }


        if ($response->successful()) {
            $data = $response->json();
            
            Log::info('Flutterwave response received', ['response' => $data]);
            
            // Check if we have the payment link
            if (isset($data['data']['link'])) {
                // Update payment record with our generated tx_ref
                $payment->update([
                    'transaction_id' => $paymentData['tx_ref'], // Use our generated tx_ref
                    'external_reference' => $data['data']['id'] ?? null, // ID might not be in response
                    'provider_response' => $data
                ]);

                Log::info('Redirecting to Flutterwave payment', [
                    'booking' => $booking->booking_reference,
                    'link' => $data['data']['link']
                ]);

                // Redirect to Flutterwave payment page
                return redirect($data['data']['link']);
            } else {
                Log::error('Flutterwave response missing payment link', [
                    'booking' => $booking->booking_reference,
                    'response' => $data
                ]);
                
                return back()->with('error', 'Payment initialization failed - no payment link received.');
            }
        }

        Log::error('Flutterwave payment initialization failed', [
            'booking' => $booking->booking_reference,
            'status' => $response->status(),
            'response' => $response->body(),
            'request_data' => $paymentData
        ]);

        // If payment initialization fails, show error page
        return back()->with('error', 'Payment initialization failed. Please try again.');
    }

    public function handleCallback(Request $request)
    {
        $transactionId = $request->tx_ref;
        $status = $request->status;

        Log::info('Payment callback received', [
            'tx_ref' => $transactionId,
            'status' => $status,
            'full_request' => $request->all()
        ]);

        if ($status === 'successful') {
            // Verify payment with Flutterwave

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
                ])->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");
            } catch (ConnectionException $e) {
                Log::error('Connection error while verifying payment', [
                    'tx_ref' => $transactionId,
                    'message' => $e->getMessage(),
                ]);

                return redirect()->route('bookings.failed')->with('error', 'Unable to verify payment. Please try again.');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
            ])->get(config('services.flutterwave.base_url') . "/v3/transactions/{$transactionId}/verify");


            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('Payment verification response', ['response' => $data]);
                
                if (isset($data['data']['status']) && $data['data']['status'] === 'successful') {
                    // Extract booking reference
                    $bookingRef = explode('-', $transactionId)[0] . '-' . explode('-', $transactionId)[1];
                    
                    $booking = FlightBooking::where('booking_reference', $bookingRef)->first();
                    $payment = BookingPayment::where('transaction_id', $transactionId)->first();

                    if ($booking && $payment) {
                        // Update booking status
                        $booking->update([
                            'status' => 'confirmed',
                            'payment_status' => 'paid',
                            'payment_method' => 'flutterwave',
                            'payment_reference' => $data['data']['flw_ref'] ?? $transactionId,
                            'payment_date' => now(),
                            'split_payment_id' => $data['data']['id'] ?? null
                        ]);

                        // Update payment status
                        $payment->update([
                            'status' => 'completed',
                            'payment_date' => now(),
                            'provider_response' => $data
                        ]);

                        // Award miles to user
                        Miles::create([
                            'user_id' => $booking->user_id,
                            'booking_id' => $booking->id,
                            'amount' => $booking->miles_earned,
                            'payment_value' => $booking->total_price_local,
                            'type' => 'earned',
                            'source' => 'flight_booking',
                            'reference' => $booking->booking_reference,
                            'description' => "Flight booking: {$booking->origin_city} to {$booking->destination_city}"
                        ]);

                        Log::info('Payment processed successfully', [
                            'booking' => $booking->booking_reference,
                            'miles_awarded' => $booking->miles_earned
                        ]);

                        return redirect()->route('bookings.success', $booking->booking_reference);
                    } else {
                        Log::error('Booking or payment not found for successful transaction', [
                            'booking_ref' => $bookingRef,
                            'tx_ref' => $transactionId
                        ]);
                    }
                } else {
                    Log::warning('Payment verification failed - transaction not successful', [
                        'tx_ref' => $transactionId,
                        'response' => $data
                    ]);
                }
            } else {
                Log::error('Payment verification request failed', [
                    'tx_ref' => $transactionId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } else {
            Log::warning('Payment callback with non-successful status', [
                'tx_ref' => $transactionId,
                'status' => $status
            ]);
        }

        return redirect()->route('bookings.failed')->with('error', 'Payment verification failed.');
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