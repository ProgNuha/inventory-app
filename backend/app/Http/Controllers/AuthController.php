<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $data['username'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Username atau password salah'], 422);
        }

        if ($user->is_locked) {
            return response()->json(['message' => 'Akun Anda terkunci, hubungi admin'], 403);
        }

        return response()->json([
            'token' => $user->createToken('auth')->plainTextToken,
            'user' => $user->only(['id', 'username', 'name', 'email', 'role']),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->only(['id', 'username', 'name', 'email', 'role']));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}