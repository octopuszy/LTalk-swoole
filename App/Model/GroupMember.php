<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/18
 * Time: 下午7:24
 */

namespace App\Model;


use think\Model;

class GroupMember extends Model
{
    protected $hidden = ['id','creater_time'];

    public function info(){
        return $this->belongsTo('Group','gnumber','gnumber');
    }

    public static function newGroupMember($data){
        $model = new self();
        foreach ($data as $key => $val){
            $model->$key = $val;
        }
        $model->save();
    }

    public static function getGroups($where){
        return self::where($where)->with('info')->select();
    }
}