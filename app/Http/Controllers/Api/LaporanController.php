<?php
// app/Http/Controllers/Api/LaporanController.php

namespace App\Http\Controllers\Api;

use App\Exports\IuranExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // <-- Import Facade Excel
use App\Models\Kas;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Import Facade PDF

class LaporanController extends Controller
{
    public function exportIuranExcel(Request $request)
    {
        // Validasi input tahun dan bulan dari query parameter
        $request->validate([
            'tahun' => 'required|integer|digits:4',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');
        
        // Buat nama file yang dinamis
        $fileName = 'laporan-iuran-' . $bulan . '-' . $tahun . '.xlsx';

        // Panggil IuranExport dan trigger unduhan file
        return Excel::download(new IuranExport($tahun, $bulan), $fileName);
    }

    public function exportKeuanganPdf(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|digits:4',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $pengurus = auth()->user();
        // BENAR ✅
        $tahun = (int) $request->query('tahun');
        $bulan = (int) $request->query('bulan');

        // Ambil data dari tabel 'kas'
        // BENAR ✅
        $query = Kas::query()->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);

        if ($pengurus->role === 'rt') {
            $query->where('rt_id', $pengurus->rt_id);
        } elseif ($pengurus->role === 'rw') {
            $query->whereHas('rt', fn($q) => $q->where('rw_id', $pengurus->rw_id));
        }

        $kasData = $query->get();

        // Pisahkan pemasukan dan pengeluaran
        $pemasukan = $kasData->where('jenis', 'Pemasukan');
        $pengeluaran = $kasData->where('jenis', 'Pengeluaran');

        // Hitung total
        $totalPemasukan = $pemasukan->sum('jumlah');
        $totalPengeluaran = $pengeluaran->sum('jumlah');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // Data yang akan dikirim ke view
        $data = [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'total_pemasukan' => $totalPemasukan,
            'total_pengeluaran' => $totalPengeluaran,
            'saldo_akhir' => $saldoAkhir,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nama_bulan' => \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM'),
            'rt' => $pengurus->rt, // Asumsi relasi sudah ada
            'rw' => $pengurus->rw, // Asumsi relasi sudah ada
        ];
        
        // Buat PDF
        $pdf = Pdf::loadView('laporan.keuangan', $data);
        
        // Download PDF
        $fileName = 'laporan-keuangan-' . $bulan . '-' . $tahun . '.pdf';
        return $pdf->download($fileName);
    }
}