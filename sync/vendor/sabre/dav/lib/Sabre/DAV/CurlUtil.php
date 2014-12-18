<?php

namespace Sabre\DAV;

class CurlUtil {

    static function get($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $curl_result = curl_exec($ch);
        if (!$curl_result) {
            return null; 
        }
        curl_close($ch);
        return $curl_result;    
    }

}

?>
