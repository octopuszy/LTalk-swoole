<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午1:18
 */

namespace App\Service;


class LoginService
{
    private $token;
    private $user;

    public function __construct($token , $user)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /*
     * 保存登陆后的初始信息
     * 存两个关联关系键值对
     * 1. token => userInfo
     * 2. uid => token
     */
    public function saveCache(){
        $this->saveTokenToUser();
        $this->saveUidToToken();
    }

    /*
     * token => userInfo
     */
    private function saveTokenToUser(){
        $key = "user:getUser:".$this->token;
        $data = [
            $key => $this->user,
        ];
        $redis_pool = RedisPoolService::getRedisPool();
        $redis_pool->setDate($data);
    }

    /*
     * uid => token
     */
    private function saveUidToToken(){
        $key = "user:getToken:".$this->user['number'];
        $data = [
            $key => $this->token,
        ];
        $redis_pool = RedisPoolService::getRedisPool();
        $redis_pool->setDate($data);
    }

    public static function isLogin($number){
        $key = "user:getToken:".$number;
        $redis_pool = RedisPoolService::getRedisPool();
        return $redis_pool->getDate($key);
    }

    public static function issetToken($token){
        $key = "user:getUser:".$token;
        $redis_pool = RedisPoolService::getRedisPool();
        return $redis_pool->getDate($key);
    }
}