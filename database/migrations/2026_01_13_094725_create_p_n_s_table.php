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
        Schema::create('p_n_s', function (Blueprint $table) {
            $table->id();

            $table->string('nip')->unique();
            $table->string('nama');

            $table->foreignId('unit_kerja_id')
                ->constrained('unit_kerjas')
                ->cascadeOnDelete();

            // TAMBAHAN (fix error no_telp)
            $table->string('no_telp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p_n_s');
    }
};
