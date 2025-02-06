<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $excludedSlugs = $request->all();

        $categories = Category::when(!empty($excludedSlugs), function ($query) use ($excludedSlugs) {
            $query->whereNotIn('slug', $excludedSlugs);
        })->get();

        return response()->json([
            "data" => [
                "categories" => $categories
            ]
        ]);
    }
}
