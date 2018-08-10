<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class JadwalLapanganResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dataBooking = null;
        if($request->has('tanggal')){
            $dataBooking = \App\Venue\Booking::where('jam_mulai',$this->jam_mulai)
                ->join('customer_booking_header','customer_booking_header.id','=','customer_booking.id_customer_booking_header')
                ->where('customer_booking_header.status_pesanan','<',BOOKING_STATUS_PESANAN_SELESAI)
                ->where('customer_booking_header.tanggal','=',$request->tanggal)
                ->where('customer_booking_header.id_toko_produk','=',$request->lapangan->id)
                ->first();
        }
        
        $headerBooking = null;
        if($dataBooking) {
            $headerBooking = \App\Venue\BookingHeader::find($dataBooking->id_customer_booking_header);
        }
        return [
            'id'        => (is_object($this->id)?$this->id:$this->id),
            'jam_mulai' => date('H:i',strtotime($this->jam_mulai)),
            'jam_selesai'=> date('H:i',strtotime($this->jam_selesai)),
            'no_hari'      => $this->hari,
            'hari'      => date('l', strtotime($this->hari)),
            'harga'     => (double)$this->harga,
            'status'    => $this->status,
            'status_info'=> $this->status==1?'Aktif':'Tidak Aktif',
            'status_booking'=> $dataBooking?0:1,
            'label_booking' => ($dataBooking?'Terbooking':'Tersedia'),
            'booking' => ($headerBooking?new BookingResource($headerBooking):null) 
        ];
    }
}
