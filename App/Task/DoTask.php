<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 上午10:03
 */

namespace App\Task;


use EasySwoole\Core\Swoole\Coroutine\PoolManager;
use App\Utility\RedisPool;

class DoTask
{
    public static function RedisSetDatas($data){
        echo '33333'.PHP_EOL;
        $pool = PoolManager::getInstance()->getPool(RedisPool::class); // 获取连接池对象
        var_dump($pool);
//        $redis = $pool->getObj();
//        foreach ($data as $key => $val){
//            if(is_array($val)){
//                $val = json_encode($val);
//            }
//            $res = $redis->exec('set', $key, $val);
//        }
//        $pool->freeObj($redis);
//        return $res;
    }
}