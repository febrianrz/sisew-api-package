<?php

namespace App\Http\Controllers\Sisew;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AppController extends Controller
{
    function checkUpdate(Request $request){
        // echo $request->service->type;die();
        if($request->service->type==0){
            return $this->checkAdminApp($request);
        } else {
            return $this->checkUserApp($request);
        }
    }

    function checkAdminApp(Request $request){
        
        $app = \App\Sisew\VersionAdminApp::where('status',1)
            ->orderBy('id','desc')
            ->first();
        return new \App\Http\Resources\Sisew\AppCheckUpdateResource($app);
    }

    function checkUserApp(Request $request){
        $app = \App\Sisew\VersionUserApp::where('status',1)
        ->orderBy('id','desc')
        ->first();
        return new \App\Http\Resources\Sisew\AppCheckUpdateResource($app);
    }

    function checkUpdateUser(Request $request){
        return $this->checkUserApp($request);
    }
}
