<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 上午10:03
 */

namespace App\Task;

use EasySwoole\Core\Swoole\ServerManager;

class DoTask
{
    public static function sendMsg($data){
        $fd = $data['fd'];
        $res = $data['data'];
        $server = ServerManager::getInstance()->getServer();
        return $server->push($fd,json_encode($res));
    }

}