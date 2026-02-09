<?php

namespace App\Http\Controllers;

use App\Models\FaceEmbedding;
use App\Models\PNS;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FaceEnrollmentController extends Controller
{
    /**
     * ==================================================
     * SIMPAN WAJAH PNS (ENROLL)
     * POST /face/enroll
     * ==================================================
     */
    public function store(Request $request): JsonResponse
    {
        Log::info('FACE ENROLL HIT', [
            'payload' => $request->except('image', 'embedding'),
        ]);

        /*
        ==================================================
        VALIDASI DASAR
        ==================================================
        */
        $validated = $request->validate([
            'pns_id'    => 'required|integer',
            'embedding' => 'required|array|size:128',
            'image'     => 'required|string',
        ]);

        /*
        ==================================================
        VALIDASI PNS
        ==================================================
        */
        $pns = PNS::find($validated['pns_id']);
        if (!$pns) {
            Log::warning('FACE ENROLL FAILED - PNS NOT FOUND', [
                'pns_id' => $validated['pns_id'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PNS tidak ditemukan.',
            ], 422);
        }

        /*
        ==================================================
        VALIDASI & NORMALISASI EMBEDDING
        ==================================================
        */
        $embedding = [];

        foreach ($validated['embedding'] as $value) {
            if (!is_numeric($value)) {
                Log::warning('INVALID EMBEDDING VALUE', [
                    'pns_id' => $pns->id,
                    'value'  => $value,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Embedding tidak valid.',
                ], 422);
            }

            $embedding[] = (float) $value;
        }

        // === L2 NORMALIZATION (WAJIB UNTUK FACE MATCHER) ===
        $norm = sqrt(array_sum(array_map(fn ($v) => $v * $v, $embedding)));
        if ($norm == 0.0) {
            return response()->json([
                'success' => false,
                'message' => 'Embedding tidak valid (norm = 0).',
            ], 422);
        }

        $embedding = array_map(fn ($v) => $v / $norm, $embedding);

        /*
        ==================================================
        BATASI JUMLAH EMBEDDING PER PNS
        ==================================================
        */
        $maxEmbeddingPerPns = 5;

        $existingEmbeddings = FaceEmbedding::where('pns_id', $pns->id)->get();
        if ($existingEmbeddings->count() >= $maxEmbeddingPerPns) {
            return response()->json([
                'success' => false,
                'message' => "Wajah untuk {$pns->nama} sudah mencapai batas ({$maxEmbeddingPerPns}).",
            ], 422);
        }

        /*
        ==================================================
        CEK DUPLIKASI WAJAH (ANTI WAJAH SAMA DI-ENROLL)
        ==================================================
        */
        foreach ($existingEmbeddings as $existing) {
            $distance = $this->cosineDistance($embedding, $existing->embedding);

            if ($distance < 0.25) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wajah ini sudah pernah didaftarkan.',
                ], 422);
            }
        }

        /*
        ==================================================
        DECODE IMAGE BASE64
        ==================================================
        */
        try {
            $base64 = preg_replace('#^data:image/\w+;base64,#i', '', $validated['image']);
            $binaryImage = base64_decode($base64, true);

            if ($binaryImage === false) {
                throw new \Exception('Base64 decode gagal');
            }
        } catch (\Throwable $e) {
            Log::error('IMAGE DECODE FAILED', [
                'pns_id' => $pns->id,
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses gambar wajah.',
            ], 422);
        }

        /*
        ==================================================
        SIMPAN DALAM TRANSAKSI
        ==================================================
        */
        return DB::transaction(function () use ($pns, $embedding, $binaryImage) {

            $directory = 'faces/pns-' . $pns->id;
            $filename  = $directory . '/' . Str::uuid() . '.png';

            Storage::disk('public')->makeDirectory($directory);
            Storage::disk('public')->put($filename, $binaryImage);

            $faceEmbedding = FaceEmbedding::create([
                'pns_id'     => $pns->id,
                'embedding'  => $embedding,
                'image_path' => $filename,
            ]);

            Log::info('FACE ENROLL SUCCESS', [
                'face_embedding_id' => $faceEmbedding->id,
                'pns_id'            => $pns->id,
                'nama'              => $pns->nama,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Wajah {$pns->nama} berhasil disimpan.",
            ]);
        });
    }

    /**
     * ==================================================
     * COSINE DISTANCE
     * ==================================================
     */
    private function cosineDistance(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $v) {
            $dot   += $v * $b[$i];
            $normA += $v * $v;
            $normB += $b[$i] * $b[$i];
        }

        return 1 - ($dot / (sqrt($normA) * sqrt($normB)));
    }
}
