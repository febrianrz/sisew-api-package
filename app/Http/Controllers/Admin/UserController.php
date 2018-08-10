<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function update(Request $request){
        $request->validate([
            'nama'      => 'required',
            'telepon'   => 'required|numeric'
        ]);
        $user = \App\AdminVenue::find($request->user->id);
        if(!$user){
            return response()->json([
                'status'    => false,
                'msg'       => 'User tidak ditemukan'
            ]);
        }
        $user->nama_depan   = $request->nama;
        $user->telepon      = $request->telepon;
        
        $user->save();
        return new \App\Http\Resources\UserResource($user);
    }

    public function changePassword(Request $request){
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required'
        ]);
        $admin = \App\AdminVenue::find($request->user->id);
        if(!password_verify($request->old_password, $admin->password)){
            return response()->json([
                'status'    => false,
                'msg'       => 'Password lama tidak sesuai'
            ]);
        }
        $admin->password = bcrypt($request->new_password);
        if(\MyFirebaseAdmin::updatePassword($admin, $request->new_password)){
            $admin->save();
            return response()->json([
                'status'    => true,
                'msg'       => 'Password berhasil diubah'
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'msg'       => 'Gagal mengubah password'
            ]);
        }
        
    }
}
