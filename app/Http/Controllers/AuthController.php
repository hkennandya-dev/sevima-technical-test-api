<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
            'username' => 'required|alpha_dash|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|alpha_dash',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = hash('sha256', Str::random(60));

        PersonalAccessToken::create([
            'user_id' => $user->id,
            'token' => $token
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }
}
