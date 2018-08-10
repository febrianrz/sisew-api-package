<?php

namespace App\Http\Controllers\Admin\Venue\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Webpatser\Uuid\Uuid;
class EventController extends Controller
{
    public function index(Request $request){
        $data = \App\Venue\Event::where('id_toko',$request->toko->id)
            ->orderBy('created_at','desc')
            ->paginate(($request->has('limit') ? $request->limit:10 ));
        return \App\Http\Resources\Venue\EventResource::collection($data);
    }

    public function save(Request $request){
        $request->validate([
            'nama'      => 'required',
            'deskripsi' => 'required',
            'syarat_ketentuan' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'kategori' => 'required',
            'status'    => 'required|in:0,1,2,3,4',
            'jenis_peserta' => 'required|numeric',
            'jenis_harga'   => 'required'
        ]);

        $kategori = \App\Venue\MasterEventKategori::firstOrCreate(['nama'=>$request->kategori]);

        $row = new \App\Venue\Event;
        $row->id                = \Webpatser\Uuid\Uuid::generate()->string;
        $row->nama_event        = $request->nama;
        $row->deskripsi         = $request->deskripsi;
        $row->syarat_ketentuan  = $request->syarat_ketentuan;
        $row->tanggal_mulai     = $request->tanggal_mulai." ".$request->jam_mulai.":00";
        $row->tanggal_selesai   = $request->tanggal_selesai." ".$request->jam_selesai.":00";
        $row->id_produk_sub_kategori = $kategori->id;
        $row->status            = $request->status;
        $row->created_by        = $request->user->id;
        $row->id_toko           = $request->toko->id;
        $row->maksimum          = 0;
        $row->harga             = 0;
        $row->jenis_peserta     = $request->jenis_peserta;
        $row->alamat            = $request->alamat;
        $row->longitude         = $request->longitude;
        $row->latitude          = $request->latitude;
        $row->jenis_harga       = $request->jenis_harga;
        $row->catatan_pendaftar = $request->catatan_pendaftar;
        $row->save();
        //masukkan gambar
        if($request->has('gambar')){
            foreach($request->gambar as $gambar){
                $rowGambar              = new \App\Venue\EventGambar;
                $rowGambar->id_toko_event = $row->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }
        if($request->has('fasilitas')){
            foreach($request->fasilitas as $fasilitas){
                $rowFasilitas              = new \App\Venue\EventFasilitas;
                $rowFasilitas->id_toko_event = $row->id;
                $rowFasilitas->nama      = $fasilitas;
                $rowFasilitas->save();
            }
        }
        if($request->has('hasil_event')){
            foreach($request->hasil_event as $fasilitas){
                $rowFasilitas              = new \App\Venue\EventHasilFasilitas;
                $rowFasilitas->id_toko_event = $row->id;
                $rowFasilitas->nama      = $fasilitas;
                $rowFasilitas->save();
            }
        }

        //masukkan paket eventnya
        if($row->jenis_harga == "Gratis"){
            $paket                  = new \App\Venue\EventPaket;
            $paket->id              = \Webpatser\Uuid\Uuid::generate()->string;
            $paket->id_toko_event   = $row->id;
            $paket->nama            = "PAKET_GRATIS";
            $paket->jumlah_peserta  = $request->maksimum;
            $paket->harga           = $request->harga;
            $paket->status          = 1;
            $paket->save();
        } else if($row->jenis_harga == "Satu harga"){
            $paket                  = new \App\Venue\EventPaket;
            $paket->id              = \Webpatser\Uuid\Uuid::generate()->string;
            $paket->id_toko_event   = $row->id;
            $paket->nama            = "PAKET_STANDART";
            $paket->jumlah_peserta  = $request->maksimum;
            $paket->harga           = $request->harga;
            $paket->status          = 1;
            $paket->save();
        } else {
            if($request->has('paket_event')){
                $arrPaketEvent = json_decode($request->paket_event);
                foreach($arrPaketEvent as $rowPaket){
                    $rowPaket               = (array)$rowPaket;
                    $paket                  = new \App\Venue\EventPaket;
                    $paket->id              = \Webpatser\Uuid\Uuid::generate()->string;
                    $paket->id_toko_event   = $row->id;
                    $paket->nama            = $rowPaket['nama'];
                    $paket->jumlah_peserta  = $rowPaket['jumlah_peserta'];
                    $paket->harga           = $rowPaket['harga'];
                    $paket->status          = 1;
                    $paket->save();
                }
            }
        }
        
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request){
        $request->validate([
            'nama'      => 'required',
            'deskripsi' => 'required',
            'syarat_ketentuan' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'kategori' => 'required',
            'status'    => 'required|in:0,1,2,3,4',
            'jenis_peserta' => 'required|numeric',
        ]);
        $row = \App\Venue\Event::find($id);

        if(!$row){
            return response()->json([
                'status'        => false,
                'msg'           => 'Event tidak ditemukan'
            ]);    
        }
        $kategori = \App\Venue\MasterEventKategori::firstOrCreate(['nama'=>$request->kategori]);

        $row->nama_event        = $request->nama;
        $row->deskripsi         = $request->deskripsi;
        $row->syarat_ketentuan  = $request->syarat_ketentuan;
        $row->tanggal_mulai     = $request->tanggal_mulai;
        $row->tanggal_selesai   = $request->tanggal_selesai;
        $row->id_produk_sub_kategori = $kategori->id;
        $row->status            = $request->status;
        $row->jenis_peserta     = $request->jenis_peserta;
        $row->alamat            = $request->alamat;
        $row->longitude         = $request->longitude;
        $row->latitude          = $request->latitude;
        
        $row->catatan_pendaftar = $request->catatan_pendaftar;
        $row->save();
        
        //hapus gambar 
        if($request->has('id_gambar_hapus')){
            foreach($request->id_gambar_hapus as $gambar){
                \App\Venue\EventGambar::where('id','=',$gambar)->delete();
            }
        }
        //masukkan gambar
        if($request->has('gambar')){
            foreach($request->gambar as $gambar){
                $rowGambar              = new \App\Venue\EventGambar;
                $rowGambar->id_toko_event   = $row->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }
        if($request->has('fasilitas')){
            $exists_data = [];
                foreach($request->get('fasilitas') as $fasilitas){
                    $exists = \App\Venue\EventFasilitas::where('id_toko_event',$row->id)
                        ->where('nama',$fasilitas)->first();
                    if(!$exists){
                        $rowFasilitas = new \App\Venue\EventFasilitas;
                        $rowFasilitas->nama = $fasilitas;
                        $rowFasilitas->id_toko_event = $row->id;
                        $rowFasilitas->save();
                    }
                    array_push($exists_data, $fasilitas);
                }
                //hapus jika tidak ada
                \App\Venue\EventFasilitas::where('id_toko_event', $row->id)
                    ->whereNotIn('nama',$exists_data)
                    ->delete();
        }

        if($request->has('hasil_event')){
            $exists_data = [];
                foreach($request->get('hasil_event') as $fasilitas){
                    $exists = \App\Venue\EventHasilFasilitas::where('id_toko_event',$row->id)
                        ->where('nama',$fasilitas)->first();
                    if(!$exists){
                        $rowFasilitas = new \App\Venue\EventHasilFasilitas;
                        $rowFasilitas->nama = $fasilitas;
                        $rowFasilitas->id_toko_event = $row->id;
                        $rowFasilitas->save();
                    }
                    array_push($exists_data, $fasilitas);
                }
                //hapus jika tidak ada
                \App\Venue\EventHasilFasilitas::where('id_toko_event', $row->id)
                    ->whereNotIn('nama',$exists_data)
                    ->delete();
        }

        if($request->has('paket_dihapus')){
            foreach($request->get('paket_dihapus') as $paket){
                \App\Venue\EventPaket::find($paket)->delete();
            }
        }


        //masukkan paket eventnya
        if($row->jenis_harga == "Gratis"){
            $paket = \App\Venue\EventPaket::where('id_toko_event',$row->id)->first();
            $paket->jumlah_peserta = $request->maksimum;
            $paket->harga = $request->harga;
            $paket->save();
        } else if($row->jenis_harga == "Satu harga"){
            $paket = \App\Venue\EventPaket::where('id_toko_event',$row->id)->first();
            $paket->jumlah_peserta = $request->maksimum;
            $paket->harga = $request->harga;
            $paket->save();
        } else {
            if($request->has('paket_event')){
                $arrPaketEvent = json_decode($request->paket_event);
                foreach($arrPaketEvent as $rowPaket){
                    $rowPaket = (array)$rowPaket;
                    $paket = \App\Venue\EventPaket::find($rowPaket['id']);
                    if(!$paket){
                        $paket = new \App\Venue\EventPaket;
                        $paket->id = \Webpatser\Uuid\Uuid::generate()->string;
                        $paket->id_toko_event = $row->id;
                    }
                    $paket->nama = $rowPaket['nama'];
                    $paket->jumlah_peserta = $rowPaket['jumlah_peserta'];
                    $paket->harga = $rowPaket['harga'];
                    $paket->status= 1;
                    $paket->save();
                }
            }
        }

        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }

    public function savePeserta($id, Request $request){
        $request->validate([
            'nama_tim'      => 'required',
            'nama_pj'      => 'required',
            'telepon'   => 'required',
            'harga'     => 'required',
            'status'    => 'required'
        ]);
        $event = \App\Venue\Event::find($id);
        if(!$event){
            return response()->json([
                'status'        => false,
                'msg'           => 'Event tidak ditemukan'
            ]);    
        }

        $row        = new \App\Venue\EventPeserta;
        $row->id    = Uuid::generate()->string;
        $row->nama_tim = $request->nama_tim;
        $row->id_toko_event = $event->id;
        $row->kode_event    = str_random(8);
        $row->amount        = $request->harga;
        $row->nama_pj1      = $request->nama_pj;
        $row->telepon_pj1   = $request->telepon;
        $row->status        = $request->status;  //ini adalah status pembayaran
        $row->id_customer   = $request->has('id_customer')?$request->id_customer:null;
        $row->kode_unik     = $request->has('kode_unik')?$request->kode_unik:0;
        $row->biaya_admin   = $request->has('fee_layanan')?$request->fee_layanan:0;
        $row->id_bank       = $request->has('id_bank')?$request->id_bank:null;
        $row->metode_pembayaran = $request->has('metode_bayar')?$request->metode_bayar:6;
        $row->expired_at    = $request->has('id_customer')?date('Y-m-d H:i:s'):null;
        $row->status_pesanan= 0;
        $row->save();
        if($request->has('id_customer')){
            $customer = \App\Customer::find($request->id_customer);
            if($customer){
                \Illuminate\Support\Facades\Mail::to($customer->email)
                        ->send(new \App\Mail\Venue\BookingEvent($row));
            }
        }
        
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }

    public function daftar(Request $request){
        $request->validate([
            'id'            => 'required',
            'nama'          => 'required',
            'email'         => 'required',
            'telepon'       => 'required',
            'auto_daftar'   => 'required',
            'peserta'       => 'required',
        ]);
        $event = \App\Venue\Event::find($request->id);
        if(!$event){
            return response()->json([
                'status'        => false,
                'msg'           => 'Event tidak ditemukan'
            ]);    
        }
        $customer = null;
        if($request->has('email')){
            $customer = \App\Customer::where('email','=',$request->id_customer)->first();
        }

        $kodeUnik   = rand(0,100);
        $feeLayanan = 3000;
        $subTotal   = 0;
        $total      = $subTotal+$kodeUnik+$feeLayanan;
        

        $row                = new \App\Venue\EventPeserta;
        $row->id            = Uuid::generate()->string;
        $row->order_id      = date('mds').rand(100,999);
        $row->nama_tim      = $request->nama;
        $row->id_toko_event = $event->id;
        $row->kode_event    = "-";
        $row->amount        = 0;
        $row->nama_pj1      = $request->nama;
        $row->telepon_pj1   = $request->telepon;
        $row->email         = $request->email;
        $row->status        = 0;  //ini adalah status pembayaran
        $row->id_customer   = $customer?$customer->id:null;
        $row->kode_unik     = $kodeUnik;
        $row->biaya_admin   = $feeLayanan;
        
        $row->expired_at    = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $row->sumber_booking = $request->sumberBooking;
        $row->status_pesanan= 0;
        $row->firebase_token= $request->header('Firebase-token');
        
        $paketEvent         = json_decode($request->peserta); 
        foreach($paketEvent as $rowPaket){
            $rowPaket = (array) $rowPaket;
            if(count($rowPaket['peserta']) == 0){
                continue;
            } else {
                foreach($rowPaket['peserta'] as $peserta){
                    $peserta = (array) $peserta;
                    $eventPesertaDetail             = new \App\Venue\EventPesertaDetail;
                    $eventPesertaDetail->id         = Uuid::generate()->string;
                    $eventPesertaDetail->id_event_peserta = $row->id;
                    $eventPesertaDetail->nama       = $peserta['nama'];
                    $eventPesertaDetail->email      = isset($peserta['email'])?$peserta['email']:null;
                    $eventPesertaDetail->telepon    = isset($peserta['telepon'])?$peserta['telepon']:null;
                    $eventPesertaDetail->harga      = (int)$rowPaket['harga'];
                    $eventPesertaDetail->id_event_paket = $rowPaket['id'];
                    $eventPesertaDetail->save();
                    $subTotal += $eventPesertaDetail->harga;
                }
                
            }
        }
        $row->sub_total = $subTotal;
        $row->total     = ($total+$subTotal);
        $row->save();
        
        // \Illuminate\Support\Facades\Mail::to($customer->email)
        //         ->send(new \App\Mail\Venue\BookingEvent($row));
        \MyFirebaseUser::bookingEvent($request, $row, "Booking Event");

        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data',
            'booking'       => new \App\Http\Resources\Venue\EventPesertaResource($row)
        ]);
    }

