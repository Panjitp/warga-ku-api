<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1, .header h2 { margin: 0; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; text-align: left; }
        .summary { margin-top: 20px; width: 40%; float: right; }
        .summary td { text-align: right; }
        .summary .label { text-align: left; }
        h3 { border-bottom: 2px solid #333; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan RT {{ $rt->nomor }} / RW {{ $rw->nomor }}</h1>
        <h2>Periode: {{ $nama_bulan }} {{ $tahun }}</h2>
    </div>

    <h3>Pemasukan</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pemasukan as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>Rp {{ number_format($item->jumlah, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Tidak ada data pemasukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top: 30px;">Pengeluaran</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengeluaran as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>Rp {{ number_format($item->jumlah, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Tidak ada data pengeluaran.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="label"><strong>Total Pemasukan</strong></td>
            <td><strong>Rp {{ number_format($total_pemasukan, 2, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label"><strong>Total Pengeluaran</strong></td>
            <td><strong>Rp {{ number_format($total_pengeluaran, 2, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label"><strong>Saldo Akhir</strong></td>
            <td><strong>Rp {{ number_format($saldo_akhir, 2, ',', '.') }}</strong></td>
        </tr>
    </table>
</body>
</html>