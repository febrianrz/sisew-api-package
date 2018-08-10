<?php

namespace App\Http\Resources\Sisew;

use Illuminate\Http\Resources\Json\Resource;

class GlobalBannerResource extends Resource
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
            'deskripsi'  => $this->nama,
            'gambar'    => env('APP_ADMIN_URL').$this->file,
        ];
    }
}
