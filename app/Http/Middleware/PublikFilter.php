<?php

namespace App\Http\Middleware;

use Closure;

class PublikFilter
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
        $appApi     = $request->header('App-api');
        $apiAuth    = $request->header('Authorization');
        $row        = \App\ApiService::where(\Illuminate\Support\Facades\DB::raw('BINARY `token`'),$appApi)
            ->where(\Illuminate\Support\Facades\DB::raw('BINARY `public_api`'),$apiAuth)
            ->where('status',1)->first();
        if(!$row){
            $apiError = \App\ApiServiceError::find(1);
            return response()->json($apiError);
        }
        $request->service = $row;
        
        return $next($request);
    }
}