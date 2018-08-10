<?php

namespace App\Produk;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Http\Request;

class Transaksi extends Model
{
    use \App\SisewModel;
    protected $table = "trx_penyewaan";
    public $incrementing = false;

    public function customer(){
        return $this->belongsTo("\App\Customer","id_customer");
    }

    public function produk(){
        return $this->belongsTo("\App\Produk\Produk","id_barang");
    }

    public function bank(){
        return $this->belongsTo("\App\Sisew\MasterBank","id_master_bank");
    }

    public function metode_pembayaran(){
        return $this->belongsTo("\App\Sisew\MetodePembayaran","id_master_metode_pembayaran");
    }

    
    public function promo(){
        // return $this->belongsTo("\App\Produk\Produk","id_barang");
        return $this->belongsTo("\App\Venue\Promo","id_promo");
    }

    /**
     * Set expired 
     * adjustment stoknya
     * send notifikasi
     * send email
     */
    public static function triggerAfterBooking(Transaksi $transaksi){
        $jenisAdjustment = "";
        $titleFirebase = "";
        $subjectEmail = "";
        switch($transaksi->status_pesanan){
            case 0:
                //jika status pending
                $transaksi->expired_at = date('Y-m-d H:i:s',strtotime('+1 hour'));
                $transaksi->save();
                // $jenisAdjustment = "+";
                $titleFirebase = "Booking | Menunggu Pembayaran";
                $subjectEmail = "Booking ".$transaksi->produk->nama." berhasil";
                break;
            case 1:
                //jika status konfirmasi pemilik
                $transaksi->expired_at = date('Y-m-d H:i:s',strtotime('+1 hour'));
                $transaksi->save();
                // $jenisAdjustment = "+";
                $titleFirebase = "Menunggu Konfirmasi Pemilik";
                break;
            case 2:
                //jika status menunggu pengambilan, 1 jam setelah tanggal sewa 
                $transaksi->expired_at = date('Y-m-d H:i:s',strtotime($transaksi->tanggal_kembali));
                $transaksi->save();
                $jenisAdjustment = "+";
                $titleFirebase = "Menunggu Pengambilan";
                break;
            case 3:
                //jika status proses peminjaman
                $transaksi->expired_at = date('Y-m-d H:i:s',strtotime($transaksi->tanggal_kembali.'+1 day'));
                $transaksi->save();
                $jenisAdjustment = "+";
                $titleFirebase = "Proses Peminjaman";
                break;
            case 4:
                //jika status proses selesai
                $transaksi->expired_at = null;
                $transaksi->save();
                $jenisAdjustment = "-";
                $titleFirebase = "Booking Selesai";
                break;
            case 5:
                //jika status proses batal
                $transaksi->expired_at = null;
                $transaksi->save();
                $jenisAdjustment = "-";
                $titleFirebase = "Booking Dibatalkan";
                break;
            case 6:
                //jika status proses barang tidak tersedia
                $transaksi->expired_at = null;
                $transaksi->save();
                // $jenisAdjustment = "-";
                $titleFirebase = "Booking Tidak Tersedia";
                break;
            case 7:
                //jika status verifikasi pengambilan customer (tidak terpakai)
                // $transaksi->expired_at = null;
                //$transaksi->save();
                break;
            case 8:
                //jika status proses customer menolak pengambilan
                $transaksi->expired_at = null;
                $transaksi->save();
                // $jenisAdjustment = "-";
                $titleFirebase = "Customer Menolak Pengambilan";
                break;
            case 9:
                //jika status proses customer verifikasi pengambilan step 2
                $transaksi->expired_at = date('Y-m-d H:i:s',strtotime($transaksi->tanggal_kembali.'+1 day'));
                $transaksi->save();
                // $jenisAdjustment = "+";
                $titleFirebase = "Proses Verifikasi Pengambilan";
                break;
            
        }
        if($jenisAdjustment != "") self::adjustStok($transaksi,$jenisAdjustment);
        if($titleFirebase != "") self::sendFirebaseNotification($transaksi,$titleFirebase);
        if($subjectEmail != "") self::sendEmailNotification($transaksi, $subjectEmail);
        
    }

