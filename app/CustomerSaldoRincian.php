<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerSaldoRincian extends Model
{
    use \App\SisewModel;
    protected $table = "customer_saldo_rincian";


    public function jenis_transaksi(){
        return $this->belongsTo('\App\Sisew\MasterJenisTransaksi','id_master_jenis_transaksi');
    }
}
