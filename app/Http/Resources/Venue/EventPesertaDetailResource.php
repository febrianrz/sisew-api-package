<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\JsonResource;

class EventPesertaDetailResource extends JsonResource
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
            'id'                => $this->id,
            'id_event_peserta'  => $this->id_event_peserta,
            'nama'              => $this->nama,
            'email'             => $this->email,
            'telepon'           => $this->telepon,
            'harga'             => $this->harga,
            'status_kehadiran'  => $this->status_kehadiran, 
            'waktu_kehadiran'   => $this->waktu_kehadiran,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'paket'             => new \App\Http\Resources\Sisew\SimpleResource($this->paket),  
        ];
    }
}
