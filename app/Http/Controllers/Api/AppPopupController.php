<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppPopup;
use App\Http\Resources\AppPopupResource;
use Illuminate\Http\Request;

class AppPopupController extends Controller
{
    /**
     * Get the current active popup for mobile application.
     * GET /api/app-popup
     *
     * Return ONLY one popup that:
     * - is active
     * - current date is between start_date and end_date
     * - ordered by sort_order then latest created
     */
    public function index()
    {
        $popup = AppPopup::where('status', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$popup) {
            return response()->json([
                'status' => false
            ]);
        }

        return response()->json([
            'status' => true,
            'popup' => new AppPopupResource($popup)
        ]);
    }
}
