<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Api\CategoryTypeController;
use App\Http\Controllers\Api\Vendor\ProductController;
use App\Http\Controllers\Api\FlightSearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by RouteServiceProvider and assigned the "api"
| middleware group. Use them for APIs that your Vue frontend can call.
*/

// 🔐 Get authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 📦 Vendor Management APIs

Route::get('/vendors', function () {
    return User::with('categories')->where('role', 'vendor')->get();
});

Route::post('/vendors', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'subaccount_id' => 'required|string|max:255',
        'categories' => 'required|array|min:1',
        'categories.*' => 'exists:categories,id',
    ]);

    $vendor = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'role' => 'vendor',
        'is_approved' => false,
        'subaccount_id' => $validated['subaccount_id'],
    ]);

    $vendor->categories()->sync($validated['categories']);

    return $vendor->load('categories');
});

Route::put('/vendors/{id}', function (Request $request, $id) {
    $vendor = User::where('role', 'vendor')->findOrFail($id);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => "required|email|unique:users,email,$id",
        'subaccount_id' => 'required|string|max:255',
        'categories' => 'array',
        'categories.*' => 'exists:categories,id',
    ]);

    $vendor->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'subaccount_id' => $validated['subaccount_id'],
    ]);

    if (isset($validated['categories'])) {
        $vendor->categories()->sync($validated['categories']);
    }

    return $vendor->load('categories');
});

Route::delete('/vendors/{id}', function ($id) {
    $vendor = User::where('role', 'vendor')->findOrFail($id);
    $vendor->categories()->detach();
    $vendor->delete();

    return response()->json(['message' => 'Vendor deleted']);
});

// 🗂️ Category Management
Route::get('/categories', function () {
    return Category::with('type')->get();
});

Route::post('/categories', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories',
        'category_type_id' => 'required|exists:category_types,id',
    ]);

    return Category::create($validated);
});

Route::put('/categories/{id}', function (Request $request, $id) {
    $category = Category::findOrFail($id);
    $category->update($request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $id,
    ]));
    return $category;
});

Route::delete('/categories/{id}', function ($id) {
    $category = Category::findOrFail($id);
    $category->delete();
    return response()->json(['message' => 'Category deleted']);
});

Route::get('/categories/{id}', function ($id) {
    return Category::with('type')->findOrFail($id);
});

// 📦 Products
Route::get('/products', function () {
    return Product::with('category', 'vendor')->get();
});

Route::get('/category-types', [CategoryTypeController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/vendor/products', [ProductController::class, 'store']);
});


Route::get('/flight-search', [FlightSearchController::class, 'search']);
use App\Http\Controllers\Api\CartController;
Route::middleware(['web', 'auth'])->post('/cart/flight', [CartController::class, 'addFlight']);

use App\Http\Controllers\FlutterwavePaymentController;


Route::middleware(['auth'])->group(function () {
    Route::get('/flights/pay', [FlutterwavePaymentController::class, 'initiate'])->name('flights.pay');
    Route::get('/flights/payment/callback', [FlutterwavePaymentController::class, 'callback'])->name('flutterwave.callback');
});


Route::middleware('auth:sanctum')->group(function () {
    // Dashboard API endpoints
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    // Cart management
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::patch('/update/{id}', [CartController::class, 'update']);
        Route::delete('/remove/{id}', [CartController::class, 'remove']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });
    
    // Wishlist management
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/add', [WishlistController::class, 'add']);
        Route::delete('/remove/{id}', [WishlistController::class, 'remove']);
        Route::post('/move-to-cart/{id}', [WishlistController::class, 'moveToCart']);
    });
    
    // Miles management
    Route::prefix('miles')->group(function () {
        Route::get('/balance', [MilesController::class, 'balance']);
        Route::get('/history', [MilesController::class, 'history']);
        Route::post('/redeem', [MilesController::class, 'redeem']);
        Route::post('/transfer', [MilesController::class, 'transfer']);
    });
    
    // Profile management
    Route::prefix('profile')->group(function () {
        Route::patch('/update', [ProfileController::class, 'update']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
        Route::patch('/notifications', [ProfileController::class, 'updateNotifications']);
        Route::patch('/preferences', [ProfileController::class, 'updatePreferences']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
    });
    
    // Payment methods
    Route::prefix('payment-methods')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index']);
        Route::post('/', [PaymentMethodController::class, 'store']);
        Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
        Route::patch('/{id}/default', [PaymentMethodController::class, 'setDefault']);
    });
});


// 🚀 Test API
Route::get('/test-api', fn () => response()->json(['message' => 'API is working']));
Route::get('/test-flight-search', [\App\Http\Controllers\Api\TestFlightController::class, 'search']);
use App\Http\Controllers\PaymentController;
Route::get('/test-split/{bookingReference}', [PaymentController::class, 'testSplitPayment']);

