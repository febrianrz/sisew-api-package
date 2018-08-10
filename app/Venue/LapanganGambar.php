<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class LapanganGambar extends Model
{
    use \App\SisewModel;
    protected $table = "toko_produk_gambars";
    public $incrementing = false;
}
