<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use \App\SisewModel;
    protected $table = "toko_events";
    public $incrementing = false;

    public function toko(){
        return $this->belongsTo('\App\Toko','id_toko');
    }

    public function kategori(){
        return $this->belongsTo('\App\Venue\MasterEventKategori','id_produk_sub_kategori');
    }

    public function gambar(){
        return $this->hasMany('\App\Venue\EventGambar','id_toko_event');
    }

    public function fasilitas(){
        return $this->hasMany('\App\Venue\EventFasilitas','id_toko_event');
    }

    public function hasilFasilitas(){
        return $this->hasMany('\App\Venue\EventHasilFasilitas','id_toko_event');
    }

    public function paket(){
        return $this->hasMany('\App\Venue\EventPaket','id_toko_event');
    }
}
