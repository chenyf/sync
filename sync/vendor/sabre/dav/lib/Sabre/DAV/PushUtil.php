<?php

namespace Sabre\DAV;

use Sabre\DAV;

class PushUtil {

    static function syncPush($data, $retry = 2) {
        $ret = null;
        $prep_data = array(
            "appid" => SYNC_PUSH_APPID,    
        );
        $post_data = array_merge($prep_data, $data);
        for($i = 0; $i < $retry; $i++) {
            $r = DAV\CurlUtil::post(SYNC_PUSH_URL, json_encode($post_data));
            if ($r) {
                if(json_decode($r, $assoc = true)["errno"] == 0) {
                    $ret = $r;
                    break;
                }
            }
        }
        return $ret;
    } 
}

?>
