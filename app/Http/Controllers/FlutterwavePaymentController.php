<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Product;

class FlutterwavePaymentController extends Controller
{
    public function initiate(Request $request)
    {
        $user = auth()->user();
        $cart = session('cart');

        if (!$cart || empty($cart['flight'])) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $flight = $cart['flight'];
        $originalPrice = $flight['originalPrice'];
        $currency = 'RWF';

        // Live convert EUR -> RWF if needed
        if ($flight['currency'] === 'EUR') {
            $rateResponse = Http::get('https://api.exchangerate.host/convert', [
                'from' => 'EUR',
                'to' => 'RWF',
                'amount' => $originalPrice
            ]);

            if ($rateResponse->successful()) {
                $converted = $rateResponse['result'];
            } else {
                return response()->json(['error' => 'Currency conversion failed'], 500);
            }
        } else {
            $converted = $flight['convertedPrice'];
        }

        $txRef = 'toonga-' . Str::uuid();
        $mainAccountId = config('services.flutterwave.subaccount_main');
        $vendorAccountId = $flight['vendor_subaccount_id'] ?? null;

        // Fetch commission from product table
        $product = Product::find($flight['product_id']);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $commission = $product->commission_percentage;

        $subaccounts = [
            [
                'id' => $mainAccountId,
                'transaction_charge_type' => 'percentage',
                'transaction_charge' => $commission
            ]
        ];

        if ($vendorAccountId) {
            $subaccounts[] = [
                'id' => $vendorAccountId,
                'transaction_charge_type' => 'percentage',
                'transaction_charge' => 100 - $commission
            ];
        }

        $payload = [
            'tx_ref' => $txRef,
            'amount' => round($converted),
            'currency' => $currency,
            'redirect_url' => route('flutterwave.callback'),
            'payment_options' => 'card,mobilemoney',
            'customer' => [
                'email' => $user->email,
                'name' => $user->name
            ],
            'subaccounts' => $subaccounts
        ];

        $response = Http::withToken(config('services.flutterwave.secret_key'))
            ->post(config('services.flutterwave.base_url') . '/v3/payments', $payload);

        if ($response->successful() && $response['status'] === 'success') {
            session()->put('pending_miles', [
                'tx_ref' => $txRef,
                'amount_rwf' => round($converted),
                'user_id' => $user->id,
                'source' => 'flight'
            ]);
            return redirect($response['data']['link']);
        }

        Log::error('Flutterwave error', ['response' => $response->json()]);
        return back()->withErrors(['payment' => 'Unable to initiate payment. Try again later.']);
    }

    public function callback(Request $request)
    {
        $pending = session('pending_miles');
        if (!$pending) return redirect('/dashboard')->with('error', 'No pending miles recorded.');

        // Save to miles table
        \DB::table('miles')->insert([
            'user_id' => $pending['user_id'],
            'amount' => floor($pending['amount_rwf'] / 1000),
            'payment_value' => $pending['amount_rwf'],
            'source' => $pending['source'],
            'reference' => $pending['tx_ref'],
            'earned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session()->forget('pending_miles');
        return redirect('/dashboard')->with('success', 'Payment completed. Miles earned.');
    }
}
