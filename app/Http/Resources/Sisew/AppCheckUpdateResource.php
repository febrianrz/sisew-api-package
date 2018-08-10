<?php

namespace App\Http\Resources\Sisew;

use Illuminate\Http\Resources\Json\Resource;

class AppCheckUpdateResource extends Resource
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
            'version_id'    => $this->version_id,
            'deskripsi' => $this->deskripsi,
            'status'    => $this->status,
            'due_date'  => $this->due_date,
            'created_at'    => $this->created_at,
            'updated_at' => $this->updated_at,
            'force_update' => $this->force_update
        ];
    }
}
