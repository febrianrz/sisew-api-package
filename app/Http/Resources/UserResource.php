<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserResource extends Resource
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
            'nama'      => $this->nama_depan." ".$this->nama_belakang,
            'email'     => $this->email,
            'telepon'   => $this->telepon,
            'api_token' => $this->api_token,
            'type'      => $this->type_app,
            'type_info' => $this->type_app==1?'Admin Venue':'Admin Ecommerce',
            'role_id'   => $this->role->id,
            'nama_role' => $this->role->name, 
            'google_id' => $this->google_id,
            'google_token' => $this->google_token,
            'status'    => $this->status,
            'toko'      => $this->getTokoUser(\App\AdminVenue::find($this->id)),
            'status_info'=> getGlobalStatusAktif($this->status)
        ];
    }
}
