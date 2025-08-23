<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryApiController extends Controller
{
    /**
     * GET /api/categories
     */
    public function index()
    {
        // return all categories
        return response()->json(Category::all());
    }

    /**
     * GET /api/categories/{id}
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }
}
