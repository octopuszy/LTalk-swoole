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
        // 销毁
        UserCacheService::delTokenUser($info['token']);
        UserCacheService::delNumberUserOtherInfo($info['user']['number']);
        UserCacheService::delFdToken($this->fd);
        echo '已销毁相关缓存...'.PHP_EOL;
    }

    private function getInfoByFd(){
        $token  = UserCacheService::getTokenByFd($this->fd);
        $user   = UserCacheService::getUserByToken($token);
        return [
            'token' => $token,
            'user'  => $user
        ];
    }
}