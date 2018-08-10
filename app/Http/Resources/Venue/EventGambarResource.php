<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class EventGambarResource extends Resource
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
            'gambar'    => url(\Illuminate\Support\Facades\Storage::url($this->gambar)),
            'created_at'=> $this->created_at
        ];
    }
}
