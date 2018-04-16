<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/16
 * Time: 下午9:15
 */

namespace App\Model;


use think\Model;

class ChatRecord extends Model
{
    public function user(){
        return $this->belongsTo('User','uid','id');
    }

    public function to_user(){
        return $this->belongsTo('User','to_id','id');
    }

    public static function newRecord($data)
    {
        $model = new self();
        foreach ($data as $key=>$value){
            $model->$key = $value;
        }
        $model->save();
    }
}