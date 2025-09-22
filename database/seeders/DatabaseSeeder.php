<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rw;
use App\Models\Rt;
use App\Models\KartuKeluarga;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- 1. Buat Pengurus RW ---
        $pengurusRwUser = User::factory()->create([
            'name' => 'Bapak RW 01',
            'email' => 'ketuarw@wargaku.com',
            'password' => Hash::make('password'),
            'role' => 'rw',
        ]);
        $rw = Rw::create([
            'nomor' => '01',
            'nama_ketua' => $pengurusRwUser->name,
            'user_id' => $pengurusRwUser->id,
        ]);
        $pengurusRwUser->update(['rw_id' => $rw->id]);
        $this->command->info('Akun Pengurus RW berhasil dibuat:');
        $this->command->warn('Email: ketuarw@wargaku.com');
        $this->command->warn('Password: password');

        // --- 2. Buat Pengurus RT ---
        $pengurusRtUser = User::factory()->create([
            'name' => 'Bapak RT 003',
            'email' => 'ketuart@wargaku.com',
            'password' => Hash::make('password'),
            'role' => 'rt',
        ]);
        $rt = Rt::create([
            'nomor' => '003',
            'nama_ketua' => $pengurusRtUser->name,
            'rw_id' => $rw->id,
            'user_id' => $pengurusRtUser->id,
        ]);
        $pengurusRtUser->update(['rt_id' => $rt->id, 'rw_id' => $rw->id]);
        $this->command->info('Akun Pengurus RT berhasil dibuat:');
        $this->command->warn('Email: ketuart@wargaku.com');
        $this->command->warn('Password: password');

        // --- 3. Buat Kartu Keluarga ---
        $kks = collect([
            [
            'nomor_kk' => '3273251012130001',
            'alamat' => 'Jl. Kenangan No. 10, RT 003/RW 01',
            ],
            [
            'nomor_kk' => '3273251012130002',
            'alamat' => 'Jl. Melati No. 5, RT 003/RW 01',
            ],
            [
            'nomor_kk' => '3273251012130003',
            'alamat' => 'Jl. Mawar No. 7, RT 003/RW 01',
            ],
            [
            'nomor_kk' => '3273251012130004',
            'alamat' => 'Jl. Anggrek No. 12, RT 003/RW 01',
            ],
            [
            'nomor_kk' => '3273251012130005',
            'alamat' => 'Jl. Dahlia No. 3, RT 003/RW 01',
            ],
        ])->map(function ($data) use ($rt) {
            return KartuKeluarga::create([
            'nomor_kk' => $data['nomor_kk'],
            'alamat' => $data['alamat'],
            'rt_id' => $rt->id,
            ]);
        });

        $kks->each(function ($kk) use ($rt, $rw) {
            $jumlah = rand(2, 6);
            Warga::factory($jumlah)->create([
                'kartu_keluarga_id' => $kk->id,
            ])->each(function ($warga) use ($rt, $rw) {
                $warga->user->update(['rt_id' => $rt->id, 'rw_id' => $rw->id]);
            });
        });
        
        $this->command->info('5 data Warga berhasil dibuat.');
    }
}