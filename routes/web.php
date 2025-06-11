<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\FlightBookingController as BookingController;

// 🌐 Public Homepage
Route::get('/', function () {
    return Inertia::render('Public/WelcomePage');
});

// 🔐 Authenticated Dashboard
Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->middleware(['auth', 'verified'])->name('dashboard');

// 👤 Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Booking routes
    Route::get('/bookings/{reference}/payment', [App\Http\Controllers\PaymentController::class, 'initiatePayment'])->name('bookings.payment');
    Route::get('/bookings/{reference}/success', [App\Http\Controllers\Api\BookingController::class, 'success'])->name('bookings.success');
    Route::get('/bookings/failed', [App\Http\Controllers\Api\BookingController::class, 'failed'])->name('bookings.failed');
    Route::post('/api/bookings/create-pending', [App\Http\Controllers\Api\BookingController::class, 'createPending'])->name('bookings.create-pending');
    
});

// Payment callback (no auth needed) - ADD THIS OUTSIDE ANY MIDDLEWARE
Route::get('/payment/callback', [App\Http\Controllers\PaymentController::class, 'handleCallback'])->name('payment.callback');

// 🧑‍💼 Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        abort_unless(Auth::user()->role === 'admin', 403);
        $vendors = User::where('role', 'vendor')->get();
        $categories = Category::all();
        return Inertia::render('Admin/Dashboard', compact('vendors', 'categories'));
    })->name('admin.dashboard');

    Route::get('/admin/vendors/pending', function () {
        $pendingVendors = User::where('role', 'vendor')->where('is_approved', false)->with('categories')->get();
        return Inertia::render('Admin/VendorApprovals', compact('pendingVendors'));
    });

    Route::post('/admin/vendors/{id}/approve', function ($id) {
        $vendor = User::findOrFail($id);
        $vendor->is_approved = true;
        $vendor->save();
        return redirect()->back();
    });

    Route::delete('/admin/vendors/{id}', function ($id) {
        $vendor = User::findOrFail($id);
        $vendor->categories()->detach();
        $vendor->delete();
        return redirect()->back();
    });

    Route::get('/admin/vendors', fn() => Inertia::render('Admin/VendorManager'));
    Route::get('/admin/categories', fn() => Inertia::render('Admin/CategoryManager'));
    Route::get('/admin/products', fn() => Inertia::render('Admin/ProductManager'));
});

// 👨‍💼 Vendor Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/vendor/dashboard', function () {
        $user = auth()->user();
        abort_if($user->role !== 'vendor' || !$user->is_approved, 403);
        return Inertia::render('Vendor/Dashboard');
    });

    Route::get('/vendor/products/create', function () {
        $user = auth()->user();
        abort_if($user->role !== 'vendor' || !$user->is_approved, 403);
        return Inertia::render('Vendor/CreateProduct', [
            'categories' => $user->categories,
        ]);
    });

    Route::post('/vendor/products', function (Request $request) {
        $user = auth()->user();
        abort_if($user->role !== 'vendor' || !$user->is_approved, 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'commission' => 'required|integer|min:1|max:100',
            'preview_images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'product_video' => 'nullable|mimes:mp4,mov,avi,webm|max:10240',
        ]);

        if (!$user->categories->contains('id', $request->category_id)) {
            abort(403, 'You do not have access to this category');
        }

        $images = [];
        if ($request->hasFile('preview_images')) {
            foreach ($request->file('preview_images') as $file) {
                $images[] = $file->store('products/images', 'public');
            }
        }

        $videoPath = null;
        if ($request->hasFile('product_video')) {
            $videoPath = $request->file('product_video')->store('products/videos', 'public');
        }

        Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'vendor_id' => $user->id,
            'commission' => $request->commission,
            'is_approved' => false,
            'images' => json_encode($images),
            'video' => $videoPath,
        ]);

        return redirect('/vendor/dashboard');
    });

    Route::get('/vendor/products', function () {
        $user = auth()->user();
        abort_if($user->role !== 'vendor', 403);

        $products = $user->products()->with('category')->get()->groupBy('is_approved');

        return Inertia::render('Vendor/VendorProductList', [
            'approved' => $products[1] ?? [],
            'pending' => $products[0] ?? []
        ]);
    });
});

