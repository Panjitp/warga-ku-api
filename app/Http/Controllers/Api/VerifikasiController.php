<?php
// app/Http/Controllers/Api/VerifikasiController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kas;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VerifikasiController extends Controller
{
    // Pengurus RT melihat daftar pembayaran yang perlu diverifikasi
    public function index()
    {
        $pengurus = auth()->user();
        $pembayarans = Pembayaran::with('tagihan.kartuKeluarga', 'warga')
            ->whereHas('tagihan.kartuKeluarga', function ($query) use ($pengurus) {
                $query->where('rt_id', $pengurus->rt_id);
            })
            ->whereHas('tagihan', function ($query) {
                $query->where('status', 'Menunggu Verifikasi');
            })
            ->get();

        return response()->json($pembayarans);
    }

    // Pengurus RT melakukan verifikasi (terima/tolak)
    public function verifikasi(Request $request, Pembayaran $pembayaran)
    {
        // Otorisasi: Pastikan pembayaran ini ada di wilayah RT nya
        $pengurus = auth()->user();
        if ($pembayaran->tagihan->kartuKeluarga->rt_id !== $pengurus->rt_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:diterima,ditolak',
            'catatan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'diterima') {
                // Update status tagihan menjadi Lunas
                $pembayaran->tagihan->update(['status' => 'Lunas']);
                
                // Update data pembayaran
                $pembayaran->update([
                    'diverifikasi_oleh' => $pengurus->id,
                    'diverifikasi_pada' => now(),
                    'catatan' => $request->catatan ?? 'Pembayaran diterima.',
                ]);
                
                // Catat sebagai Pemasukan di Buku Kas
                Kas::create([
                    'rt_id' => $pengurus->rt_id,
                    'jenis' => 'Pemasukan',
                    'keterangan' => 'Iuran dari KK No. ' . $pembayaran->tagihan->kartuKeluarga->nomor_kk . ' (' . $pembayaran->tagihan->iuran->jenis_iuran . ')',
                    'jumlah' => $pembayaran->tagihan->jumlah,
                    'tanggal' => now(),
                    'pembayaran_id' => $pembayaran->id,
                ]);

            } else { // Jika ditolak
                $pembayaran->tagihan->update(['status' => 'Ditolak']);
                $pembayaran->update([
                    'diverifikasi_oleh' => $pengurus->id,
                    'diverifikasi_pada' => now(),
                    'catatan' => $request->catatan ?? 'Pembayaran ditolak. Silakan hubungi pengurus.',
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Verifikasi pembayaran berhasil.']);
    }
}