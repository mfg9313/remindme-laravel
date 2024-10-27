<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle user login and issue tokens.
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'ok' => false,
                'err' => 'ERR_INVALID_CREDS',
                'msg' => 'incorrect username or password',
            ], 401);
        }

        // Create Access Token, Short-lived
        $accessToken = $user->createToken('access_token')->plainTextToken;

        // Create Refresh Token
        $refreshToken = Str::uuid()->toString();
        $expiresAt = Carbon::now()->addDays(30);

        RefreshToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $refreshToken),
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ],
        ]);
    }


    public function refresh(Request $request): \Illuminate\Http\JsonResponse
    {
        $refreshToken = $request->bearerToken();

        if (!$refreshToken) {
            return response()->json([
                'ok' => false,
                'err' => 'ERR_INVALID_REFRESH_TOKEN',
                'msg' => 'invalid refresh token',
            ], 401);
        }

        $hashedToken = hash('sha256', $refreshToken);

        $tokenRecord = RefreshToken::where('token', $hashedToken)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$tokenRecord) {
            return response()->json([
                'ok' => false,
                'err' => 'ERR_INVALID_REFRESH_TOKEN',
                'msg' => 'invalid refresh token',
            ], 401);
        }

        $user = $tokenRecord->user;

        // Create new Access Token
        $accessToken = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'ok' => true,
            'data' => [
                'access_token' => $accessToken,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'ok' => true,
                'msg' => 'Logged out successfully.',
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'ok' => false,
                'msg' => 'An error occurred during logout. Please try again.',
            ], 500);
        }
    }
}
