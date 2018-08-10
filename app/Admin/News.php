<?php

namespace App\Admin;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use \App\SisewModel;
    protected $table = "global_notifikasi";
    public $incrementing = false;
}
