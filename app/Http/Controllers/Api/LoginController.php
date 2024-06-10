<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\General;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Str;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        //set validasi
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Email harus berupa alamat email yang valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);
        
       
        //response error validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get "email" dan "password" dari input
        $credentials = $request->only('email', 'password');

        //check jika "email" dan "password" tidak sesuai
        if(!$token = auth()->guard('api_admin')->attempt($credentials)) {

            //response login "failed"
            return response()->json([
                'success' => false,
                'message' => 'Email or Password is incorrect'
            ], 401);

        }

        $user = auth()->guard('api_admin')->user();
        $user = User::with('general')->findOrFail($user->id);
        $permission = $user->getPermissionArray();

        
        //response login "success" dengan generate "Token"
        return response()->json([
            'success' => true,
            'user'    => $user,  
            'token'   => $token,
            'permission' => $permission
        ], 200);
    }
    
    /**
     * getUser
     *
     * @return void
     */
    public function getUser()
    {
        $user = auth()->guard('api_admin')->user();
        $user = User::with('general')->findOrFail($user->id);
        // $user = $user->getPermissionArray();
        // dd($user);
        $credentials = [
            'email' => $user->email,
            'password' => $user->password // This should be the plain text password if available
        ];
       
        $token = auth()->guard('api_admin')->attempt($credentials);
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
    
        return response()->json([
            'success' => true,
            'user'    => $user,
            'token' => $token
        ], 200);
    }
    
    /**
     * refreshToken
     *
     * @param  mixed $request
     * @return void
     */
    public function refreshToken(Request $request)
    {
        //refresh "token"
        $refreshToken = JWTAuth::refresh(JWTAuth::getToken());

        //set user dengan "token" baru
        $user = JWTAuth::setToken($refreshToken)->toUser();

        //set header "Authorization" dengan type Bearer + "token" baru
        $request->headers->set('Authorization','Bearer '.$refreshToken);

        //response data "user" dengan "token" baru
        return response()->json([
            'success' => true,
            'user'    => $user,
            'token'   => $refreshToken,  
        ], 200);
    }
    
    /**
     * logout
     *
     * @return void
     */
    public function logout()
    {
        //remove "token" JWT
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        //response "success" logout
        return response()->json([
            'success' => true,
        ], 200);

    }

    public function updateProfile(Request $request){

        
        $auth = auth()->user();
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            // 'email'    => 'required|unique:customers,email,'.$auth->id,
            // 'logo'   => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Ukuran maksimum untuk avatar (2MB)
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            // 'logo.image' => 'Logo harus berupa gambar.',
            // 'logo.mimes' => 'Logo harus dalam format jpeg, png, jpg, atau gif.',
            // 'logo.max' => 'Ukuran file LOGO tidak boleh lebih dari 2MB.',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        $update = User::find($auth->id);
       
        $update->name = $request->name;
        // $update->email = $request->email;
    
        // Check if avatar is uploaded
      
        $update->save();

        $updateGeneral = General::where('id_user',$auth->id)->first();
       
        if($updateGeneral){
            $updateGeneral->nama_company = $request->nama_company;
            $updateGeneral->alamat = $request->alamat;
            $updateGeneral->whatsapp = $request->whatsapp;
            $updateGeneral->instagram = $request->instagram;
            $updateGeneral->facebook = $request->facebook;
            $updateGeneral->tiktok = $request->tiktok;
            $updateGeneral->youtube = $request->youtube;
            if ($request->hasFile('logo')) {
                $avatar = $request->file('logo');
                $avatarName = 'logo_' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = $avatar->storeAs('general', $avatarName, 'public'); // Store avatar in storage/app/public/avatars
                $updateGeneral->logo = $avatarPath; // Save avatar path to the database
            }

            $updateGeneral->save();
        }else{
            $saveGeneral = new General();
            $saveGeneral->nama_company = $request->nama_company;
            $saveGeneral->alamat = $request->alamat;
            $saveGeneral->id_user = $auth->id;
            $saveGeneral->whatsapp = $request->whatsapp;
            $saveGeneral->instagram = $request->instagram;
            $saveGeneral->facebook = $request->facebook;
            $saveGeneral->tiktok = $request->tiktok;
            $saveGeneral->youtube = $request->youtube;
            if ($request->hasFile('logo')) {
                $avatar = $request->file('logo');
                $avatarName = 'logo_' . time() . '.' . $avatar->getClientOriginalExtension();
                $avatarPath = $avatar->storeAs('general', $avatarName, 'public'); // Store avatar in storage/app/public/avatars
                $saveGeneral->logo = $avatarPath; // Save avatar path to the database
            }

            $saveGeneral->save();

        }
    
        if($update) {
    
            return response()->json([
                'success' => true,
                'message' => 'update Customer Berhasil',
                'user'    => auth()->user()
            ], 200);
            //return with Api Resource
    
        }
    
        return response()->json([
            'success' => false,
            'message' => 'update Customer Gagal',
            'user'    => auth()->user()
        ], 201);
    
        //return failed with Api Resource
        // return new CustomerResource(false, 'Register Customer Gagal!', null);
    }
    

    public function updatePassword(Request $request)
    {
        // Validasi request
        $request->validate([
            'oldpassword' => 'required|string|min:8|confirmed',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'oldpassword.required' => 'Kolom kata sandi lama diperlukan.',
            'oldpassword.string' => 'Kata sandi lama harus berupa teks.',
            'oldpassword.min' => 'Kata sandi lama harus terdiri dari minimal 8 karakter.',
            'oldpassword.confirmed' => 'Konfirmasi kata sandi lama tidak cocok.',
            'password.required' => 'Kolom kata sandi baru diperlukan.',
            'password.string' => 'Kata sandi baru harus berupa teks.',
            'password.min' => 'Kata sandi baru harus terdiri dari minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.'
        ]);

        $user = auth()->user();

        // Periksa apakah password lama sesuai
        if (!Hash::check($request->oldpassword, $user->password)) {
            return response()->json(['error' => 'Password lama tidak sesuai'], 422);
        }

        // Update password pengguna
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'update Password Customer Berhasil',
            'user'    => auth()->user()
        ], 200);
    }
}
