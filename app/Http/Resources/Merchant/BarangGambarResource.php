<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\Resource;

class BarangGambarResource extends Resource
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
            'id'            => $this->id,
            'gambar'        => url(\Illuminate\Support\Facades\Storage::url($this->gambar)),
            'size_117'      => url(\Illuminate\Support\Facades\Storage::url(\App\Http\Libraries\GambarLibrary::getGambar117($this->gambar))),
            'size_300'      => url(\Illuminate\Support\Facades\Storage::url(\App\Http\Libraries\GambarLibrary::getGambar300($this->gambar))),
            'id_barang'     => $this->id_barang,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at
        ];
        
    }
}
