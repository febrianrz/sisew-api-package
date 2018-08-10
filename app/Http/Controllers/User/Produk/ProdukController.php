<?php

namespace App\Http\Controllers\User\Produk;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $produk = \App\Produk\Produk::query();
        $produk->select(['barang.*']);
        $produk->join('tokos','tokos.id','=','barang.id_toko');
        $produk->leftJoin('provinsis','provinsis.id','=','tokos.provinsis_id');
        $produk->leftJoin('kotas','kotas.id','=','tokos.kotas_id');

        // $produk->where('tokos.jenis_merchant',MERCHANT_JENIS_VENUE);
        // $produk->where('tokos.status_online',MERCHANT_STATUS_ONLINE);
        $produk->where('tokos.status_online','=',1);
        $produk->where('barang.status',1);
        
        if($request->has('id_kategori')) $produk->where('id_kategori',$request->id_kategori);
        if($request->has('id_subkategori')) $produk->where('id_sub_kategori',$request->id_subkategori);

        if($request->has('harga_terendah')) $produk->where('barang.harga_jual','>=',$request->harga_terendah);
        if($request->has('harga_termahal')) $produk->where('barang.harga_jual','<=',$request->harga_termahal);
        if($request->has('kata_kunci')) {
            $katakunci = $request->kata_kunci;
            $produk->where(function($query) use($katakunci) {
                        $query->where('barang.nama_barang','like','%'.$katakunci.'%');
                        $query->orWhere('provinsis.nama','like','%'.$katakunci.'%');
                        $query->orWhere('kotas.nama','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.nama','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.alamat_gmap','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.alamat','like','%'.$katakunci.'%');

                    });
        }
        if($request->has('order_by')){
            if($request->order_by == "termahal") $produk->orderBy('barang.harga_jual','desc');
            if($request->order_by == "termurah") $produk->orderBy('barang.harga_jual','asc');
            if($request->order_by == "terbaru") $produk->orderBy('barang.created_at','desc');
        }
        return \App\Http\Resources\Merchant\BarangResource::collection($produk->paginate(($request->has('limit') ? $request->limit:8 )));
    }

    public function detail($id,Request $request)
    {
        $produk = \App\Produk\Produk::findOrFail($id);
        return new \App\Http\Resources\User\Produk\ProdukResource($produk);
    }

    public function setFavorite($id, Request $request){
        $barang = \App\Produk\Produk::find($id);
        if(!$barang){
            return response()->json([
                'status'    => false,
                'msg'       => 'Barang tidak valid'
            ]);
        }
        $cek = \App\ProdukFavorite::where('id_barang',$id)
            ->where('id_customer',$request->user->id)
            ->first();
        if(!$cek){
            $new = new \App\ProdukFavorite;
            $new->id_customer   = $request->user->id;
            $new->id_barang     = $id;
            $new->save();
        }
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan sebagai favorite'
        ]);
    }

    public function unFavorite($id, Request $request){
        $barang = \App\Produk\Produk::find($id);
        if(!$barang){
            return response()->json([
                'status'    => false,
                'msg'       => 'Barang tidak valid'
            ]);
        }
        \App\ProdukFavorite::where('id_barang',$id)
            ->where('id_customer',$request->user->id)
            ->delete();
        return response()->json([
                'status'    => true,
                'msg'       => 'Berhasil menghapus dari favorite'
        ]);
    }
}
