<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/16
 * Time: 下午8:58
 */

namespace App\WebsocketController;




use App\Exception\Websocket\FriendException;
use App\Service\ChatService;
use App\Service\FriendService;

class Chat extends BaseWs
{
    /*
     * 处理个人聊天
     * @param number
     * @param data
     *
     * 1. 验证用户是否存在，是否在线
     * 2. 检查是否是好友关系
     * 3. 异步给双方发送消息，做标记是自己的还是对方发的
     * 4. 异步存储消息记录
     */
    public function personalChat(){
        $content = $this->request()->getArg('content');
        $user = $this->getUserInfo();
        $to_number = $content['number'];
        $data = $content['data'];

        $to_user = $this->onlineValidate($to_number);
        if(isset($to_user['errorCode'])) {
            $this->response()->write(json_encode($to_user));
            return;
        }
        // 查二者是否已经是好友
        $isFriend = FriendService::checkIsFriend($user['user']['id'], $to_user['user']['id']);
        if(!$isFriend){
            $err = (new FriendException([
                'msg' => '非好友状态',
                'errorCode' => 40005
            ]))->getMsg();
            $this->response()->write(json_encode($err));
            return;
        }
        // 异步发送消息
        $chat_data = [
            'from'  => $user,
            'to'    => $to_user,
            'data'  => $data
        ];
        ChatService::sendPersonalMsg($chat_data);

        // 异步存储消息
        ChatService::savePersonalMsg($chat_data);
    }

    /*
     * 处理群组聊天
     */
    public function groupChat(){

    }
}