<?php

namespace Baikal\Core;
use Sabre\DAV\Exception;

/**
 * This is an authentication backend that uses a database to manage passwords.
 *
 * Format of the database tables must match to the one of \Sabre\DAV\Auth\Backend\PDO
 *
 * @copyright Copyright (C) 2013 Lukasz Janyst. All rights reserved.
 * @author Lukasz Janyst <ljanyst@buggybrain.net>
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class PDOBasicAuth extends \Sabre\DAV\Auth\Backend\AbstractBasic {

    /**
     * Reference to PDO connection
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * PDO table name we'll be using
     *
     * @var string
     */
    protected $tableName;

    /**
     * Authentication realm
     *
     * @var string
     */
    protected $authRealm;

    private $ssoApi = "http://api.sso.letv.com/api/clientLogin";

    /**
     * Creates the backend object.
     *
     * If the filename argument is passed in, it will parse out the specified file fist.
     *
     * @param PDO $pdo
     * @param string $tableName The PDO table name to use
     */
    public function __construct(\PDO $pdo, $authRealm, $tableName = 'users') {

        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->authRealm = $authRealm;
    }

    /**
     * Validates a username and password
     *
     * This method should return true or false depending on if login
     * succeeded.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    /*public function validateUserPass($username, $password) {
        $sql = 'SELECT username, digesta1 FROM '.$this->tableName.' WHERE username = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($username));
        $result = $stmt->fetchAll();


        if (!count($result)) return false;

        $hash = md5( $username . ':' . $this->authRealm . ':' . $password );
        if( $result[0]['digesta1'] == $hash )
        {
            $this->currentUser = $username;
            return true;
        }
        return false;

    }*/
    public function validateUserPass($username, $password){
        $errNo = 0;
        $errMsg = '';
        return $this->ssoTalk($username, $password, $errNo, $errMsg);
    }

    private function ssoTalk($username, $password, &$errNo, &$errMsg){

        $data = array(
            "loginname" =>  $username,
            "password"  =>  $password,
            "plat"      =>  "mobile_tv",
            "ip"        =>  "10.58.69.48"
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->ssoApi);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        /*curl_setopt($ch, CURLOPT_NOSIGNAL, 1);    //注意，毫秒超时一定要设置这个
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);  //超时毫秒，cUR*/
//        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 1800); // The number of seconds to keep DNS entries in memory. This option is set to 120 (2 minutes) by default.  )
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);

        if ($errNo > 0) {
            return false;
        }

        $resultArr = json_decode($result, true);

        if(isset($resultArr['bean']['uid'])){
            $this->currentUser = $resultArr['bean']['uid'];
        }
        if(empty($this->currentUser)){
            throw new Exception("Username or password does not match");
        }
        return true;
    }

}
