<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function setFirebaseToken(Request $request){
        if($request->user){
            $request->user->token_firebase = $request->token;
            $request->user->save();
        }
        $token = \App\Sisew\UserAPIToken::find($request->api->id);
        if($token){
            $token->firebase_token = $request->token;
            $token->save();
            return response()->json([
                'status'    => true,
                'msg'       => 'Berhasil menyimpan data'
            ]);
        } else {
            return response()->json([
                'status'    => true,
                'msg'       => 'Token tidak valid',
            ]);
        }
    }

    public function getSaldo(Request $request){
        $customer = \App\Customer::getUserTokenAndId($request);
        $saldo = \App\CustomerSaldo::where('id_customer','=',$customer->customer_id)->first();
        $pemasukkan = \App\CustomerSaldoRincian::where('id_customer',$customer->customer_id)
            ->where('id_master_jenis_transaksi',4)
            ->orderBy('created_at','desc')
            ->take(100)
            ->get();

        $dataPemasukkan = [];
        foreach($pemasukkan as $pm){
            $tmp = [
                'id'            => $pm->id,
                'id_booking'    => $pm->id_customer_booking,
                'jenis_transaksi'=> $pm->jenis_transaksi?$pm->jenis_transaksi->nama:"-",
                'amount'        => $pm->amount,
                'kode_booking'  => $pm->kode_booking,
                'created_at'    => $pm->created_at,
                'source'         => getJenisBooking($pm->jenis),
                'tanggal'       => getTanggalIndonesia($pm->created_at),
                'ref_no'        => date('YmdHis',strtotime($pm->created_at))
            ];
            array_push($dataPemasukkan,$tmp);
        }
        return response()->json([
            'data'=>[
                'saldo'         => (!$saldo?0:$saldo->amount),
                'pemasukkan'    => $dataPemasukkan
            ]
        ]);
    }

    public function updateprofile(Request $request){
        $request->validate([
            'nama'      => 'required',
            'telepon'   => 'required|numeric'
        ]);
        $user = \App\Customer::find($request->user->id);
        if(!$user){
            return response()->json([
                'status'    => false,
                'msg'       => 'User tidak ditemukan'
            ]);
        }
        $user->nama   = $request->nama;
        $user->telepon      = $request->telepon;
        $user->save();
        return new \App\Http\Resources\CustomerResource($user);
    }

    public function changePassword(Request $request){
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required'
        ]);
        $admin = \App\Customer::find($request->user->id);
        if(!password_verify($request->old_password, $admin->password)){
            return response()->json([
                'status'    => false,
                'msg'       => 'Password lama tidak sesuai'
            ]);
        }
        $admin->password = bcrypt($request->new_password);
        if(\MyFirebaseUser::updatePassword($admin, $request->new_password)){
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

    public function generateSisewToken(Request $request){
        $request->validate([
            'nama_os'       => 'required',
            'api_os'        => 'required|numeric',
            'nama_device'   => 'required',
            'android_id'    => 'required'
        ]);
        //cek apakah android id dan date valid, kalau masih ada, jangan dibuat lagi, pakai yang ada
        $token = \App\Sisew\UserAPIToken::where('android_id','=',$request->android_id)
            ->whereDate('expired_at','>=',date('Y-m-d H:i:s'))
            ->first();
        if(!$token){
            $token              = new \App\Sisew\UserAPIToken;
            $token->id          = \Webpatser\Uuid\Uuid::generate()->string; 
            $token->app_key     = str_random(200);
            $token->public_key  = str_random(200);
            $token->nama_os     = $request->nama_os;
            $token->nomor_api   = $request->api_os;
            $token->nama_device = $request->nama_device;
            $token->android_id  = $request->android_id;
            $token->expired_at  = date('Y-m-d H:i:s',strtotime('+1 month'));
            $token->id_customer = $request->customer_id;
        }
        if($token->id_customer) $token->id_customer = $request->customer_id;
        
        $token->last_access_at = date('Y-m-d H:i:s',strtotime('+1 month'));
        $token->save();

        
        return response()->json([
            'status'    => true,
            'msg'       => 'Token generated',
            'app_key'   => $token->app_key,
            'public_key'=> $token->public_key,
            'expired_at'=> (strtotime($token->expired_at) * 1000)
        ]);
    }
}
