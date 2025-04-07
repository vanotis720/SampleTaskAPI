<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * APIs for managing authentication
 */
class AuthController extends Controller
{
    use ApiResponser;

    /**
     * Register a new user
     * 
     * This endpoint registers a new user and returns an access token.
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user (min 8 characters). Example: password123
     * @bodyParam password_confirmation string required The password confirmation. Example: password123
     *
     * @response 201 {
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "created_at": "2023-01-01T00:00:00.000000Z",
     *    "updated_at": "2023-01-01T00:00:00.000000Z"
     *  },
     *  "access_token": "1|laravel_sanctum_token_example",
     *  "token_type": "Bearer"
     * }
     * 
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *    "email": [
     *      "The email has already been taken."
     *    ]
     *  }
     * }
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
            'User registered successfully',
            201
        );
    }

    /**
     * Login user
     * 
     * This endpoint logs in a user and returns an access token.
     *
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password123
     *
     * @response {
     *  "user": {
     *    "id": 1,
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "created_at": "2023-01-01T00:00:00.000000Z",
     *    "updated_at": "2023-01-01T00:00:00.000000Z"
     *  },
     *  "access_token": "1|laravel_sanctum_token_example",
     *  "token_type": "Bearer"
     * }
     * 
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *    "email": [
     *      "The provided credentials are incorrect."
     *    ]
     *  }
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->errorResponse(
                'The provided credentials are incorrect.',
                422
            );
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ],
            'User logged in successfully'
        );
    }

    /**
     * Logout user
     * 
     * This endpoint logs out the currently authenticated user by revoking their token.
     *
     * @authenticated
     * 
     * @response {
     *  "message": "Logged out successfully"
     * }
     * 
     * @response 401 {
     *  "message": "Unauthenticated."
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            null,
            'Logged out successfully'
        );
    }
}
