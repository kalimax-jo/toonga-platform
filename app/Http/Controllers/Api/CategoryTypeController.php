<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryType;

class CategoryTypeController extends Controller
{
    public function index()
    {
        return response()->json(CategoryType::all());
    }
}
