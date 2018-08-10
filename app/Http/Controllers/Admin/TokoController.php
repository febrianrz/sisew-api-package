<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TokoController extends Controller
{
    public function setFirebaseToken(Request $request)
    {
        $request->user->google_token = $request->token;
        $request->user->save();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function update(Request $request){
        $request->validate([
            'nama'      => 'required',
            'telepon'   => 'required',
            'alamat'    => 'required',
            'deskripsi' => 'required',
            'status_online' => 'required'
        ]);
        $toko              = \App\Toko::find($request->toko->id);

        $kota              = $request->has('kota')?\App\Sisew\Kota::where('nama','like',$request->kota)->first():null;
        $provinsi          = $request->has('provinsi')?\App\Sisew\Provinsi::where('nama','like',$request->provinsi)->first():null;
        $kecamatan          = $request->has('kecamatan')?\App\Sisew\Kecamatan::where('nama','like',$request->kecamatan)->first():null;

        $toko->nama        = $request->nama;
        $toko->keterangan  = $request->deskripsi;
        $toko->alamat      = $request->alamat;
        $toko->telepon     = $request->telepon;
        
        $toko->status_online = $request->status_online;

        if($request->hasFile('logo')) {
            $toko->logo = $request->logo->store('public/venue/'.$toko->id);
        }
        
        if($kota) $toko->kotas_id = $kota->id;
        if($kecamatan) $toko->id_kecamatan = $kecamatan->id;
        if($provinsi) $toko->provinsis_id = $provinsi->id;
        if($request->has('alamat_gmap')) $toko->alamat_gmap = $request->alamat_gmap;
        if($request->has('latitude')) $toko->latitude = $request->latitude;
        if($request->has('longitude')) $toko->longitude = $request->longitude; 
        if($request->has('kodepos')) $toko->kodepos = $request->kodepos;

        if($request->has('no_rekening')) $toko->no_rekening = $request->no_rekening;
        if($request->has('atas_nama')) $toko->atas_nama = $request->atas_nama;
        if($request->has('nama_bank')) $toko->nama_bank = $request->nama_bank;

        if($request->has('whatsapp')) $toko->whatsapp_no = $request->whatsapp;
        if($request->has('website')) $toko->website = $request->website;
        if($request->has('akun_ig')) $toko->akun_ig = $request->akun_ig;
        $toko->save();

        if($request->has('banner')){
            foreach($request->banner as $gambar){
                $rowGambar              = new \App\TokoBanner;
                $rowGambar->id_toko   = $toko->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }

        return new \App\Http\Resources\TokoResource($toko);
    }

    public function getSaldo(Request $request){
        $saldo = \App\TokoSaldo::where('id_toko',$request->toko->id)->first();

        $pemasukkan = \App\TokoSaldoRincian::where('id_toko',$request->toko->id)
            ->where('id_master_jenis_transaksi',2)
            ->orderBy('created_at','desc')
            ->take(100)
            ->get();
        $dataPemasukkan = [];
        foreach($pemasukkan as $pm){
            $tmp = [
                'id'            => $pm->id,
                'id_booking'    => $pm->id_customer_booking,
                'jenis_transaksi'=> $pm->jenis_transaksi->nama,
                'amount'        => $pm->amount,
                'kode_booking'  => $pm->kode_booking,
                'created_at'    => $pm->created_at,
                'tanggal'       => getTanggalIndonesia($pm->created_at),
                'ref_no'        => date('YmdHis',strtotime($pm->created_at))
            ];
            array_push($dataPemasukkan,$tmp);
        }

        $penerimaan = \App\TokoSaldoRincian::where('id_toko',$request->toko->id)
            ->where('id_master_jenis_transaksi',1)
            ->orderBy('created_at','desc')
            ->take(100)
            ->get();
        $dataPenerimaan = [];
        foreach($penerimaan as $pm){
            $tmp = [
                'id'            => $pm->id,
                'id_booking'    => null,
                'jenis_transaksi'=> $pm->jenis_transaksi->nama,
                'amount'        => $pm->amount,
                'kode_booking'  => null,
                'created_at'    => $pm->created_at,
                'tanggal'       => getTanggalIndonesia($pm->created_at),
                'ref_no'        => date('YmdHis',strtotime($pm->created_at))
            ];
            array_push($dataPenerimaan,$tmp);
        }

        return response()->json([
            'data'=>[
                'saldo'         => (!$saldo?0:$saldo->amount),
                'point'         => \App\TokoSaldoRincian::getPointThisMonth(\App\Toko::find($request->toko->id)),
                'pemasukkan'    => $dataPemasukkan,
                'penerimaan'    => $dataPenerimaan,
            ]
        ]);
    }

    public function setFavorite($id, Request $request){
        $venue = \App\Toko::find($id);
        if(!$venue){
            return response()->json([
                'status'        => false,
                'msg'           => 'Venue tidak ditemukan'
            ]);
        }

        $cek = \App\TokoFavorite::where('id_toko',$id)
            ->where('id_customer',$request->user->id)
            ->first();
        if(!$cek){
            $new = new \App\TokoFavorite;
            $new->id_toko = $id;
            $new->id_customer = $request->user->id;
            $new->save();
        }
        return response()->json([
            'status'        => true,
            'msg'           => 'Berhasil menyimpan sebagai favorite'
        ]);
    }

    public function unFavorite($id, Request $request){
        $venue = \App\Toko::find($id);
        if(!$venue){
            return response()->json([
                'status'        => false,
                'msg'           => 'Venue tidak ditemukan'
            ]);
        }
        \App\TokoFavorite::where('id_toko',$id)
            ->where('id_customer',$request->user->id)
            ->delete();
        return response()->json([
                'status'        => true,
                'msg'           => 'Berhasil menghapus dari favorite'
            ]);
    }
}
