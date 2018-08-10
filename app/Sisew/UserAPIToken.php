<?php

namespace App\Sisew;

use Illuminate\Database\Eloquent\Model;

class UserAPIToken extends Model
{
    use \App\SisewModel;
    protected $table = "api_public_key";
    public $incrementing = false;
}
