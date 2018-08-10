<?php

namespace App\Http\Controllers\Admin\Merchant\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Merchant\Kategori;
class KategoriController extends Controller
{
    public function index(Request $request){
        $data = Kategori::paginate(($request->has('limit') ? $request->limit:10 ));
        return \App\Http\Resources\Merchant\KategoriResource::collection($data);
    }

    public function save(Request $request){
        $request->validate([
            'nama'      => 'required',
            'keterangan'=> 'required',
            'status'    => 'required|in:0,1'
        ]);

        $row = new Kategori;
        $row->nama      = $request->nama;
        $row->keterangan= $request->keterangan;
        $row->status    = $request->status;
        $row->save();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request){
        $row = Kategori::find($id);
        if(!$row){
            return response()->json([
                'status'    => false,
                'msg'       => 'Data tidak ditemukan'
            ]); 
        }
        $request->validate([
            'nama'      => 'required',
            'keterangan'=> 'required',
            'status'    => 'required|in:0,1'
        ]);
        $row->nama      = $request->nama;
        $row->keterangan= $request->keterangan;
        $row->status    = $request->status;
        $row->save();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }
}
