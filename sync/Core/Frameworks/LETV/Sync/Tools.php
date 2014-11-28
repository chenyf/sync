<?php
/**
 * Created by PhpStorm.
 * User: wanghao1
 * Date: 14-6-19
 * Time: 下午4:01
 */
namespace LETV\Sync;
class Tools {

    public static function authorizeWithToken(){
        $session = self::getHeader("sessionid");
        return !empty($session);
    }

    public static function getHeader($name) {
        global $_SERVER;
        $name = strtoupper(str_replace(array('-'),array('_'),$name));
        if (isset($_SERVER['HTTP_' . $name])) {
            return $_SERVER['HTTP_' . $name];
        }

        // There's a few headers that seem to end up in the top-level
        // server array.
        switch($name) {
            case 'CONTENT_TYPE' :
            case 'CONTENT_LENGTH' :
                if (isset($_SERVER[$name])) {
                    return $_SERVER[$name];
                }
                break;

        }
        return;

    }
}