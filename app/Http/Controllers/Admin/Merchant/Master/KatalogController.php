<?php

namespace App\Http\Controllers\Admin\Merchant\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Merchant\Katalog;
use Webpatser\Uuid\Uuid;
class KatalogController extends Controller
{
    public function index(Request $request){
        $data = Katalog::where('id_toko',$request->toko->id)->paginate(($request->has('limit') ? $request->limit:10 ));
        return \App\Http\Resources\Merchant\KatalogResource::collection($data);
    }

    public function save(Request $request){
        $request->validate([
            'nama'      => 'required',
            'deskripsi' => 'required',
            'id_kategori'=> 'required|exists:kategori_katalog,id',
            'status'    => 'required|in:0,1'
        ]);
        $row = new Katalog;
        $row->id    = Uuid::generate()->string;
        $row->deskripsi = $request->deskripsi;
        $row->nama  = $request->nama;
        $row->id_toko = $request->toko->id;
        $row->id_kategori = $request->id_kategori;
        $row->status = $request->status;
        if($request->has('gambar')){
            $row->gambar = $request->gambar->store('public/venue/'.$request->toko->id);
        }
        $row->save();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request){
        $request->validate([
            'nama'      => 'required',
            'deskripsi' => 'required',
            'id_kategori'=> 'required|exists:kategori_katalog,id',
            'status'    => 'required|in:0,1'
        ]);
        $row = Katalog::find($id);
        $row->deskripsi = $request->deskripsi;
        $row->nama  = $request->nama;
        $row->id_toko = $request->toko->id;
        $row->id_kategori = $request->id_kategori;
        $row->status = $request->status;
        if($request->has('gambar')){
            $row->gambar = $request->gambar->store('public/venue/'.$request->toko->id);
        }
        $row->save();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }
}
