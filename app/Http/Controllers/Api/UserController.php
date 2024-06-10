<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\User;
class UserController extends Controller
{
    public function index()
    {
        try {
            // Get users
            $users = User::when(request()->q, function($users) {
                $users = $users->where('name', 'like', '%' . request()->q . '%');
            })->with('roles')->latest()->paginate(5);
    
            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            // Get roles
            $roles = Role::all();

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'name' => 'required',
                'email' => 'required|unique:users',
                'password' => 'required|confirmed'
            ]);
    
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
    
            // Assign roles to user
            $user->assignRole($request->roles);
    
            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            // Get user
            $user = User::with('roles')->findOrFail($id);

            // Get roles
            $roles = Role::all();

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'roles' => $roles
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        try {
            // Validate request
            $request->validate([
                'name' => 'required',
                'email' => 'required|unique:users,email,' . $user->id,
                'password' => 'nullable|confirmed'
            ]);

            // Check if password is empty
            if ($request->password == '') {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email
                ]);
            } else {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);
            }

            // Assign roles to user
            $user->syncRoles($request->roles);

            // Return JSON response
            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
            // Find user
            $user = User::findOrFail($id);
    
            // Delete user
            $user->delete();
    
            // Return JSON response
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
