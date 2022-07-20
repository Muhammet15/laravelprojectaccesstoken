<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;
class AuthController extends Controller
{
  public $successStatus = 200;

  /**
  * Kullanıcı Oluşturma
  *
  * @param [string] name
  * @param [string] email
  * @param [string] password
  * @return [string] message
  */
public function register(Request $request)
{


  $user = new User([
  'name' => $request->name,
  'email' => $request->email,
  'password' => bcrypt($request->password),
  ]);


//bcrypt
  $user->save();
    $message['success'] = 'Kullanıcı Başarıyla Oluşturuldu.';
    return response()->json(['message' => $message], 201);

}
/**
* Kullanıcı Girişi ve token oluşturma
*
* @param [string] email
* @param [string] password
* @return [string] token
* @return [string] token_type
* @return [string] expires_at
* @return [string] success
*/
public function login(Request $request){
      $email = $request->input('email');
      $password = $request->input('password');
      if(Auth::attempt(['email'=> $request->email , 'password' => $request -> password])){
          $user = Auth::user();
          $message['id'] =$user->id;
          $message['mail'] =$user->email;
          $message['token'] = $user->createToken('MyApp')->accessToken;
          $message['token_type'] = 'Bearer';
          $message['experies_at'] = Carbon::parse(Carbon::now()->addWeeks(1))->toDateTimeString();
          $message['success'] = 'Kullanıcı Girişi Başarılı';
          $token=User::findOrFail($user->id);
          $token ->api_token="Bearer ".$message['token'];
          $token->save();
          return response()->json(['message' => $message], $this->successStatus);
        }
        else {
          $message['Error'] = 'Kullanıcı Girişi Başarısız';
        return response()->json(['message' => $message], $this->successStatus);
        }
  }

  public function getUserList(Request $request){
    $header = $request->header('Authorization');
    if (empty($header)) {
      $message = "header Authorization kayıp" ;
      return response()->json(['status'=>false,'message'=>$message],422);
    }else {
      //    $header == "Bearer ".$api_token  $about = User::where($header, $userr->api_token)->get();
      $us = DB::table('users')->where('api_token', $header
      )->first();
      var_dump($us->api_token);
      var_dump($header);
      if ($header == $us->api_token)
      {
        $users = User::get()->all();
          return response()->json(['users'=>$users,200]);
      }
      else {
        $message = "header Authorization doğru değil" ;
        return response()->json(['message' => $message], $this->successStatus);
        }
      }
    }

    public function logoutUser(Request $request){
      $header = $request->header('Authorization');
      if (empty($header)) {
        $message = "header Authorization kayıp" ;
        return response()->json(['status'=>false,'message'=>$message],422);
      }else {
        //$api_token=str_replace("Bearer ","",api_token); ya bearer diye kayıt ettririz yada böyle ayrırıız
        $us = DB::table('users')->where('api_token', $header)->first();
        var_dump($us->api_token);
        var_dump($header);
        if ($header == $us->api_token)
        {
          User::where('api_token',$us->api_token)->update(['api_token'=>null]);
          $message = "Logout yapılıp token sıfırlanmıştır..." ;
          return response()->json(['message' => $message], 200);
        }
        else {
          $message = "Bir sorun oluştu." ;
          return response()->json(['message' => $message], $this->successStatus);
          }
        }
      }

}
