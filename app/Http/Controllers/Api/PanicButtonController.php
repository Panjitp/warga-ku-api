<?php
// app/Http/Controllers/Api/PanicButtonController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PanicLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PanicButtonController extends Controller
{
    public function trigger(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|string',
            'longitude' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Catat kejadian darurat ke database
        PanicLog::create([
            'user_id' => auth()->user()->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // ================== PENTING ==================
        // Di aplikasi nyata, di sinilah Anda akan memicu
        // pengiriman Push Notification (misalnya via Firebase Cloud Messaging)
        // ke semua Pengurus RT/RW. Untuk proyek ini, kita hanya
        // akan mencatatnya di database.
        // ===============================================

        return response()->json(['message' => 'Sinyal darurat telah dikirimkan ke pengurus terdekat.']);
    }
}