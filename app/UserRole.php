<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use \App\SisewModel;
    protected $table = "user_roles";
}
