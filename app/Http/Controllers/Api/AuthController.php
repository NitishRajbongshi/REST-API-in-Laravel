<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validation
        $credentials = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        // 2. Create User
        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password']),
        ]);

        // 3. Generate Sanctum Token
        $token = $user->createToken('myAppToken')->plainTextToken;

        // 4. Prepare Response Data
        $response = [
            'user' => $user,
            'token' => $token
        ];

        // 5. Return JSON Response with 201 Status
        return response()->json($response, 201);
    }

    /**
     * Handle user login and token issuance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return Response::json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user->tokens()->delete(); // Revoke all tokens...
        $token = $user->createToken('myAppToken')->plainTextToken;

        return Response::json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully'
        ], 200);
    }
}
