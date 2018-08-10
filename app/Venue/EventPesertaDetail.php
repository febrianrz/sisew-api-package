<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class EventPesertaDetail extends Model
{
    use \App\SisewModel;
    protected $table = "toko_event_peserta_detail";
    public $incrementing = false;

    public function paket(){
        return $this->belongsTo('\App\Venue\EventPaket','id_event_paket');
    }
}
