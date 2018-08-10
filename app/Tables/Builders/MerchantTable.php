<?php

namespace App\Tables\Builders;

use LaravelEnso\VueDatatable\app\Classes\Table;

class MerchantTable extends Table
{
    protected $templatePath = __DIR__.'/../Templates/merchants.json';

    public function query()
    {
        return \App\Toko::select(\DB::raw('
            id as "dtRowId", nama, alamat, status_online, created_at, 
            (CASE 
                WHEN jenis_merchant = 1 THEN "Venue"
                ELSE "Merchant"
            END
            ) as jenis_merchant
        '));
    }
}
