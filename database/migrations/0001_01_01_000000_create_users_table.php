<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // --- KOLOM TAMBAHAN KITA ---
            $table->enum('role', ['rw', 'rt', 'warga'])->default('warga');
            $table->string('phone_number')->nullable();
            $table->unsignedBigInteger('rt_id')->nullable(); // Relasi ke tabel RT
            $table->unsignedBigInteger('rw_id')->nullable(); // Relasi ke tabel RW
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            // --------------------------

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
