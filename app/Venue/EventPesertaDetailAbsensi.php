<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class EventPesertaDetailAbsensi extends Model
{
    use \App\SisewModel;
    protected $table = "toko_event_peserta_detail_absensi";
    public $incrementing = false;
}
