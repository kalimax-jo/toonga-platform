<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\FlightBooking;
use App\Models\BookingPayment;
use App\Models\Miles;
use App\Models\User;
use Inertia\Inertia;
use App\Models\Product;

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

        // Get vendor and admin subaccounts
$vendor = null;
$adminUser = null;
$product = null;
$commissionPercentage = 10; // Default fallback

// Get admin/toonga subaccount
$adminUser = User::where('role', 'admin')
    ->whereNotNull('subaccount_id')
    ->first();

// Try to get vendor from product relationship first
if ($booking->product && $booking->product->vendor) {
    $vendor = $booking->product->vendor;
    $product = $booking->product;
    $commissionPercentage = $product->commission; // Get commission from product table
    
    Log::info('Vendor found via product relationship', [
        'vendor' => $vendor->name,
        'product_id' => $product->id,
        'commission_from_product' => $commissionPercentage,
        'booking' => $booking->booking_reference
    ]);
} else {
    // Fallback: find vendor by airline code
    $vendor = User::where('carrier_code', $booking->airline_code)
        ->where('role', 'vendor')
        ->where('is_approved', true)
        ->whereNotNull('subaccount_id')
        ->first();
    
    if ($vendor) {
        // Try to find matching product for this vendor and get commission
        $product = Product::where('vendor_id', $vendor->id)
            ->where('category_id', 1) // Flight category
            ->where('is_approved', true)
            ->first();
        
        if ($product) {
            $commissionPercentage = $product->commission;
            Log::info('Found product for vendor fallback', [
                'vendor' => $vendor->name,
                'product_id' => $product->id,
                'commission_from_product' => $commissionPercentage
            ]);
        }
    }
    
    Log::warning('Using fallback vendor lookup', [
        'booking_reference' => $booking->booking_reference,
        'airline_code' => $booking->airline_code,
        'vendor_found' => $vendor ? $vendor->name : 'None',
        'product_found' => $product ? 'Yes' : 'No',
        'commission_used' => $commissionPercentage
    ]);
}

// Prepare split payment configuration
$splitConfig = [];

// Only proceed with split if we have both admin and vendor subaccounts
if ($vendor && $vendor->subaccount_id && $adminUser && $adminUser->subaccount_id) {
    $vendorPercentage = 100 - $commissionPercentage; // e.g., 80% if commission is 20%
    $toognaPercentage = $commissionPercentage; // e.g., 20%
    
    $splitConfig = [
        [
            'id' => $vendor->subaccount_id,
            'transaction_split_ratio' => $vendorPercentage
        ],
        [
            'id' => $adminUser->subaccount_id,
            'transaction_split_ratio' => $toognaPercentage
        ]
    ];
    
    Log::info('Split payment configured with product commission', [
        'vendor' => $vendor->name,
        'vendor_subaccount' => $vendor->subaccount_id,
        'vendor_percentage' => $vendorPercentage,
        'toonga_subaccount' => $adminUser->subaccount_id,
        'toonga_percentage' => $toognaPercentage,
        'commission_from_product' => $commissionPercentage,
        'product_id' => $product ? $product->id : 'None',
        'booking' => $booking->booking_reference
    ]);
} else {
    Log::warning('Cannot configure split payment - missing subaccounts', [
        'vendor_exists' => $vendor ? 'Yes' : 'No',
        'vendor_subaccount' => $vendor ? $vendor->subaccount_id ?? 'Missing' : 'N/A',
        'admin_subaccount' => $adminUser ? $adminUser->subaccount_id ?? 'Missing' : 'No admin found',
        'product_commission' => $product ? $product->commission : 'No product',
        'booking' => $booking->booking_reference
    ]);
}

        // Convert to RWF if needed (since Flutterwave account is RWF)
        $amountRWF = $booking->total_price_local;
        $currency = $booking->currency_used;

        // If booking is in EUR, convert to RWF
        if ($currency === 'EUR') {
            $amountRWF = $booking->total_price_local * $booking->exchange_rate; // Convert to RWF
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
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://api.flutterwave.com/v3/payments', $paymentData);

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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY'),
            ])->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify");

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

    public function testSplitPayment($bookingReference)
{
    $booking = FlightBooking::where('booking_reference', $bookingReference)
        ->with(['product.vendor'])
        ->first();
    
    if (!$booking) {
        return response()->json(['error' => 'Booking not found']);
    }
    
    // Get admin user
    $adminUser = User::where('role', 'admin')
        ->whereNotNull('subaccount_id')
        ->first();
    
    // Get vendor
    $vendor = null;
    $product = null;
    $commissionPercentage = 10;
    
    if ($booking->product && $booking->product->vendor) {
        $vendor = $booking->product->vendor;
        $product = $booking->product;
        $commissionPercentage = $product->commission;
    } else {
        $vendor = User::where('carrier_code', $booking->airline_code)
            ->where('role', 'vendor')
            ->where('is_approved', true)
            ->whereNotNull('subaccount_id')
            ->first();
        
        if ($vendor) {
            $product = Product::where('vendor_id', $vendor->id)
                ->where('category_id', 1)
                ->where('is_approved', true)
                ->first();
            
            if ($product) {
                $commissionPercentage = $product->commission;
            }
        }
    }
    
    // Calculate amounts
    $totalAmountRWF = $booking->total_price_local * $booking->exchange_rate;
    $vendorPercentage = 100 - $commissionPercentage;
    $vendorAmount = $totalAmountRWF * ($vendorPercentage / 100);
    $toognaAmount = $totalAmountRWF * ($commissionPercentage / 100);
    
    // Check if split payment would be configured
    $canSplit = $vendor && $vendor->subaccount_id && $adminUser && $adminUser->subaccount_id;
    
    return response()->json([
        'booking_reference' => $booking->booking_reference,
        'total_amount_eur' => $booking->total_price_eur,
        'total_amount_rwf' => $totalAmountRWF,
        'exchange_rate' => $booking->exchange_rate,
        'split_payment_possible' => $canSplit,
        'commission_source' => $product ? 'Product table' : 'Default',
        'commission_percentage' => $commissionPercentage,
        'vendor' => [
            'found' => $vendor ? true : false,
            'name' => $vendor ? $vendor->name : 'Not found',
            'carrier_code' => $vendor ? $vendor->carrier_code : 'N/A',
            'subaccount_id' => $vendor ? $vendor->subaccount_id : 'Missing',
            'percentage' => $vendorPercentage,
            'amount_rwf' => round($vendorAmount, 2)
        ],
        'toonga' => [
            'found' => $adminUser ? true : false,
            'name' => $adminUser ? $adminUser->name : 'Not found',
            'subaccount_id' => $adminUser ? $adminUser->subaccount_id : 'Missing',
            'percentage' => $commissionPercentage,
            'amount_rwf' => round($toognaAmount, 2)
        ],
        'product_info' => $product ? [
            'id' => $product->id,
            'title' => $product->title,
            'commission' => $product->commission,
            'vendor_id' => $product->vendor_id
        ] : 'No product found',
        'flutterwave_split_config' => $canSplit ? [
            [
                'id' => $vendor->subaccount_id,
                'transaction_split_ratio' => $vendorPercentage
            ],
            [
                'id' => $adminUser->subaccount_id,
                'transaction_split_ratio' => $commissionPercentage
            ]
        ] : 'Split not possible - missing subaccounts'
    ]);
}
}