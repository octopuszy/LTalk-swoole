<?php
/**
 * Created by PhpStorm.
 * User: Yu
 * Date: 2018/4/13
 * Time: 17:22
 */

namespace App\HttpController\Api;


use App\Exception\ParameterException;
use App\Exception\RegisterException;
use App\Model\User as UserModel;
use App\Validate\RegisterValidate;
use EasySwoole\Core\Http\AbstractInterface\Controller;

class Login extends Controller
{
    public function index(){
        $this->response()->write('login');
    }

    /*
     * 用户注册
     */
    public function register(){
        // 验证
        (new RegisterValidate())->goCheck($this->request());
        $email = $this->request()->getRequestParam('email');
        $password = $this->request()->getRequestParam('password');
        $repassword = $this->request()->getRequestParam('repassword');

        // 判断两次密码是否输入一致（这块应该放到验证器中，但原生验证器并不支持自定义验证函数，后期优化
        if (strcmp($password,$repassword)){
            throw new ParameterException(['msg'=>'两次密码输入不一致']);
        }

        // 查询用户是否已经存在
        $user = UserModel::getUser(['email'=>$email]);
        if(!empty($user)){
            throw new RegisterException([
                'msg'=>'已有用户，请直接登录',
                'errorCode'=>20001
            ]);
        }

        // 入库
        $data = [
            'email' => $email,
            'password' => md5($password)
        ];
        try{
            UserModel::newUser($data);
        }catch (\Exception $e){
            throw $e;
        }
        $this->writeJson(200, true);
    }


    /*
     * 用户登录
     * 验证通过后，将信息存入 redis
     * 返回 token
     */
    public function login(){
        
    }

}