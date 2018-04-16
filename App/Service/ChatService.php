<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/16
 * Time: 下午9:20
 */

namespace App\Service;


use App\Model\ChatRecord;
use App\Task\Task;
use App\Task\TaskHelper;
use EasySwoole\Core\Swoole\Task\TaskManager;

class ChatService
{
    /*
     *  发送聊天消息
     *  异步，做标记是自己的还是对方发的
     */
    public static function sendPersonalMsg($data){
        // 给自己发
        $myData = [
            'flag'  => 1,                       // 自己的消息 1，对方的消息 2
            'data'  => $data['data'],
            'number'=> $data['to']['user']['number']    // 跟谁聊
        ];
        $taskData = (new TaskHelper('sendMsg', $data['from']['fd'], 'chat', $myData))
            ->getTaskData();
        $taskClass = new Task($taskData);
        TaskManager::async($taskClass);

        // 给对方发
        $toData = [
            'flag'  => 2,                       // 自己的消息 1，对方的消息 2
            'data'  => $data['data'],
            'number'=> $data['from']['user']['number']  // 哪来的
        ];
        $taskData = (new TaskHelper('sendMsg', $data['to']['fd'], 'chat', $toData))
            ->getTaskData();
        $taskClass = new Task($taskData);
        TaskManager::async($taskClass);
    }

    /*
     * 存储消息记录
     */
    public static function savePersonalMsg($data){
        $taskData = [
            'method' => 'saveMysql',
            'data'  => [
                'class'    => 'App\Model\ChatRecord',
                'method'   => 'newRecord',
                'data'     => [
                    'uid'       => $data['from']['user']['id'],
                    'to_id'     => $data['to']['user']['id'],
                    'data'      => $data['data']
                ]
            ]
        ];
        $taskClass = new Task($taskData);
        TaskManager::async($taskClass);
    }

}