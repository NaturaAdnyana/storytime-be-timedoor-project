<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['username_or_email'])
            ->orWhere('username', $credentials['username_or_email'])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $loginAttempt = Auth::attempt([
            'email' => filter_var($credentials['username_or_email'], FILTER_VALIDATE_EMAIL) ? $credentials['username_or_email'] : $user->email,
            'password' => $credentials['password'],
        ]);

        if ($loginAttempt) {
            $request->session()->regenerate();

            $expirationTime = Carbon::now()->addDays(7)->timestamp;
            $authExpirationCookie = Cookie::make('auth_expiration', $expirationTime, env('SESSION_LIFETIME', 420), '/', null, false, false);

            return response()->json([
                "status" => "success",
                "message" => "Login successful.",
            ], 200)->withCookie($authExpirationCookie);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            Auth::login($user);

            $request->session()->regenerate();

            $expirationTime = Carbon::now()->addDays(7)->timestamp;
            $authExpirationCookie = Cookie::make('auth_expiration', $expirationTime, env('SESSION_LIFETIME', 420), '/', null, false, false);

            return response()->json([
                "status" => "success",
                "message" => "Register successful.",
            ], 201)->withCookie($authExpirationCookie);
        }

        return response()->json(['message' => 'Register Failed'], 400);
    }

    public function user(Request $request)
    {
        return response()->json([
            "data" => [
                "user" => $request->user()
            ]
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $validatedData = $request->validated();
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
            ]
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $forgetAuthExpiration = Cookie::forget('auth_expiration');

        return response()->json([
            "data" => ["message" => "Logged out successfully"]
        ])->withCookie($forgetAuthExpiration);
    }
}
