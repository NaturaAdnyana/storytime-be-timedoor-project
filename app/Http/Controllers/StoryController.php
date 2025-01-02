<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoryController extends Controller
{
    // response as json
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $categoryName = $request->input('category');
        $sortBy = $request->input('sort_by', 'newest');

        $stories = Story::with('category')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('title', 'like', "%$keyword%");
            })
            ->when($categoryName, function ($query) use ($categoryName) {
                return $query->whereHas('category', function ($query) use ($categoryName) {
                    return $query->where('name', $categoryName);
                });
            })
            ->when($sortBy, function ($query) use ($sortBy) {
                if ($sortBy === 'newest') {
                    return $query->orderBy('created_at', 'desc');
                } elseif ($sortBy === 'popular') {
                    return $query->orderBy('bookmark_count', 'desc');
                } elseif ($sortBy === 'a-z') {
                    return $query->orderBy('title', 'asc');
                } elseif ($sortBy === 'z-a') {
                    return $query->orderBy('title', 'desc');
                }
            })
            ->paginate(10);

        return response()->json($stories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'cover_image_url' => 'required|url',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
        ]);

        $story = Story::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'cover_image_url' => $request->cover_image_url,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return response()->json($story);
    }
}
