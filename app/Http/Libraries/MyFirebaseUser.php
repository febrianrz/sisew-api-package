<?php

//untuk library koneksi ke firebase

class MyFirebaseUser {

    public static function getFirebase(){
        $file = "";
        if (session('firebase_project') == "dev") {
            $file = '/firebase/development/situsewa-user-firebase-adminsdk-rcbkb-da170dd5c1.json';
        } else {
            $file = '/firebase/production/situsewa-user-firebase-adminsdk-rcbkb-da170dd5c1.json';
        }
        $serviceAccount = \Kreait\Firebase\ServiceAccount::fromJsonFile(storage_path().$file);
        $firebase = (new \Kreait\Firebase\Factory())
            ->withServiceAccount($serviceAccount)->create();

        return $firebase;
    }

    public static function createUser(\App\Customer $user, $password){
        $userProperties = [
            'uid'   => $user->guid_firebase,
            'email' => $user->email,
            'emailVerified' => false,
            'password' => $password,
            'displayName' => $user->nama,
            'disabled' => false,
        ];
        $auth = self::getFirebase()->getAuth();
        try{
            $createdUser = $auth->createUser($userProperties);
        } catch(Exception $e){

        }
        
    }


    public static function updatePassword(\App\Customer $user,$password){
        $auth = self::getFirebase()->getAuth();
        $status = true;
        if($user->guid_firebase){
            try{
                $updateEmail = $auth->changeUserPassword($user->guid_firebase,$password);
            } catch(Exception $e){
                //coba buat user baru
                self::createUser($user,$password);
                $status = false;
            }
        }
        return $status;
    }

    public static function bookingLapangan(\Illuminate\Http\Request $request,\App\Venue\BookingHeader $booking, \App\Customer $customer,$title){
        $result = new \App\Http\Resources\Venue\BookingResource($booking);
        
        $data = [
            'title'         => $title,
            'body'          => $booking->lapangan->nama,
            'data'          => (string)json_encode($result->toArray($request)),
            'jenis'         => "booking_lapangan",
        ];       
        

        if($customer->token_firebase){
            try{
                $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($customer->token_firebase)
                ->withData($data);
                $messaging = self::getFirebase()->getMessaging();
                $messaging->send($message);
            } catch (Exception $e) {
                self::createLog($customer, $data, $e->getMessage(),0);
            }
        }
    }

    public static function bookingBarang(\Illuminate\Http\Request $request,\App\Produk\Transaksi $booking, \App\Customer $customer,$title){
        
        // 'data'          => (string)json_encode($result->toArray($request)),
        // $result = new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($booking);
        // $data = [
        //     'title'         => $title,
        //     'body'          => $booking->produk->nama_barang,
        //     'data'          => $booking->id,            
        //     'jenis'         => "booking_barang",
        // ];      
        

        // if($customer->token_firebase){
        //     try{
        //         $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($booking->firebase_token)
        //         ->withData($data);
        //         $messaging = self::getFirebase()->getMessaging();
        //         $messaging->send($message);
        //     } catch (Exception $e) {
        //         self::createLog($customer, $data, $e->getMessage(),0,$booking->firebase_token);
        //     }
        // }

        self::bookingBarangV2($request, $booking, $title);
    }

    public static function bookingBarangV2(\Illuminate\Http\Request $request,\App\Produk\Transaksi $booking, $title){
        
        $result = new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($booking);
        $data = [
            'title'         => $title,
            'body'          => $booking->produk->nama_barang,
            'data'          => $booking->id,
            'jenis'         => "booking_barang",
        ];      
        self::pushBookingBarang($request, $booking);

        if($booking->firebase_token){
            try{
                $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($booking->firebase_token)
                ->withData($data);
                $messaging = self::getFirebase()->getMessaging();
                $messaging->send($message);
            } catch (Exception $e) {
                // print_r($e->getMessage());
                //self::createLog($customer, $data, $e->getMessage(),0);
                $log = new \App\FirebaseNotifCustomerLog;
                $log->id_customer = null;
                $log->data = json_encode($data);
                $log->message = $e->getMessage();
                $log->status = 0;
                $log->firebase_token = $booking->firebase_token;
                $log->save();
            }
        }
    }

    public static function bookingEvent(\Illuminate\Http\Request $request,\App\Venue\EventPeserta $booking, $title){
        
        $result = new \App\Http\Resources\Venue\EventPesertaResource($booking);
        $data = [
            'title'         => $title,
            'body'          => $booking->event->nama_event,
            'data'          => $booking->id,
            'jenis'         => "booking_event",
        ];      
        self::pushBookingEvent($request, $booking);

        if($booking->firebase_token){
            try{
                $message = \Kreait\Firebase\Messaging\MessageToRegistrationToken::create($booking->firebase_token)
                ->withData($data);
                $messaging = self::getFirebase()->getMessaging();
                $messaging->send($message);
            } catch (Exception $e) {
                // self::createLog($customer, $data, $e->getMessage(),0);
                $log = new \App\FirebaseNotifCustomerLog;
                $log->id_customer = null;
                $log->data = json_encode($data);
                $log->message = $e->getMessage();
                $log->status = 0;
                $log->firebase_token = $booking->firebase_token;
                $log->save();
            }
        }
    }

    
    private static function createLog(\App\Customer $customer, $data, $message, $status=0, $firebase_token=""){
        $log = new \App\FirebaseNotifCustomerLog;
        $log->id_customer = $customer->id;
        $log->data = json_encode($data);
        $log->message = $message;
        $log->status = $status;
        $log->firebase_token = $firebase_token;
        $log->save();
    }

    public static function pushBookingBarang(\Illuminate\Http\Request $request, \App\Produk\Transaksi $booking){
        $result = new \App\Http\Resources\Admin\Merchant\ProdukSewaResource($booking);
        $firebase = self::getFirebase();
        $database = $firebase->getDatabase();
        try {
            $database->getReference('riwayat/barang/'.$booking->id)
                ->set((object)$result->toArray($request));
        } catch(Exception $e){
            $log = new \App\FirebaseNotifCustomerLog;
            $log->id_customer = 'push database '.$booking->id;
            $log->data = (string)json_encode($result->toArray($request));
            $log->message = 'push booking to database error';
            $log->status = 0;
            $log->firebase_token = $booking->firebase_token;
            $log->save();
        }
    }

    public static function pushBookingEvent(\Illuminate\Http\Request $request, \App\Venue\EventPeserta $booking){
        $result = new \App\Http\Resources\Venue\EventPesertaResource($booking);
        $firebase = self::getFirebase();
        $database = $firebase->getDatabase();
        try {
            $database->getReference('riwayat/event/'.$booking->id)
                ->set((object)$result->toArray($request));
        } catch(Exception $e){
            $log = new \App\FirebaseNotifCustomerLog;
            $log->id_customer = 'push database '.$booking->id;
            $log->data = (string)json_encode($result->toArray($request));
            $log->message = 'push booking to database error';
            $log->status = 0;
            $log->firebase_token = $booking->firebase_token;
            $log->save();
        }
    }
}