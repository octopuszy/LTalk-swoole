<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午3:41
 */

namespace App\WebsocketController;


use App\Service\LoginService;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;

class BaseWs extends WebSocketController
{
    // 验证 token
    protected function onRequest(?string $actionName): bool
    {
        $content = $this->request()->getArg('content');
        if(!isset($content['token'])){
            return false;
        }
        if(!LoginService::issetToken($content['token'])){
            return false;
        }
        return true;
    }
}