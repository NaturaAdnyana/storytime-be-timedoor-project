<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload_image(Request $request)
    {
        $validatedData = $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'type' => 'required|string|in:profile,stories',
        ]);

        $image = $validatedData['file'];
        $type = $validatedData['type'];

        $imageName = time() . '.' . $image->extension();

        $folder = ($type === 'profile') ? 'photos/profiles' : 'photos/stories';

        $image->storeAs($folder, $imageName, 'public');

        return response()->json(['url' => Storage::url("$folder/$imageName")], 200);
    }
}
