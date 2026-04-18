<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $officer = Auth::guard('api')->user();

        if (!$officer->is_active) {
            Auth::guard('api')->logout();
            return response()->json(['error' => 'Account is disabled'], 403);
        }

        return response()->json([
            'token' => $token,
            'officer' => [
                'id' => $officer->id,
                'username' => $officer->username,
                'full_name' => $officer->full_name,
            ],
        ]);
    }

    public function me()
    {
        $officer = Auth::guard('api')->user();
        return response()->json([
            'id' => $officer->id,
            'username' => $officer->username,
            'full_name' => $officer->full_name,
        ]);
    }

    public function logout()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Logged out']);
    }

    public function refresh()
    {
        $token = Auth::guard('api')->refresh();
        return response()->json(['token' => $token]);
    }
}
