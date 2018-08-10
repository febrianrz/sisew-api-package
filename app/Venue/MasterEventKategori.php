<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class MasterEventKategori extends Model
{
    use \App\SisewModel;
    protected $table = "master_event_kategori";
    protected $fillable = ['nama'];
}
