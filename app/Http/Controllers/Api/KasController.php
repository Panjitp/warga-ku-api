<?php
// app/Http/Controllers/Api/KasController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KasController extends Controller
{
    /**
     * Mencatat transaksi pengeluaran baru untuk RT.
     */
    public function catatPengeluaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengurus = auth()->user();

        $pengeluaran = Kas::create([
            'rt_id' => $pengurus->rt_id,
            'jenis' => 'Pengeluaran', // Jenis transaksi di-set sebagai Pengeluaran
            'keterangan' => $request->keterangan,
            'jumlah' => $request->jumlah,
            'tanggal' => $request->tanggal,
        ]);

        return response()->json([
            'message' => 'Transaksi pengeluaran berhasil dicatat!',
            'data' => $pengeluaran,
        ], 201);
    }
}