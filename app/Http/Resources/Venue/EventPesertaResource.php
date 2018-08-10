<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class EventPesertaResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $statusKonfirmasiKehadiran = false;
        $isBetweenTwoDates = false;
        if(isBetweenTwoDates($this->event->tanggal_mulai,$this->event->tanggal_selesai)){
            $isBetweenTwoDates = true;
        }
        if(($this->status == 3) && $isBetweenTwoDates){
            $statusKonfirmasiKehadiran = true;
        }
        return [
            'id'        => $this->id,
            'order_id'  => $this->order_id,
            'nama_tim'  => $this->nama_tim,
            'id_event'  => $this->id_toko_event,
            'kode_event'=> strtoupper($this->kode_event),
            'kode_unik' => $this->kode_unik,
            'amount'    => $this->amount,
            'nama_pj'   => $this->nama_pj1,
            'telepon_pj'=> $this->telepon_pj1,
            'fee_layanan'=> $this->biaya_admin,
            'status'    => $this->status, //status pembayaran
            'bank'      => $this->bank?new \App\Http\Resources\Sisew\SimpleResource($this->bank):null,
            'metode_pembayaran' => $this->metodePembayaran?new \App\Http\Resources\Sisew\SimpleResource($this->metodePembayaran):null,
            'id_metode_pembayaran'  => $this->metode_pembayaran,
            'nama_metode_pembayaran'  => $this->metodePembayaran?$this->metodePembayaran->nama:null,
            'nama_bank'             => $this->bank?$this->bank->nama:null,
            'no_rekening'           => $this->bank?$this->bank->no_rekening:null,
            'atas_nama'             => $this->bank?$this->bank->atas_nama:null,
            'label_status_pembayaran' => getStatusPembayaranBookingLapangan($this->status),
            'status_pesanan' => $this->status_pesanan,
            'label_status_pesanan' => getStatusPesananBookingEvent($this->status_pesanan),
            'created_at'=> $this->created_at,
            'tanggal_booking' => date('d/m/Y H:i',strtotime($this->created_at)),
            'expired_at'=> date('d/m/Y H:i',strtotime($this->expired_at)),
            'sub_total' => $this->sub_total,
            'total'     => $this->total,
            'konfirmasi_kehadiran' => $statusKonfirmasiKehadiran,
            'event'     => new EventResource($this->event),
            'peserta_tambahan' => EventPesertaDetailResource::collection($this->pesertaTambahan)
        ];
    }
}
