<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|string',
        ]);

        $image = $request->file('file');
        $type = $request->input('type');

        $imageName = time() . '.' . $image->extension();

        $folder = ($type === 'profile') ? 'photos/profiles' : 'photos/stories';

        $image->storeAs($folder, $imageName);

        return response()->json(['url' => Storage::url("$folder/$imageName")]);
    }
}
