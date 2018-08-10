<?php

namespace App\Http\Resources\User\Produk;

use Illuminate\Http\Resources\Json\Resource;

class ProdukResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_produk'     => $this->id,
            'id_toko'       => $this->id_toko,
            'nama_toko'     => $this->toko->nama,
            'kode_produk'   => $this->kode_barang,
            'nama_produk'   => $this->nama_barang,
            'satuan'        => 'unit',
            'harga_sewa'    => $this->harga_jual,
            'stok'          => $this->stok_awal,
            'stok_saat_ini' => $this->stok_awal,
            'deskripsi'     => $this->deskripsi,
            'id_kategori'   => $this->subkategori->kategori->id,
            'kategori'      => $this->subkategori->kategori->nama,
            'id_sub_kategori' => $this->subkategori->id,
            'sub_kategori'  => $this->subkategori->nama,
            'created_at'    => $this->created_at,
            'gambar'        => $this->gambar
        ];
    }
}
