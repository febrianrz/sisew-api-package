<?php

namespace App\Http\Controllers\User\Venue;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Venue\Event;
class EventController extends Controller
{
    public function eventList(Request $request){
        
        $event = Event::query();
        $event->select(['toko_events.*']);
        $event->join('tokos','tokos.id','=','toko_events.id_toko');
        $event->leftJoin('provinsis','provinsis.id','=','tokos.provinsis_id');
        $event->leftJoin('kotas','kotas.id','=','tokos.kotas_id');

        $event->where('tokos.status_online',MERCHANT_STATUS_ONLINE);
        $event->where('toko_events.status',1);
        $event->where('toko_events.tanggal_selesai','>=',date('Y-m-d').' 00:00:00');

        if($request->has('kata_kunci')) {
            $katakunci = $request->kata_kunci;
            $event->where(function($query) use($katakunci) {
                        $query->where('toko_events.nama_event','like','%'.$katakunci.'%');
                        $query->orWhere('provinsis.nama','like','%'.$katakunci.'%');
                        $query->orWhere('kotas.nama','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.nama','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.alamat_gmap','like','%'.$katakunci.'%');
                        $query->orWhere('tokos.alamat','like','%'.$katakunci.'%');

                    });
        }


        /**
         * Ordering
         */
        if($request->has('order_by')){
            if($request->order_by == "terbaru") $event->orderBy('toko_events.created_at','desc');
            
        }
        return \App\Http\Resources\Venue\EventResource::collection($event->paginate(($request->has('limit') ? $request->limit:10 )));
    }

    public function getRiwayat(Request $request){
        if($request->user){
            $data = \App\Venue\EventPeserta::where('id_customer',$request->user->id)
                ->orWhere('firebase_token','=',$request->header('Firebase-token'))
                ->orderBy('created_at','desc')
                ->get();
            return \App\Http\Resources\Venue\EventPesertaResource::collection($data);
        } else {
            $data = \App\Venue\EventPeserta::where('firebase_token','=',$request->header('Firebase-token'))
                ->orderBy('created_at','desc')
                ->get();
            return \App\Http\Resources\Venue\EventPesertaResource::collection($data);
        }
        
    }

    public function setMetodePembayaran(Request $request){
        $request->validate([
            'id_event_peserta'  => 'required'
        ]);
        $eventPeserta = \App\Venue\EventPeserta::where('id',$request->id_event_peserta)->first();
        if(!$eventPeserta){
            return response()->json([
                'status'    => false,
                'msg'       => 'Booking Event tidak ditemukan'
            ]);
        } 
        $eventPeserta->metode_pembayaran = $request->id_metode;
        $eventPeserta->id_bank           = $request->id_bank;
        $eventPeserta->status = STATUS_PEMBAYARAN_MENUNGGU_PEMBAYARAN;
        $eventPeserta->save();
        \MyFirebaseUser::pushBookingEvent($request, $eventPeserta);
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data',
            'booking'       => new \App\Http\Resources\Venue\EventPesertaResource($eventPeserta)
        ]);
    }

    public function konfirmasiPembayaran(Request $request){
        $request->validate([
            'id_event_peserta'  => 'required'
        ]);
        $eventPeserta = \App\Venue\EventPeserta::where('id',$request->id_event_peserta)
            ->first();
        if(!$eventPeserta){
            return response()->json([
                'status'    => false,
                'msg'       => 'Booking Event tidak ditemukan'
            ]);
        } 
        $eventPeserta->status = STATUS_PEMBAYARAN_MENUNGGU_KONFIRMASI;
        $eventPeserta->save();
        \MyFirebaseUser::pushBookingEvent($request, $eventPeserta);
        return response()->json([
            'status'    => true,
            'msg'       => 'Konfirmasi Pembayaran Berhasil'
        ]);
    }
}
