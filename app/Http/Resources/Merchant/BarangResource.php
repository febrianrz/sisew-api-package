<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\Resource;

class BarangResource extends Resource
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
            $cek = \App\ProdukFavorite::where('id_barang',$this->id)
            ->where('id_customer',$request->user->id)
            ->first();
            if($cek) $is_favorite = true;
        }
        
        $stokDisewa = \App\Produk\Transaksi::getTotalBarangDisewa($this->id);
        $stokSaatIni = $this->stok_awal - $stokDisewa;
        return [
            'id'            => $this->id,
            'kode_barang'   => $this->kode_barang,
            'nama_barang'   => $this->nama_barang,
            'deskripsi'     => $this->deskripsi?$this->deskripsi:"",
            'satuan'        => $this->satuan,
            'harga_jual'    => $this->harga_jual,
            'stok_awal'     => $this->stok_awal,
            'stok_saat_ini' => $stokSaatIni,
            'id_katalog'    => "",
            'katalog'       => '',
            "kategori"      => $this->kategori?new \App\Http\Resources\Sisew\SimpleResource($this->kategori):null,
            "sub_kategori"  => $this->sub_kategori?new \App\Http\Resources\Sisew\SimpleResource($this->sub_kategori):null,
            "sub_sub_kategori" => $this->sub_sub_kategori?new \App\Http\Resources\Sisew\SimpleResource($this->sub_sub_kategori):null,
            'status'        => $this->status,
            "toko"          => new \App\Http\Resources\TokoResource($this->toko),
            "gambar"        => BarangGambarResource::collection($this->gambar),
            "hitungan_harga" => $this->hitungan_harga,
            "satuan_harga"  => $this->satuan_harga,
            "fasilitas"     => $this->fasilitas,
            "info_tambahan" => $this->infoTambahan,
            'favorite'      => $is_favorite,
            'kebijakan_pembatalan' => $this->kebijakan_pembatalan?$this->kebijakan_pembatalan:"",
            'kebijakan_overtime' => $this->kebijakan_overtime?$this->kebijakan_overtime:"",
            'kebijakan_penggunaan' => $this->kebijakan_penggunaan?$this->kebijakan_penggunaan:""
        ];
    }
}
