<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/15
 * Time: 下午7:50
 */

namespace App\Service;


use App\Model\User as UserModel;

class FriendService
{
    public static function getFriends($arr){
        $res = [];
        foreach ($arr as $val){
            $res[] = self::friendInfo(['id'=>$val]);
        }
        return $res;
    }

    public static function friendInfo($where){
        $user = UserModel::where($where)->find();
        $data['number'] = $user['number'];
        $data['nickname'] = $user['nickname'];
        $data['last_login'] = $user['last_login'];
        $data['online']  = UserCacheService::getFdByNum($user['number'])?1:0;   // 是否在线
        return $data;
    }
}