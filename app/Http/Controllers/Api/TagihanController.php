<?php
// app/Http/Controllers/Api/TagihanController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Iuran;
use App\Models\KartuKeluarga;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagihanController extends Controller
{
    /**
     * LANGKAH 1 BARU: Menampilkan iuran yang BISA ditagihkan.
     * Iuran yang sudah ditagihkan di bulan/tahun ini tidak akan muncul.
     */
    public function getAvailableIuran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required|integer|digits:4',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengurus = auth()->user();
        $tahun = (int) $request->query('tahun');
        $bulan = (int) $request->query('bulan');

        // Ambil ID iuran yang SUDAH ditagihkan di RT ini pada periode ini
        $generatedIuranIds = Tagihan::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->whereHas('kartuKeluarga', function ($q) use ($pengurus) {
                $q->where('rt_id', $pengurus->rt_id);
            })
            ->distinct()
            ->pluck('iuran_id');

        // Ambil semua jenis iuran di RW, KECUALI yang sudah ditagihkan
        $availableIurans = Iuran::where('rw_id', $pengurus->rw_id)
            ->whereNotIn('id', $generatedIuranIds)
            ->get();

        return response()->json($availableIurans);
    }

    /**
     * LANGKAH 2 DIMODIFIKASI: Generate tagihan untuk SATU jenis iuran spesifik.
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required|integer|digits:4',
            'bulan' => 'required|integer|min:1|max:12',
            'iuran_id' => 'required|exists:iurans,id', // WAJIB menyertakan iuran_id
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengurus = auth()->user();
        $tahun = $request->tahun;
        $bulan = $request->bulan;
        $iuranId = $request->iuran_id;
        $tanggalTagihan = "{$tahun}-{$bulan}-01";

        // Ambil detail iuran yang dipilih
        $iuran = Iuran::find($iuranId);
        // Pastikan iuran ini milik RW yang sama dengan pengurus
        if ($iuran->rw_id !== $pengurus->rw_id) {
            return response()->json(['message' => 'Jenis iuran tidak valid.'], 422);
        }

        // Ambil semua KK di RT tersebut
        $kartuKeluargas = KartuKeluarga::where('rt_id', $pengurus->rt_id)->get();
        if ($kartuKeluargas->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data Kartu Keluarga di RT ini.'], 404);
        }

        // Pencegahan duplikasi yang lebih kuat
        $existingTagihan = Tagihan::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('iuran_id', $iuranId)
            ->whereHas('kartuKeluarga', fn($q) => $q->where('rt_id', $pengurus->rt_id))
            ->exists();

        if ($existingTagihan) {
            return response()->json(['message' => 'Tagihan untuk iuran ini sudah pernah dibuat pada periode ini.'], 409); // 409 Conflict
        }

        $generatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($kartuKeluargas as $kk) {
                Tagihan::create([
                    'kartu_keluarga_id' => $kk->id,
                    'iuran_id' => $iuran->id,
                    'tanggal_tagihan' => $tanggalTagihan,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'jumlah' => $iuran->nominal,
                    'status' => 'Belum Lunas',
                ]);
                $generatedCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan saat membuat tagihan.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Proses selesai. Berhasil membuat {$generatedCount} tagihan baru untuk '{$iuran->jenis_iuran}'."]);
    }

    /**
     * Menampilkan daftar tagihan di RT tersebut.
     */
    public function index(Request $request)
    {
        $pengurus = auth()->user();
        
        $tagihans = Tagihan::with('kartuKeluarga', 'iuran')
            ->whereHas('kartuKeluarga', function ($query) use ($pengurus) {
                $query->where('rt_id', $pengurus->rt_id);
            })
            // Tambahkan filter jika ada
            ->when($request->has('bulan'), function ($query) use ($request) {
                return $query->where('bulan', $request->bulan);
            })
            ->when($request->has('tahun'), function ($query) use ($request) {
                return $query->where('tahun', $request->tahun);
            })
            ->when($request->has('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->latest()
            ->get();
            
        return response()->json($tagihans);
    }
}