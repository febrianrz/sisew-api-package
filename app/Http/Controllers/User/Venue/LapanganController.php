<?php

namespace App\Http\Controllers\User\Venue;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LapanganController extends Controller
{
    /**
     * Menampilkan list venue saja
     * Mandatory variable
     * @var tanggal
     * Optional Variable
     * @var nama (nama venue/kota)
     * @var jenis_lapangan (jenis lapangan)
     * @var jenis_lantai (jenis lantai lapangan)
     */
    public function venueList(Request $request){
        $request->validate([
            'tanggal'   => 'required|date_format:Y-m-d'
        ]);

        $venue = \App\Toko::query();
        $venue->where('jenis_merchant',MERCHANT_JENIS_VENUE);
        $venue->where('status_online',MERCHANT_STATUS_ONLINE);

        return \App\Http\Resources\TokoResource::collection($venue->paginate(($request->has('limit') ? $request->limit:10 )));

    }

    public function lapanganList(Request $request){
        $request->validate([
            'tanggal'   => 'required|date_format:Y-m-d'
        ]);

        $venue = \App\Venue\Lapangan::query();
        
        /**
         *   @Query("tanggal") String tanggal,
         *   @Query("jam") String jam,
         *   @Query("jenis_lantai") String jenis_lantai,
         *   @Query("jenis_lapangan") String jenis_lapangan,
         *   @Query("harga_terendah") Integer harga_terendah,
         *   @Query("harga_termahal") Integer harga_termahal,
         *   @Query("order_by") String orderBy,
         *   @Query("kata_kunci") String kataKunci
         */

        $venue->select(['toko_produks.*']);
        
        $venue->join('tokos','tokos.id','=','toko_produks.id_toko');
        $venue->join('jenis_lantai','jenis_lantai.id','=','toko_produks.id_jenis_lantai');
        // $venue->leftJoin('produk_sub_kategoris','produk_sub_kategoris.id','=','toko_produks.id_produk_sub_kategori');
        $venue->leftJoin('provinsis','provinsis.id','=','tokos.provinsis_id');
        $venue->leftJoin('kotas','kotas.id','=','tokos.kotas_id');
        
        $venue->where('tokos.jenis_merchant',MERCHANT_JENIS_VENUE);
        $venue->where('tokos.status_online',MERCHANT_STATUS_ONLINE);
        $venue->where('toko_produks.status','=',1);
        $venue->where('tokos.status_online','=',1);
        /**
         * Filtering
         */

        if($request->has('jenis_lantai')) $venue->where('jenis_lantai.nama','=',$request->jenis_lantai);
        if($request->has('jenis_lapangan')) $venue->where('produk_sub_kategoris.nama','=',$request->jenis_lapangan);
        // if($request->has('harga_terendah')) $venue->where('toko_produks.harga','>=',$request->harga_terendah);
        // if($request->has('harga_termahal')) $venue->where('toko_produks.harga','<=',$request->harga_termahal);
        if($request->has('kata_kunci')) {
            $katakunci = $request->kata_kunci;
            $venue->where(function($query) use($katakunci) {
                        $query->where('toko_produks.nama','like','%'.$katakunci.'%');
                        $query->orWhere('provinsis.nama','like','%'.$katakunci.'%');
                        $query->orWhere('kotas.nama','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.nama','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.alamat_gmap','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.alamat','like','%'.$katakunci.'%');

                    });
        }


        /**
         * Ordering
         */
        if($request->has('order_by')){
            if($request->order_by == "terbaru") $venue->orderBy('toko_produks.created_at','desc');
            
        }

        return \App\Http\Resources\Venue\LapanganResource::collection($venue->paginate(($request->has('limit') ? $request->limit:10 )));

    }

    public function getJadwal($id, Request $request){
        $request->validate([
            'tanggal'   => 'required'
        ]);
        $lapangan   = \App\Venue\Lapangan::findOrFail($id);
        $tanggal = $request->tanggal;
        $hari                   = date('w',strtotime($tanggal))+1;
        if($request->has('hari')){
            $hari = $request->hari;
        }
        $request->tanggal   = $tanggal;
        $request->lapangan  = $lapangan;
        $adminController = new \App\Http\Controllers\Admin\Venue\LapanganController;
        return $adminController->getListJadwal($lapangan, $hari);
    }

    public function setFavorite($id, Request $request){
        $lapangan = \App\Venue\Lapangan::find($id);
        if(!$lapangan) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Lapangan tidak valid'
            ]);
        }
        /**
         * Cek dulu apakah user dan lapangan sudah ada di favorite
         */        
        $cek = \App\TokoProdukFavorite::where('id_toko_produk',$id)
            ->where('id_customer',$request->user->id)->first();
        if(!$cek){
            $new = new \App\TokoProdukFavorite;
            $new->id_toko_produk    = $id;
            $new->id_customer       = $request->user->id;
            $new->save();
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan sebagai favorite'
        ]);
    }

    public function unFavorite($id, Request $request){
        $lapangan = \App\Venue\Lapangan::find($id);
        if(!$lapangan) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Lapangan tidak valid'
            ]);
        }
        \App\TokoProdukFavorite::where('id_toko_produk',$id)
            ->where('id_customer',$request->user->id)
            ->delete();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menghapus dari favorite'
        ]);

    }
}
