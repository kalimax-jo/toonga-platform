<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Category;

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

// Get all vendors with categories
Route::get('/vendors', function () {
    return User::with('categories')->where('role', 'vendor')->get();
});

// Create a new vendor
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

// Update vendor
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

// Delete vendor
Route::delete('/vendors/{id}', function ($id) {
    $vendor = User::where('role', 'vendor')->findOrFail($id);
    $vendor->categories()->detach();
    $vendor->delete();

    return response()->json(['message' => 'Vendor deleted']);
});

// 🗂️ Category Management

// Get all categories
Route::get('/categories', function () {
    return Category::all();
});

// Create new category
Route::post('/categories', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:categories',
    ]);
    return Category::create($validated);
});

// Update category
Route::put('/categories/{id}', function (Request $request, $id) {
    $category = Category::findOrFail($id);
    $category->update($request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $id,
    ]));
    return $category;
});

// Delete category
Route::delete('/categories/{id}', function ($id) {
    $category = Category::findOrFail($id);
    $category->delete();
    return response()->json(['message' => 'Category deleted']);
});

// 🚀 Test API
Route::get('/test-api', fn () => response()->json(['message' => 'API is working']));
