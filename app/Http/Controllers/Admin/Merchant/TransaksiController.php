<?php

namespace App\Http\Controllers\Admin\Merchant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function getRiwayat(Request $request){
        $data = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $data->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $data->join('tokos','tokos.id','=','barang.id_toko');
        $data->where('tokos.id',$request->toko->id);
        $data->orderBy('trx_penyewaan.created_at','DESC');
        // $data->where('status_pesanan',1);
        
        if($request->has('s')){
            $data->where('trx_penyewaan.kode_sewa',$request->s);
        } else {
            if($request->has('status_pemesanan')){
                if($request->status_pemesanan != 99){
                    $data->where('status_pesanan', $request->status_pemesanan);
                }
            }
            if($request->has('pengambilan_hari_ini')){
                //pengambilan hari ini
                if($request->pengambilan_hari_ini == 1){
                    $data->where('status_pesanan', 1);
                    $data->where('tanggal_sewa', date('Y-m-d'));
                }
            }
            if($request->has('berakhir_hari_ini')){
                if($request->berakhir_hari_ini == 1){
                    // $data->where('status_pesanan', 2);
                    $data->whereDate('tanggal_kembali', date('Y-m-d'));
                }
            }
            if($request->has('sewa_berjalan')){
                if($request->sewa_berjalan == 1){
                    $data->where('status_pesanan', 3);
                }
            }
        }

