<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    /** Currently active alerts, most recent first (shown as the red banner). */
    public function active(): JsonResponse
    {
        return response()->json(
            Alert::where('is_active', true)
                ->orderByDesc('issued_at')
                ->get()
        );
    }
}
