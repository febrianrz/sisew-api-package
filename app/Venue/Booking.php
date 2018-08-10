<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use \App\SisewModel;
    protected $table = "customer_booking";
    public $incrementing = false;

    
}
