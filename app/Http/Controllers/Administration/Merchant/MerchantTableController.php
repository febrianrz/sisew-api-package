<?php

namespace App\Http\Controllers\Administration\Merchant;

use Illuminate\Http\Request;
use App\Tables\Builders\MerchantTable;
use App\Http\Controllers\Controller;
use LaravelEnso\VueDatatable\app\Traits\Excel;
use LaravelEnso\VueDatatable\app\Traits\Datatable;

class MerchantTableController extends Controller
{
    use Datatable, Excel;

    protected $tableClass = MerchantTable::class;

}
