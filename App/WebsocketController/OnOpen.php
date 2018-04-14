<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午5:08
 */

namespace App\WebsocketController;


use App\Service\UserCacheService;

class OnOpen extends BaseWs
{
    /*
     * 用户连线后初始化
     * 传参：token
     * 1. 获取用户 fd
     * 2. 初始化所有相关缓存
     * 3. 向所有好友发送上线提醒
     * 4. 向所有群聊发送上线提醒
     */
    public function init(){
        $this->saveCache();
    }

    private function saveCache(){
        $content = $this->request()->getArg('content');
        $token = $content['token'];
        $fd = $this->client()->getFd();

    }
}