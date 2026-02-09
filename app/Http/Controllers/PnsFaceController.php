<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PnsFaceController extends Controller
{
    public function embedding(Request $request)
    {
        $pns = auth('pns')->user();

        if (!$pns || empty($pns->face_embedding)) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah belum terdaftar',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pns_id' => $pns->id,
            'embedding' => json_decode($pns->face_embedding),
        ]);
    }
}
