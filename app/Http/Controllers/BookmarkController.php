<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $categoryName = $request->input('category');
        $sortBy = $request->input('sort_by', 'newest');
        $userId = auth()->id();

        $stories = Story::with(['user', 'category', 'images', 'bookmarks' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
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
            ->whereHas('bookmarks', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->paginate(10);

        return response()->json([
            "data" => [
                "stories" => $stories
            ]
        ], 200);
    }

    public function store(Story $story)
    {
        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        $bookmark = $story->bookmarks()->where('user_id', auth()->id())->first();

        $message = "Successfully added story to bookmarks";

        if ($bookmark) {
            $bookmark->delete();
            $story->decrement('bookmark_count');
            $message = "Successfully remove story from bookmarks";
        } else {
            $story->bookmarks()->create([
                'user_id' => auth()->id(),
            ]);
            $story->increment('bookmark_count');
        }

        return response()->json(['message' => $message], 200);
    }
}
