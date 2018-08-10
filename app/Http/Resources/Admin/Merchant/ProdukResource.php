<?php

namespace App\Http\Resources\Admin\Merchant;

use Illuminate\Http\Resources\Json\Resource;

class ProdukResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
