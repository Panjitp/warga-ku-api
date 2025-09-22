<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Iuran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IuranController extends Controller
{
    // Menampilkan semua jenis iuran di RW nya
    public function index()
    {
        $iurans = Iuran::where('rw_id', auth()->user()->rw_id)->get();
        return response()->json($iurans);
    }

    // Membuat jenis iuran baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_iuran' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nominal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $iuran = Iuran::create([
            'rw_id' => auth()->user()->rw_id,
            'jenis_iuran' => $request->jenis_iuran,
            'deskripsi' => $request->deskripsi,
            'nominal' => $request->nominal,
        ]);

        return response()->json(['message' => 'Jenis iuran berhasil dibuat!', 'data' => $iuran], 201);
    }
    
    // (Method show, update, destroy bisa ditambahkan di sini jika diperlukan)
}