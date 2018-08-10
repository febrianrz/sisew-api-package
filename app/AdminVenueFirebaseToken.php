<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;


class AdminVenueFirebaseToken extends Model
{
    use \App\SisewModel;
    protected $table = "admin_venue_firebase_token";
    public $increments = false;

    public static function storeFirebaseToken(Request $request){
        // cek dulu apakah firebase token ini sudah ada atau belum
        $firebase_token = $request->header('Firebase-token');
        $current = AdminVenueFirebaseToken::where('firebase_token',$firebase_token)->first();
        
        if(!$current){
            $Authorization = explode(' ',$request->header('Authorization'));
            $userAdmin = \App\AdminVenue::where(\Illuminate\Support\Facades\DB::raw('BINARY `api_token`'),$Authorization[1])->first();
            
            $ownerMerchant = null;
            if(!$userAdmin->parent_id){
                $ownerMerchant = $userAdmin;
            } else {
                $ownerMerchant = \App\AdminVenue::find($userAdmin->parent_id)->first();
            }

            $merchant = \App\Toko::where('admin_venue_id',$ownerMerchant->id)->first();

            //jika tidak ada, maka buat row baru
            $rowFirebase = new AdminVenueFirebaseToken;
            $rowFirebase->id = \Webpatser\Uuid\Uuid::generate()->string;
            $rowFirebase->firebase_token = $firebase_token;
            $rowFirebase->firebase_id = $request->header('Firebase-id');
            $rowFirebase->firebase_guid = $request->header('Firebase-uid');
            $rowFirebase->creation_time = $request->header('Firebase-creation-time');
            $rowFirebase->admin_user_token = $Authorization[1];
            $rowFirebase->id_admin_venue = $userAdmin?$userAdmin->id:null;
            $rowFirebase->email_admin_venue = $userAdmin?$userAdmin->email:null;
            $rowFirebase->id_toko = $merchant->id;
            $rowFirebase->status = 1;
            $rowFirebase->save();
        } else {
            $current->updated_at = date('Y-m-d H:i:s');
            $current->save();
        }
    }

    public static function getAdminTokenByStoreId($id_toko){
        return AdminVenueFirebaseToken::where('id_toko','=',$id_toko)
                ->whereNotNull('firebase_token')
                ->get();
    }
}
