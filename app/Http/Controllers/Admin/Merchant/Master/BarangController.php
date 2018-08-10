<?php

namespace App\Http\Controllers\Admin\Merchant\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Merchant\Barang;
use Webpatser\Uuid\Uuid;
class BarangController extends Controller
{
    public function index(Request $request){
        $data = Barang::select(['barang.*']);
        $data->where('id_toko','=',$request->toko->id);
        // print_r($data->get());die();
        return \App\Http\Resources\Merchant\BarangResource::collection($data->paginate(($request->has('limit') ? $request->limit:50 )));
    }

    public function save(Request $request){
        $request->validate([
            'kode_barang'   => 'required',
            'nama_barang'   => 'required',
            'satuan'        => 'required',
            'harga_jual'    => 'required|numeric',
            'stok_awal'     => 'required|numeric',
            'status'        => 'required|in:0,1,2',
        ]);

        $kategori = $request->has('kategori')?\App\Merchant\Kategori::where('nama',$request->kategori)->first():null;
        $subKategori = $request->has('sub_kategori')?\App\Merchant\SubKategori::where('nama',$request->sub_kategori)->first():null;
        $subSubKategori = $request->has('sub_sub_kategori')?\App\Merchant\SubSubKategori::where('nama',$request->sub_sub_kategori)->first():null;

        $row = new Barang;
        $row->id            = Uuid::generate()->string;
        $row->kode_barang   = $request->kode_barang;
        $row->nama_barang   = $request->nama_barang;
        $row->satuan        = 'Unit';
        $row->harga_jual    = $request->harga_jual;
        $row->harga_beli    = 0;
        $row->stok_awal     = $request->stok_awal;
        $row->status        = $request->status;
        $row->deskripsi     = $request->has('deskripsi')?$request->deskripsi:"";
        $row->id_toko       = $request->toko->id;
        $row->id_kategori   = $kategori?$kategori->id:null;
        $row->id_sub_kategori = $subKategori?$subKategori->id:null;
        $row->id_sub_sub_kategori = $subSubKategori?$subSubKategori->id:null;
        $row->hitungan_harga    = $request->has('hitungan_harga')?$request->hitungan_harga:1;
        $row->satuan_harga      = $request->has('satuan_harga')?$request->satuan_harga:'Jam';
        $row->kebijakan_pembatalan = $request->kebijakan_pembatalan;
        $row->kebijakan_overtime = $request->kebijakan_overtime;
        $row->kebijakan_penggunaan = $request->kebijakan_penggunaan;
        $row->save();
        //masukkan gambar
        if($request->has('gambar')){
            foreach($request->gambar as $gambar){
                $rowGambar              = new \App\Merchant\BarangGambar;
                $rowGambar->id_barang   = $row->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }
        //masukkan fasilitas
        // \App\FasilitasBarang::where('id_toko_produk', $row->id)->delete();
        if($request->has('fasilitas')){
            foreach($request->get('fasilitas') as $fasilitas){
                $rowFasilitas = new \App\FasilitasBarang;
                $rowFasilitas->fasilitas = $fasilitas;
                $rowFasilitas->id_toko_produk = $row->id;
                $rowFasilitas->save();
            }
        }

        if($request->has('info')){
            foreach($request->get('info') as $info){
                $rowFasilitas = new \App\InfoTambahanBarang;
                $rowFasilitas->info = $info;
                $rowFasilitas->id_toko_produk = $row->id;
                $rowFasilitas->save();
            }
        }
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request){
        $request->validate([
            'kode_barang'   => 'required',
            'nama_barang'   => 'required',
            'satuan'        => 'required',
            'harga_jual'    => 'required|numeric',
            'stok_awal'     => 'required|numeric',
            'status'        => 'required|in:0,1,2'
        ]);
        $kategori = $request->has('kategori')?\App\Merchant\Kategori::where('nama',$request->kategori)->first():null;
        $subKategori = $request->has('sub_kategori')?\App\Merchant\SubKategori::where('nama',$request->sub_kategori)->first():null;
        $subSubKategori = $request->has('sub_sub_kategori')?\App\Merchant\SubSubKategori::where('nama',$request->sub_sub_kategori)->first():null;
        
        $row = Barang::find($id);
        $row->kode_barang   = $request->kode_barang;
        $row->nama_barang   = $request->nama_barang;
        $row->satuan        = 'Unit';
        $row->harga_jual    = $request->harga_jual;
        $row->harga_beli    = 0;
        $row->stok_awal     = $request->stok_awal;
        $row->status        = $request->status;
        if($request->has('deskripsi')){
            $row->deskripsi     = $request->deskripsi;
        }
        $row->id_toko       = $request->toko->id;
        $row->id_kategori   = $kategori?$kategori->id:$row->id_kategori;
        $row->id_sub_kategori = $subKategori?$subKategori->id:$row->id_sub_kategori;
        $row->id_sub_sub_kategori = $subSubKategori?$subSubKategori->id:$row->id_sub_sub_kategori;
        $row->hitungan_harga    = $request->has('hitungan_harga')?$request->hitungan_harga:1;
        $row->satuan_harga      = $request->has('satuan_harga')?$request->satuan_harga:'Jam';
        $row->kebijakan_pembatalan = $request->kebijakan_pembatalan;
        $row->kebijakan_overtime = $request->kebijakan_overtime;
        $row->kebijakan_penggunaan = $request->kebijakan_penggunaan;
        $row->save();

        //hapus gambar 
        if($request->has('id_gambar_hapus')){
            foreach($request->id_gambar_hapus as $gambar){
                $rowGambar =  \App\Merchant\BarangGambar::find($gambar);
                $rowGambar->delete();
            }
        }
        //masukkan gambar
        if($request->has('gambar')){
            foreach($request->gambar as $gambar){
                if($gambar)
                $rowGambar              = new \App\Merchant\BarangGambar;
                $rowGambar->id_barang   = $row->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }
        //masukkan fasilitas
        if($request->has('fasilitas')){
            $exists_data = [];
                foreach($request->get('fasilitas') as $fasilitas){
                    $exists = \App\FasilitasBarang::where('id_toko_produk',$row->id)
                        ->where('fasilitas',$fasilitas)->first();
                    if(!$exists){
                        $rowFasilitas = new \App\FasilitasBarang;
                        $rowFasilitas->fasilitas = $fasilitas;
                        $rowFasilitas->id_toko_produk = $row->id;
                        $rowFasilitas->save();
                    }
                    array_push($exists_data, $fasilitas);
                    
                }
                //hapus jika tidak ada
                \App\FasilitasBarang::where('id_toko_produk', $row->id)
                    ->whereNotIn('fasilitas',$exists_data)
                    ->delete();
        } else {
            \App\FasilitasBarang::where('id_toko_produk', $row->id)
                    ->delete();
        }
        
        if($request->has('info')){
            $exists_data = [];
                foreach($request->get('info') as $info){
                    $exists = \App\InfoTambahanBarang::where('id_toko_produk',$row->id)
                        ->where('info',$info)->first();
                    if(!$exists){
                        $rowFasilitas = new \App\InfoTambahanBarang;
                        $rowFasilitas->info = $info;
                        $rowFasilitas->id_toko_produk = $row->id;
                        $rowFasilitas->save();
                    }
                    array_push($exists_data, $fasilitas);
                }
                \App\InfoTambahanBarang::where('id_toko_produk', $row->id)
                    ->whereNotIn('info',$exists_data)
                    ->delete();
        } else {
            \App\InfoTambahanBarang::where('id_toko_produk', $row->id)
                    ->delete();
        }
        
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }
}
