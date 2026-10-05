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
        Schema::create('face_embeddings', function (Blueprint $table) {
            $table->id();

            // FK HARUS ke tabel p_n_s (sesuai migration PNS Anda)
            $table->foreignId('pns_id')
                ->constrained('p_n_s')
                ->cascadeOnDelete();

            // hasil embedding wajah (array → JSON)
            $table->longText('embedding');

            // path image (opsional)
            $table->string('image_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_embeddings');
    }
};
