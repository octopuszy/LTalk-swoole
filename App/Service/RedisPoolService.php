<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 下午12:45
 */

namespace App\Service;


use App\Utility\RedisPool;
use EasySwoole\Core\Swoole\Coroutine\PoolManager;

class RedisPoolService
{
    private $pool;
    private $pool_obj;

    private static $obj;

    public static function getRedisPool(){
        if(empty(self::$obj)) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    private function __construct()
    {
        $this->pool = PoolManager::getInstance()->getPool(RedisPool::class);
        $this->pool_obj = $this->pool->getObj();
    }



    public function setDate($data){
        foreach ($data as $key => $val){
            if(is_array($val)){
                $val = json_encode($val);
            }
            $res = $this->pool_obj->exec('set', $key, $val);
        }
    }

    public function getDate($key){
        $res = $this->pool_obj->exec('get', $key);
        return $res;
    }

    public function __destruct()
    {
        $this->pool->freeObj($this->pool_obj);
    }

}