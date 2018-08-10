<?php

namespace App\Http\Controllers\User\Produk;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        return \App\Http\Resources\User\Produk\KategoriResource::collection(\App\Produk\Kategori::where('status',1)->get());
    }
}
