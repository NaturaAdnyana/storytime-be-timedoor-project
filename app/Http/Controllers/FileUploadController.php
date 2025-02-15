<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload_image(UploadImageRequest $request)
    {

        $image = $request->file('file');
        $type = $request->input('type');

        $imageName = time() . '.' . $image->extension();

        $folder = ($type === 'profile') ? 'photos/profiles' : 'photos/story';

        $image->storeAs($folder, $imageName, 'public');

        // return response()->json(['url' => Storage::url("$folder/$imageName")], 200);
        // $avatar = asset($request->user()->avatar);
        return response()->json(['url' => asset(Storage::url("$folder/$imageName"))], 200);
    }
}
