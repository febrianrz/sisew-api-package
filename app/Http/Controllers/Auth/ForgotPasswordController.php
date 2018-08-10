<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function sendResetLinkResponse($response)
    {
        return ['status' => trans($response)];
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        throw new UnprocessableEntityHttpException(trans($response));
    }

    public function forgot(Request $request){
        if($request->service->type==0){
            return $this->doForgotAdmin($request);
        } else {
            return $this->doForgotUser($request);
        }

        
        
    }

    public function doForgotAdmin(Request $request){
        
        $request->validate([
            'email' => 'required'
        ]);
        $user = \App\AdminVenue::where('email',$request->email)->first();
        if(!$user) {
            return response()->json([
                'status'    => false,
                'msg'       => 'Maaf, email tersebut belum terdaftar'
            ]);
        }
        $reset = \App\UserResetPassword::where('id_user',$user->id)
                    ->where('kadaluarsa','>=',date('Y-m-d H:i:s'))
                    ->where('status','=','0')
                    ->first();
                    
        if(!$reset){
            $reset = new \App\UserResetPassword;
            $reset->id = \Webpatser\Uuid\Uuid::generate()->string;
            $reset->id_user = $user->id;
            $reset->token = str_random(100);
            $reset->status = 0;
            $reset->kode = strtoupper(str_random(6));
            $reset->kadaluarsa = date('Y-m-d H:i:s',strtotime('+30 minutes'));
            $reset->save();
        }
        try{
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\ForgotPasswordAdmin($user, $reset));
        } catch(\Exception $e){
            \DB::table('email_logs')->insert([
                'email' => $user->email,
                'title' => 'Failed send forgot password admin',
                'message'=> $e->getMessage()
            ]);
        }
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Periksa email Anda untuk melanjutkan perubahan katasandi'
        ]);
    }

    public function doForgotUser(Request $request){
        
        $request->validate([
            'email' => 'required|email'
        ]);
        
        $user = \App\Customer::where('email',$request->email)->first();
        $reset = \App\CustomerResetPassword::where('id_user',$user->id)
                    ->where('kadaluarsa','>=',date('Y-m-d H:i:s'))
                    ->where('status','=','0')
                    ->first();

                    
        if(!$reset){
            $reset = new \App\CustomerResetPassword;
            $reset->id = \Webpatser\Uuid\Uuid::generate()->string;
            $reset->id_user = $user->id;
            $reset->token = str_random(100);
            $reset->status = 0;
            $reset->kode = strtoupper(str_random(6));
            $reset->kadaluarsa = date('Y-m-d H:i:s',strtotime('+30 minutes'));
            $reset->save();
        }
        try{
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\CustomerForgotPassword($user, $reset));
        } catch(\Exception $e){
            \DB::table('email_logs')->insert([
                'email' => $user->email,
                'title' => 'Failed send forgot password user',
                'message'=> $e->getMessage()
            ]);
        }
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Periksa email Anda untuk melanjutkan perubahan katasandi'
        ]);
    }

    public function reset(Request $request){
        if($request->service->type==0){
            return $this->doResetAdmin($request);
        } else {
            return $this->doResetCustomer($request);
        }

    }

    private function doResetAdmin(Request $request){
        $request->validate([
            'email'         => 'required|email',
            'new_password'  =>'required|min:6',
            'kode'          =>'required'
        ]);
        $user = \App\AdminVenue::where('email',$request->email)->first();
        if(!$user){
            return response()->json([
                'status'    => false,
                'msg'       => 'Email tidak terdaftar'
            ]);
        }
        $reset = \App\UserResetPassword::where('id_user',$user->id)
            ->where('kadaluarsa','>=',date('Y-m-d H:i:s'))
            ->where('status',0)
            ->where('kode',$request->kode)
            ->first();
        if(!$reset){
            return response()->json([
                'status'    => false,
                'msg'       => 'Request tidak valid atau kadaluarsa'
            ]);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();
        $reset->akses_at = date('Y-m-d H:i:s');
        $reset->status = 1;
        $reset->save();
        \MyFirebaseAdmin::updatePassword($user,$request->new_password);
        try{
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\SuccessResetPasswordAdmin($user));
        } catch(\Exception $e){
            \DB::table('email_logs')->insert([
                'email' => $user->email,
                'title' => 'Failed send success password admin',
                'message'=> $e->getMessage()
            ]);
        }
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Password berhasil diubah'
        ]);
    }

    private function doResetCustomer(Request $request){
        $request->validate([
            'email'         => 'required|email|exists:customers,email',
            'new_password'  =>'required|min:6',
            'kode'          =>'required|exists:customer_forgot_password,kode'
        ]);
        $user = \App\Customer::where('email',$request->email)->first();
        $reset = \App\CustomerResetPassword::where('id_user',$user->id)
            ->where('kadaluarsa','>=',date('Y-m-d H:i:s'))
            ->where('status',0)
            ->where('kode',$request->kode)
            ->first();
        if(!$reset){
            return response()->json([
                'status'    => false,
                'msg'       => 'Request tidak valid atau kadaluarsa'
            ]);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();
        $reset->akses_at = date('Y-m-d H:i:s');
        $reset->status = 1;
        $reset->save();
        // echo $request->new_password;die();
        \MyFirebaseUser::updatePassword($user,$request->new_password);
        try{
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\SuccessResetPasswordCustomer($user));
        } catch(\Exception $e){
            \DB::table('email_logs')->insert([
                'email' => $user->email,
                'title' => 'Failed send success password customer',
                'message'=> $e->getMessage()
            ]);
        }
        
        return response()->json([
            'status'    => true,
            'msg'       => 'Password berhasil diubah'
        ]);
    }
}
