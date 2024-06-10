<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        try {
            // Get cart
            $carts = Cart::with('product')->where('cashier_id', auth()->user()->id)->latest()->get();

            // Get all customers
            $customers = Customer::latest()->get();

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Carts and customers fetched successfully',
                'data' => [
                    'carts' => $carts,
                    'carts_total' => $carts->sum('price'),
                    'customers' => $customers,
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

    public function searchProduct(Request $request)
    {
        try {
            // Find product by barcode
            $product = Product::where('barcode', $request->barcode)->first();

            if($product) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product found',
                    'data' => $product  
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'data' => null  
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            // Check stock product
            $product = Product::whereId($request->product_id)->first();
            if($product->stock < $request->qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Out of Stock Product!',
                    'data' => null,
                ], 400);
            }

            // Check cart
            $cart = Cart::with('product')
                    ->where('product_id', $request->product_id)
                    ->where('cashier_id', auth()->user()->id)
                    ->first();

            if($cart) {
                // Increment qty
                $cart->increment('qty', $request->qty);

                // Sum price * quantity
                $cart->price = $cart->product->sell_price * $cart->qty;

                $cart->save();
            } else {
                // Insert cart
                Cart::create([
                    'cashier_id' => auth()->user()->id,
                    'product_id' => $request->product_id,
                    'qty' => $request->qty,
                    'price' => $product->sell_price * $request->qty,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product added successfully!',
                'data' => null,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function destroyCart(Request $request)
    {
        try {
            // Find cart by ID
            $cart = Cart::with('product')
                ->whereId($request->cart_id)
                ->first();
            
            // Delete cart
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product removed successfully!',
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Algorithm to generate no invoice
            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }

            // Generate no invoice
            $invoice = 'TRX-' . Str::upper($random);

            // Insert transaction
            $transaction = Transaction::create([
                'cashier_id' => auth()->user()->id,
                'customer_id' => $request->customer_id,
                'invoice' => $invoice,
                'cash' => $request->cash,
                'change' => $request->change,
                'discount' => $request->discount,
                'grand_total' => $request->grand_total,
            ]);

            // Get carts
            $carts = Cart::where('cashier_id', auth()->user()->id)->get();

            // Insert transaction detail
            foreach($carts as $cart) {
                // Insert transaction detail
                $transaction->details()->create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $cart->product_id,
                    'qty' => $cart->qty,
                    'price' => $cart->price,
                ]);

                // Get price
                $total_buy_price  = $cart->product->buy_price * $cart->qty;
                $total_sell_price = $cart->product->sell_price * $cart->qty;

                // Get profits
                $profits = $total_sell_price - $total_buy_price;

                // Insert profits
                $transaction->profits()->create([
                    'transaction_id' => $transaction->id,
                    'total' => $profits,
                ]);

                // Update stock product
                $product = Product::find($cart->product_id);
                $product->stock = $product->stock - $cart->qty;
                $product->save();
            }

            // Delete carts
            Cart::where('cashier_id', auth()->user()->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully!',
                'data' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }





    public function print(Request $request)
    {
        //get transaction
        $transaction = Transaction::with('details.product', 'cashier', 'customer')->where('invoice', $request->invoice)->firstOrFail();

        //return view
        return view('print.nota', compact('transaction'));
    }
}
