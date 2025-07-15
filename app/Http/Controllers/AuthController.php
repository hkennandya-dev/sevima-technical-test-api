<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required',
            'username'    => 'required|alpha_dash|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'username'    => $request->input('username'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'username'    => 'required|alpha_dash',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->input('username'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = base64_encode(str()->random(40));

        return response()->json([
            'user'  => $user,
            'token' => $token
        ]);
    }
}