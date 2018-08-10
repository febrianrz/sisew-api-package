<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{
    public function index(Request $request){
        return \App\Http\Resources\Admin\NewsResource::collection(\App\Admin\News::whereNull('deleted_at')->orderBy('created_at','desc')->paginate(($request->has('limit') ? $request->limit:10 )));
    }

    public function detail($id, Request $request){
        $row = \App\Admin\News::findOrFail($id);
        return new \App\Http\Resources\Admin\NewsResource($row);
    }

    public function save(Request $request)
    {
        $row = new \App\Admin\News;
        $row->id = \Ramsey\Uuid\Uuid::uuid1();
        $row->judul = $request->judul;
        $row->isi = $request->isi;
        $row->gambar = $request->gambar;
        $row->status = $request->status;
        $row->for = 2;
        $row->save();
        \MyFirebaseAdmin::sendPemberitahuan($row);
        echo "berhasil menyimpan data";
    }
}
