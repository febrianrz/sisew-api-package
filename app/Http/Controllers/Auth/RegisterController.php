<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function doRegister(Request $request){
        if($request->service->type==0){
            $request->validate([
                'user_nama'             => 'required|max:30',
                'user_email'            => 'required',
                'user_jenis_kelamin'    => 'required',
                'user_telepon'          => 'required|numeric',
                'user_password'         => 'required',
                'user_alamat'           => 'required',
                'toko_nama'             => 'required',
                'toko_alamat'           => 'required',
                'toko_telepon'          => 'required|numeric',
                'toko_logo'             => 'required|image',
                'toko_deskripsi'        => 'required',
                'toko_jenis'            => 'required'
            ]);
            return $this->doRegisterAdmin($request);
        } else {
            if(!$request->type){
                return response()->json([
                    'status'    => false,
                    'msg'       => 'Tipe registrasi tidak valid'
                ]);
            }
            if($request->type == 1){
                //1- registrasi via form registrasi
                //2- registrasi via google akun
                $request->validate([
                    'email' => 'required|email|unique:customers,email',
                    'password'  => 'required|min:6',
                    'telepon'   => 'required',
                    'nama'      => 'required'
                ]);    
            } else {
                $request->validate([
                    'email' => 'required|email|unique:customers,email',
                    'guid'  => 'required'
                ]);    
            }
            
            return $this->doRegisterUser($request);
        }
    }


    /**
     * 1. Cek dulu di internal database ada atau tidak adminnya
     * 2. Jika ada, kembalikan pesan user telah terdaftar
     * 3. Jika tidak ada, daftarkan firebase terlebih dahulu
     * 4. Jika firebase berhasil, maka simpan ke database
     * 5. Jika tidak berhasil, kembalikan pesan mencoba kembali
     */

    private function doRegisterAdmin(Request $request){
        $isExists = \App\AdminVenue::where('email','=',$request->user_email)->first();
        if($isExists)
            return response()->json(['status'=>false, 'message'=>'Admin dengan email tersebut telah terdaftar']);
        
        //data user
        $user               = new \App\AdminVenue;
        $user->id           = \Webpatser\Uuid\Uuid::generate()->string;
        $user->user_role_id = 2; //owner
        $user->nama_depan   = $request->user_nama;
        $user->email        = $request->user_email;
        $user->jenis_kelamin = $request->user_jenis_kelamin;
        $user->telepon      = $request->user_telepon;
        $user->password     = bcrypt($request->user_password);
        $user->alamat       = $request->user_alamat;
        $user->sumber_daftar= $request->service->type_device;
        $user->status       = 1;
		$user->type_app     = $request->toko_jenis;
        $user->google_id    = \Webpatser\Uuid\Uuid::generate()->string;
        $user->api_token    = str_random(200);
        $user->firebase_auth_created = 1;
        

        //data toko
        $toko           = new \App\Toko;
        $toko->id       = \Webpatser\Uuid\Uuid::generate()->string;
        $toko->nama     = $request->toko_nama;
        $toko->alamat   = $request->toko_alamat;
        $toko->telepon  = $request->toko_telepon;
        $toko->logo     = $request->toko_logo->store('public/venue/'.$toko->id);
        $toko->admin_venue_id = $user->id;
        $toko->keterangan = $request->toko_deskripsi;
        $toko->id_kecamatan = 1;
        $toko->kotas_id = 1;
        $toko->provinsis_id = 1;
        $toko->jenis_merchant =$request->toko_jenis;
        
        if(!\MyFirebaseAdmin::createUserAdmin($user,$request->user_password)){
            return response()->json(['status'=>false, 'message'=>'Mohon mencoba menggunakan email lain.']);
        }
        $user->save();
        $toko->save();    
        //send email
        try{
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\RegisterAdmin($user));
        } catch(\Exception $e){
            \DB::table('email_logs')->insert([
                'email' => $user->email,
                'title' => 'Failed send register admin',
                'message'=> $e->getMessage()
            ]);
        }
        
        $login = new LoginController;
        $request->email = $request->user_email;
        $request->password = $request->user_password;
        return $login->doLoginAdmin($request);
    }

    public function doRegisterUser(Request $request){
        $customer       = new \App\Customer;
        $customer->id   = \Webpatser\Uuid\Uuid::generate()->string;
        $customer->email= $request->email;
        $customer->status= 1;
        $customer->register_from = 0;
        $customer->token = str_random(100);
        $customer->token_firebase = $request->header('Firebase-token');;
        $password       = $request->has('password')?$request->password:str_random(6);
        if($request->type == 1){
            //register user
            $customer->nama     = $request->nama?$request->nama:$request->email;
            $customer->telepon  = $request->telepon;
            $customer->password = bcrypt($password);
            $customer->firebase_auth_created = 1;
            $customer->guid_firebase = \Webpatser\Uuid\Uuid::generate()->string;
            $customer->save();
            \MyFirebaseUser::createUser($customer,$password);
        } else {
            $customer->nama     = $request->nama?$request->nama:$request->email;
            $customer->password = bcrypt(str_random(6));
            $customer->firebase_auth_created = 1;
            $customer->guid_firebase = $request->header('Firebase-id');;
            $customer->save();
        }
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
