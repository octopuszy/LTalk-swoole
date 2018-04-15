<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 上午10:03
 */

namespace App\Task;


use App\Service\FriendService;
use App\Service\UserCacheService;
use EasySwoole\Core\Socket\Response;
use EasySwoole\Core\Swoole\Coroutine\PoolManager;
use App\Utility\RedisPool;
use EasySwoole\Core\Swoole\ServerManager;

class DoTask
{
    public static function sendMsg($data){
        $fd = $data['fd'];
        $res = $data['data'];
        $server = ServerManager::getInstance()->getServer();
        return $server->push($fd,json_encode($res));
    }

    public static function FriendOk($data){
        $from_number = $data['from_number'];
        $number      = $data['umber'];
        $check       = $data['check'];

        $from_user = FriendService::friendInfo(['number'=>$from_number]);
        $user = FriendService::friendInfo(['number'=>$number]);

        if($from_user['online']){
            if($check){
                self::sendMsg([
                    'fd'    => UserCacheService::getFdByNum($from_number),
                    'data'  => [
                        'method'    => 'newFriend',
                        'data'      => $user
                    ]
                ]);
            }else{
                self::sendMsg([
                    'fd'    => UserCacheService::getFdByNum($from_number),
                    'data'  => [
                        'method'    => 'newFriendFail',
                        'data'      => $number.' 拒绝好友申请'
                    ]
                ]);
            }

        }

        if($check){
            if($user['online']){
                self::sendMsg([
                    'fd'    => UserCacheService::getFdByNum($number),
                    'data'  => [
                        'method'    => 'newFriend',
                        'data'      => $from_user
                    ]
                ]);
            }
        }
    }
}