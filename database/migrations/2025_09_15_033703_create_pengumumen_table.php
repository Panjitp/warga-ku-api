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
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Siapa yg mempublikasi
            $table->string('judul');
            $table->text('isi');
            $table->string('gambar')->nullable(); // Path ke file gambar (jika ada)
            $table->enum('tujuan', ['SEMUA', 'RW', 'RT']); // Target audiens
            $table->foreignId('rt_id')->nullable()->constrained('rts')->onDelete('cascade');
            $table->foreignId('rw_id')->nullable()->constrained('rws')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumumans');
    }
};
