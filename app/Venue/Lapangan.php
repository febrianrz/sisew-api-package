<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lapangan extends Model
{

    use SoftDeletes;
    use \App\SisewModel;

    protected $table = "toko_produks";
    public $incrementing = false;
    protected $dates = ['deleted_at'];

    public function subProduk(){
        return $this->belongsTo('\App\Venue\SubKategori','id_produk_sub_kategori');
    }

    public function jenisLantai()
    {
        return $this->belongsTo('\App\Venue\JenisLantai','id_jenis_lantai');
    }

    public function jadwal(){
        return $this->hasMany('\App\Venue\JadwalLapangan','id_toko_produk');
    }

    public function toko(){
        return $this->belongsTo('\App\Toko','id_toko');
    }

    public function gambar(){
        return $this->hasMany('\App\Venue\LapanganGambar','id_produk');
    }

    public function fasilitas(){
        return $this->hasMany('\App\FasilitasLapangan','id_toko_produk');
    }
}
