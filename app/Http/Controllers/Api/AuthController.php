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

    public function updateAkunWarga(Request $request)
    {
        $user = $request->user();

        // 1. Validasi: Tambahkan pengecekan UNIQUE agar nama tidak boleh sama dengan warga lain
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'current_password' => 'required|current_password',
            'new_password' => 'nullable|min:6|confirmed'
        ], [
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'username.unique' => 'Username ini sudah dipakai orang lain. Silakan tambahkan angka/kata lain (misal: Ambarukmo123).'
        ]);

        // 2. Update Nama Tampilan DAN Username Login-nya!
        $user->name = $request->username;
        $user->username = $request->username; // <--- BARIS SAKTI AGAR LOGIN BISA PAKAI NAMA BARU

        // 3. Update password JIKA warga mengisinya
        if ($request->filled('new_password')) {
            $user->password = bcrypt($request->new_password);
        }
        $user->save();

        // 4. SINKRONISASI KE TABEL WARGA (AGAR DASBOR KADER BERUBAH)
        $keluarga = \App\Models\WargaKeluarga::where('user_id', $user->id)->first();
        if ($keluarga) {
            $keluarga->nama_kepala_keluarga = $request->username;
            $keluarga->save();

            \App\Models\WargaDewasa::where('keluarga_id', $keluarga->id)
                ->where('jenis_kelamin', 'L')
                ->update(['nama_lengkap' => $request->username]);
        }

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Data akun berhasil diperbarui!'
        ]);
    }
}
