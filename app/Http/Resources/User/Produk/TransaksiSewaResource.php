<?php

namespace App\Http\Resources\User\Produk;

use Illuminate\Http\Resources\Json\Resource;

class TransaksiSewaResource extends Resource
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
            'kode_sewa' => $this->kode_sewa,
            'no_ref'    => $this->kode_referensi,
            'nama'      => $this->nama_penyewa,
            'email'     => $this->email_penyewa?$this->email_penyewa:$this->customer->email,
            'telepon'   => $this->telepon_penyewa,
            'id_produk' => $this->id_barang,
            'quantity'  => $this->quantity,
            'id_customer'=> $this->customer->id,
            'nama_produk'=> $this->produk->nama_barang,
            'id_toko'   => $this->produk->toko->id,
            'nama_toko' => $this->produk->toko->nama,
            'logo_toko' => 'https://venue.situsewa.id/images/icon-sisew.png',
            'tanggal_sewa'=> $this->tanggal_sewa,
            'tanggal_pengembalian' => $this->tanggal_kembali,
            'metode_pembayaran' => 'Transfer',
            'bank_tujuan'   => 'BCA',
            'nama_rekening' => 'Febrian Reza',
            'no_rekening'   => '5875001652',
            'harga_satuan'  => $this->harga_satuan,
            'kode_unik'     => $this->kode_unik,
            'biaya_admin'   => $this->biaya_admin,
            'kode_promo'    => ($this->promo?$this->promo->kode_potongan:null),
            'potongan_promo'=> $this->potongan_promo,
            'total_tagihan' => $this->total_harga,
            'keterangan'    => $this->keterangan,
            'produk'        => new ProdukResource($this->produk),
            'status'        => getStatusPembayaranBookingPoduk($this->status),
            'tanggal_pemesanan' => $this->created_at,
            'gambar_produk' => $this->produk->gambar
            
        ];
    }
}
