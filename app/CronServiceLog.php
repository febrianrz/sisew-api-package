<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CronServiceLog extends Model
{
    use \App\SisewModel;
    protected $table = "cron_service_log";
}
