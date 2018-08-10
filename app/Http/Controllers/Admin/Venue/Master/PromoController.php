<?php

namespace App\Http\Controllers\Admin\Venue\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PromoController extends Controller
{
    public function index(Request $request){
        return \App\Http\Resources\Venue\PromoResource::collection(\App\Venue\Promo::where('id_toko',$request->toko->id)->paginate(($request->has('limit') ? $request->limit:10 )));
    }

    public function save(Request $request)
    {
        $request->validate([
            'kode'      => 'required|max:30',
            'jenis'     => 'required|in:1,2',
            'nilai'     => 'required|numeric',
            'keterangan'=> 'required',
            'status'    => 'required|in:1,0'
        ]);
        $row = new \App\Venue\Promo;
        $row->id            = \Webpatser\Uuid\Uuid::generate()->string;
        $row->kode_potongan = $request->kode;
        $row->jenis_potongan = $request->jenis;
        $row->nilai_potongan = $request->nilai;
        $row->keterangan    = $request->keterangan;
        $row->status        = $request->status;
        $row->id_toko       = $request->toko->id;
        $row->id_user       = $request->user->id;
        $row->deleted       = 0;
        $row->save();
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'kode'      => 'required|max:15',
            'jenis'     => 'required|in:1,2',
            'nilai'     => 'required|numeric',
            'keterangan'=> 'required',
            'status'    => 'required|in:1,0'
        ]);
        $row = \App\Venue\Promo::find($id);
        if(!$row){
            return response()->json([
                'status'        => false,
                'msg'           => 'Promo tidak ditemukan'
            ]);    
        }
        $row->kode_potongan = $request->kode;
        $row->jenis_potongan = $request->jenis;
        $row->nilai_potongan = $request->nilai;
        $row->keterangan    = $request->keterangan;
        $row->status        = $request->status;
        $row->save();
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }
    
}
