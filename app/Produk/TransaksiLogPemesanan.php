<?php

namespace App\Produk;

use Illuminate\Database\Eloquent\Model;

class TransaksiLogPemesanan extends Model
{
    use \App\SisewModel;
    protected $table = "trx_penyewaan_log_status_pemesanan";
}
