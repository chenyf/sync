<?php
/**
 * Created by PhpStorm.
 * User: wanghao1
 * Date: 14-6-23
 * Time: 下午3:49
 */


namespace LETV\Auth;

use Sabre\HTTP\AbstractAuth;

class SessionAuth extends AbstractAuth{

    const AUTH_TYPE = 'session';
    const SESSION_SERVE_URI = '/require4session';


    public function requireLogin() {

        $this->httpResponse->setHeader('WWW-Authenticate','Session authenticate needed.');
        $this->httpResponse->sendStatus(401);
        $this->httpResponse->setHeader("Location", SESSION_SERVE_URI);

    }


    public function getSession() {

        return $this->httpRequest->getHeader('sessionid');
    }

    public function getLoginName(){
        return $this->httpRequest->getHeader('username');
    }
    public function getAuthToken(){
        return $this->httpRequest->getHeader('authtoken');
    }


}