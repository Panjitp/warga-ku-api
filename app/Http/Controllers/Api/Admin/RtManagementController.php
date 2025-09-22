<?php
// app/Http/Controllers/Api/Admin/RtManagementController.php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RtManagementController extends Controller
{
    /**
     * Menampilkan daftar RT di bawah RW yang login.
     */
    public function index()
    {
        $rts = Rt::where('rw_id', auth()->user()->rw_id)->get();
        return response()->json($rts);
    }

    /**
     * Membuat data RT baru.
     */
    public function store(Request $request)
    {
        $rw_id = auth()->user()->rw_id;
        $validator = Validator::make($request->all(), [
            'nomor' => [
                'required',
                'string',
                'max:10',
                Rule::unique('rts')->where(function ($query) use ($rw_id) {
                    return $query->where('rw_id', $rw_id);
                }),
            ],
            'nama_ketua' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rt = Rt::create([
            'nomor' => $request->nomor,
            'nama_ketua' => $request->nama_ketua,
            'rw_id' => $rw_id,
        ]);

        return response()->json(['message' => 'Data RT berhasil dibuat!', 'data' => $rt], 201);
    }

    /**
     * Menampilkan detail satu RT.
     */
    public function show(Rt $rt)
    {
        if ($rt->rw_id !== auth()->user()->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        return response()->json($rt);
    }

    /**
     * Memperbarui data RT.
     */
    public function update(Request $request, Rt $rt)
    {
        if ($rt->rw_id !== auth()->user()->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // (Tambahkan validasi jika perlu)
        $rt->update($request->all());
        return response()->json(['message' => 'Data RT berhasil diperbarui.', 'data' => $rt]);
    }

    /**
     * Menghapus data RT.
     */
    public function destroy(Rt $rt)
    {
        if ($rt->rw_id !== auth()->user()->rw_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $rt->delete();
        return response()->json(['message' => 'Data RT berhasil dihapus.']);
    }
}