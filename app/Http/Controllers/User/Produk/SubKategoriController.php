<?php

namespace App\Http\Controllers\User\Produk;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubKategoriController extends Controller
{
    public function index(Request $request)
    {
        $data = \App\Produk\SubKategori::query();
        $data->where('status',1);
        if($request->has('id_kategori')){
            $data->where('id_master_kategori',$request->id_kategori);
        }
        return \App\Http\Resources\User\Produk\SubKategoriResource::collection($data->get());
    }
}
