<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use Notifiable;
    use \App\SisewModel;
    
    protected $table = "customers";
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'created_at', 'updated_at'
    ];


    public static function updateSaldo(Customer $customer, $amount){
        $saldo = CustomerSaldo::where('id_customer',$customer->id)->first();
        //jika belum ada, buat baru
        if(!$saldo){
            $saldo = new CustomerSaldo;
            $saldo->id = \Webpatser\Uuid\Uuid::generate()->string;
            $saldo->id_customer = $customer->id;
            $saldo->amount = $amount;
            $saldo->save();
        } else {
            $saldo->amount += $amount;
            $saldo->save();
        }
    }

    public static function pembatalanBookingBarang(Customer $customer, \App\Produk\Transaksi $transaksi){
        //tambahkan saldonya
        self::updateSaldo($customer,$transaksi->total_harga);
        //masukkan rinciannya
        $rincian = new CustomerSaldoRincian;
        $rincian->id_customer = $customer->id;
        $rincian->id_booking = $transaksi->id;
        $rincian->id_master_jenis_transaksi = 4;
        $rincian->amount = $transaksi->total_harga;
        $rincian->keterangan = "Merchant menyatakan barang tidak tersedia untuk saat ini";
        $rincian->jenis = 3;
        $rincian->kode_booking = $transaksi->kode_sewa;
        $rincian->save();
    }

    public static function getUserTokenAndId(\Illuminate\Http\Request $request) {
        $firebase_token         = $request->header('Firebase-token');
        $user_token             = $request->header('User-Token');
        $arr['firebase_token']  = $firebase_token;
        $customer               = Customer::where('token','=',$user_token)->first();
        if($customer) $arr['customer_id']  = $customer->id;
        else $arr['customer_id']           = null;
        // print_r((object)$arr);die();
        return (object)$arr;

    }
}

