<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    use \App\SisewModel;
    protected $table = "api_service";
}
