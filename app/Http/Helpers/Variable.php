<?php

define('WAKTU_EXPIRED_BOOKING_LAPANGAN',10); //dalam minute

define('BOOKING_PEMBAYARAN_DP',1);
define('BOOKING_PEMBAYARAN_LUNAS',2);

define('BOOKING_STATUS_PEMBAYARAN_BELUM_LUNAS',1);
define('BOOKING_STATUS_PEMBAYARAN_MENUNGGU_VERIFIKASI',2);
define('BOOKING_STATUS_PEMBAYARAN_LUNAS',3);

define('BOOKING_STATUS_PESANAN_MENUNGGU_PEMBAYARAN',0);
define('BOOKING_STATUS_PESANAN_MENUNGGU_JADWAL_BERMAIN',1);
define('BOOKING_STATUS_PESANAN_KONFIRMASI_BERMAIN',2);
define('BOOKING_STATUS_PESANAN_SELESAI',3);
define('BOOKING_STATUS_PESANAN_BATAL',4);

define('SUMBER_BOOKING_ANDROID_ADMIN',1);
define('SUMBER_BOOKING_WEBSITE_ADMIN',2);
define('SUMBER_BOOKING_ANDROID_USER',3);
define('SUMBER_BOOKING_WEBSITE_USER',4);

define('STATUS_KONFIRMASI_BELUM_BERMAIN',0);
define('STATUS_KONFIRMASI_BERMAIN',1);

define('PENYEWAAN_STATUS_MENUNGGU_PEMBAYARAN',0);
define('PENYEWAAN_STATUS_MENUNGGU_VERIFIKASI',1);
define('PENYEWAAN_STATUS_LUNAS',2);
define('PENYEWAAN_STATUS_KADALUARSA',3);
define('PENYEWAAN_STATUS_BATAL',4);

define('FCM_JENIS_BOOKING',1);

define('MERCHANT_JENIS_VENUE',1);
define('MERCHANT_JENIS_PENYEWAAN',2);

define('MERCHANT_STATUS_ONLINE',1);
define('MERCHANT_STATUS_OFFLINE',0);

define('STATUS_PEMBAYARAN_ATUR_METODE_PEMBAYARAN',0);
define('STATUS_PEMBAYARAN_MENUNGGU_PEMBAYARAN',1);
define('STATUS_PEMBAYARAN_MENUNGGU_KONFIRMASI',2);
define('STATUS_PEMBAYARAN_LUNAS',3);
define('STATUS_PEMBAYARAN_BATAL',4);
define('STATUS_PEMBAYARAN_KADALUARSA',5);

define('STATUS_PESANAN_BATAL',4);

define('JENIS_TRANSAKSI_BOOKING_LAPANGAN',1);
define('JENIS_TRANSAKSI_BOOKING_EVENT',2);
define('JENIS_TRANSAKSI_BOOKING_BARANG',3);

define('JENIS_TRANSAKSI_TRANSFER_SISEW_KE_VENUE',1);
define('JENIS_TRANSAKSI_PEMBAYARAN_BOOKING_ONLINE',2);
define('JENIS_TRANSAKSI_PEMBATALAN_BOOKING_ONLINE',3);
