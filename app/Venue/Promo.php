<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use \App\SisewModel;
    protected $table = "potongan_harga";
    public $incrementing = false;
}
