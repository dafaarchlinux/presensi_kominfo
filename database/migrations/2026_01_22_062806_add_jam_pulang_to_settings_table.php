<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {

            // tambah jam pulang
            $table->time('jam_pulang')->nullable()->after('jam_masuk');

            // ubah status agar support masuk & pulang
            $table->enum('status', [
                'hadir',
                'terlambat',
                'alfa',
                'izin',
                'sakit',
                'pulang',
                'pulang_cepat',
                'telat_pulang'
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn('jam_pulang');

            $table->enum('status', [
                'hadir',
                'terlambat',
                'alfa',
                'izin',
                'sakit'
            ])->change();
        });
    }
};
