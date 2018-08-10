<?php

namespace App\Http\Resources\User\Produk;

use Illuminate\Http\Resources\Json\Resource;

class SubKategoriResource extends Resource
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
            'id_master_kategori' => $this->id_master_kategori,
            'kategori' => $this->kategori->nama,
            'status'    => $this->status,
            'created_at'=> $this->created_at,
            'updated_at'=> $this->updated_at
        ];
    }
}
