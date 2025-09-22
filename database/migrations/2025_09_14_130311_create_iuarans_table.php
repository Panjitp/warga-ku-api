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
        Schema::create('iurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rw_id')->constrained('rws')->onDelete('cascade');
            $table->string('jenis_iuran'); // Contoh: Iuran Keamanan, Iuran Kebersihan
            $table->text('deskripsi')->nullable();
            $table->decimal('nominal', 10, 2); // Nominal iuran
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iuarans');
    }
};
