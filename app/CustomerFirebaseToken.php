<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerFirebaseToken extends Model
{
    use \App\SisewModel;
    protected $table = "customer_firebase_token";
    public $increments = false;
}
