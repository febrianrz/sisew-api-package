<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class BookingDetailResource extends Resource
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
            'ref_no'    => $this->ref_no,
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai'=> $this->jam_selesai,
            'status'    => $this->status
        ];
    }
}
