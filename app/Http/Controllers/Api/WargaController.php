<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WargaController extends Controller
{
    /**
     * Menampilkan daftar warga sesuai RT pengurus yang login.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Asumsi user pengurus RT punya rt_id.
        // Jika user adalah RW, kita bisa tampilkan semua warga di RW tsb (logika bisa dikembangkan)
        if ($user->role === 'rt' && $user->rt_id) {
            $wargas = Warga::with('user', 'kartuKeluarga')
                ->whereHas('kartuKeluarga', function ($query) use ($user) {
                    $query->where('rt_id', $user->rt_id);
                })->get();

            return response()->json($wargas);
        }

        return response()->json(['message' => 'Anda tidak memiliki akses untuk RT ini.'], 403);
    }

    public function detail(User $user)
    {
        // Ambil profil warga yang terhubung dengan user ini
        $warga = $user->warga()->first();
        if (!$warga) {
            return response()->json(['message' => 'Data warga tidak ditemukan'], 404);
        }
        // Muat relasi user untuk mengambil phone_number
        $warga->load('user:id,phone_number');
        return response()->json($warga);
    }

    /**
     * Menyimpan data warga baru.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            // Data untuk tabel users
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // Data untuk tabel wargas
            'kartu_keluarga_id' => 'required|exists:kartu_keluargas,id',
            'nik' => 'required|string|size:16|unique:wargas',
            'nama_lengkap' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string',
            'status_perkawinan' => 'required|string',
            'pekerjaan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Gunakan transaction untuk memastikan kedua data (user & warga) berhasil dibuat
        DB::beginTransaction();
        try {
            $newUser = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'warga', // Setiap warga baru otomatis role-nya 'warga'
                'rt_id' => $user->rt_id,
            ]);

            $newWarga = Warga::create([
                'user_id' => $newUser->id,
                'kartu_keluarga_id' => $request->kartu_keluarga_id,
                'nik' => $request->nik,
                'nama_lengkap' => $request->nama_lengkap,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'status_perkawinan' => $request->status_perkawinan,
                'pekerjaan' => $request->pekerjaan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Warga berhasil ditambahkan!',
                'data' => $newWarga
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan, data gagal disimpan.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan detail satu warga.
     */
    public function show(Warga $warga)
    {
        // Otorisasi: pastikan pengurus hanya bisa melihat warga di RT-nya
        if (auth()->user()->rt_id != $warga->kartuKeluarga->rt_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }
        
        return response()->json($warga->load('user', 'kartuKeluarga'));
    }

    /**
     * Memperbarui data warga.
     */
    public function update(Request $request, Warga $warga)
    {
        // Otorisasi
        if (auth()->user()->rt_id != $warga->kartuKeluarga->rt_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'kartu_keluarga_id' => 'sometimes|required|exists:kartu_keluargas,id',
            'nik' => ['sometimes', 'required', 'string', 'size:16', Rule::unique('wargas')->ignore($warga->id)],
            'nama_lengkap' => 'sometimes|required|string|max:255',
            'tempat_lahir' => 'sometimes|required|string|max:100',
            // ...tambahkan validasi lain sesuai kebutuhan...
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $warga->update($request->all());

        return response()->json([
            'message' => 'Data warga berhasil diperbarui!',
            'data' => $warga
        ]);
    }

    /**
     * Menghapus data warga.
     */
    public function destroy(Warga $warga)
    {
        // Otorisasi
        if (auth()->user()->rt_id != $warga->kartuKeluarga->rt_id) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Hapus user terkait, data warga akan terhapus otomatis karena cascade
        $warga->user()->delete();

        return response()->json(['message' => 'Data warga berhasil dihapus.']);
    }
}