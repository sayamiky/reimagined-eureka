<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Jobs\SendWelcomeEmailJob;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        SendWelcomeEmailJob::dispatch($user);

        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'data' => $user,
            'token' => $token,
            'message' => 'User registered successfully'
        ], 201);
    }

    function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // revoke previous tokens
        // $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;
        return response()->json([
            'data' => $user,
            'token' => $token,
            'message' => 'User login successfully'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    function test() {
        Mail::to('your_test_mail@gmail.com')->send(new WelcomeMail(User::first()));
    }
}
