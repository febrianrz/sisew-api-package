<?php

namespace App\Merchant;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use \App\SisewModel;
    protected $table = "barang";
    public $incrementing = false;

    public function kategori(){
        return $this->belongsTo('\App\Merchant\Kategori','id_kategori');
    }

    public function sub_kategori(){
        return $this->belongsTo('\App\Merchant\SubKategori','id_sub_kategori');
    }

    public function sub_sub_kategori(){
        return $this->belongsTo('\App\Merchant\SubSubKategori','id_sub_sub_kategori');
    }

    public function gambar(){
        return $this->hasMany('\App\Merchant\BarangGambar','id_barang')->orderBy('created_at','asc')->take(3);
    }

    public function fasilitas(){
        return $this->hasMany('\App\FasilitasBarang','id_toko_produk');
    }

    public function infoTambahan(){
        return $this->hasMany('\App\InfoTambahanBarang','id_toko_produk');
    }

    
}
