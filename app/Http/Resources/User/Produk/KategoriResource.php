<?php

namespace App\Http\Resources\User\Produk;

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
            'status'    => $this->status,
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at
        ];
    }
}
