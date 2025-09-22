<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermintaanPerubahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PerubahanDataController extends Controller
{
    /**
     * Endpoint untuk Warga: Mengajukan perubahan data.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_name' => ['required', 'string', Rule::in(['name', 'phone_number'])], // Batasi kolom yg bisa diubah
            'new_value' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();
        $fieldName = $request->field_name;

        PermintaanPerubahan::create([
            'user_id' => $user->id,
            'field_name' => $fieldName,
            'old_value' => $user->{$fieldName}, // Ambil nilai lama dari user
            'new_value' => $request->new_value,
        ]);

        return response()->json(['message' => 'Permintaan perubahan data telah dikirim dan akan ditinjau oleh Pengurus RT.'], 201);
    }

    /**
     * Endpoint untuk Pengurus RT: Melihat daftar permintaan.
     */
    public function index()
    {
        $pengurus = auth()->user();
        $permintaan = PermintaanPerubahan::with('user:id,name')
            ->where('status', 'Menunggu Persetujuan')
            ->whereHas('user', function ($query) use ($pengurus) {
                $query->where('rt_id', $pengurus->rt_id);
            })
            ->latest()
            ->get();
        
        return response()->json($permintaan);
    }

    /**
     * Endpoint untuk Pengurus RT: Menyetujui atau menolak permintaan.
     */
    public function proses(Request $request, PermintaanPerubahan $permintaan)
    {
        // Otorisasi sederhana
        if ($permintaan->user->rt_id !== auth()->user()->rt_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', Rule::in(['Disetujui', 'Ditolak'])],
            'review_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        DB::beginTransaction();
        try {
            // Jika disetujui, update data asli user
            if ($request->status === 'Disetujui') {
                $userTarget = $permintaan->user;
                $userTarget->update([
                    $permintaan->field_name => $permintaan->new_value,
                ]);
            }
            
            // Update status permintaan
            $permintaan->update([
                'status' => $request->status,
                'reviewed_by' => auth()->id(),
                'review_notes' => $request->review_notes,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Permintaan telah berhasil diproses.']);
    }
}