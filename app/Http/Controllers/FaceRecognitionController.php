<?php

namespace App\Http\Controllers;

use App\Models\FaceEmbedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FaceRecognitionController extends Controller
{
    /**
     * ==================================================
     * ENDPOINT UNTUK face-api.js
     * GET /face/embeddings
     * ==================================================
     *
     * Format:
     * {
     *   "1": [ [0.01, 0.02, ...], [0.03, ...] ],
     *   "2": [ [0.04, ...] ]
     * }
     */
    public function embeddings(): JsonResponse
    {
        $faces = FaceEmbedding::query()
            ->select(['pns_id', 'embedding'])
            ->orderBy('pns_id')
            ->get();

        if ($faces->isEmpty()) {
            Log::info('FACE EMBEDDINGS EMPTY');
            return response()->json([]);
        }

        $grouped = [];

        foreach ($faces as $face) {
            // Skip data rusak / tidak valid
            if (
                empty($face->embedding) ||
                !is_array($face->embedding)
            ) {
                continue;
            }

            $grouped[$face->pns_id][] = array_values($face->embedding);
        }

        Log::info('FACE EMBEDDINGS SERVED', [
            'total_pns'  => count($grouped),
            'total_rows'=> $faces->count(),
        ]);

        return response()->json($grouped);
    }
}
