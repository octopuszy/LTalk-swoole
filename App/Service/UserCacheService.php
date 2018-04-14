<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午5:18
 */

namespace App\Service;


class UserCacheService
{
    /*
     * 保存 token => userInfo
     */
    public static function saveTokenToUser($token, $user){
        $key = "user:getUser:".$token;
        $data = [
            $key => $user,
        ];
        $redis_pool = RedisPoolService::getRedisPool();
        $redis_pool->setDate($data);
    }

    /*
     * 保存 number => token
     */
    public static  function saveNumToToken($number, $token){
        $key = "user:getToken:".$number;
        $data = [
            $key => $token,
        ];
        $redis_pool = RedisPoolService::getRedisPool();
        $redis_pool->setDate($data);
    }

    /*
     * 根据number获取token
     */
    public static function getTokenByNum($number){
        $key = "user:getToken:".$number;
        $redis_pool = RedisPoolService::getRedisPool();
        return $redis_pool->getDate($key);
    }

    /*
     * 根据 token 获得 user 信息
     */
    public static function getUserByToken($token){
        $key = "user:getUser:".$token;
        $redis_pool = RedisPoolService::getRedisPool();
        return $redis_pool->getDate($key);
    }

    /*
     * 保存 number => fd
     */
    public static function saveNumToFd($number, $fd){
        $key = "user:getfd:".$number;
        $data = [
            $key => $fd,
        ];
        $redis_pool = RedisPoolService::getRedisPool();
        $redis_pool->setDate($data);
    }


}