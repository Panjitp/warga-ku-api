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
        Schema::create('sarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User warga yang mengirim
            $table->string('judul');
            $table->text('isi');
            $table->enum('status', ['Dikirim', 'Dibaca', 'Ditindaklanjuti', 'Selesai'])->default('Dikirim');
            $table->foreignId('rt_id')->constrained('rts')->onDelete('cascade'); // Untuk routing ke pengurus RT
            $table->foreignId('rw_id')->constrained('rws')->onDelete('cascade'); // Untuk routing ke pengurus RW
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarans');
    }
};
