<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RtAccountController extends Controller
{
    /**
     * Menampilkan daftar semua akun Ketua RT di bawah RW yang sedang login.
     */
    public function index()
    {
        $rw_id = auth()->user()->rw_id;
        $akunRt = User::where('role', 'rt')->where('rw_id', $rw_id)->get();
        return response()->json($akunRt);
    }

    /**
     * Membuat akun Ketua RT baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'rt_id' => [ // Pastikan rt_id yang diinput ada di bawah RW yang sama
                'required',
                Rule::exists('rts', 'id')->where(function ($query) {
                    return $query->where('rw_id', auth()->user()->rw_id);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'rt',
            'rt_id' => $request->rt_id,
            'rw_id' => auth()->user()->rw_id, // Otomatis set rw_id
            'status' => 'aktif',
        ]);

        return response()->json(['message' => 'Akun Ketua RT berhasil dibuat!', 'data' => $user], 201);
    }

    /**
     * Menampilkan detail satu akun Ketua RT.
     */
    public function show(User $akunRt)
    {
        // Otorisasi: Pastikan akun yg diminta adalah akun RT di bawah RW yg benar
        if ($akunRt->role !== 'rt' || $akunRt->rw_id !== auth()->user()->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return response()->json($akunRt);
    }

    /**
     * Memperbarui akun Ketua RT.
     */
    public function update(Request $request, User $akunRt)
    {
        // Otorisasi
        if ($akunRt->role !== 'rt' || $akunRt->rw_id !== auth()->user()->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($akunRt->id)],
            'status' => 'sometimes|required|in:aktif,tidak_aktif',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $akunRt->update($request->only('name', 'email', 'status'));
        return response()->json(['message' => 'Akun Ketua RT berhasil diperbarui!', 'data' => $akunRt]);
    }

    /**
     * Menonaktifkan akun (soft delete).
     */
    public function destroy(User $akunRt)
    {
        // Otorisasi
        if ($akunRt->role !== 'rt' || $akunRt->rw_id !== auth()->user()->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $akunRt->update(['status' => 'tidak_aktif']);
        return response()->json(['message' => 'Akun Ketua RT telah dinonaktifkan.']);
    }
}