<?php

namespace App\Venue;

use Illuminate\Database\Eloquent\Model;

class BookingHeader extends Model
{
    use \App\SisewModel;
    protected $table = "customer_booking_header";
    public $incrementing = false;

    public function lapangan()
    {
        return $this->belongsTo('\App\Venue\Lapangan','id_toko_produk');
    }

    public function detail(){
        return $this->hasMany('\App\Venue\Booking','id_customer_booking_header');
    }

    public function promo(){
        return $this->belongsTo('\App\Venue\Promo','id_promo');
    }

    public function metodePembayaran(){
        return $this->belongsTo('\App\Sisew\MetodePembayaran','metode_pembayaran');
    }

    public function bank(){
        return $this->belongsTo('\App\Sisew\MasterBank','id_master_bank');
    }

    public static function getTotalPemesanan(\App\Toko $toko, $tanggal_awal, $tanggal_akhir){
        $tanggal_awal = $tanggal_awal." 00:00:00";
        $tanggal_akhir = $tanggal_akhir." 23:59:59";
        $booking = BookingHeader::where('customer_booking_header.created_at','>=',$tanggal_awal)
            ->join('toko_produks','toko_produks.id','=','customer_booking_header.id_toko_produk')
            ->join('tokos','tokos.id','=','toko_produks.id_toko')
            ->where('tokos.id','=',$toko->id)
            ->get();
        return count($booking);
    }

    public static function getTotalPemasukkan(\App\Toko $toko, $tanggal_awal, $tanggal_akhir){
        $tanggal_awal = $tanggal_awal." 00:00:00";
        $tanggal_akhir = $tanggal_akhir." 23:59:59";
        $booking = BookingHeader::where('customer_booking_header.created_at','>=',$tanggal_awal)
            ->join('toko_produks','toko_produks.id','=','customer_booking_header.id_toko_produk')
            ->join('tokos','tokos.id','=','toko_produks.id_toko')
            ->where('tokos.id','=',$toko->id)
            ->get();
        return collect($booking)->sum('sudah_dibayar');
    }

    public static function isLapanganBisaDibooking(Lapangan $lapangan, $tanggal, $jam){
        $booking    = Booking::select(['customer_booking.*'])
            ->join('customer_booking_header','customer_booking_header.id','=','customer_booking.id_customer_booking_header')
            ->where('customer_booking_header.id_toko_produk','=',$lapangan->id)
            ->where('customer_booking_header.status_pesanan','<',3)
            ->where('customer_booking.jam_mulai','=',$jam)
            ->where('customer_booking_header.tanggal','=',$tanggal)->get();
        if(count($booking) == 0){
            return true;
        } else {
            return false;
        }
    }


}
