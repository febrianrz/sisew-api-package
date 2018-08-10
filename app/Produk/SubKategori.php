<?php

namespace App\Produk;

use Illuminate\Database\Eloquent\Model;

class SubKategori extends Model
{
    use \App\SisewModel;
    protected $table = "master_sewa_sub_kategori";

    public function kategori()
    {
        return $this->belongsTo('\App\Produk\Kategori','id_master_kategori');
    }
}
