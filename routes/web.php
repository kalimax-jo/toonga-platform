<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// 🔐 Dashboard (auth)
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 👤 Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 🧑‍💼 Admin Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        $user = Auth::user();
        abort_unless($user->role === 'admin', 403);

        $vendors = User::where('role', 'vendor')->get();
        $categories = Category::all();

        return Inertia::render('Admin/Dashboard', [
            'vendors' => $vendors,
            'categories' => $categories,
        ]);
    })->name('admin.dashboard');

    // ✅ Vendor approval page
    Route::get('/admin/vendors/pending', function () {
        $pendingVendors = User::where('role', 'vendor')->where('is_approved', false)->with('categories')->get();
        return Inertia::render('Admin/VendorApprovals', [
            'pendingVendors' => $pendingVendors
        ]);
    });

    // Approve vendor
    Route::post('/admin/vendors/{id}/approve', function ($id) {
        $vendor = User::findOrFail($id);
        $vendor->is_approved = true;
        $vendor->save();
        return redirect()->back();
    });

    Route::post('/admin/approve-vendor/{id}', function ($id) {
    $vendor = \App\Models\User::where('role', 'vendor')->findOrFail($id);
    $vendor->is_approved = true;
    $vendor->save();

    return response()->json(['message' => 'Vendor approved']);
})->middleware(['auth']);


    // Optional: delete vendor
    Route::delete('/admin/vendors/{id}', function ($id) {
        $vendor = User::findOrFail($id);
        $vendor->categories()->detach();
        $vendor->delete();
        return redirect()->back();
    });

    // Render UI pages for Vue admin tools
    Route::get('/admin/vendors', fn() => Inertia::render('Admin/VendorManager'));
    Route::get('/admin/categories', fn() => Inertia::render('Admin/CategoryManager'));
});

// 👨‍🔧 Vendor Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/vendor/dashboard', function () {
        $user = auth()->user();
        if ($user->role !== 'vendor' || !$user->is_approved) {
            abort(403);
        }

        return Inertia::render('Vendor/Dashboard');
    });

    Route::get('/vendor/products/create', function () {
        $user = auth()->user();
        if ($user->role !== 'vendor' || !$user->is_approved) {
            abort(403);
        }

        $categories = $user->categories;
        return Inertia::render('Vendor/CreateProduct', [
            'categories' => $categories,
        ]);
    });

    Route::post('/vendor/products', function (Request $request) {
        $user = auth()->user();
        if ($user->role !== 'vendor' || !$user->is_approved) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'commission' => 'required|integer|min:1|max:100',
        ]);

        if (!$user->categories->contains('id', $request->category_id)) {
            abort(403, 'You do not have access to this category');
        }

        Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'vendor_id' => $user->id,
            'commission' => $request->commission,
            'is_approved' => false,
        ]);

        return redirect('/vendor/dashboard');
    });
});

// 📦 Product Approval by Sales Reviewer (admin for now)
Route::middleware(['auth'])->group(function () {
    Route::get('/sales/dashboard', function () {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $pendingProducts = Product::with('category', 'vendor')
            ->where('is_approved', false)
            ->get();

        return Inertia::render('Sales/Dashboard', [
            'pendingProducts' => $pendingProducts
        ]);
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

// 👤 Debug helper
Route::get('/whoami', fn () => auth()->user());

require __DIR__.'/auth.php';
