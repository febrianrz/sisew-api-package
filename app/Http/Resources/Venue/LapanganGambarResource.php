<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class LapanganGambarResource extends Resource
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
            'id'        => $this->id,
            'id_produk' => $this->id_produk,
            'gambar'    => url(\Illuminate\Support\Facades\Storage::url($this->gambar)),
            'main_gambar'=> $this->main_gambar,
            'created_at'=> $this->created_at,
            'updated_at'=>$this->updated_at
        ];
    }
}
