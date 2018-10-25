<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Lcobucci\JWT\Parser;
use UAParser\Exception\ReaderException;

class SystemApiController extends Controller {
    public $StatusCode = 200;
    public $Code = 100;


    public function login(Request $request)
    {
        $RequestData = $request->only(['email', 'password']);
        $validator = Validator::make($RequestData, [
            'email'     => 'required|exists:staff,email',
            'password'  => 'required'
        ]);

        if ($validator->errors()->any()) {
            return $this->ValidationError($validator, __('Validation Error'));
        }

        try {

            $client = new \GuzzleHttp\Client();
            $response = $client->post( 'http://localhost:8080/task/public/oauth/token', [
                'form_params' => [
                    'client_id' => 2,
                    'client_secret' => 'zhsSTiVzFbIW4R9QOS2oZSkCELPOTxwOr4lhxbZi',
                    'grant_type' => 'password',
                    'username' => $RequestData['email'],
                    'password' => $RequestData['password'],
                    'scope' => '*',
                ]
            ]);
            $auth = json_decode((string)$response->getBody());
            if ($auth->access_token) {
                $admin = Admin::select("staff.*")
                    ->where('email', $RequestData['email'])
                    ->first();
                $auth->admin = $admin;
                return $this->json(true, __('login successful'),['auth'=>$auth]);
            } else {
                return $this->json(false,__('invalid Auth'));
            }
        } catch (\ReaderException $e) {
            return $this->json(false,__('invalid credentials'));
        }

    }
    public function logout(Request $request)
    {
        $user = Auth::user();
        $value = $request->bearerToken();
        $id = (new Parser())->parse($value)->getHeader('jti');
        $user->token()->where('id', '=', $id)->first()->revoke();

        return $this->json(true,__('Logged out'));
    }


    function no_access(){
        return ['status'=>false,'msg'=> __('You don\'t have permission to preform this action')];
    }

    function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function setStatusCode($StatusCode){
        $this->StatusCode = $StatusCode;
        return $this;
    }



    public function getStatusCode(){
        return $this->StatusCode;
    }

    public function setCode($code){
        $this->Code = $code;
        return $this;
    }

    public function getCode(){
        return $this->Code;
    }

    function ReturnMethod($condition,$truemsg,$falsemsg,$data=false){
        if($condition)
            return ['status'=>true,'msg'=>$truemsg,'data'=>$data];
        else
            return ['status'=>false,'msg'=>$falsemsg,'data'=>$data];
    }

    public function respondSuccess($data,$message = 'Success'){
        return $this->setStatusCode(200)->setCode(100)->respondWithoutError($data,$message);
    }

    public function respondCreated($data,$message = 'Row has been created'){
        return $this->setStatusCode(200)->setCode(100)->respondWithoutError($data,$message);
    }

    public function respondNotFound($data,$message = 'Not Found!'){
        return $this->setStatusCode(200)->setCode(101)->respondWithError($data,$message);
    }

    public function respond($data,$headers=[]){
        $data['version'] = $this->lastupdate;
        return response()->json($data,$this->getStatusCode(),$headers);
    }

    public function respondWithoutError($data,$message){

        return response()->json([
            'status' => true,
            'msg' => $message,
            'code' => $this->getCode(),
            'data'=>$data
        ],$this->getStatusCode());
    }

    public function respondWithError($data,$message){
        return response()->json([
            'status' => false,
            'msg' => $message,
            'code' => $this->getCode(),
            'data'=>$data
        ],$this->getStatusCode());
    }

    public function ValidationError($validation,$message){
        $errorArray = $validation->errors()->messages();

        $data = array_column(array_map(function($key,$val) {
            return ['key'=>$key,'val'=>implode('|',$val)];
        },array_keys($errorArray),$errorArray),'val','key');

        return $this->setCode(103)->respondWithError($data,implode("\n",array_flatten($errorArray)));
    }
    public function json($status,$msg = '', $data = [], $code = 200)
    {
        echo json_encode( ['status' => $status,'msg' => $msg, 'code' => $code, 'data' => (object)$data]);
    }
}