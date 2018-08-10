<?php
namespace Situsewa\Cores\App\Http\Libraries;
//untuk library koneksi ke firebase

class MyFirebaseAdmin {

    public static function getFirebase(){
        $file = "";
        if (session('firebase_project') == "dev") {
            $file = '/firebase/development/situsewa-venue-firebase-adminsdk-nd5j2-685826b576.json';
        } else {
            $file = '/firebase/production/situsewa-venue-firebase-adminsdk-nd5j2-685826b576.json';
        }
        $serviceAccount = \Kreait\Firebase\ServiceAccount::fromJsonFile(storage_path().$file);
        $firebase = (new \Kreait\Firebase\Factory())
            ->withServiceAccount($serviceAccount)->create();

        return $firebase;
    }

    public static function bookingLapangan(\Illuminate\Http\Request $request,\App\Venue\BookingHeader $booking, $title){
        $result = new \App\Http\Resources\Venue\BookingResource($booking);
        // print_r();die();
        $data = [
            'title'         => $title,
            'body'          => $booking->lapangan->nama,
            'data'          => (string)json_encode($result->toArray($request)),
            'jenis'         => "booking_lapangan",
        ];       
        $user = \App\AdminVenue::find($booking->lapangan->toko->owner->id);
        if($user){
            if($user->google_token){
                try{
                    $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($user->google_token)
                        ->withData($data);
                    $messaging = self::getFirebase()->getMessaging();
                    $messaging->send($message);
                } catch (Exception $e) {
                    self::createLog($user, $data, $e->getMessage(),0);
                }
                
            }
        }
        
    }

    public static function sendPemberitahuan(\App\Admin\News $news){
        $title = 'Informasi Situsewa';
        $body = $news->judul;
        $notification = \Kreait\Firebase\Messaging\Notification::create()
            ->withTitle($title)
            ->withBody($body);
        

        $data = [
            'id'    => $news->id,
            'jenis'         => "pemberitahuan",
        ];
        if($news->for == 2){
            //untuk admin venue
            foreach(\App\AdminVenue::where('status',1)->get() as $user){
                if($user->google_token){
                    $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($user->google_token)
                    ->withNotification($notification)
                    ->withData($data);
                    $messaging = self::getFirebase()->getMessaging();
                    $messaging->send($message);
                }
            }
        }
        
        
    }

    public static function updateDashboard(\App\Toko $toko){
        $totalPemesananHariIni  = \App\Venue\BookingHeader::getTotalPemesanan($toko, date('Y-m-d'),date('Y-m-d'));
        $totalPemesananBulanIni = \App\Venue\BookingHeader::getTotalPemesanan($toko, date('Y-m-01'),date('Y-m-t'));
        $totalPemasukkanHariIni = \App\Venue\BookingHeader::getTotalPemasukkan($toko, date('Y-m-d'),date('Y-m-d'));
        $totalPemasukkanBulanIni= \App\Venue\BookingHeader::getTotalPemasukkan($toko, date('Y-m-01'),date('Y-m-t'));

        $firebase = self::getFirebase();
        $database = $firebase->getDatabase();
        $reference = $database->getReference('venue/'.$toko->id.'/dashboard')
            ->set([
                'totalPemesananHariIni'     => $totalPemesananHariIni,
                'totalPemasukkanHariIni'    => $totalPemasukkanHariIni,
                'totalPemesananBulanIni'    => $totalPemesananBulanIni,
                'totalPemasukkanBulanIni'   => $totalPemasukkanBulanIni,
            ]);
    }

    public static function createUserAdmin(\App\AdminVenue $user, $password){
        $status = true;
        
        $userProperties = [
            'uid'   => $user->google_id,
            'email' => $user->email,
            'emailVerified' => false,
            'password' => $password,
            'displayName' => $user->nama_depan,
            'disabled' => false,
        ];
        $auth = self::getFirebase()->getAuth();
        try{
            $createdUser = $auth->createUser($userProperties);
        } catch(Exception $e){
            $status = false;
        }
        return $status;
    }

    public static function updatePassword(\App\AdminVenue $user,$password){
        $auth = self::getFirebase()->getAuth();
        $status = true;
        if($user->google_id){
            try{        
                $updateEmail = $auth->changeUserPassword($user->google_id,$password);
            } catch(Exception $e){
                self::createLog($user, ['update_password'=>'Admin '.$user->nama_depan], $e->getMessage(),0);
                self::createUserAdmin($user,$password);
                $status = false;
            }
        }
        return $status;
    }

    public static function updateEmail(\App\AdminVenue $user){
        $auth = self::getFirebase()->getAuth();
        if($user->google_id){
            $updateEmail = $auth->changeUserEmail($user->google_id,$user->email);
        }
        
    }

    public static function bookingBarang(\Illuminate\Http\Request $request, \App\Produk\Transaksi $booking, $title){
        // $result = new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($booking);
        // // print_r();die();
        // $data = [
        //     'title'         => $title,
        //     'body'          => $booking->produk->nama_barang,
        //     'data'          => (string)json_encode($result->toArray($request)),
        //     'jenis'         => "booking_barang",
        // ];       
        // $user = \App\AdminVenue::find($booking->produk->toko->owner->id);
        // if($user){
        //     if($user->google_token){
        //         try{
        //             $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($user->google_token)
        //                 ->withData($data);
        //             $messaging = self::getFirebase()->getMessaging();
        //             $messaging->send($message);
        //         } catch (Exception $e) {
        //             self::createLog($user, $data, $e->getMessage(),0);
        //         }
                
        //     }
        // }

        self::bookingBarangV2($request, $booking, $title);
                
    }

    public static function bookingBarangV2(\Illuminate\Http\Request $request, \App\Produk\Transaksi $booking, $title){
        $result = new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($booking);
        $data = [
            'title'         => $title,
            'body'          => $booking->produk->nama_barang,
            'data'          => $booking->id,
            'jenis'         => "booking_barang",
        ];       
        
        \MyFirebaseUser::pushBookingBarang($request,$booking);
        $user = \App\AdminVenue::find($booking->produk->toko->owner->id);
        foreach(\App\AdminVenueFirebaseToken::getAdminTokenByStoreId($booking->produk->toko->id) as $adminToko){
            try{
                // echo $adminToko->firebase_token;
                $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($adminToko->firebase_token)
                    ->withData($data);
                $messaging = self::getFirebase()->getMessaging();
                $messaging->send($message);
            } catch (Exception $e) {
                // print_r($e->getMessage());die();
                self::createLog($user, $data, $e->getMessage(),0);
            }
        }        
    }

    private static function createLog(\App\AdminVenue $customer, $data, $message, $status=0){
        $log = new \App\FirebaseNotifAdminLog;
        $log->id_admin = $customer->id;
        $log->data = json_encode($data);
        $log->message = $message;
        $log->status = $status;
        $log->save();
    }
}