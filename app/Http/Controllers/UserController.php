<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function login(Request $request)
    {
        // Validate input fields
        $validatedData = $request->validate([
            'username_or_email' => 'required',
            'password' => 'required',
        ], [
            'username_or_email.required' => 'The username or email field is required.',
            'password.required' => 'The password field is required.',
        ]);

        $user = User::where('email', $validatedData['username_or_email'])
            ->orWhere('username', $validatedData['username_or_email'])
            ->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                "status" => "error",
                "message" => "The provided credentials are incorrect.",
            ], 401);
        }

        $token = $user->createToken("authToken")->plainTextToken;

        // $cookie = cookie('token', $token, 60 * 24 * 7, '/', null, true, true, false, 'None');

        return response()->json([
            "status" => "success",
            "message" => "Login successful.",
            "data" => [
                // "user" => [
                //     "id" => $user->id,
                //     "username" => $user->username,
                //     "name" => $user->name,
                //     "email" => $user->email,
                // ],
                "token" => $token
            ]
        ], 200);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[A-Za-z0-9._]+$/'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed', 'max:255', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            ],
            [
                'name.required' => "The name field is required.",
                'username.required' => "The username field is required",
                'username.regex' => 'The username may only contain letters, numbers, dots, and underscores.',
                'email.required' => 'The email field is required.',
                'email.email' => 'The email must be a valid email address.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
            ]
        );

        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken("tokenName")->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Register successful.",
            "data" => [
                // "user" => [
                //     "id" => $user->id,
                //     "username" => $user->username,
                //     "name" => $user->name,
                //     "email" => $user->email,
                // ],
                "token" => $token
            ]
        ], 201);
    }

    public function user(Request $request)
    {
        // $avatar = asset($request->user()->avatar);

        // return JsonResource::make([
        //     "data" => [
        //         'message' => 'User fetched successfully.',
        //         "user" => [
        //             'id' => $request->user()->id,
        //             'name' => $request->user()->name,
        //             'username' => $request->user()->username,
        //             'email' => $request->user()->email,
        //             'bio' => $request->user()->bio,
        //             'avatar' => $request->user()->avatar ? $avatar : null,
        //         ],
        //     ]
        // ]);

        return response()->json([
            "data" => [
                "user" => $request->user()
            ]
        ]);
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                // 'username' => ['string', 'max:255', 'unique:users', 'regex:/^[A-Za-z0-9._]+$/'],
                // 'email' => ['string', 'email', 'max:255', 'unique:users'],
                'bio' => ['string', 'max:255'],
                'avatar' => ['string', 'max:255'],
                'old_password' => ['string', 'min:8', 'max:255'],
                'new_password' => ['string', 'min:8', 'confirmed', 'max:255', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            ],
            [
                'name.required' => "The name field is required.",
                // 'username.regex' => 'The username may only contain letters, numbers, dots, and underscores.',
                // 'email.email' => 'The email must be a valid email address.',
                'old_password.min' => 'The password must be at least 8 characters.',
                'new_password.min' => 'The password must be at least 8 characters.',
                'new_password.confirmed' => 'The password confirmation does not match.',
                'new_password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
            ]
        );

        $user = $request->user();

        if ($request->filled('old_password') && !Hash::check($request->old_password, $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($request->filled('old_password') && !$request->filled('new_password')) {
            throw ValidationException::withMessages([
                'new_password' => ['The new password is required when the old password is provided.'],
            ]);
        }

        if ($request->filled('name')) {
            $user->name = $validatedData['name'];
        }

        if ($request->filled('bio')) {
            $user->bio = $validatedData['bio'];
        }

        if ($request->filled('avatar')) {
            $user->avatar = $validatedData['avatar'];
        }

        if ($request->filled('new_password')) {
            $user->password = Hash::make($validatedData['new_password']);
        }

        $user->save();

        return response()->json([
            "data" => [
                'message' => 'Profile updated successfully.',
                // 'user' => $user,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "data" => [
                "message" => "Logged out successfully"
            ]
        ]);
    }
}
