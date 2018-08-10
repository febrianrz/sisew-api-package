<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TokoSaldoRincian extends Model
{
    use \App\SisewModel;
    protected $table = "toko_saldo_rincian";

    public function jenis_transaksi(){
        return $this->belongsTo('\App\Sisew\MasterJenisTransaksi','id_master_jenis_transaksi');
    }

    public static function getPointThisMonth(Toko $toko){
        $today = \Carbon\Carbon::today();
        return TokoSaldoRincian::where('id_toko',$toko->id)
            ->whereBetween('created_at', [date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')])
            ->sum('point');
    }
}
