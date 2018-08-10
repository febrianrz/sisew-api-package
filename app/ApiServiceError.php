<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApiServiceError extends Model
{
    use \App\SisewModel;
    protected $table = "api_service_error_message";
    protected $primaryKey = "code";
}
