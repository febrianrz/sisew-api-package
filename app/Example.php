<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Example extends Model
{
    use \App\SisewModel;
    protected $casts = ['is_active' => 'boolean'];
}
