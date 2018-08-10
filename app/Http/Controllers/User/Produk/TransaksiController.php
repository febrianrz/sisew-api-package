<?php

namespace App\Http\Controllers\User\Produk;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TransaksiController extends Controller
{
    public function doSewa(Request $request){
        $request->validate([
            'nama'              => 'required',
            'telepon'           => 'required',
            'id_produk'         => 'required|exists:barang,id',
            'qty'               => 'required',
            'tanggal_sewa'      => 'required'
            
        ]);
        $transaksi = new \App\Http\Controllers\Admin\Merchant\TransaksiController;
        return $transaksi->doBooking($request);

    }

    public function doSewaV2(Request $request){
        $transaksi = new \App\Http\Controllers\Admin\Merchant\TransaksiController;
        return $transaksi->doBookingV2($request);

    }

    public function setMetodePembayaran(Request $request){
        $request->validate([
            'id_booking'      => 'required',
            'id_metode'       => 'required|exists:master_metode_pembayaran,id',
            'id_bank'         => 'required|exists:master_bank,id'
        ]);
        $transaksi = \App\Produk\Transaksi::find($request->id_booking);
        if(!$transaksi){
            return response()->json(['status'=>false,'msg'=>'Booking tidak valid']);    
        }
        $transaksi->id_master_metode_pembayaran = $request->id_metode;
        $transaksi->id_master_bank              = $request->id_bank;
        $transaksi->status                      = 1;
        $transaksi->save();

        //set expired
        \App\Produk\Transaksi::triggerAfterBooking($transaksi);

        // \MyFirebaseUser::pushBookingBarang($request,\App\Produk\Transaksi::find($transaksi->id));

        $logPemesanan = new \App\Produk\TransaksiLogPemesanan;
        $logPemesanan->id_trx_penyewaan = $transaksi->id;
        $logPemesanan->status = $transaksi->status_pesanan;
        $logPemesanan->save();
        return new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($transaksi);
    }

    public function riwayat(Request $request){
        $transaksi = \App\Produk\Transaksi::query();
        $customer = \App\Customer::getUserTokenAndId($request);
        // print_r($customer);die();
        // $transaksi->where('id_customer',$request->user->id);
        $transaksi->where('firebase_token',"=",$customer->firebase_token);
        if($customer->customer_id != null){
            $transaksi->orWhere('id_customer',"=",$customer->customer_id);
        }
        $transaksi->orderBy('trx_penyewaan.created_at','DESC');
        return \App\Http\Resources\Admin\Merchant\ProdukSewaResource::collection($transaksi->get());
        
    }
    public function detail($id, Request $request){
        $transaksi = \App\Produk\Transaksi::findOrFail($id);
        return new \App\Http\Resources\User\Produk\TransaksiSewaResource($transaksi);
    }

    public function konfirmasiBayar(Request $request){
        $request->validate([
            'id_transaksi'   => 'required'
        ]);
        $transaksi = \App\Produk\Transaksi::find($request->id_transaksi);
        if(!$transaksi){
            return response()->json([
                'status'    => false,
                'msg'       => 'Transaksi Tidak Ditemukan'
            ]);
        }

        $transaksi->status = 2;
        $transaksi->save();
        \MyFirebaseUser::pushBookingBarang($request, $transaksi);
        return response()->json([
            'status'    => true,
            'msg'       => 'Konfirmasi Berhasil'
        ]);
    }

    public function konfirmasiPesanan(Request $request){
        $request->validate([
            'id_transaksi'   => 'required',
            'status_pesanan' => 'required'
        ]);
        $transaksi = \App\Produk\Transaksi::find($request->id_transaksi);
        if(!$transaksi){
            return response()->json([
                'status'    => false,
                'msg'       => 'Transaksi Tidak Ditemukan'
            ]);
        }

        $transaksi->status_pesanan = $request->status_pesanan;
        $transaksi->save();

        $logPemesanan = new \App\Produk\TransaksiLogPemesanan;
        $logPemesanan->id_trx_penyewaan = $transaksi->id;
        $logPemesanan->status = $transaksi->status_pesanan;
        $logPemesanan->save();

        //jika status pesanan = 3 atau (proses peminjaman yang telah disetujui customer, maka update saldonya venue)
        if($request->status_pesanan == 3){
            \App\Toko::updateSaldoBookingBarang($transaksi);
        } else if($request->status_pesanan == 8){
            //jika user menolak pengambilan barang
        }
        

        //notifikasi
        //kirim notifikasi admin
        //set expired
        \App\Produk\Transaksi::triggerAfterBooking($transaksi);
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Konfirmasi Berhasil'
        ]);
    }

    public function doCekBarang(Request $request){
        $request->validate([
            'id_produk'         => 'required|exists:barang,id',
            'qty'               => 'required',
            'tanggal_sewa'      => 'required',
            'durasi'            => 'required',
            'jam_sewa'          => 'required'
        ]);
        $barang = \App\Produk\Produk::find($request->id_produk);

        $tanggalPinjam = date('Y-m-d H:i:s',strtotime($request->tanggal_sewa." ".$request->jam_sewa));
        if($barang->satuan_harga == "Jam"){
            $tanggalKembali = date('Y-m-d H:i:s',strtotime($tanggalPinjam." +".$request->durasi." hours"));
        } else if($barang->satuan_harga == "Hari"){
            $tanggalKembali = date('Y-m-d H:i:s',strtotime($tanggalPinjam." +".$request->durasi." days"));
        } else if($barang->satuan_harga == "Bulan"){
            $tanggalKembali = date('Y-m-d H:i:s',strtotime($tanggalPinjam." +".$request->durasi." months"));
        }

        if(\App\Produk\Transaksi::cekBarangTersedia($request, $tanggalPinjam, $tanggalKembali)){
            return response()->json([
                'status'    => true,
                'msg'       => 'Barang Tersedia'
            ]);
        } else {
            return response()->json([
                'status'    => false,
                'msg'       => 'Barang Tidak Tersedia'
            ]);
        }
    }

}
