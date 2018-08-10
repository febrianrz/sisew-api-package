<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class LapanganResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        
        $is_favorite = false;
        if($request->user){
            $cek = \App\TokoProdukFavorite::where('id_toko_produk',$this->id)
                ->where('id_customer',$request->user->id)->first();
            if($cek) $is_favorite = true;
        }
        
        return [
            'id'        => $this->id,
            'nama'      => $this->nama,
            'nama_venue'      => $this->toko->nama,
            'slug'      => $this->slug,
            'keterangan'=> $this->keterangan,
            'harga'     => $this->harga,
            'status'    => $this->status,
            'status_info'=> $this->status==1?'Aktif':'Tidak Aktif',
            'id_sub_kategori' => $this->id_produk_sub_kategori,
            'sub_kategori' => $this->subProduk?$this->subProduk->nama:null,
            'id_jenis_lantai' => $this->id_jenis_lantai,
            'jenis_lantai' => $this->jenisLantai->nama,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'gambar'    => LapanganGambarResource::collection($this->gambar->take(3)),
            'fasilitas' => $this->fasilitas,
            'venue'     => new \App\Http\Resources\TokoResource($this->toko),
            'favorite'  => $is_favorite
        ];
    }
}
