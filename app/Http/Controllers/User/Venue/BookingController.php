<?php

namespace App\Http\Controllers\User\Venue;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    public function doBooking(Request $request){
        $bookingController = new \App\Http\Controllers\Admin\Venue\BookingController; 
        $lapangan = \App\Venue\Lapangan::find($request->id_lapangan);
        $request->toko = $lapangan->toko;
        // die();
        return $bookingController->index($request);
    }

    public function getRiwayat(Request $request){
        if($request->user){
            $data = \App\Venue\BookingHeader::where('id_customer',$request->user->id)->get();
            return \App\Http\Resources\Venue\BookingResource::collection($data);
        } else {
            $data = \App\Venue\BookingHeader::where('id_customer','user-global')->get();
            return \App\Http\Resources\Venue\BookingResource::collection($data);
        }
        
    }

    /**
    *   Merubah status pembayaran menjadi 2 (Menunggu Verifikasi Admin)
    */
    public function konfirmasiPembayaran($id, Request $request){
        $bookingHeader = \App\Venue\BookingHeader::where('id',$id)
            ->where('status_pembayaran',STATUS_PEMBAYARAN_MENUNGGU_KONFIRMASI)
            ->first();
        if(!$bookingHeader){
            return response()->json([
                'status'    => false,
                'msg'       => 'Booking tidak ditemukan'
            ]);
        }
        $bookingHeader->status_pembayaran = STATUS_PEMBAYARAN_MENUNGGU_VERIFIKASI;
        $bookingHeader->save();
        $customerData   = \App\Customer::where('id',$bookingHeader->id_customer)->first();
        if($customerData){
            \MyFirebaseUser::bookingLapangan($request, $bookingHeader, $customerData, "Perubahan Status Pembayaran ".$bookingHeader->kode_booking);
        }
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Konfirmasi Berhasil'
        ]);
    }
}
