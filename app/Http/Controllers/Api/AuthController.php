<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login untuk Akun Pengelola / Warga
     *
     * @unauthenticated
     * @body username string required Username akun
     * @body password string required Kata sandi akun
     *
     * @response array{status: string, pesan: string, data: array{user: array{}, token: string}}
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('posyandu')->where('username', $request->username)->first();

        // Cek apakah user ada dan password benar
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau kata sandi salah.'],
            ]);
        }

        // Generate Token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout akun
     *
     * @authenticated
     */
    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Logout berhasil'
        ]);
    }
}
