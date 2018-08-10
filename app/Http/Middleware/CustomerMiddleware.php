<?php

namespace App\Http\Middleware;

use Closure;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        setConnectionEnv($request);
        //cek app-api dan authorization api exist
        $appApi         = $request->header('App-api');
        $tmpAuthHeader = explode(' ',$request->header('Authorization'));
        
        if($tmpAuthHeader[0]!='Bearer' || count($tmpAuthHeader)!= 2){
            //jika prefixnya buka bearer
            $apiError = \App\ApiServiceError::find(1);
            return response()->json(['status'=>false,'msg'=>'API Token Tidak Valid']);
        }
        $userToken  = $tmpAuthHeader[1];
        $rowService = \App\Sisew\UserAPIToken::where(\Illuminate\Support\Facades\DB::raw('BINARY `app_key`'),$appApi)
            ->where(\Illuminate\Support\Facades\DB::raw('BINARY `public_key`'),$userToken)
            ->whereDate('expired_at','>=',date('Y-m-d H:i:s'))
            ->first();
        if(!$rowService){
            //jika tidak ada di user login api public, maka cek dari api_service
            $apiAuth    = $request->header('Authorization');
            $rowService = \App\ApiService::where(\Illuminate\Support\Facades\DB::raw('BINARY `token`'),$appApi)
                ->where(\Illuminate\Support\Facades\DB::raw('BINARY `public_api`'),$apiAuth)
                ->where('status',1)->first();
            
            // if(!$rowService){
            //     $apiError = \App\ApiServiceError::find(1);
            //     return response()->json(['status'=>false,'msg'=>'row service gagal','app-key'=>$appApi, 'publik-key'=>$apiAuth]);
            // }

            //untuk sementara
            $rowService = \App\ApiService::find(4);
        } else {
            //update firebase id, firebase_creation
            $rowService->firebase_token         = $request->header('Firebase-token');
            $rowService->firebase_id            = $request->header('Firebase-id');
            $rowService->firebase_creation_time = $request->header('Firebase-creation-time');
            $rowService->save();
        }

        $toko    = null;
        //cek apakah user token valid
        if(isset($rowService->id_customer)){
            $userRow = \App\Customer::find($rowService->id_customer);
        } else {
            $userRow = null;
        }
        
        
        $request->sumberBooking = isset($rowService->type_device)?$rowService->type_device:4;    
        $request->service = $rowService;
        $request->api     = $rowService;
        $token_user = $request->header('User-Token');
        $request->user    = \App\Customer::where('token',$token_user)->first();
        $request->toko    = $toko;

        // print_r($request->toko);die();
        $this->setCustomerFirebase($request);
        return $next($request);
    }

    /**
     * 1. Cek dulu apakah token firebase ada atau tidak
     */
    private function setCustomerFirebase(\Illuminate\Http\Request $request){
        $firebase_token = $request->header('Firebase-token');
        $firebase_guid = $request->header('Firebase-uid');
        $csFirebase = \App\CustomerFirebaseToken::where('firebase_token','=',$firebase_token)->first();
        if(!$csFirebase){
            $newCsFirebase = new \App\CustomerFirebaseToken;
            $newCsFirebase->id = \Webpatser\Uuid\Uuid::generate()->string;
            $newCsFirebase->firebase_token = $firebase_token;
            $newCsFirebase->firebase_guid = $firebase_guid;
            $newCsFirebase->last_access_at = date('Y-m-d H:i:s');
            $newCsFirebase->save();
            $csFirebase = \App\CustomerFirebaseToken::where('firebase_token','=',$firebase_token)->first();
        } 
        
        // cek token user 
        $token_user = $request->header('User-Token');
        $user = \App\Customer::where('token',$token_user)->first();
        if($user){
            $csFirebase->id_customer = $user->id;
            $csFirebase->email = $user->email;
            $user->guid_firebase = $firebase_guid;
            $user->save();
        }
        // $csFirebase->last_access_at = date('Y-m-d H:i:s');
        $csFirebase->save();
    }
}
