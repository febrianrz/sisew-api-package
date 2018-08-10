<?php

namespace App\Tables\Builders;

use App\Migration;
use LaravelEnso\VueDatatable\app\Classes\Table;

class MigrationTable extends Table
{
    protected $templatePath = __DIR__.'/../Templates/migrations.json';

    public function query()
    {
        return Migration::select(\DB::raw('
            id as "dtRowId", migration, batch
        '));
    }
}
