<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        try {
            // Get customers
            $customers = Customer::when(request()->q, function($customers) {
                $customers = $customers->where('name', 'like', '%' . request()->q . '%');
            })->latest()->paginate(5);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $customers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        try {
            // No specific data to retrieve, just return success
            return response()->json([
                'success' => true,
                'message' => 'Form to create a customer'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'name' => 'required',
                'no_telp' => 'required|unique:customers',
                'address' => 'required',
            ]);

            // Create customer
            $customer = Customer::create([
                'name' => $request->name,
                'no_telp' => $request->no_telp,
                'address' => $request->address,
            ]);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Customer $customer)
    {
        try {
            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $customer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            // Validate request
            $request->validate([
                'name' => 'required',
                'no_telp' => 'required|unique:customers,no_telp,' . $customer->id,
                'address' => 'required',
            ]);

            // Update customer
            $customer->update([
                'name' => $request->name,
                'no_telp' => $request->no_telp,
                'address' => $request->address,
            ]);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $customer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Find customer by ID
            $customer = Customer::findOrFail($id);

            // Delete customer
            $customer->delete();

            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }






}
