<?php
namespace App\Http\Controllers\Sisew\Cron;

use App\Http\Controllers\Sisew\CronInterface\CronInterface;

class BookingLapanganCronController implements CronInterface {

    /**
     * Implements method run
     * untuk eksekusi cronnya
     */

     public function run(){
            $this->expiredPembayaran();
     }

    private function expiredPembayaran(){
         /**
          * Expired user yang belum melakukan pembayaran
          */
        $listBooking = \App\Venue\BookingHeader::where('status_pembayaran','<=',STATUS_PEMBAYARAN_MENUNGGU_KONFIRMASI)
            ->where('expired_at','<=',date('Y-m-d H:i:s'))
            ->orderBy('expired_at','asc')
            ->take(100)
            ->get();
        
        foreach($listBooking as $booking){
            
            $booking->status_pembayaran = STATUS_PEMBAYARAN_KADALUARSA; //kadaluarsa
            $booking->save();
            $customerData = \App\Customer::where('email',$booking->email)->first();
            $request = new \Illuminate\Http\Request;
            try{
                if($customerData){
                    \MyFirebaseUser::bookingLapangan($request, $booking, $customerData, "Booking lapangan ".$booking->kode_booking);
                }
                \MyFirebaseAdmin::bookingLapangan($request, $booking, 'Booking '.$booking->kode_booking.' dibatalkan');
            } catch(\Exception $e){
                
            }
            
            //kirim email sudah dibatalkan
        }
    }
}