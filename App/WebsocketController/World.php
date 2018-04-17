<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/17
 * Time: 下午7:51
 */

namespace App\WebsocketController;


use App\Service\WorldService;
use App\Task\Task;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Swoole\Task\TaskManager;

class World extends BaseWs
{
    /*
     * 世界聊天模块
     * 1. 获取聊天数据
     * 2. 获取所有在线fd
     * 3. 异步推送
     */
    public function chat(){
        $content = $this->request()->getArg('content');
        $user = $this->getUserInfo();
        $data = $content['data'];

        $taskData = [
            'method' => 'sendToALl',
            'data'  => [
                'method'    => 'worldChat',
                'data'      => [
                    'user'  => $user['user'],
                    'msg'   => $data
                ]
            ]
        ];
        $taskClass = new Task($taskData);
        TaskManager::async($taskClass);
        $this->sendMsg(['data'=>'ok']);
    }
}