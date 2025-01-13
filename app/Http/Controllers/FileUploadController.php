<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload_image(Request $request)
    {
        $validatedData = $request->validate(
            [
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'type' => 'required|string|in:profile,story',
            ],
            [
                'file.required' => 'The image file is required.',
                'file.image' => 'The uploaded file must be an image.',
                'file.mimes' => 'The image must be in jpeg, png, jpg, gif, or webp format.',
                'file.max' => 'The image size must not exceed 2MB.',
                'type.in' => 'The type must be either "profile" or "story".',
            ]
        );

        $image = $validatedData['file'];
        $type = $validatedData['type'];

        $imageName = time() . '.' . $image->extension();

        $folder = ($type === 'profile') ? 'photos/profiles' : 'photos/story';

        $image->storeAs($folder, $imageName, 'public');

        return response()->json(['url' => Storage::url("$folder/$imageName")], 200);
    }
}
