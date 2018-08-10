<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request)
    {
        $user = User::where('email', '=', request()->input('email'))->first();

        if (is_null($user)) {
            return false;
        }

        if (!Hash::check(request()->input('password'), $user->password)) {
            return false;
        }

        if ($user->isDisabled()) {
            throw new AuthenticationException(__(
                'Your account has been disabled. Please contact the administrator'
            ));
        }

        auth()->login($user, request()->input('remember'));

        return true;
    }

    protected function authenticated(Request $request, $user)
    {
        return response()->json([
            'auth' => auth()->check(),
            'csrfToken' => csrf_token(),
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
    }

    public function doLogin(Request $request){
        $validatedData = $request->validate([
            'email'     => 'required',
            'password'  => 'required',
        ]);
        if($request->service->type==0){
            return $this->doLoginAdmin($request);
        } else {
            return $this->doLoginUser($request);
        }
    }

    public function doLoginAdmin(Request $request)
    {
        $user = \App\AdminVenue::where('email',$request->email)->first();
        if(!$user){
            return response()->json(['data'=>null]);    
        }
        if(!$user->api_token){
            $user->api_token = str_random(100);
            $user->save();
        }
        // \App\User::createUserFirebase($request->email,$request->password,$user->nama,$user->telepon);
        // $this->doFirebaseLoginAdmin($request,$user);
        // $user = \App\User::find($user->id);
        return new \App\Http\Resources\UserResource($user);
    }

    public function doLoginUser(Request $request)
    {
        
        if (!\Illuminate\Support\Facades\Auth::guard('customer')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['status'=>false,'message'=>'Email dan Password tidak sesuai','data'=>[]]);
        } else {
            $user = \App\Customer::where('email',$request->email)->first();
            if(!$user->token){
                $user->token = str_random(100);
                $user->save();
            }
            return new \App\Http\Resources\CustomerResource($user);
        }
    }

    public function doLoginGoogle(Request $request)
    {
        
        $user = \App\Customer::where('email',$request->email)
            ->first();
        if($user){
            if(!$user->token){
                $user->token = str_random(100);
                $user->save();
            }
            return new \App\Http\Resources\CustomerResource($user);
        } else {
            /**
             * Jika tidak ada, maka buat user baru
             */
            $customer       = new \App\Customer;
            $customer->id   = \Webpatser\Uuid\Uuid::generate()->string;
            $customer->email= $request->email;
            $customer->status= 1;
            $customer->register_from = 0;
            $customer->token = str_random(100);
            $customer->token_firebase = $request->header('Firebase-token');;
            $password           = $request->has('password')?$request->password:str_random(6);
            $customer->nama     = $request->nama?$request->nama:$request->email;
            $customer->password = bcrypt(str_random(6));
            $customer->firebase_auth_created = 1;
            $customer->guid_firebase = $request->header('Firebase-uid');
            $customer->save();
            try{
                \Illuminate\Support\Facades\Mail::to($request->email)
                    ->send(new \App\Mail\RegisterUser($customer));
            } catch(\Exception $e){
                \DB::table('email_logs')->insert([
                    'email' => $request->email,
                    'title' => 'Failed send register customer',
                    'message'=> $e->getMessage()
                ]);
            }
            return new \App\Http\Resources\CustomerResource($customer);
        }
    }
}
