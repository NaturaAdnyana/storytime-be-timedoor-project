<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoryController extends Controller
{
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

    public function show($slug)
    {
        $story = Story::where('slug', $slug)->first();

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        return response()->json($story);
    }

    public function update(Request $request, $slug)
    {
        $story = Story::where('slug', $slug)->first();

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        if ($story->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'title' => 'required|string',
            'cover_image_url' => 'required|url',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
        ]);

        $story->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'cover_image_url' => $request->cover_image_url,
            'category_id' => $request->category_id,
            'content' => $request->content,
        ]);

        return response()->json($story);
    }

    public function bookmark($slug)
    {
        $story = Story::where('slug', $slug)->first();

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        $bookmark = $story->bookmarks()->where('user_id', auth()->id())->first();

        if ($bookmark) {
            $bookmark->delete();
            $story->decrement('bookmark_count');
        } else {
            $story->bookmarks()->create([
                'user_id' => auth()->id(),
            ]);
            $story->increment('bookmark_count');
        }

        return response()->json(['message' => 'Success']);
    }

    public function destroy($slug)
    {
        $story = Story::where('slug', $slug)->first();

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        if ($story->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $story->delete();

        return response()->json(['message' => 'Success']);
    }
}
