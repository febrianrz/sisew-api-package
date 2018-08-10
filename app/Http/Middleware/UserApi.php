<?php

namespace App\Http\Middleware;

use Closure;

class UserApi
{
    /**
     * Untuk user yang sudah login
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        setConnectionEnv($request);
        $appApi         = $request->header('App-api');
        $tmpAuthHeader = explode(' ',$request->header('Authorization'));
        
        if($tmpAuthHeader[0]!='Bearer' || count($tmpAuthHeader)!= 2){
            //jika prefixnya buka bearer
            $apiError = \App\ApiServiceError::find(1);
            return response()->json($apiError);
        }
        $userToken  = $tmpAuthHeader[1];
        $rowService = \App\ApiService::where(\Illuminate\Support\Facades\DB::raw('BINARY `token`'),$appApi)
            ->where('status',1)->first();
        if(!$rowService){
            $apiError = \App\ApiServiceError::find(1);
            return response()->json($apiError);
        }
        $userRow = null;
        $toko    = null;
        if($rowService->type==0){
            //cek apakah admin token valid
            $userRow = \App\AdminVenue::where(\Illuminate\Support\Facades\DB::raw('BINARY `api_token`'),$userToken)->first();
            if(!$userRow) {
                return response()->json(\App\ApiServiceError::find(2));
            }
            if(!$userRow->parent_id){
                $owner = $userRow;
            } else {
                // echo $userRow->parent_id;die();
                $owner = \App\AdminVenue::find($userRow->parent_id)->first();
                // echo $owner->nama;
            }
            
            $toko = \App\Toko::where('admin_venue_id',$owner->id)->first();
        } else {
            //cek apakah user token valid
            $userRow = \App\Customer::where(\Illuminate\Support\Facades\DB::raw('BINARY `token`'),$userToken)->first();
            if(!$userRow) {
                return response()->json(\App\ApiServiceError::find(2));
            }

        }

    
        $request->sumberBooking = $rowService->type_device;    
        $request->service = $rowService;
        $request->user    = $userRow;
        $request->toko    = $toko;

        // print_r($request->toko);die();
        \App\AdminVenueFirebaseToken::storeFirebaseToken($request);
        return $next($request);
    }
}