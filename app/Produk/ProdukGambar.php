<?php

namespace App\Produk;

use Illuminate\Database\Eloquent\Model;

class ProdukGambar extends Model
{
    use \App\SisewModel;
    protected $table = "barang_gambar";
    public $incrementing = false;
}