    /**
     * Jenisnya berisi "+" atau "-"
     * Menghitung adjustment untuk field stok disewa
     */
    public static function adjustStok(Transaksi $transaksi,$jenis){
        if(!in_array($jenis,["+","-"])){
            echo "Jenis adjustment tidak sesuai";die();
        }
        if($jenis=="+"){
            $jenis = 1;
        } else {
            $jenis = 0;
        }
        /** ditable log stok, lihat jenis terakhirnya, 
         * positive adjusment atau negative 
         * Jika status terakhirnya sama dengan status baru, maka abaikan, 
         * jika tidak maka lakukan perhitungan stok
         * 
         * */
        $produk = Produk::find($transaksi->produk->id);
        if($produk){
            $lastAdjustStok = \Illuminate\Support\Facades\DB::table('barang_log_stok')
            ->where('id_barang','=',$transaksi->id_barang)
            ->where('id_trx_penyewaan','=',$transaksi->id)
            ->where('quantity',$transaksi->quantity)
            ->orderBy('created_at','desc')
            ->first();
            if(!$lastAdjustStok){
                //jika tidak ada, maka insert log dan update statusnya
                \Illuminate\Support\Facades\DB::table('barang_log_stok')
                    ->insert([
                        'id_barang' => $transaksi->id_barang,
                        'id_trx_penyewaan' => $transaksi->id,
                        'quantity'  => $transaksi->quantity,
                        'jenis'     => 1,
                        'created_at'=> date('Y-m-d H:i:s'),
                        'updated_at'=> date('Y-m-d H:i:s')
                    ]);
                $produk->stok_disewa += $transaksi->quantity;
                $transaksi->save();
            } else {
                // jika ada, maka cek dulu status akhirnya positif atau negatif
                if($lastAdjustStok->jenis != $jenis){
                    //jika tidak sama, baru lakukan perhitungan
                    \Illuminate\Support\Facades\DB::table('barang_log_stok')
                    ->insert([
                        'id_barang' => $transaksi->id_barang,
                        'id_trx_penyewaan' => $transaksi->id,
                        'quantity'  => $transaksi->quantity,
                        'jenis'     => 1,
                        'created_at'=> date('Y-m-d H:i:s'),
                        'updated_at'=> date('Y-m-d H:i:s')
                    ]);
                    if($jenis == 1){
                        $produk->stok_disewa += $transaksi->quantity;
                        $transaksi->save();
                    } else {
                        $produk->stok_disewa -= $transaksi->quantity;
                        $transaksi->save();
                    }
                }
            }
        }
    }

    public static function sendFirebaseNotification(Transaksi $transaksi, $title){
        $request = new \Illuminate\Http\Request;
        
        \MyFirebaseUser::bookingBarangV2($request, $transaksi, $title);
        //kirim notifikasi admin
        \MyFirebaseAdmin::bookingBarangV2($request, $transaksi, $title);
    }

    public static function sendEmailNotification(Transaksi $transaksi, $title){
        if($transaksi->customer){
            try{
                if($transaksi->status_pesanan == 0){
                    \Illuminate\Support\Facades\Mail::to($transaksi->customer->email)
                        ->send(new \App\Mail\Merchant\SewaBarang($transaksi, $title));
                }
                
            } catch(\Exception $e){
                \DB::table('email_logs')->insert([
                    'email' => $transaksi->customer->email,
                    'title' => 'Failed send booking barang '.$transaksi->kode_sewa,
                    'message'=> $e->getMessage()
                ]);
            }
        }
        
    }

    /**
     * Cek dari tanggal hari ini sampai tanggal mulai peminjaman,
     * apakah ada yang proses peminjaman
     * jika ada hitung barang yang dipinjamnya
     * 
     */
    public static function cekBarangTersedia(Request $request, $waktuAwal, $waktuAkhir){
        $barang = Produk::find($request->id_produk);
        $barangOutstandingAwal = Transaksi::whereIn('status_pesanan',[2,3,9])
            ->where('tanggal_sewa','<=',$waktuAkhir)
            ->where('id_barang',$barang->id)
            ->sum('quantity'); 

        $barangKembaliAwal = Transaksi::whereIn('status_pesanan',[2,3])
            ->where('tanggal_kembali','<=',$waktuAkhir)
            ->where('id_barang',$barang->id)
            ->sum('quantity'); 
        
        
        $qtyTersedia = $barang->stok_awal-($barangOutstandingAwal-$barangKembaliAwal);
        
        // echo "Tanggal Pinjam :".$waktuAwal."<br/>";
        // echo "Tanggal Akhir :".$waktuAkhir."<br/>";
        // echo "Peminjaman :".$barangOutstandingAwal."<br/>";
        // echo "Pengembalian :".$barangKembaliAwal."<br/>";
        // echo "Stok Awal :".$barang->stok_awal."<br/>";
        // echo "Stok Saat Ini :".$qtyTersedia."<br/>";
        return ($qtyTersedia >= $request->qty)?true:false;
    }

    public static function getTotalBarangDisewa($id_barang){
        $arrayStatusOutstanding = [2,3,9];
        return Transaksi::where('id_barang','=',"$id_barang")
            ->whereIn('status_pesanan',$arrayStatusOutstanding)
            ->count();
    }

    public static function getTotalDisewaBulanan(\App\Toko $toko, $no_bulan){
        $data = Transaksi::select(['trx_penyewaan.*']);
        $data->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $data->join('tokos','tokos.id','=','barang.id_toko');
        $data->where('tokos.id',$toko->id);
        $data->orderBy('trx_penyewaan.created_at','DESC');
        $data->whereIn('trx_penyewaan.status_pesanan',[2,3,9,4]);
        $data->whereMonth('trx_penyewaan.created_at','=',$no_bulan);

        return $data->count();
    }
}
