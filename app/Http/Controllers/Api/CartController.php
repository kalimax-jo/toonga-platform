<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function addFlight(Request $request)
    {
        $flight = $request->input('flight');

        // Live convert EUR to RWF if needed
        if (($flight['currency'] ?? null) === 'EUR') {
            $converted = CurrencyHelper::convert($flight['originalPrice'], 'EUR', 'RWF');

            if ($converted) {
                $flight['convertedPrice'] = round($converted);
                $flight['convertedCurrency'] = 'RWF';
            } else {
                return response()->json(['error' => 'Currency conversion failed'], 500);
            }
        }

        // ✅ Now move this here — after $flight is fully built
        session(['cart.flight' => $flight]);
        Log::info('Flight added to cart:', $flight);

        return response()->json(['message' => 'Flight added to cart']);
    }
}
