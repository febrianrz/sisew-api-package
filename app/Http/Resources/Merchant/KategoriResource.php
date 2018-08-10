<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\Resource;

class KategoriResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'    => $this->id,
            'nama'  => $this->nama,
            'keterangan' => $this->keterangan,
            'status'    => $this->status
        ];
    }
}
