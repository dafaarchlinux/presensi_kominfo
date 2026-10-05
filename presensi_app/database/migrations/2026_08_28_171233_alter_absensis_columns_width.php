<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->string('metode', 50)->nullable()->change();
            $table->string('status', 50)->nullable()->change();
            $table->string('status_pulang', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        //
    }
};
