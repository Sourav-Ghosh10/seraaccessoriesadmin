<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RewardTransaction;

class RewardTransactionController extends Controller
{
    public function index()
    {
        $transactions = RewardTransaction::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $transactions,
            'message' => 'Reward transactions retrieved successfully.'
        ]);
    }
}
