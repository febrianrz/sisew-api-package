<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\Resource;

class NewsResource extends Resource
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
            'judul'     => $this->judul,
            'isi'       => $this->isi,
            'gambar'    => env('APP_ADMIN_URL').'/'.$this->gambar,
            'status'    => $this->status,
            'for'       => $this->for,
            'created_at'=> getTanggalIndonesia($this->created_at),
            'updated_at'=> getTanggalIndonesia($this->updated_at)
        ];
    }
}
