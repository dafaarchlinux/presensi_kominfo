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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();

            // FK HARUS ke tabel p_n_s
            $table->foreignId('pns_id')
                ->constrained('p_n_s')
                ->cascadeOnDelete();

            $table->date('tanggal');

            // nullable supaya izin / sakit / alfa tidak error
            $table->time('jam_masuk')->nullable();

            $table->enum('status', [
                'hadir',
                'terlambat',
                'alfa',
                'izin',
                'sakit'
            ]);

            $table->enum('metode', ['face', 'manual'])
                ->default('face');

            $table->timestamps();

            // 1 PNS hanya boleh 1 absensi per hari
            $table->unique(['pns_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
