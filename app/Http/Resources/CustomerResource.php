<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CustomerResource extends Resource
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
            'nama'      => $this->nama,
            'email'     => $this->email,
            'telepon'   => $this->telepon,
            'api_token' => $this->token,
            'email_validation' => false
        ];
    }
}
