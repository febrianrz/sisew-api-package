<?php

namespace App\Http\Controllers\Admin\Venue;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\Resource;
use \Webpatser\Uuid\Uuid;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'nama'              => 'required',
            'telepon'           => 'required',
            'id_lapangan'       => 'required|exists:toko_produks,id',
            'tanggal'           => 'required',
            'id_jadwal.*'       => 'required|exists:toko_produk_jadwal_harga,id',
            'jenis_bayar'       => 'required',
        ]);
        if(!$this->validateJadwalBooking($request)){
            return response()->json([
                'status'    => false,
                'message'       => 'Jadwal tidak dapat dipesan'
            ]);
        }
        
        $lapangan = $request->toko->lapangan->find($request->id_lapangan);
        $i = 1;
        
        $headerID           = Uuid::generate()->string;
        $parent_id          = Uuid::generate()->string;
        $listKodeBooking    = [];
        $listJamMulai       = [];
        $totalTagihan       = 0;

        foreach($request->id_jadwal as $jd){
            $jadwal = $lapangan->jadwal->find($jd);
            if($jadwal){
                $kodeBooking = $this->generateKodeBooking();
                $totalTagihan += $jadwal->harga;
                array_push($listKodeBooking,$kodeBooking);
                array_push($listJamMulai,$jadwal->jam_mulai);
                $book                   = new \App\Venue\Booking;
                $book->id               = $i==1?$parent_id:Uuid::generate()->string;
                $book->parent_id        = $i==1?null:$parent_id;
                $book->nama_pemesan     = $request->nama;
                $book->no_telp          = $request->telepon;
                $book->kode_booking     = $kodeBooking;
                $book->ref_no           = $this->generateRefNo();
                $book->id_customer      = null;
                $book->id_toko_produk   = $lapangan->id;
                $book->tanggal          = $request->tanggal;
                $book->jam_mulai        = $jadwal->jam_mulai;
                $book->jam_selesai      = $jadwal->jam_selesai;
                $book->durasi           = 1;
                $book->harga_booking    = $jadwal->harga;
                $book->biaya_admin      = 0;
                $book->kode_unik        = 0;
                $book->status_bayar     = $request->jenis_bayar;
                $book->status           = $request->jenis_bayar==1?2:3;
                $book->sumber_booking   = 2;
                $book->status_bermain   = 0;
                $book->id_promo         = 0;
                $book->potongan_promo   = 0;
                $book->dp_nominal       = $request->dp;
                $book->expired_at       = date('Y-m-d H:i:s',strtotime('+30 minutes'));
                $book->id_customer_booking_header = $headerID;
                $book->save();
                $i++;
            }
        }
        if(count($listKodeBooking) > 0) {
            $promo = \App\Venue\Promo::find($request->id_promo);
            $potongan_promo = 0;
            $kekuranganHarga = 0;
            if($promo){
                if($promo->jenis_potongan==1){
                    $potongan_promo = $promo->nilai_potongan;
                } else {
                    $potongan_promo = $promo->nilai_potongan/100*$totalTagihan;
                }
            }
            $hargaSetelahPromo = $totalTagihan - $potongan_promo;
            if($request->has('fee_layanan')){
                $hargaSetelahPromo += $request->fee_layanan;
            }
            
            if($request->has('kode_unik')){
                $hargaSetelahPromo += $request->kode_unik;
            }
            $sudahDibayar = $hargaSetelahPromo;
            
            if($request->jenis_bayar==1){
                //jika DP
                $kekuranganHarga    = $hargaSetelahPromo - $request->dp;
                $sudahDibayar       = $request->dp?$request->dp:0;
            } 

            //jika service dari user, maka pembayaran belum lunas karena belum transfer
            if(in_array($request->sumberBooking,[3,4])){
                $status_pembayaran = BOOKING_STATUS_PEMBAYARAN_BELUM_LUNAS; 
                $status_pesanan = BOOKING_STATUS_PESANAN_MENUNGGU_PEMBAYARAN;
            } else {
                $status_pembayaran = BOOKING_STATUS_PEMBAYARAN_BELUM_LUNAS; 
                if($request->jenis_bayar == BOOKING_PEMBAYARAN_LUNAS){
                    $status_pembayaran = BOOKING_STATUS_PEMBAYARAN_LUNAS;
                }
                $status_pesanan = BOOKING_STATUS_PESANAN_MENUNGGU_JADWAL_BERMAIN;
                if($request->jenis_bayar == BOOKING_PEMBAYARAN_LUNAS){
                    $status_pesanan = BOOKING_STATUS_PESANAN_MENUNGGU_JADWAL_BERMAIN;
                } else {
                    if($request->dp <= 0){
                        $status_pesanan = BOOKING_STATUS_PESANAN_MENUNGGU_JADWAL_BERMAIN;
                    }
                }
            }
            

            //masukkan ke table header
            $customerData   = \App\Customer::where('email',$request->email)->first();
            $bookingHeader                  = new \App\Venue\BookingHeader;
            $bookingHeader->id              = $headerID;
            $bookingHeader->id_toko_produk  = $lapangan->id;
            $bookingHeader->id_toko         = $request->toko->id;
            $bookingHeader->nama            = $request->nama;
            $bookingHeader->email           = $request->email;
            $bookingHeader->telepon         = $request->telepon;
            $bookingHeader->id_promo        = $request->id_promo;
            $bookingHeader->subtotal        = $totalTagihan;
            $bookingHeader->total           = $hargaSetelahPromo;
            $bookingHeader->potongan_promo  = $potongan_promo;
            $bookingHeader->tanggal         = $request->tanggal;
            $bookingHeader->kekurangan      = $kekuranganHarga;
            $bookingHeader->nominal_dp      = $request->dp;
            $bookingHeader->jenis_bayar     = $request->jenis_bayar;
            $bookingHeader->status_pembayaran = $status_pembayaran;
            $bookingHeader->status_pesanan   = $status_pesanan;
            $bookingHeader->fee_layanan     = $request->has('fee_layanan')?$request->fee_layanan:0;
            $bookingHeader->id_customer     = $customerData?$customerData->id:null;
            $bookingHeader->kode_booking    = $this->generateKodeBookingHeader();
            $bookingHeader->sumber_booking  = $request->sumberBooking;
            $bookingHeader->status_konfirmasi = STATUS_KONFIRMASI_BELUM_BERMAIN;
            $bookingHeader->sudah_dibayar   = $sudahDibayar;
            $bookingHeader->kode_unik       = $request->has('kode_unik')?$request->kode_unik:0;
            $bookingHeader->metode_pembayaran = $request->has('metode_pembayaran')?$request->metode_pembayaran:6;
            $bookingHeader->id_master_bank  = $request->has('id_bank')?$request->id_bank:null;
            $bookingHeader->expired_at       = date('Y-m-d H:i:s',strtotime('+'.WAKTU_EXPIRED_BOOKING_LAPANGAN.' minutes'));
            $bookingHeader->save();
            
            // //update saldo jika booking dari user android dan web 
            // if(in_array($request->sumberBooking,[3,4])){
            //     $toko = \App\Toko::find($request->toko->id);
            //     if($request->has('kode_unik')){
            //         $sudahDibayar -= $request->kode_unik;
            //     } if($request->has('fee_layanan')){
            //         $sudahDibayar -= $request->fee_layanan;
            //     }
            //     $toko->updateSaldo($request->toko->id, $sudahDibayar,JENIS_TRANSAKSI_PEMBAYARAN_BOOKING_ONLINE,
            //         JENIS_TRANSAKSI_BOOKING_LAPANGAN,$bookingHeader->id,$bookingHeader->kode_booking);
            // }

            \MyFirebaseAdmin::updateDashboard($request->toko);
            if($customerData){
                try{
                    \Illuminate\Support\Facades\Mail::to($request->email)
                        ->send(new \App\Mail\Venue\BookingLapangan($bookingHeader));
                } catch(\Exception $e){
                    \DB::table('email_logs')->insert([
                        'email' => $request->email,
                        'title' => 'Failed send booking lapangan '.$bookingHeader->kode_booking,
                        'message'=> $e->getMessage()
                    ]);
                }
                
                // push notification user
                \MyFirebaseUser::bookingLapangan($request, $bookingHeader, $customerData, "Booking lapangan ".$bookingHeader->kode_booking);
            }
            \MyFirebaseAdmin::bookingLapangan($request, $bookingHeader, 'Booking lapangan '.$bookingHeader->kode_booking);
            
            return new \App\Http\Resources\Venue\BookingResource($bookingHeader);
        }
    }

    private function validateJadwalBooking(Request $request){
        $status = true;
        $lapangan = $request->toko->lapangan->find($request->id_lapangan);
        foreach($request->id_jadwal as $jd){
            if($status){
                $jadwal = $lapangan->jadwal->find($jd);
                if($jadwal){
                    if(!\App\Venue\BookingHeader::isLapanganBisaDibooking($lapangan, $request->tanggal, $jadwal->jam_mulai)){
                        $status = false;
                    }
                } else {
                    
                    $status = false;
                }
            }
        }
        return $status;
    }

    private function generateKodeBooking(){
        $string = "";
        do{
            $string = str_random(8);
            $kodeBooking = \App\Venue\Booking::where('kode_booking',$string)->get();
        } while(!$kodeBooking->isEmpty());
        return strtoupper($string);
    }

    private function generateKodeBookingHeader(){
        $string = "";
        do{
            $string = str_random(8);
            $kodeBooking = \App\Venue\BookingHeader::where('kode_booking',$string)->get();
        } while(!$kodeBooking->isEmpty());
        return strtoupper($string);
    }

    private function generateRefNo(){
        return date('YmdHis').rand(10,99);
    }

    public function getRiwayat(Request $request)
    {
        $tanggal = date('Y-m-d');
        if($request->tanggal){
            $tanggal = $request->tanggal;
        }
        $from  = $tanggal." 00:00:00";
        $to  = $tanggal." 23:59:59";
        //jika search ada, maka tanggal tidak berlaku
        if($request->has('s')){
            $riwayat = \App\Venue\BookingHeader::where('id_toko',$request->toko->id)
                ->where('kode_booking',$request->s)->get();
        } else {
            $riwayat = \App\Venue\BookingHeader::where('id_toko',$request->toko->id)
                ->whereBetween('created_at',[$from, $to])->get();
        }
        
        return \App\Http\Resources\Venue\BookingResource::collection($riwayat);
    }

    public function getRiwayatDetail($id, Request $request){
        $riwayat = \App\Venue\BookingHeader::findOrFail($id);
        return new \App\Http\Resources\Venue\BookingResource($riwayat);
    }

    public function konfirmasiBermain(Request $request){
        $bookingHeader = \App\Venue\BookingHeader::find($request->id_booking);
        if($bookingHeader){
            $bookingHeader->status_pesanan = BOOKING_STATUS_PESANAN_KONFIRMASI_BERMAIN;
            $bookingHeader->status_pembayaran = BOOKING_STATUS_PEMBAYARAN_LUNAS;
            $bookingHeader->status_konfirmasi = STATUS_KONFIRMASI_BERMAIN;
            $bookingHeader->waktu_konfirmasi = date('Y-m-d H:i:s');

            //cek dulu sudah bayar lunas atau belum
            if($bookingHeader->kekurangan){
                $bookingHeader->sudah_dibayar = $bookingHeader->sudah_dibayar + $bookingHeader->kekurangan;
                $bookingHeader->kekurangan = 0;
            }

            $bookingHeader->save();
            return response()->json([
                'status'=>true,
                'msg'   => 'Konfirmasi Berhasil'
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'msg'   => 'Kode Booking Tidak Valid'
            ]);
        }
        
    }
    public function konfirmasiBatal(Request $request){
        $bookingHeader = \App\Venue\BookingHeader::find($request->id_booking);
        if($bookingHeader){
            $bookingHeader->status_pesanan = BOOKING_STATUS_PESANAN_BATAL;
            $bookingHeader->waktu_pembatalan = date('Y-m-d H:i:s');
            $bookingHeader->save();
            return response()->json([
                'status'=>true,
                'msg'   => 'Pembatalan Berhasil'
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'msg'   => 'Kode Booking Tidak Valid'
            ]);
        }
        
    }
}
