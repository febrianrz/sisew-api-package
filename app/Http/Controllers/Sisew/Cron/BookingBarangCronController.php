<?php
namespace App\Http\Controllers\Sisew\Cron;

use App\Http\Controllers\Sisew\CronInterface\CronInterface;

class BookingBarangCronController implements CronInterface {

    /**
     * Implements method run
     * untuk eksekusi cronnya
     */

    public function run(){
        $this->expired();
    }

    private function expired(){
        //cek expired trx penyewaan
        $bookings = \App\Produk\Transaksi::whereNotNull('expired_at')
            ->where('expired_at','<=',date('Y-m-d H:i:s'))
            ->orderBy('expired_at','asc')
            ->take(100)
            ->get();
        foreach($bookings as $booking){
            //cek berdasarkan status pembayaran
            //jika belum lunas, maka dibatalkan saja
            if($booking->status < 3){
                $booking->status = 4;
                $booking->status_pesanan = 5;
                $booking->save();
                \App\Produk\Transaksi::triggerAfterBooking($booking);
            } else if($booking->status == 3){
                //jika pembayaran lunas, cek berdasarkan status pesanan
                if($booking->status_pesanan == 0){
                    $booking->status_pesanan = 5;
                    $booking->save();
                } else if($booking->status_pesanan == 1){
                    $booking->status_pesanan = 5;
                    $booking->save();
                } else if($booking->status_pesanan == 2){
                    //jika belum konfirmasi pengambilan, maka anggap saja barangnya sudah dipinjam
                    $booking->status_pesanan = 3;
                    $booking->save();
                } else if($booking->status_pesanan == 3){
                    //jika status pesanan sudah peminjaman, maka anggap barang sudah dikembalikan
                    $booking->status_pesanan = 3;
                    $booking->save();
                } else if($booking->status_pesanan == 4){
                    $booking->status_pesanan = 4;
                    $booking->save();
                } else if($booking->status_pesanan == 5){
                    $booking->status_pesanan = 5;
                    $booking->save();
                } else if($booking->status_pesanan == 6){
                    $booking->status_pesanan = 5;
                    $booking->save();
                } else if($booking->status_pesanan == 9){
                    //jika belum konfirmasi pengambilan, maka anggap saja barangnya sudah dipinjam
                    $booking->status_pesanan = 3;
                    $booking->save();
                }
                \App\Produk\Transaksi::triggerAfterBooking($booking);
            }
        }
    }

}
