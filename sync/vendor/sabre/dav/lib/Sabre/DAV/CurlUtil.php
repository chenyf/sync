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

    static function post($url, $data, $headers = array("Content-Type: application/json")) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $curl_result = curl_exec($ch);
        if (!$curl_result) {
            return null; 
        }
        curl_close($ch);
        return $curl_result;
    }
}

?>
