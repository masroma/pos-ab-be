<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        //get roles
        $roles = Role::when(request()->q, function($roles) {
            $roles = $roles->where('name', 'like', '%'. request()->q . '%');
        })->with('permissions')->latest()->paginate(5);

        if($roles){
            return response()->json([
                'success' => true,
                'message' => 'Data role retrieved successfully',
                'data'    => $roles
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data role not found',
                'data'    => null
            ], 404); // Atau bisa diganti dengan 400 jika lebih sesuai
        }

        //render with inertia
       
    }

    public function create()
    {
        //get permission all
        $permissions = Permission::all();
        if($permissions){
            return response()->json([
                'success' => true,
                'message' => 'Data role retrieved successfully',
                'data'    => $permissions
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data role not found',
                'data'    => null
            ], 404); // Atau bisa diganti dengan 400 jika lebih sesuai
        }

        //render with inertia
     
    }

    public function store(Request $request)
    {
        /**
         * Validate request
         */
        $request->validate([
            'name'          => 'required',
            'permissions'   => 'required',
        ]);

        try {
            $role = Role::create(['name' => $request->name]);

            //assign permissions to role
            $role->givePermissionTo($request->permissions);
            return response()->json([
                'success' => true,
                'message' => 'Data role saved successfully',
                'data'    => $role
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Role creation failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create role',
                'error' => $e->getMessage()
            ], 500); // HTTP 500 for server error
        }
    }

    public function edit($id)
    {
       
        try {
            // Get the role with its permissions
            $role = Role::with('permissions')->findOrFail($id);
    
            // Get all permissions
            $permissions = Permission::all();
    
            return response()->json([
                'success' => true,
                'message' => 'Role and permissions fetched successfully',
                'data' => [
                    'role' => $role,
                    'permissions' => $permissions
                ]
            ], 200); // HTTP 200 for successful retrieval
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Failed to fetch role or permissions: '.$e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch role or permissions',
                'error' => $e->getMessage()
            ], 500); // HTTP 500 for server error
        }



        //render with inertia
      
    }

    public function update(Request $request, Role $role)
    {
        /**
         * validate request
         */
        $request->validate([
            'name'          => 'required',
            'permissions'   => 'required',
        ]);

        try {
        //update role
            $role->update(['name' => $request->name]);

            //sync permissions
            $role->syncPermissions($request->permissions);
            return response()->json([
                'success' => true,
                'message' => 'Role and permissions update successfully',
                'data' => $role
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Failed to update role or permissions: '.$e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to update role or permissions',
                'error' => $e->getMessage()
            ], 500); // HTTP 500 for server error
        }

       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the role by ID
            $role = Role::findOrFail($id);
    
            // Delete the role
            $role->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully',
            ], 200); // HTTP 200 for successful deletion
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Role deletion failed: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500); // HTTP 500 for server error
        }
      
    }
}
