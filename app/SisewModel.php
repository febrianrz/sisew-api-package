<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

trait SisewModel
{
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
        $this->setConnection(session('connection_name'));
    }
}
