<?php

namespace App\Http\Controllers\Admin\Venue;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // $totalPemesananHariIni  = \App\Venue\BookingHeader::getTotalPemesanan($request->toko, date('Y-m-d'),date('Y-m-d'));
        // $totalPemesananBulanIni = \App\Venue\BookingHeader::getTotalPemesanan($request->toko, date('Y-m-01'),date('Y-m-t'));
        // $totalPemasukkanHariIni = \App\Venue\BookingHeader::getTotalPemasukkan($request->toko, date('Y-m-d'),date('Y-m-d'));
        // $totalPemasukkanBulanIni= \App\Venue\BookingHeader::getTotalPemasukkan($request->toko, date('Y-m-01'),date('Y-m-t'));
        $data = [
            'total_pemesanan_hari_ini'      => 0,
            'total_pemasukkan_hari_ini'     => 0,
            'total_pemesanan_bulan_ini'     => 0,
            'total_pemasukkan_bulan_ini'    => 0
        ];
        // \MyFirebaseAdmin::updateDashboard($request->toko);
        return response()->json($data);
    }
}
