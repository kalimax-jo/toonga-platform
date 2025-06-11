<?php

namespace App\Http\Controllers;

use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $items = WishlistItem::where('user_id', $user->id)->get();
        return response()->json($items);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'nullable|integer',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'currency' => 'required|string|size:3',
            'quantity' => 'nullable|integer',
            'image' => 'nullable|string',
            'url' => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $item = WishlistItem::create($data);
        return response()->json($item);
    }

    public function remove($id)
    {
        $user = auth()->user();
        WishlistItem::where('user_id', $user->id)->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function moveToCart($id)
    {
        $user = auth()->user();
        $item = WishlistItem::where('user_id', $user->id)->where('id', $id)->first();
        if ($item) {
            $item->delete();
        }
        return response()->json(['success' => true]);
    }
}
