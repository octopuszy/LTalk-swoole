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
    /*
     * 发送好友请求
     *
     */
    function sendReq(){
        $content = $this->request()->getArg('content');
    }

    /*
     * 处理好友请求
     */
    function doReq(){

    }
}