<?php

namespace App\Produk;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use \App\SisewModel;
    protected $table = "barang";
    public $incrementing = false;

    public function toko()
    {
        return $this->belongsTo('\App\Toko','id_toko');
    }

    public function gambar(){
        return $this->hasMany('\App\Produk\ProdukGambar','id_barang')->orderBy('created_at','desc')->take(3);
    }

    public function kategori(){
        return $this->belongsTo('\App\Produk\Kategori','id_kategori');
    }

    public function subkategori(){
        return $this->belongsTo('\App\Produk\SubKategori','id_sub_kategori');
    }

    public function fasilitas(){
        return $this->hasMany('\App\FasilitasBarang','id_toko_produk');
    }
}
