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
        $sortBy = $request->input('sort', 'newest');
        $paginate = $request->input('paginate', 12);
        $userId = auth('sanctum')->id();

        $stories = Story::with(['user' => function ($query) {
            $query->with('image');
        }, 'category', 'images', 'bookmarks' => function ($query) use ($userId) {
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
                if ($sortBy === 'popular') {
                    return $query->orderBy('bookmark_count', 'desc');
                } elseif ($sortBy === 'a-z') {
                    return $query->orderBy('title', 'asc');
                } elseif ($sortBy === 'z-a') {
                    return $query->orderBy('title', 'desc');
                } else {
                    return $query->latest();
                }
            })
            ->paginate($paginate);

        return response()->json([
            "data" => [
                "stories" => $stories,
                // "type" => $sortBy
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'string',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
        ]);

        $slug = Str::slug($request->title);

        if (Story::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => 'The title is already in use. Please choose a different title.',
                'errors' => [
                    'title' => ['The title is already in use. Please choose a different title.'],
                ],
            ], 422);
        }

        $story = Story::create([
            'title' => $request->title,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        foreach ($request->images as $path) {
            $story->images()->create(['path' => $path]);
        }

        return response()->json([
            'message' => 'Story created successfully!',
            // 'story' => $story->load('images')
        ], 201);
    }

    public function show($slug)
    {
        $userId = auth('sanctum')->id();

        $story = Story::with(['user' => function ($query) {
            $query->with('image');
        }, 'category', 'images', 'bookmarks' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->where('slug', $slug)->first();

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        return response()->json([
            "data" => $story,
        ]);
    }

    public function update(Request $request, Story $story)
    {
        $slug = Str::slug($request->title);

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        if ($story->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $existingStory = Story::where('slug', $slug)->where('id', '!=', $story->id)->exists();

        if ($existingStory) {
            return response()->json([
                'message' => 'The title is already in use. Please choose a different title.',
                'errors' => [
                    'title' => ['The title is already in use. Please choose a different title.'],
                ],
            ], 422);
        }

        $request->validate([
            'title' => 'required|string',
            'images' => 'required|array',
            'images.*' => 'string',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|string',
        ]);

        $story->update([
            'title' => $request->title,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        if ($request->has('images')) {
            $story->images()->delete();

            foreach ($request->images as $path) {
                $story->images()->create(['path' => $path]);
            }
        }

        return response()->json($story);
    }

    public function my_stories(Request $request)
    {
        $keyword = $request->input('keyword');
        $categoryName = $request->input('category');
        $sortBy = $request->input('sort_by', 'newest');
        $userId = auth()->id();
        // $userId = auth('sanctum')->id();

        $stories = Story::with(['user' => function ($query) {
            $query->with('image');
        }, 'category', 'images', 'bookmarks' => function ($query) use ($userId) {
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
                if ($sortBy === 'popular') {
                    return $query->orderBy('bookmark_count', 'desc');
                } elseif ($sortBy === 'a-z') {
                    return $query->orderBy('title', 'asc');
                } elseif ($sortBy === 'z-a') {
                    return $query->orderBy('title', 'desc');
                } else {
                    return $query->latest();
                }
            })
            ->where('user_id', $userId)
            // ->whereHas('bookmarks', function ($query) use ($userId) {
            //     $query->where('user_id', $userId);
            // })
            ->paginate(10);

        return response()->json([
            "data" => [
                "stories" => $stories
            ]
        ]);

        // if (auth('sanctum')->check()) {
        //     return response()->json(['message' => auth('sanctum')->id()]);
        // } else {
        //     return response()->json(['message' => auth('sanctum')->id()]);
        // }
    }

    public function getSimilarStories($slug)
    {
        $userId = auth('sanctum')->id();

        $story = Story::where('slug', $slug)->first();

        if (!$story) {
            return response()->json(['message' => 'Story not found'], 404);
        }

        $stories = Story::with(['user' => function ($query) {
            $query->with('image');
        }, 'category', 'images', 'bookmarks' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->where('category_id', $story->category_id)
            ->where('id', '!=', $story->id)
            ->inRandomOrder()
            ->paginate(3);

        return response()->json([
            "data" => [
                "stories" => $stories
            ]
        ]);
    }

    public function destroy(Story $story)
    {
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
