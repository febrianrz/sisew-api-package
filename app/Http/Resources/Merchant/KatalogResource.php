<?php

namespace App\Http\Resources\Merchant;

use Illuminate\Http\Resources\Json\Resource;

class KatalogResource extends Resource
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
            'deskripsi' => $this->deskripsi,
            'status'=> $this->status,
            'id_kategori' => $this->id_kategori,
            'kategori'  => $this->kategori->nama,
            'gambar'    => url(\Illuminate\Support\Facades\Storage::url($this->gambar)),
        ];
    }
}
