<?php

namespace App\Http\Controllers\Admin\Venue;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Venue\LapanganResource;
use App\Venue\Lapangan;

class LapanganController extends Controller
{
    public function index(Request $request)
    {
        
        $data = Lapangan::select(['toko_produks.*']);
        $data->join('tokos','tokos.id','=','toko_produks.id_toko');
        $data->where('tokos.id',$request->toko->id);
        
        
        // return LapanganResource::collection(Lapangan::where('id_toko',$request->toko->id)->where('status',1)->paginate(($request->has('limit') ? $request->limit:10 )));
        return LapanganResource::collection($data->get());
    }

    public function getJadwal($id, Request $request)
    {
        
        $lapangan   = Lapangan::findOrFail($id);
        $tanggal    = date('Y-m-d');
        
        if($request->has('tanggal')){
            $tanggal = $request->tanggal;
        } else {
            $request->tanggal   = $tanggal;
        }
        $request->lapangan      = $lapangan;
        $hari                   = date('w',strtotime($tanggal))+1;
        if($request->has('hari')){
            
            $hari = $request->hari;
        }
        return $this->getListJadwal($lapangan, $hari);
    }

    public function getListJadwal(Lapangan $lapangan, $hari){
        $jadwal     = \App\Venue\JadwalLapangan::query();
        $jadwal->where('id_toko_produk',$lapangan->id);
        $jadwal->where('hari',$hari);
        $jadwal->orderBy('hari','asc');
        $jadwal->orderBy('jam_mulai','asc');
        return \App\Http\Resources\Venue\JadwalLapanganResource::collection($jadwal->get());
    }

    public function getJadwalSeminggu($id, Request $request){
        $lapangan = Lapangan::findOrFail($id);
        $request->lapangan      = $lapangan;
        $senin = $this->getListJadwal($lapangan, 2);
        $selasa = $this->getListJadwal($lapangan, 3);
        $rabu = $this->getListJadwal($lapangan, 4);
        $kamis = $this->getListJadwal($lapangan, 5);
        $jumat = $this->getListJadwal($lapangan, 6);
        $sabtu = $this->getListJadwal($lapangan, 7);
        $minggu = $this->getListJadwal($lapangan, 1);

        return response()->json([
            'senin' => $senin,
            'selasa'=> $selasa,
            'rabu'  => $rabu,
            'kamis' => $kamis,
            'jumat' => $jumat,
            'sabtu' => $sabtu,
            'minggu'=> $minggu
        ]);
    }

