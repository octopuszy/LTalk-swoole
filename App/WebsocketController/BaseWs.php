<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午3:41
 */

namespace App\WebsocketController;


use App\Exception\Websocket\WsException;
use App\Service\LoginService;
use App\Service\UserCacheService;
use EasySwoole\Core\Socket\AbstractInterface\WebSocketController;

class BaseWs extends WebSocketController
{
    function actionNotFound(?string $actionName)
    {
        $data = (new WsException([
            'msg' => '请求方法不存在',
            'errorCode' => 60001
        ]))->getMsg();
        $this->response()->write(json_encode($data));
    }

    // 验证 token
    protected function onRequest(?string $actionName): bool
    {
        $content = $this->request()->getArg('content');
        if(!isset($content['token'])){
            return false;
        }
        if(!UserCacheService::getUserByToken($content['token'])){
            return false;
        }

        return true;
    }
}