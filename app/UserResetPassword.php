<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserResetPassword extends Model
{
    use \App\SisewModel;
    protected $table = "user_forgot_password";
    public $incrementing = false;
}
