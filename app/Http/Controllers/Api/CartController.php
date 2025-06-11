<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    // ✅ Keep your existing flight-specific method
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

        // Store in session (for your existing booking flow)
        session(['cart.flight' => $flight]);
        
        // ALSO store in unified cart (for dashboard)
        $this->addToUnifiedCart($flight, 'flight');
        
        Log::info('Flight added to cart:', $flight);

        return response()->json(['message' => 'Flight added to cart']);
    }

    // ✅ New unified cart methods for dashboard
    public function index()
    {
        $cartItems = $this->getCartItems();
        
        return response()->json([
            'items' => $cartItems,
            'total' => $this->calculateTotal($cartItems),
            'itemCount' => count($cartItems),
            'categories' => $this->getCartCategories($cartItems)
        ]);
    }
    
    public function add(Request $request)
    {
        $request->validate([
            'item.type' => 'required|string|in:flight,hotel,car,electronics,food,furniture',
            'item.id' => 'required',
            'item.title' => 'required|string',
            'item.price' => 'required|numeric',
            'quantity' => 'integer|min:1'
        ]);
        
        $item = $request->item;
        $item['quantity'] = $request->quantity ?? 1;
        
        // Handle currency conversion for all items
        if (isset($item['currency']) && $item['currency'] === 'EUR') {
            $converted = CurrencyHelper::convert($item['price'], 'EUR', 'RWF');
            if ($converted) {
                $item['convertedPrice'] = round($converted);
                $item['convertedCurrency'] = 'RWF';
            }
        }
        
        $this->addToUnifiedCart($item, $item['type']);
        
        return response()->json([
            'message' => 'Item added to cart',
            'item' => $item,
            'itemCount' => count($this->getCartItems())
        ]);
    }
    
    public function update(Request $request, $cartId)
    {
        $cartItems = $this->getCartItems();
        $itemIndex = collect($cartItems)->search(function ($item) use ($cartId) {
            return $item['cart_id'] === $cartId;
        });
        
        if ($itemIndex === false) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        
        $cartItems[$itemIndex] = array_merge($cartItems[$itemIndex], $request->only(['quantity', 'notes']));
        $this->saveCartItems($cartItems);
        
        return response()->json([
            'message' => 'Item updated',
            'item' => $cartItems[$itemIndex]
        ]);
    }
    
    public function remove($cartId)
    {
        $cartItems = $this->getCartItems();
        $cartItems = collect($cartItems)->reject(function ($item) use ($cartId) {
            return $item['cart_id'] === $cartId;
        })->values()->toArray();
        
        $this->saveCartItems($cartItems);
        
        return response()->json([
            'message' => 'Item removed from cart',
            'itemCount' => count($cartItems)
        ]);
    }
    
    public function clear()
    {
        $this->saveCartItems([]);
        
        // Also clear session-based cart
        session()->forget('cart');
        
        return response()->json(['message' => 'Cart cleared']);
    }

    // ✅ Helper method to add items to unified cart
    private function addToUnifiedCart($item, $type)
    {
        $cartItems = $this->getCartItems();
        
        $unifiedItem = [
            'cart_id' => uniqid(),
            'type' => $type,
            'id' => $item['id'] ?? uniqid(),
            'title' => $item['title'] ?? $this->generateTitle($item, $type),
            'subtitle' => $this->generateSubtitle($item, $type),
            'price' => $item['convertedPrice'] ?? $item['price'],
            'originalPrice' => $item['price'],
            'currency' => $item['convertedCurrency'] ?? $item['currency'] ?? 'RWF',
            'quantity' => $item['quantity'] ?? 1,
            'image' => $this->getTypeIcon($type),
            'data' => $item, // Store full item data
            'added_at' => now()->toISOString()
        ];
        
        $cartItems[] = $unifiedItem;
        $this->saveCartItems($cartItems);
    }
    
    private function generateTitle($item, $type)
    {
        switch ($type) {
            case 'flight':
                return ($item['origin'] ?? 'Origin') . ' to ' . ($item['destination'] ?? 'Destination');
            case 'hotel':
                return $item['name'] ?? $item['hotel_name'] ?? 'Hotel Booking';
            case 'car':
                return $item['model'] ?? $item['car_type'] ?? 'Car Rental';
            default:
                return $item['name'] ?? $item['product_name'] ?? 'Product';
        }
    }
    
    private function generateSubtitle($item, $type)
    {
        switch ($type) {
            case 'flight':
                return $item['airline'] ?? $item['carrier'] ?? 'Flight';
            case 'hotel':
                return $item['location'] ?? $item['city'] ?? 'Hotel';
            case 'car':
                return $item['location'] ?? $item['pickup_location'] ?? 'Car Rental';
            default:
                return $item['brand'] ?? $item['category'] ?? '';
        }
    }
    
    private function getTypeIcon($type)
    {
        $icons = [
            'flight' => '✈️',
            'hotel' => '🏨',
            'car' => '🚗',
            'electronics' => '📱',
            'food' => '🍕',
            'furniture' => '🛋️'
        ];
        return $icons[$type] ?? '📦';
    }
    
    private function getCartItems()
    {
        return Cache::get("unified_cart_" . auth()->id(), []);
    }
    
    private function saveCartItems($items)
    {
        Cache::put("unified_cart_" . auth()->id(), $items, now()->addDays(30));
    }
    
    private function calculateTotal($items)
    {
        return collect($items)->sum(function ($item) {
            return $item['price'] * ($item['quantity'] ?? 1);
        });
    }
    
    private function getCartCategories($items)
    {
        return collect($items)->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'count' => $group->count(),
                'total' => $group->sum(function ($item) {
                    return $item['price'] * ($item['quantity'] ?? 1);
                })
            ];
        })->values();
    }
}