        return \App\Http\Resources\Admin\Merchant\ProdukSewaResource::collection($data->get());
    }

    //
    public function doBooking(Request $request){
        $request->validate([
            'nama'              => 'required',
            'telepon'           => 'required',
            'id_produk'         => 'required',
            'qty'               => 'required',
            'tanggal_sewa'      => 'required',
        ]);

        
        $customer   = $request->has('email')?\App\Customer::where('email',$request->email)->first():null;
        $produk     = $request->has('id_produk')?\App\Produk\Produk::find($request->id_produk):null;
        if(!$produk){
            return response()->json(['status'=>false,'msg'=>'Barang tidak valid']);
        }
        $promo      = $request->has('id_promo')?\App\Venue\Promo::find($request->id_promo):null;
        $bank       = $request->has('id_bank')?\App\Sisew\MasterBank::find($request->id_bank):null;
        $metode     = $request->has('id_metode_pembayaran')?\App\Sisew\MetodePembayaran::find($request->id_metode_pembayaran):null;
        $durasi     = $request->has('durasi')?$request->durasi:1;
        $tanggal_kembali = $request->has('tanggal_kembali')?$request->tanggal_kembali:null;
        $tanggal_mulai = $request->tanggal_sewa." ".($request->has('jam_mulai')?$request->jam_mulai.":00":date('H:i:s'));
        $nilai_promo = 0;
        if($promo){
            if($promo->jenis_potongan == 2){
                $nilai_promo = $produk->harga_jual*$promo->nilai_potongan/100;
            } else {
                $nilai_promo = $promo->nilai_potongan;
            }
        }
        $total_harga = ($produk->harga_jual-$nilai_promo)*$durasi; 
        if($request->has('kode_unik')){
            $total_harga += $request->kode_unik;
        }
        if($request->has('fee_layanan')){
            $total_harga += $request->fee_layanan;
        }
        if($tanggal_kembali==null){
            $dt = new \DateTime($tanggal_mulai);
            if($produk->satuan_harga == "Hari"){
                $dt->add(new \DateInterval('P'.$durasi.'D'));
            } else if($produk->satuan_harga == "Bulan"){
                $dt->add(new \DateInterval('PT'.$durasi.'M'));
            } else {
                $dt->add(new \DateInterval('PT'.$durasi.'H'));
            }
            
            $tanggal_kembali = $dt->format('Y-m-d H:i:s');
        }
        
        
        $transaksi  = new \App\Produk\Transaksi;
        $transaksi->id                          = \Webpatser\Uuid\Uuid::generate()->string;
        $transaksi->kode_sewa                   = $this->generateKodeSewa();
        $transaksi->kode_referensi              = $this->generateRefNo();
        $transaksi->nama_penyewa                = $request->nama;
        $transaksi->email_penyewa               = $request->email;
        $transaksi->telepon_penyewa             = $request->telepon;
        $transaksi->id_customer                 = $customer?$customer->id:null;
        $transaksi->id_barang                   = $request->id_produk;
        $transaksi->quantity                    = $request->qty;
        $transaksi->tanggal_sewa                = $tanggal_mulai;
        $transaksi->tanggal_kembali             = $tanggal_kembali;
        $transaksi->id_master_metode_pembayaran = $metode?$metode->id:6;
        $transaksi->id_master_bank              = $bank?$bank->id:0;
        $transaksi->harga_satuan                = $produk->harga_jual;
        $transaksi->kode_unik                   = $request->has('kode_unik')?$request->kode_unik:0;
        $transaksi->biaya_admin                 = $request->has('fee_layanan')?$request->fee_layanan:0;
        $transaksi->id_promo                    = $promo?$promo->id:null;
        $transaksi->potongan_promo              = $nilai_promo;
        $transaksi->total_harga                 = $total_harga;
        $transaksi->nominal_db                  = 0;
        $transaksi->kekurangan                  = 0;
        $transaksi->status                      = $request->has('status')?$request->status:0; //status pembayaran
        $transaksi->status_pesanan              = $request->has('status_pesanan')?$request->status_pesanan:0;
        $transaksi->keterangan                  = $request->keterangan;
        $transaksi->durasi                      = $durasi;
        $transaksi->satuan_harga                = $produk->satuan_harga;
        $transaksi->sumber_booking              = $request->sumberBooking;
        $transaksi->save();
        
        //buat log status pemesanan
        $logPemesanan = new \App\Produk\TransaksiLogPemesanan;
        $logPemesanan->id_trx_penyewaan = $transaksi->id;
        $logPemesanan->status = $transaksi->status_pesanan;
        $logPemesanan->save();

        //set expired
        \App\Produk\Transaksi::triggerAfterBooking($transaksi);

        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function doBookingV2(Request $request){
        /**
         * Booking method versi 2, bertahap seperti tiket.com
         * pertanggal 11 Juli 2018
         */
        $request->validate([
            'nama'              => 'required',
            'telepon'           => 'required',
            'id_produk'         => 'required',
            'qty'               => 'required',
            'tanggal_sewa'      => 'required',
        ]);

        $this->registerCustomer($request);

        $produk     = $request->has('id_produk')?\App\Produk\Produk::find($request->id_produk):null;
        if(!$produk){
            return response()->json(['status'=>false,'msg'=>'Barang tidak valid']);
        }
        $customer   = $request->has('email')?\App\Customer::where('email',$request->email)->first():null;
        


        $promo      = $request->has('id_promo')?\App\Venue\Promo::find($request->id_promo):null;
        $durasi     = $request->has('durasi')?$request->durasi:1;
        $tanggal_kembali = $request->has('tanggal_kembali')?$request->tanggal_kembali:null;
        $tanggal_mulai = $request->tanggal_sewa." ".($request->has('jam')?$request->jam.":00":date('H:i:s'));
        $nilai_promo = 0;
        if($promo){
            if($promo->jenis_potongan == 2){
                $nilai_promo = $produk->harga_jual*$promo->nilai_potongan/100;
            } else {
                $nilai_promo = $promo->nilai_potongan;
            }
        }
        $total_harga = ($produk->harga_jual-$nilai_promo)*$durasi;
        $kode_unik = rand(1,200); 
        $total_harga += $kode_unik;
        $fee_layanan = 3000;
        $total_harga += $fee_layanan;
        
        $dt = new \DateTime($tanggal_mulai);
        if($produk->satuan_harga == "Hari"){
            $dt->add(new \DateInterval('P'.($durasi*$produk->hitungan_harga).'D'));
        } else if($produk->satuan_harga == "Bulan"){
            $dt->add(new \DateInterval('PT'.($durasi*$produk->hitungan_harga).'M'));
        } else {
            $dt->add(new \DateInterval('PT'.($durasi*$produk->hitungan_harga).'H'));
        }
        
        $tanggal_kembali = $dt->format('Y-m-d H:i:s');
        
        $transaksi  = new \App\Produk\Transaksi;
        $transaksi->id                          = \Webpatser\Uuid\Uuid::generate()->string;
        $transaksi->kode_sewa                   = $this->generateKodeSewa();
        $transaksi->kode_referensi              = $this->generateRefNo();
        $transaksi->nama_penyewa                = $request->nama;
        $transaksi->email_penyewa               = $request->email;
        $transaksi->telepon_penyewa             = $request->telepon;
        $transaksi->id_customer                 = $customer?$customer->id:null;
        $transaksi->id_barang                   = $request->id_produk;
        $transaksi->quantity                    = $request->qty;
        $transaksi->tanggal_sewa                = $tanggal_mulai;
        $transaksi->tanggal_kembali             = $tanggal_kembali;
        $transaksi->harga_satuan                = $produk->harga_jual;
        $transaksi->kode_unik                   = $kode_unik;
        $transaksi->biaya_admin                 = $fee_layanan;
        $transaksi->id_promo                    = $promo?$promo->id:null;
        $transaksi->potongan_promo              = $nilai_promo;
        $transaksi->total_harga                 = $total_harga;
        $transaksi->nominal_db                  = 0;
        $transaksi->id_master_metode_pembayaran = 0;
        $transaksi->id_master_bank              = 0;
        $transaksi->kekurangan                  = 0;
        $transaksi->status                      = 100; //status pembayaran setup metode pembayaran
        $transaksi->status_pesanan              = 0;
        $transaksi->keterangan                  = $request->keterangan;
        $transaksi->durasi                      = $durasi;
        $transaksi->satuan_harga                = $produk->satuan_harga;
        $transaksi->sumber_booking              = $request->sumberBooking;
        $transaksi->expired_at                  = date('Y-m-d H:i:s',strtotime('+30 minutes'));
        $transaksi->firebase_token              = $request->header('Firebase-token');
        $transaksi->save();
        
        //buat log status pemesanan
        $logPemesanan = new \App\Produk\TransaksiLogPemesanan;
        $logPemesanan->id_trx_penyewaan = $transaksi->id;
        $logPemesanan->status = $transaksi->status_pesanan;
        $logPemesanan->save();
        
        return new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($transaksi);
    }

    private function registerCustomer($request){
        if($request->auto_daftar == 1){
            $customer = \App\Customer::where('email','=',$request->email)->first();
            if(!$customer){
                $customer       = new \App\Customer;
                $customer->id   = \Webpatser\Uuid\Uuid::generate()->string;
                $customer->email= $request->email;
                $customer->status= 1;
                $customer->register_from = 0;
                $customer->token = str_random(100);
                $customer->token_firebase = $request->header('Firebase-token');
                $customer->nama     = $request->nama?$request->nama:$request->email;
                $customer->password = bcrypt(str_random(6));
                $customer->firebase_auth_created = 1;
                $customer->guid_firebase = \Webpatser\Uuid\Uuid::generate()->string;
                $customer->save();
                // \MyFirebaseUser::createUser($customer,$password);
                // try{
                //     \Illuminate\Support\Facades\Mail::to($request->email)
                //         ->send(new \App\Mail\RegisterUser($customer));
                // } catch(\Exception $e){
                //     \DB::table('email_logs')->insert([
                //         'email' => $request->email,
                //         'title' => 'Failed send register customer',
                //         'message'=> $e->getMessage()
                //     ]);
                // }
            }
        }
    }

    private function generateKodeSewa(){
        $string = "";
        do{
            $string = str_random(8);
            $kodeBooking = \App\Produk\Transaksi::where('kode_sewa',$string)->get();
        } while(!$kodeBooking->isEmpty());
        return strtoupper($string);
    }

    private function generateRefNo(){
        return date('YmdHis').rand(10,99);
    }

    public function konfirmasiPesanan(Request $request){
        $request->validate([
            'id_booking'    => 'required',
            'status'        => 'required'
        ]);
        $trx  = \App\Produk\Transaksi::where('id',$request->id_booking)->first();
        if(!$trx) return response()->json(['status'=>false,'Booking tidak valid']);
        //jika status konfirmasi pesanan == 3 (validasi bahwa barang sudah dipinjam), harus cek kode peminjaman terlebih dahulu
        if($request->status == 3){
            $row = \App\Produk\Transaksi::where('id',$request->id_booking)
                ->where('kode_unik_pengambilan',$request->kode_pengambilan)
                ->where('status',3) //yang pembayarannya sudah lunas
                ->first();
            if(!$row){
                return response()->json([
                    'status'    => false,
                    'msg'       => 'Kode Peminjaman Tidak Valid'
                ]);
            }
            
            $row->status_pesanan = $request->status;
            $row->save();
            \App\Toko::updateSaldoBookingBarang($row);
            \MyFirebaseUser::bookingBarangV2($request, $row, "Konfirmasi Pesanan");
            
            //update saldo toko
            
        } else {
            $row = \App\Produk\Transaksi::find($request->id_booking);
            $customer = \App\Customer::find($row->id_customer);
            $row->status_pesanan = $request->status;
            $row->save();
            \MyFirebaseUser::bookingBarangV2($request, $row, "Konfirmasi Pesanan");
            if($request->status >= 4){
                //jika statusnya 6 (barang tidak tersedia, maka update saldo customer jika booking via aplikasi dan web user)
                //update saldo customer
                if($request->status == 6){
                    if(($row->sumber_booking == 3 || $row->sumber_booking == 4) && $customer){
                        \App\Customer::pembatalanBookingBarang($customer,$row);
                    }
                }

                //jika statusnya 9 (verifikasi pengambilan step 2)
                if($request->status == 9){
                    $row->kode_unik_pengambilan = rand(100000, 999999);
                    $row->save();
                }
            }   
        }
        

        $customer = \App\Customer::find($row->id_customer);
        //update log status pemesanan
        $logPemesanan = new \App\Produk\TransaksiLogPemesanan;
        $logPemesanan->id_trx_penyewaan = $row->id;
        $logPemesanan->status = $row->status_pesanan;
        $logPemesanan->save();
        
        //set expired
        \App\Produk\Transaksi::triggerAfterBooking($row);
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function doBatal(Request $request){
        $request->validate([
            'id_booking'    => 'required|exists:trx_penyewaan,id',
            'status'        => 'required'
        ]);
        $row = \App\Produk\Transaksi::find($request->id_booking);
        $row->status_pesanan = $request->status;
        $row->save();
        //set expired
        \App\Produk\Transaksi::triggerAfterBooking($row);
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }
}
