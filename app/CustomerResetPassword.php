<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerResetPassword extends Model
{
    use \App\SisewModel;
    protected $table = "customer_forgot_password";
    public $incrementing = false;
}
