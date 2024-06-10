<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profit;

class ProfitController extends Controller
{
    public function filter(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            // Get data profits by range date
            $profits = Profit::with('transaction')
                ->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date)
                ->get();

            // Get total profit by range date    
            $total = Profit::whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date)
                ->sum('total');

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Profits data fetched successfully',
                'data' => [
                    'profits' => $profits,
                    'total' => (int) $total
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

}
