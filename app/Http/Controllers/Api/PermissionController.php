<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Auth;

class PermissionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
       
        if(request()->jumlahperpage){
            $perPage = request()->jumlahperpage;
        }else{
            $perPage =10;
        }
        $permissions = Permission::when(request()->q, function($permissions) {
            $permissions = $permissions->where('name', 'like', '%'. request()->q . '%');
        })->latest()->paginate($perPage);

        //return inertia view
        if($permissions){
            return response()->json([
                'success' => true,
                'message' => 'Data permission retrieved successfully',
                'data'    => $permissions
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data permission not found',
                'data'    => null
            ], 404); // Atau bisa diganti dengan 400 jika lebih sesuai
        }
       
       
    }
}
