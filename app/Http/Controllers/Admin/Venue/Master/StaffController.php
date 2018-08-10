<?php

namespace App\Http\Controllers\Admin\Venue\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StaffController extends Controller
{
    public function index(Request $request){
        $owner_id = $request->toko->users_id;
        $data     = \App\AdminVenue::where('parent_id',$owner_id)->paginate(($request->has('limit') ? $request->limit:10 ));
        return \App\Http\Resources\UserResource::collection($data);
    }

    public function save(Request $request){
        $request->validate([
            'nama'      => 'required',
            'email'     => 'required|unique:users,email',
            'telepon'   => 'required',
            'password'  => 'required|min:6',
            'role_id'   => 'required|exists:user_roles,id',
            'status'    => 'required|in:0,1'
        ]);
        $row = new \App\AdminVenue;

        $row->id = \Webpatser\Uuid\Uuid::generate()->string;
        $row->user_role_id  = $request->role_id;
        $row->parent_id     = $request->toko->users_id;
        $row->nama_depan    = $request->nama;
        $row->email         = $request->email;
        $row->password      = bcrypt($request->password);
        $row->telepon       = $request->telepon;
        $row->status        = $request->status;
        $row->type_app      = $request->toko->owner->type_app;
        $row->api_token     = str_random(200);
        $row->google_id     = \Webpatser\Uuid\Uuid::generate()->string;
        $row->save();
        \MyFirebaseAdmin::createUserAdmin($row,$request->password);
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request){
        $request->validate([
            'nama'      => 'required',
            'email'      => 'required',
            'telepon'   => 'required',
            'role_id'   => 'required|exists:user_roles,id',
            'status'    => 'required|in:0,1'
        ]);
        $email_diubah = false;
        $row = \App\AdminVenue::find($id);
        if(!$row){
            return response()->json([
                'status'        => false,
                'msg'           => 'User tidak ditemukan'
            ]);    
        }
        $row->user_role_id  = $request->role_id;
        $row->nama_depan    = $request->nama;
        
        if($request->has('password')){
            $row->password      = bcrypt($request->password);
        }
        
        $row->telepon       = $request->telepon;
        $row->status        = $request->status;
        $row->save();
        // echo $request->password;die();
        if($request->has('password')){
            //jika passwordnya diubah maka update juga ke firebase
            \MyFirebaseAdmin::updatePassword($row,$request->password);
        }
        
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }
}
