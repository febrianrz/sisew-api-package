<?php

namespace App\Produk;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use \App\SisewModel;
    protected $table = "master_sewa_kategori";
}
