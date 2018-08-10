<?php

namespace App\Http\Controllers\Admin\Merchant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request){

        \App\Toko::setDashboardBarangFirebase(\App\Toko::find($request->toko->id));

        $dataPesananHariIni = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $dataPesananHariIni->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $dataPesananHariIni->whereDate('trx_penyewaan.created_at', DB::raw('CURDATE()'));
        $dataPesananHariIni->where('barang.id_toko','=',$request->toko->id);

        $dataSewaBerjalan = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $dataSewaBerjalan->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $dataSewaBerjalan->where('barang.id_toko','=',$request->toko->id);
        $dataSewaBerjalan->whereIn('trx_penyewaan.status_pesanan',[2,3,9]);

        $dataPengambilanHariIni = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $dataPengambilanHariIni->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $dataPengambilanHariIni->where('barang.id_toko','=',$request->toko->id);
        $dataPengambilanHariIni->whereDate('trx_penyewaan.tanggal_sewa', DB::raw('CURDATE()'));
        
        $dataBerakhirHariIni = \App\Produk\Transaksi::select(['trx_penyewaan.*']);
        $dataBerakhirHariIni->join('barang','barang.id','=','trx_penyewaan.id_barang');
        $dataBerakhirHariIni->where('barang.id_toko','=',$request->toko->id);
        $dataBerakhirHariIni->whereDate('trx_penyewaan.tanggal_kembali', DB::raw('CURDATE()'));


        $pesanan_hari_ini       = count($dataPesananHariIni->get());
        $sewa_berjalan          = count($dataSewaBerjalan->get());
        $pengambilan_hari_ini   = count($dataPengambilanHariIni->get());
        $berakhir_hari_ini      = count($dataBerakhirHariIni->get());
        $toko = \App\Toko::find($request->toko->id);
        return response()->json([
            'total_pesanan_hari_ini'    => $pesanan_hari_ini,
            'total_sewa_berjalan'       => $sewa_berjalan,
            'total_pengambilan_hari_ini'=> $pengambilan_hari_ini,
            'total_berakhir_hari_ini'   => $berakhir_hari_ini,
            'grafik_sewa_barang_tahunan'=> [
                'bulan_1'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,1),
                'bulan_2'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,2),
                'bulan_3'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,3),
                'bulan_4'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,4),
                'bulan_5'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,5),
                'bulan_6'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,6),
                'bulan_7'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,7),
                'bulan_8'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,8),
                'bulan_9'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,9),
                'bulan_10'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,10),
                'bulan_11'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,11),
                'bulan_12'  => \App\Produk\Transaksi::getTotalDisewaBulanan($toko,12),
            ]
        ]);
    }
    

    
}
