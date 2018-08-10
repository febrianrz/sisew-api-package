<?php

namespace App\Http\Resources\Venue;

use Illuminate\Http\Resources\Json\Resource;

class BookingResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $prefix_promo   = ($this->promo?($this->promo->jenis_potongan==1?"Rp":""):"");
        $subfix_promo   = ($this->promo?($this->promo->jenis_potongan==2?"%":""):"");
        $kode_promo     = ($this->promo?($this->promo->kode_potongan." (".$prefix_promo.number_format($this->promo->nilai_potongan,0,",",".").$subfix_promo.")"):null);

        return $data = [
            'id'                    => $this->id,
            'nama_pemesan'          => $this->nama,
            'telepon_pemesan'       => $this->telepon,
            'email_pemesan'         => $this->email,
            'lapangan'              => $this->lapangan?$this->lapangan->nama:null,
            'nama_venue'            => $this->lapangan?$this->lapangan->toko->nama:null,
            'tanggal'               => $this->tanggal,
            'kode_booking'          => $this->kode_booking,
            'sub_tagihan'           => $this->subtotal,
            'jenis_pembayaran'      => $this->jenis_bayar==1?'DP':'Lunas',
            'nominal_dp'            => $this->nominal_dp,
            'kode_promo'            => $kode_promo,
            'potongan_promo'        => $this->potongan_promo,
            'total_tagihan'         => $this->total,
            'kekurangan'            => $this->kekurangan,
            'status_pembayaran'     => $this->status_pembayaran,
            'status_pesanan'        => $this->status_pesanan,
            'status_konfirmasi'     => $this->status_konfirmasi,
            'sumber_booking'        => $this->sumber_booking,
            'kode_unik'             => $this->kode_unik,
            'id_metode_pembayaran'  => $this->metode_pembayaran,
            'nama_metode_pembayaran'  => $this->metodePembayaran->nama,
            'nama_bank'             => $this->bank?$this->bank->nama:null,
            'no_rekening'           => $this->bank?$this->bank->no_rekening:null,
            'atas_nama'             => $this->bank?$this->bank->atas_nama:null,
            'expired_at'            => $this->expired_at,
            'fee_layanan'           => $this->fee_layanan,
            'label_status_pembayaran' => getStatusPembayaranBookingLapangan($this->status_pembayaran),
            'label_status_pesanan'  => getStatusPesananBookingLapangan($this->status_pesanan),
            'tanggal_pesan'         => $this->created_at,
            'detail_booking'        => BookingDetailResource::collection($this->detail)
        ];
    }
}

