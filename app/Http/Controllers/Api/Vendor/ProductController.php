<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'commission' => 'required|numeric|min:0|max:100',
            'additional_data' => 'nullable|array',
        ]);

        $product = new Product();
        $product->vendor_id = auth()->id();
        $product->title = $request->title;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->commission_percentage = $request->commission;
        $product->additional_data = $request->additional_data ?? null;
        $product->is_approved = false;
        $product->save();

        return response()->json(['message' => 'Product submitted successfully']);
    }
}