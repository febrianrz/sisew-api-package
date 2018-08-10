<?php
namespace Situsewa\Cores\App\Http\Helpers;

function getJenisBooking($status){
    $arr = [1=>'Booking Lapangan','Booking Event','Booking Barang'];
    return $arr[$status];
}

function getStatusPesananBookingLapangan($status){
    $arr = ['Pending','Menunggu Bermain','Konfirmasi Bermain','Dibatalkan','-'];
    return $arr[$status];
}

function getStatusPesananBookingEvent($status){
    $arr = ['Pending','Aktif','Konfirmasi Kehadiran','Dibatalkan','-'];
    return $arr[$status];
}

function getStatusPesananBookingBarang($status){
    $arr = ['Pending',
        'Konfirmasi Pemilik', 
        'Menunggu Pengambilan',
        'Proses Peminjaman',
        'Selesai',
        'Batal',
        'Barang Tidak Tersedia',
        'Verifikasi Pengambilan Customer',
        'Customer Menolak Pengambilan',
        'Menunggu Pengambilan Step 2'
    ];
    return $arr[$status];
}

function getStatusPembayaranBookingLapangan($status){
    $arr = [
        0 => 'Belum Membayar',
        1 => 'Menunggu Pembayaran',
        2 => 'Menunggu Verifikasi',
        3 => 'Lunas',
        4 => 'Batal',
        5 => 'Kadaluarsa',
        100 => 'Pilih Pembayaran'
    ];
    return $arr[$status];
}

function getStatusPembayaranBookingPoduk($status){
    $arr = ['Menunggu Pembayaran','Menunggu Verifikasi','Lunas','Kadaluarsa','Batal'];
    return $arr[$status];
}

function getGlobalStatusAktif($status){
    $arr = ['Tidak Aktif','Aktif'];
    return $arr[$status];
}

function getStatusOnlineInfo($status){
    $arr = ['Offline','Online'];
    return $arr[$status];
}

function getSumberBooking($status){
    $arr = [1=>'Android Admin','Website Admin','Android User','Website User'];
    return isset($arr[$status])?$arr[$status]:'Android User';
}

function getTanggalIndonesia($waktu){
    $tanggal = date('Y-m-d', strtotime($waktu));
    $bulan = array (
		1 =>   'Jan',
		'Feb',
		'Mar',
		'Apr',
		'Mei',
		'Jun',
		'Jul',
		'Ags',
		'Sep',
		'Okt',
		'Nov',
		'Des'
	);
    $pecahkan = explode('-', $tanggal);
    $hari = [
        'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'
    ];
	
	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
    // variabel pecahkan 2 = tahun
    
    $hariString = $hari[date('w', strtotime($waktu))];
    $tanggalString = date('d',strtotime($waktu));
    $bulanString = $bulan[date('n', strtotime($waktu))];
    $tahunString = date('Y', strtotime($waktu));
    $jamString  = date('H:i', strtotime($waktu))." WIB";
	return $hariString.", ".$tanggalString . ' ' . $bulanString . ' ' . $tahunString." ".$jamString;
}

function formatRupiah($harga){
    return "Rp".number_format($harga);
}

function isBetweenTwoDates($firstDate, $lastDate){
    $paymentDate = date('Y-m-d H:i:s');
    $paymentDate=date('Y-m-d H:i:s', strtotime($paymentDate));
    
    $contractDateBegin = date('Y-m-d H:i:s', strtotime($firstDate));
    $contractDateEnd = date('Y-m-d H:i:s', strtotime($lastDate));

    if (($paymentDate > $contractDateBegin) && ($paymentDate < $contractDateEnd))
    {
      return true;
    }
    else
    {
      return false;
    }
}

function isExpiredDate($findDate, $limitDate){
    $expire = date('Y-m-d H:i:s',strtotime($limitDate));
    $today = date('Y-m-d H:i:s', strtotime($findDate));

    if($today >= $expire){
        return true;
    } else {
        return false;
    }
}

function getKuotaTersediaEvent(\App\Venue\Event $event){
    $tersedia = 0;
    foreach($event->paket as $paket){
        $pesertaPaket = \Illuminate\Support\Facades\DB::table('toko_event_peserta_detail')
            ->join('toko_event_peserta','toko_event_peserta.id','=','toko_event_peserta_detail.id_event_peserta')
            ->where('toko_event_peserta.status','=',3)
            ->where('toko_event_peserta_detail.id_event_paket','=',$paket->id)
            ->count();
        $tersedia += $paket->jumlah_peserta - $pesertaPaket;
    }
    return $tersedia;
}

function setConnectionEnv($request){
    $env         = $request->header('App-Env');
    if($env == "local"){
        session([
            'connection_name' => 'mysql',
            'firebase_project' => 'dev'
        ]);
    } else if ($env == "dev_server"){
        session([
            'connection_name' => 'mysql_dev_server',
            'firebase_project' => 'dev'
        ]);
    } else if ($env == "test_server"){
        session([
            'connection_name' => 'mysql_test_server',
            'firebase_project' => 'prod'
        ]);
    } else {
        session([
            'connection_name' => 'mysql_prod_server',
            'firebase_project' => 'prod'
        ]);
    }
}