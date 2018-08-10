<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CronService extends Model
{
    use \App\SisewModel;
    protected $table = "cron_service";
}