    public function getPeserta($id, Request $request){
        $data = \App\Venue\EventPeserta::where('id_toko_event',$id)->get();
        return \App\Http\Resources\Venue\EventPesertaResource::collection($data);
    }

    public function updatePeserta($id, $id_peserta, Request $request){
        $request->validate([
            'nama_tim'      => 'required',
            'nama_pj'      => 'required',
            'telepon'   => 'required',
            'harga'     => 'required',
            'status'    => 'required'
        ]);
        $event = \App\Venue\Event::find($id);
        if(!$event){
            return response()->json([
                'status'        => false,
                'msg'           => 'Event tidak ditemukan'
            ]);    
        }
        $row = \App\Venue\EventPeserta::find($id_peserta);
        if(!$row){
            return response()->json([
                'status'        => false,
                'msg'           => 'Peserta tidak ditemukan'
            ]);    
        }
        $row->nama_tim = $request->nama_tim;
        $row->id_toko_event = $event->id;
        $row->amount = $request->harga;
        $row->nama_pj1 = $request->nama_pj;
        $row->telepon_pj1 = $request->telepon;
        $row->status = $request->status;
        $row->save();
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan data'
        ]);
    }

    public function getRiwayat(Request $request){
        $toko = $request->toko;
        $riwayat = \App\Venue\EventPeserta::orderBy('created_at','desc')
            ->with(['event' => function($q) use ($toko){
                $q->where('id_toko','=',$toko->id);
            }])->get();
        return \App\Http\Resources\Venue\EventPesertaResource::collection($riwayat);
    }

    public function konfirmasiKehadiran(Request $request){
        $request->validate([
            'id'      => 'required'
        ]);
        $eventPeserta = \App\Venue\EventPeserta::find($request->id);
        if(!$eventPeserta){
            return response()->json([
                'status'        => false,
                'msg'           => 'Kode Booking Tidak Valid'
            ]);
        }

        foreach($eventPeserta->pesertaTambahan as $peserta){
            $absen = new \App\Venue\EventPesertaDetailAbsensi;
            $absen->id = Uuid::generate()->string;
            $absen->id_peserta_detail = $peserta->id;
            $absen->save();
        }

        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil mengkonfirmasi kehadiran'
        ]);
    }

}
