<?php

namespace App\Merchant;

use Illuminate\Database\Eloquent\Model;

class Katalog extends Model
{
    use \App\SisewModel;
    protected $table = "katalog";
    public $incrementing = false;

    public function kategori(){
        return $this->belongsTo('\App\Merchant\Kategori','id_kategori');
    }
}
