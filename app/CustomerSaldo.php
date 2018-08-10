<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerSaldo extends Model
{
    use \App\SisewModel;
    protected $table = "customer_saldo";
    public $incrementing = false;
}
