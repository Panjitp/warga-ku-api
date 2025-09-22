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
        Schema::create('rts', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 10); // Nomor RT, misal: "01", "12"
            $table->string('nama_ketua');
            // Kunci asing yang menghubungkan RT ini ke RW
            $table->foreignId('rw_id')->constrained('rws')->onDelete('cascade');
            // Relasi ke user yang menjadi ketua RT
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rts');
    }
};
