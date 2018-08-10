<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Migration extends Model
{
    use \App\SisewModel;
    protected $table = "migrations";
    protected $fillable = ['migration', 'batch'];

}