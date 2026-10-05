<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceEmbedding extends Model
{
    /**
     * =========================
     * TABLE (WAJIB)
     * =========================
     */
    protected $table = 'face_embeddings';

    /**
     * =========================
     * MASS ASSIGNMENT
     * =========================
     */
    protected $fillable = [
        'pns_id',
        'embedding',
        'image_path',
    ];

    /**
     * =========================
     * CASTING
     * =========================
     */
    protected $casts = [
        'embedding' => 'array',
    ];

    /**
     * =========================
     * RELATION
     * =========================
     */
    public function pns(): BelongsTo
    {
        return $this->belongsTo(PNS::class, 'pns_id');
    }

    /**
     * =========================
     * SAFETY: PASTIKAN EMBEDDING BERSIH
     * (TANPA NORMALISASI ULANG)
     * =========================
     */
    public function getEmbeddingAttribute($value): array
    {
        // Dari cast array
        if (is_array($value)) {
            return array_values(array_map('floatval', $value));
        }

        // Dari JSON string (fallback)
        $decoded = json_decode($value, true);

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_map('floatval', $decoded));
    }
}
