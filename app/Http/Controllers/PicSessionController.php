<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Login/logout PIC dari halaman beranda (JSON, dipanggil lewat fetch)
class PicSessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            return response()->json(['message' => 'Email atau password salah.'], 422);
        }

        $user = Auth::user();

        // Akun tanpa PIC (dan bukan Super Admin) tidak punya project untuk dikelola
        if (! $user->isSuperAdmin() && $user->pic === null) {
            Auth::logout();
            $request->session()->invalidate();

            return response()->json(['message' => 'Akun ini belum ditautkan ke PIC. Hubungi admin.'], 422);
        }

        $request->session()->regenerate();

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true]);
    }
}
