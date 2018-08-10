<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class PromoResource extends Resource
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
            'kode_potongan' => $this->kode_potongan,
            'nilai_potongan' => (double) $this->nilai_potongan,
            'jenis_potongan' => (int) $this->jenis_potongan,
            'keterangan'     => $this->keterangan,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'status'        => $this->status
        ];
    }
}
