<?php

namespace App\Sisew;

use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    use \App\SisewModel;
    protected $table = "master_metode_pembayaran";
}
