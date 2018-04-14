<?php
/**
 * Created by PhpStorm.
 * User: yuzhang
 * Date: 2018/4/14
 * Time: 上午9:22
 */

namespace App\HttpController;


class Common
{
    public static function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for ($i=0;$i<$length;$i++){
            $str .= $strPol[rand(0,$max)];
        }

        return $str;
    }
}