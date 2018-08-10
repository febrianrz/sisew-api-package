<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\Resource;

class ResetPasswordResource extends Resource
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
            'token'     => $this->token,
            'kadaluarsa'=> $this->kadaluarsa,
            'kode'      => $this->kode
        ];
    }
}
