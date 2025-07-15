<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string',
            'username' => 'required|alpha_dash|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Some fields are invalid',
                'error' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Register successfully',
            'data' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|alpha_dash',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Some fields are invalid',
                'error' => $validator->errors()
            ], 400);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = hash('sha256', Str::random(60));

        PersonalAccessToken::create([
            'user_id' => $user->id,
            'token'   => $token
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Login successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        PersonalAccessToken::where('token', $token)->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Logout successfully'
        ], 200);
    }

    public function me()
    {
        return response()->json([
            'status' => 200,
            'message' => 'Data fetched successfully',
            'data' => auth()->user()
        ], 200);
    }
}
