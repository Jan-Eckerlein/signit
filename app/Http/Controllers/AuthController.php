<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
			'handler' => 'required|string|in:token,session',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

		
		if ($request->handler === 'token') {
			$token = $user->createToken('auth_token')->plainTextToken;
		} else {
			Auth::login($user);
		}

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
			'token' => $token ?? false,
        ], 201);
    }

    /**
     * Login user
     */
	public function login(Request $request): JsonResponse
	{
		$request->validate([
			'email' => 'required|string|email',
			'password' => 'required|string',
			'handler' => 'required|string|in:token,session',
		]);

		$user = User::where('email', $request->email)->first();

		if (!$user || !Hash::check($request->password, $user->password)) {
			throw ValidationException::withMessages([
				'email' => ['The provided credentials are incorrect.'],
			]);
		}

		if ($request->handler === 'token') {
			$token = $user->createToken('auth_token')->plainTextToken;
		} else {
			Auth::login($user);
		}

		return response()->json([
			'message' => 'User logged in successfully',
			'user' => new UserResource($user),
			'token' => $token ?? false,
		]);
	}

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete the current token if using token authentication
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        } else {
            // Fallback to session logout
            Auth::logout();
        }

        return response()->json([
            'message' => 'User logged out successfully',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
        ]);

        $user->update($request->only('name'));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Refresh session
     */
    public function refresh(Request $request): JsonResponse
    {
		if (!$request->hasSession()) {
			return response()->json([
				'message' => 'No session found',
			], 400);
		}
		
        $user = $request->user();
        
        // Regenerate session
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Session refreshed successfully',
            'user' => new UserResource($user),
        ]);
    }
} 