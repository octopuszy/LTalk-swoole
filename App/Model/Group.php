<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/18
 * Time: 下午7:23
 */

namespace App\Model;


use think\Model;

class Group extends Model
{

    public static function getSum($where){
        return self::where($where)->count();
    }

    public static function getGroup($where, $single = false){
        if($single){
            return self::where($where)->find();
        }else{
            return self::where($where)->select();
        }

    }

    public static function newGroup($data){
        $model = new self();
        foreach ($data as $key => $val){
            $model->$key = $val;
        }
        $model->save();
    }

}