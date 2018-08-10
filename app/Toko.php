<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Facades\DB;

class Toko extends Model
{
    use \App\SisewModel;
    public $incrementing = false;
    public $guarded  = [];
    public function lapangan()
    {
        return $this->hasMany('\App\Venue\Lapangan','id_toko','id');
    }

    public function owner(){
        return $this->belongsTo('\App\AdminVenue','admin_venue_id');
    }

    public function provinsi(){
        return $this->belongsTo('\App\Sisew\Provinsi','provinsis_id');
    }

    public function kota(){
        return $this->belongsTo('\App\Sisew\Kota','kotas_id');
    }

    public function kecamatan(){
        return $this->belongsTo('\App\Sisew\Kecamatan','id_kecamatan');
    }

    public function updateSaldo($id_toko, $amount, $jenisTransaksi, $source, $id_booking, $kode_booking){
        $toko = Toko::find($id_toko);
        $saldo = TokoSaldo::where('id_toko',$toko->id)->first();
        if(!$saldo){
            $saldo = new TokoSaldo;
            $saldo->id_toko = $toko->id;
            $saldo->amount  = $amount;
            $saldo->save();
        } else {
            $saldo->amount = $saldo->amount + $amount;
            $saldo->save();
        }
        
        //masukkan detail
        $saldoRincian                       = new TokoSaldoRincian;
        $saldoRincian->id_toko              = $toko->id;
        $saldoRincian->id_customer_booking  = $id_booking;
        $saldoRincian->id_master_jenis_transaksi = $jenisTransaksi;
        $saldoRincian->amount               = $amount;
        $saldoRincian->keterangan           = $kode_booking;
        $saldoRincian->jenis                = $source;
        $saldoRincian->kode_booking         = $kode_booking;
        $saldoRincian->save();
    
    }

    public static function updateSaldoBookingBarang(\App\Produk\Transaksi $booking){
        if($booking->sumber_booking == 3 || $booking->sumber_booking == 4){
            $toko = Toko::find($booking->produk->toko->id);

            $saldo = TokoSaldo::where('id_toko',$toko->id)->first();
            $amount = $booking->total_harga - $booking->biaya_admin - $booking->kode_unik;
            if(!$saldo){
                $saldo = new TokoSaldo;
                $saldo->id_toko = $toko->id;
                $saldo->amount  = $amount;
                $saldo->save();
            } else {
                $saldo->amount = $saldo->amount + $amount;
                $saldo->save();
            }
            
            $point = \App\Sisew\MasterPoint::where('key','=','TRANSAKSI_BARANG')->first();


            //masukkan detail
            $saldoRincian                       = new TokoSaldoRincian;
            $saldoRincian->id_toko              = $toko->id;
            $saldoRincian->id_customer_booking  = $booking->id;
            $saldoRincian->id_master_jenis_transaksi = 2; // table master jenis transaksi
            $saldoRincian->amount               = $amount;
            $saldoRincian->keterangan           = $booking->kode_sewa;
            $saldoRincian->jenis                = 3;
            $saldoRincian->point                = $point?$point->point:0;
            $saldoRincian->kode_booking         = $booking->kode_sewa;
            $saldoRincian->save();
        }
    }

    public function banner(){
        return $this->hasMany('\App\TokoBanner','id_toko')->orderBy('created_at','desc')->take(3);
    }


    public static function setDashboardBarangFirebase(Toko $toko){
        $bookingPending = \App\Produk\Transaksi::where('status_pesanan','=',0)
        ->whereHas('produk', function($query) use($toko){
            $query->where('id_toko','=',$toko->id);
        })->count();

        $bookingKonfirmasi = \App\Produk\Transaksi::where('status_pesanan','=',1)
        ->whereHas('produk', function($query) use($toko){
            $query->where('id_toko','=',$toko->id);
        })->count();

        $dataPengambilanHariIni = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $dataPengambilanHariIni->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $dataPengambilanHariIni->where('barang.id_toko','=',$toko->id);
        $dataPengambilanHariIni->whereDate('trx_penyewaan.tanggal_sewa', DB::raw('CURDATE()'));

        $dataBerakhirHariIni = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $dataBerakhirHariIni->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $dataBerakhirHariIni->where('barang.id_toko','=',$toko->id);
        $dataBerakhirHariIni->whereDate('trx_penyewaan.tanggal_kembali', DB::raw('CURDATE()'));

        $array = [
            'booking_barang_pending'            => $bookingPending,
            'booking_barang_konfirmasi'         => $bookingKonfirmasi,
            'booking_barang_mulai_hari_ini'     => $dataPengambilanHariIni->get()->count(),
            'booking_barang_selesai_hari_ini'   => $dataBerakhirHariIni->get()->count(),
            'total_pemasukkan_hari_ini'         => self::getTotalPemasukkan($toko,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
            'total_pemasukkan_bulan_ini'        => self::getTotalPemasukkan($toko,date('Y-m-01 00:00:00'),date('Y-m-t 23:59:59')),
            'total_pemasukkan_minggu_ini'       => self::getTotalPemasukkan($toko,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')),
            'total_pemasukkan_tahun_ini'        => self::getTotalPemasukkan($toko,date('Y-01-01 00:00:00'),date('Y-12-31 23:59:59')),
        ];
        
        $firebase = \MyFirebaseAdmin::getFirebase();
        $database = $firebase->getDatabase();
        $reference = $database->getReference('venue/'.$toko->id.'/dashboard_barang')
            ->set($array);
    }

    public static function getTotalPemasukkan(Toko $toko, $start, $end){
        // echo $toko->id."<br/>";
        return \App\Produk\Transaksi::whereIn('status_pesanan',[2,3,4])
            ->whereDate('trx_penyewaan.created_at', '>=',$start)
            ->whereDate('trx_penyewaan.created_at', '<=',$end)
            ->whereHas('produk', function($query) use($toko){
                $query->where('id_toko','=',$toko->id);
            })->sum('harga_satuan');
    }
}
