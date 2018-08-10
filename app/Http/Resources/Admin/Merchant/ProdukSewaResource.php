<?php

namespace App\Http\Resources\Admin\Merchant;

use Illuminate\Http\Resources\Json\Resource;

class ProdukSewaResource extends Resource
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
            'id'            => $this->id,
            'kode_sewa'     => $this->kode_sewa,
            'kode_referensi'=> $this->kode_referensi,
            'nama_penyewa'  => $this->nama_penyewa,
            'email_penyewa' => $this->email_penyewa,
            'telepon_penyewa' => $this->telepon_penyewa,
            'customer'      => $this->customer?new \App\Http\Resources\CustomerResource($this->customer):null,
            'barang'        => new \App\Http\Resources\Merchant\BarangResource($this->produk),
            'quantity'      => $this->quantity,
            'tanggal_sewa'  => getTanggalIndonesia($this->tanggal_sewa),
            'tanggal_kembali'=> getTanggalIndonesia($this->tanggal_kembali),
            'metode_pembayaran'=> $this->metode_pembayaran,
            'bank'          => $this->bank,
            'harga_satuan'  => $this->harga_satuan,
            'kode_unik'     => $this->kode_unik,
            'fee_layanan'   => $this->biaya_admin,
            'total_harga'   => $this->total_harga,
            'promo'         => $this->promo,
            'status'        => $this->status,
            'label_status'  => getStatusPembayaranBookingLapangan($this->status),
            'status_pesanan'=> $this->status_pesanan,
            'label_status_pesanan' => getStatusPesananBookingBarang($this->status_pesanan),
            'sumber_booking'=> $this->sumber_booking,
            'label_sumber_booking' => getSumberBooking($this->sumber_booking),
            'durasi'        => $this->durasi,
            'satuan_harga'  => $this->satuan_harga,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'expired_at'    => date('Y-m-d H:i:s',strtotime($this->expired_at)),
            'kode_unik_pengambilan' => $this->kode_unik_pengambilan
            
        ];
    }
}