// 📓 Sales Reviewer / Product Approvals
Route::middleware(['auth'])->group(function () {
    Route::get('/sales/dashboard', function () {
        abort_if(Auth::user()->role !== 'admin', 403);
        $pendingProducts = Product::with('category', 'vendor')
            ->where('is_approved', false)
            ->get();

        return Inertia::render('Sales/Dashboard', compact('pendingProducts'));
    });

    Route::post('/sales/products/{id}/approve', function ($id) {
        $product = Product::findOrFail($id);
        $product->is_approved = true;
        $product->save();
        return redirect()->back();
    });

    Route::post('/sales/products/{id}/reject', function ($id) {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->back();
    });

});


Route::get('/flights', function () {
    return Inertia::render('Public/FlightsPage');
});


Route::get('/hotels', function () {
    return Inertia::render('Public/HotelsPage');
});

Route::get('/cars', function () {
    return Inertia::render('Public/CarsPage');
});

Route::get('/food', function () {
    return Inertia::render('Public/FoodPage');
});

Route::get('/furniture', function () {
    return Inertia::render('Public/FurniturePage');
});


Route::get('/electronics', function () {
    $products = \App\Models\Product::with('category', 'vendor')
        ->whereHas('category.type', function ($query) {
            $query->where('name', 'Electronics');
        })
        ->where('is_approved', true)
        ->get();

    return Inertia::render('Public/ElectronicsPage', [
        'products' => $products,
    ]);
});




// 🛠 Debug Helper
Route::get('/whoami', fn () => auth()->user());

use App\Http\Controllers\FlutterwavePaymentController;

Route::middleware(['auth'])->group(function () {
    Route::get('/flights/pay', [FlutterwavePaymentController::class, 'initiate'])->name('flights.pay');
    Route::get('/flights/payment/callback', [FlutterwavePaymentController::class, 'callback'])->name('flutterwave.callback');
});

use App\Http\Controllers\Api\CartController;

Route::middleware(['web', 'auth'])->post('/api/cart/flight', [CartController::class, 'addFlight']);


Route::get('/flights/checkout', function () {
    $flight = session('cart.flight');

    if (!$flight) {
        return redirect('/flights')->with('error', 'No flight selected.');
    }

    return Inertia::render('Flights/CheckoutPage', [
        'flightData' => $flight,
    ]);
})->middleware('auth');

Route::get('/debug/session', function () {
    return response()->json([
        'cart' => session('cart'),
        'flight' => session('cart.flight'),
        'all' => session()->all(),
    ]);
});

// Add this at the end of your web.php file
Route::get('/test-amadeus', function() {
    return response()->json([
        'api_key' => env('AMADEUS_API_KEY'),
        'api_secret' => env('AMADEUS_API_SECRET'),
        'key_length' => strlen(env('AMADEUS_API_KEY') ?? ''),
        'secret_length' => strlen(env('AMADEUS_API_SECRET') ?? ''),
        'app_env' => env('APP_ENV'),
        'app_debug' => env('APP_DEBUG'),
    ]);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cart', [DashboardController::class, 'cart'])->name('dashboard.cart');
    Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/dashboard/miles', [DashboardController::class, 'miles'])->name('dashboard.miles');
    Route::get('/dashboard/wishlist', [DashboardController::class, 'wishlist'])->name('dashboard.wishlist');
    Route::get('/dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
});


use App\Http\Controllers\PaymentController;

// Test split payment route
Route::get('/test-split/{bookingReference}', [PaymentController::class, 'testSplitPayment']);
// Add these routes for cart functionality
Route::middleware(['auth'])->group(function () {
    // Existing dashboard routes...
    
    // Cart management routes
    Route::delete('/bookings/{booking}', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::delete('/cart/clear', [DashboardController::class, 'clearCart'])->name('cart.clear');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
});



require __DIR__.'/auth.php';