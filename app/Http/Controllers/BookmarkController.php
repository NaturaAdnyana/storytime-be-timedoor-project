<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function store($id)
    {
        $story = Story::find($id);

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

        return response()->json(['message' => 'Success'], 200);
    }
}