    public function save(Request $request){
        $request->validate([
            'nama'      => 'required',
            'keterangan'=> 'required',
            'status'    => 'required|in:1,0',
            'jenis_lantai' => 'required|exists:jenis_lantai,id',
            'produk_kategori' => 'required|exists:produk_sub_kategoris,id',
            'harga'     => 'required|numeric',
            'gambar.*'    => 'image'
        ]);

        $row = new Lapangan;
        $row->id       = \Webpatser\Uuid\Uuid::generate()->string;

        $row->id_toko   = $request->toko->id;
        $row->nama      = $request->nama;
        $row->keterangan= $request->keterangan;
        $row->id_produk_sub_kategori = $request->produk_kategori;
        $row->id_jenis_lantai = $request->jenis_lantai;
        $row->slug      = $request->nama;
        $row->harga     = $request->harga;
        $row->status    = $request->status;
        $row->save();
        //masukkan gambar
        if($request->has('gambar')){
            foreach($request->gambar as $gambar){
                $rowGambar              = new \App\Venue\LapanganGambar;
                $rowGambar->id          = \Webpatser\Uuid\Uuid::generate()->string;
                $rowGambar->id_produk   = $row->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }
        //masukkan fasilitas
        foreach($request->get('fasilitas') as $fasilitas){
            $rowFasilitas = new \App\FasilitasLapangan;
            $rowFasilitas->fasilitas = $fasilitas;
            $rowFasilitas->id_toko_produk = $row->id;
            $rowFasilitas->save();
        }
        

        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menyimpan data'
        ]);
    }

    public function update($id, Request $request){
        $request->validate([
            'nama'      => 'required',
            'keterangan'=> 'required',
            'status'    => 'required|in:1,0',
            'jenis_lantai' => 'required|exists:jenis_lantai,id',
            'produk_kategori' => 'required|exists:produk_sub_kategoris,id'
        ]);

        $row = Lapangan::find($id);
        if(!$row){
            return response()->json([
                'status'    => false,
                'msg'       => 'Lapangan Tidak Ditemukan'
            ]);    
        }
    
        $row->id_toko   = $request->toko->id;
        $row->nama      = $request->nama;
        $row->keterangan= $request->keterangan;
        $row->id_produk_sub_kategori = $request->produk_kategori;
        $row->id_jenis_lantai = $request->jenis_lantai;
        $row->slug      = $request->nama;
        $row->status    = $request->status;
        $row->save();

        //masukkan gambar
        if($request->has('gambar')){
            foreach($request->gambar as $gambar){
                $rowGambar              = new \App\Venue\LapanganGambar;
                $rowGambar->id          = \Webpatser\Uuid\Uuid::generate()->string;
                $rowGambar->id_produk   = $row->id;
                $rowGambar->gambar      = $gambar->store('public/venue/'.$request->toko->id);
                $rowGambar->save();
            }
        }
        //masukkan fasilitas
        \App\FasilitasLapangan::where('id_toko_produk', $row->id)->delete();
        foreach($request->get('fasilitas') as $fasilitas){
            $rowFasilitas = new \App\FasilitasLapangan;
            $rowFasilitas->fasilitas = $fasilitas;
            $rowFasilitas->id_toko_produk = $row->id;
            $rowFasilitas->save();
        }

        // [belum berhasil]
        //hapus gambar lama jika ada 
        if($request->has('id_gambar_hapus')){
            foreach($request->id_gambar_hapus as $gambar_id){
                $rowGambar = \App\Venue\LapanganGambar::find($gambar_id);
                if($rowGambar){
                    $rowGambar->delete();
                }
                
            }
        }

        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil mengupdate Data'
        ]);
    }

    public function setJadwal($id, Request $request){
        $request->validate([
            'jam_mulai'         => 'required',
            'jam_selesai'       => 'required',
            'hari'              => 'required|numeric',
            'harga'             => 'required|numeric',
            'status'            => 'required|in:1,0'
        ]);

        $lapangan               = Lapangan::findOrFail($id);
        $jadwal                 = new \App\Venue\JadwalLapangan;
        $jadwal->id             = \Webpatser\Uuid\Uuid::generate()->string;
        $jadwal->id_toko_produk = $lapangan->id;
        $jadwal->jam_mulai      = $request->jam_mulai;
        $jadwal->jam_selesai    = $request->jam_selesai;
        $jadwal->hari           = $request->hari;
        $jadwal->harga          = $request->harga;
        $jadwal->status         = $request->status;
        $jadwal->save();
        // return response()->json([
        //     'status'    => true,
        //     'msg'       => $jadwal->id
        // ]);
        return new \App\Http\Resources\Venue\JadwalLapanganResource($jadwal);
    }   

    public function updateJadwal($id_lapangan, $id_jadwal, Request $request){
        $jadwal = \App\Venue\JadwalLapangan::findOrFail($id_jadwal);
        $jadwal->jam_mulai      = $request->jam_mulai;
        $jadwal->jam_selesai    = $request->jam_selesai;
        $jadwal->hari           = $request->hari;
        $jadwal->harga          = $request->harga;
        $jadwal->status         = $request->status;
        $jadwal->save();
        return new \App\Http\Resources\Venue\JadwalLapanganResource($jadwal);
    }

    public function delete($id, Request $request){
        $row = Lapangan::find($id);
        $row->delete();
        return response()->json([
            'status'    => true,
            'msg'       => 'Berhasil menghapus data'
        ]);
    }
}