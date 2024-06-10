<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;

class SaleController extends Controller
{
    public function filter(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);
    
            // Get data sales by range date
            $sales = Transaction::with('cashier', 'customer')
                ->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date)
                ->get();
    
            // Get total sales by range date    
            $total = Transaction::whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date)
                ->sum('grand_total');
    
            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Sales data fetched successfully',
                'data' => [
                    'sales' => $sales,
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
