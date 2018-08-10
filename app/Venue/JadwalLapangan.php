<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class JadwalLapangan extends Model
{
    use \App\SisewModel;
    protected $table = "toko_produk_jadwal_harga";
    public $incrementing = false;

    public function lapangan()
    {
        return $this->belongsTo('\App\Venue\Lapangan','id_toko_produk');
    }

    
}
