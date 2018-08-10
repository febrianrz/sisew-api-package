<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TokoResource extends Resource
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
            'keterangan'=> $this->keterangan,
            'alamat'    => $this->alamat,
            'logo'      => url(\Illuminate\Support\Facades\Storage::url($this->logo)),
            'domain'    => $this->domain,
            'kecamatan' => $this->kecamatan?$this->kecamatan->id:null,
            'nama_kecamatan' => $this->kecamatan?$this->kecamatan->nama:null,
            'kodepos'   => $this->kodepos,
            'kota'      => $this->kotas_id,
            'nama_kota' => $this->kota?$this->kota->nama:null,
            'nama_provinsi' => $this->provinsi?$this->provinsi->nama:null,
            'provinsi'  => $this->provinsi?$this->provinsi->id:null,
            'telepon'   => $this->telepon,
            'longitude' => $this->longitude,
            'latitude'  => $this->latitude,
            'alamat_gmap'=> $this->alamat_gmap,
            'status_online' => $this->status_online,
            'status_online_info' => getStatusOnlineInfo($this->status_online),
            'nama_bank' => $this->nama_bank,
            'atas_nama' => $this->atas_nama,
            'no_rekening'=> $this->no_rekening,
            'whatsapp'  => $this->whatsapp_no,
            'website'   => $this->website,
            'akun_ig'   => $this->akun_ig,
            'banner'    => TokoBannerResource::collection($this->banner)
        ];
    }
}
