<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/15
 * Time: 下午5:18
 */

namespace App\Sock\Parser;


use App\Service\UserCacheService;

class OnClose
{
    private $fd;

    public function __construct($fd)
    {
        $this->fd = $fd;
    }

    public function close(){
        $info = $this->getInfoByFd();
        if($info){
            // 销毁
            UserCacheService::delTokenUser($info['token']);
            UserCacheService::delNumberUserOtherInfo($info['user']['number']);
            UserCacheService::delFdToken($this->fd);
            UserCacheService::delFds($this->fd);

        }
    }

    private function getInfoByFd(){
        $token  = UserCacheService::getTokenByFd($this->fd);
        if(!$token){
            return [];
        }
        $user   = UserCacheService::getUserByToken($token);
        return [
            'token' => $token,
            'user'  => $user
        ];
    }
}