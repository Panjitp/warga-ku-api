<?php
// app/Http/Controllers/Api/ProfilController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyNewEmail;
use App\Models\User;


class ProfilController extends Controller
{
    /**
     * Mengubah password user yang sedang login.
     */
    public function ubahPassword(Request $request)
    {
        $user = auth()->user();

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'password_saat_ini' => 'required|string',
            'password_baru' => 'required|string|min:8|confirmed', // 'confirmed' akan mencocokkan dengan 'password_baru_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Verifikasi Password Saat Ini
        if (!Hash::check($request->password_saat_ini, $user->password)) {
            return response()->json([
                'errors' => ['password_saat_ini' => ['Password saat ini tidak cocok.']]
            ], 422);
        }

        // 3. Update dengan Password Baru
        $user->update([
            'password' => Hash::make($request->password_baru)
        ]);

        return response()->json(['message' => 'Password berhasil diperbarui!']);
    }

    public function requestUbahEmail(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email_baru' => 'required|email|unique:users,email',
            ],
            [
                'email_baru.required' => 'Email baru wajib diisi.',
                'email_baru.email'    => 'Format email tidak valid.',
                'email_baru.unique'   => 'Email sudah digunakan.',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();
        $newEmail = $request->email_baru;
        $token = Str::random(60);

        // Simpan email baru dan token verifikasi sementara
        $user->update([
            'new_email' => $newEmail,
            'email_verify_token' => $token,
        ]);

        // Buat URL verifikasi (tanpa perlu signed route untuk API sederhana ini)
        $verificationUrl = url('/api/profil/verifikasi-email/' . $token);

        // Kirim email
        Mail::to($newEmail)->send(new VerifyNewEmail($verificationUrl));

        return response()->json(['message' => 'Tautan verifikasi telah dikirim ke alamat email baru Anda.']);
    }

    /**
     * Warga mengklik link verifikasi dari email barunya.
     */
    public function verifikasiEmailBaru($token)
    {
        // Cari user berdasarkan token
        \Illuminate\Support\Facades\Log::info('Token diterima dari URL: ' . $token);

        $user = \App\Models\User::where('email_verify_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token verifikasi tidak valid atau sudah kedaluwarsa.'], 404);
        }

        // Update email utama dan hapus data sementara
        $user->update([
            'email' => $user->new_email,
            'new_email' => null,
            'email_verify_token' => null,
        ]);

        return response()->json(['message' => 'Email berhasil diperbarui! Silakan login menggunakan email baru Anda.']);
    }

    // app/Http/Controllers/Api/ProfilController.php
    public function toggleDirektori(Request $request)
    {
        $warga = auth()->user()->warga;

        if (!$warga) {
            return response()->json(['message' => 'Profil warga tidak ditemukan.'], 404);
        }

        // Balikkan nilainya (jika true jadi false, jika false jadi true)
        $warga->update([
            'tampilkan_di_direktori' => !$warga->tampilkan_di_direktori
        ]);

        $status = $warga->tampilkan_di_direktori ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json(['message' => 'Tampil di direktori berhasil ' . $status]);
    }
}