<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPaket extends Model
{
    use \App\SisewModel;
    use SoftDeletes;
    protected $table = "toko_events_paket";
    public $incrementing = false;
}
