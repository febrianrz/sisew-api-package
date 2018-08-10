<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminVenue extends Authenticatable
{
    use Notifiable;
    use \App\SisewModel;
    // $sess_connection = session('connection_name');
    
    protected $table = "admin_venue";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public $incrementing = false;

    

    //buat akun firebase
    public static function createUserFirebase($email, $password, $nama=null, $telepon=null, $photoURL="",$disabled=false){
        $serviceAccount = \Kreait\Firebase\ServiceAccount::fromJsonFile(storage_path().'/firebase/situsewa-venue-firebase-adminsdk-nd5j2-685826b576.json');
        $firebase = (new \Kreait\Firebase\Factory)
        ->withServiceAccount($serviceAccount)
        ->create();
        $auth = $firebase->getAuth();
        $userProperties = [
            'email' => $email,
            'emailVerified' => true,
            'password' => $password,
            'photoUrl' => $photoURL,
            'disabled' => $disabled,
        ];
        $createdUser = $auth->createUser($userProperties);
    }

    public static function getUserFromToko(Toko $toko){
        
        $id_owner = $toko->users_id;
        $listUser = [$id_owner];
        //ambil semua user yang parent idnya == id owner
        $users = AdminVenue::where('parent_id',$id_owner)->where('status',1)
            ->get();
        foreach($users as $user){
            array_push($listUser,$user->id);
        }
        return $listUser;
    }

    public function role(){
        return $this->belongsTo('\App\UserRole','user_role_id');
    }

    public function getTokoUser(AdminVenue $user){
        $owner_id = $user->parent_id?$user->parent_id:$user->id;

        return Toko::where('admin_venue_id','=',$owner_id)->first();
        
    }
}
