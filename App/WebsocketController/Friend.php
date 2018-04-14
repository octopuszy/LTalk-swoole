<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午3:40
 */

namespace App\WebsocketController;



class Friend extends BaseWs
{
    function actionNotFound(?string $actionName)
    {
        $this->response()->write("action call {$actionName} not found");
    }

    function test(){
        $this->response()->write("hello");
    }

}