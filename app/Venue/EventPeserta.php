<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class EventPeserta extends Model
{
    use \App\SisewModel;
    protected $table = "toko_event_peserta";
    public $incrementing = false;

    public function event(){
        return $this->belongsTo('\App\Venue\Event','id_toko_event');
    }

    public function customer(){
        return $this->belongsTo('\App\Customer','id_customer');
    }

    public function bank(){
        return $this->belongsTo('\App\Sisew\MasterBank','id_bank');
    }

    public function metodePembayaran(){
        return $this->belongsTo('\App\Sisew\MetodePembayaran','metode_pembayaran');
    }

    public function pesertaTambahan(){
        return $this->hasMany('\App\Venue\EventPesertaDetail','id_event_peserta');
    }
    
}
