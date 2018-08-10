<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class EventResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $totalPesertaValid = 0;
        //peserta yang statusnya aktif mendaftar, tidak dapat mendaftar lagi
        if($request->user){
            $isPesertaSudahMendaftar = (\App\Venue\EventPeserta::where('id_customer',$request->user->id)
            ->whereIn('status',[2,3])
            ->whereIn('status_pesanan',[0,1,2])
            ->first())?true:false;
        } else {
            $isPesertaSudahMendaftar = false;
        }
        
        $event = \App\Venue\Event::find($this->id);
        $statusKuotaFull = getKuotaTersediaEvent($event)>0?false:true;
        
        /**
         * Status untuk di client,
         * 1. Tersedia.
         * 2. Sedang Berlangsung.
         * 3. Telah Berakhir.
         * 4. Full Kuota
         */
        $idStatusClient = [
            'tersedia'  => 1,
            'sedang_berlangsung'    => 2,
            'telah_berakhir'        => 3,
            'full_kuota'            => 4
        ];
        $status =  $idStatusClient['tersedia'];
        $labelStatus = "Tersedia";
        if($statusKuotaFull) {
            $status = $idStatusClient['full_kuota'];
            $labelStatus = "Full Kuota";
        } 
        if(isBetweenTwoDates($this->tanggal_mulai,$this->tanggal_selesai)){
            $labelStatus = "Sedang Berlangsung";
            $status = $idStatusClient['sedang_berlangsung'];
        }
        if(isExpiredDate(date('Y-m-d H:i:s'),$this->tanggal_selesai)){
            $labelStatus = "Berakhir";
            $status = $idStatusClient['telah_berakhir'];
        }

        return [
            'id'        => $this->id,
            'id_toko'   => $this->toko?$this->toko->id:null,
            'nama_toko' => $this->toko?$this->toko->nama:null,
            'nama_event'=> $this->nama_event,
            'deskripsi' => $this->deskripsi,
            'syarat_ketentuan'  => $this->syarat_ketentuan,
            'tanggal_mulai'     => $this->tanggal_mulai,
            'tanggal_selesai'   => $this->tanggal_selesai,
            'id_kategori'   => $this->kategori?$this->kategori->id:null,
            'nama_kategori' => $this->kategori?$this->kategori->nama:null,
            'maksimum'      => $this->maksimum,
            'harga'         => $this->harga,
            'id_status'     => (int)$this->status,
            'created_at'    => $this->created_at,
            'status'        => $this->status,
            'status_kuota_full' => $statusKuotaFull,
            'bisaDaftar'    => 1,
            "total_pendaftar" => 0,
            "label_status"  => $labelStatus,
            "total_peserta_valid"=>$totalPesertaValid,
            "jenis_peserta"=> $this->jenis_peserta,
            "peserta_sudah_mendaftar" => $isPesertaSudahMendaftar,
            'venue'     => new \App\Http\Resources\TokoResource($this->toko),
            'gambar'        => EventGambarResource::collection($this->gambar),
            'jenis_harga'         => $this->jenis_harga,
            'catatan_pendaftar'         => $this->catatan_pendaftar,
            'alamat'         => $this->alamat,
            'longitude'         => $this->longitude,
            'latitude'         => $this->latitude,
            'fasilitas'     => \App\Http\Resources\Sisew\SimpleResource::collection($this->fasilitas),
            'hasil_fasilitas'     => \App\Http\Resources\Sisew\SimpleResource::collection($this->hasilFasilitas),
            'paket'     => \App\Http\Resources\Sisew\SimpleResource::collection($this->paket)
        ];
    }
}
