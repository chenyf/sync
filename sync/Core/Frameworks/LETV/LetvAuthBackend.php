<?php
namespace LETV;

use Sabre\DAV\Auth\Backend;
use Sabre\DAV;

class LetvAuthBackend implements Backend\BackendInterface{

    private $uid;

    public function __construct() {

        $this->uid = null;
    }

    function authenticate(\Sabre\DAV\Server $server, $realm) {
        $token = $server->httpRequest->getHeader("token");
        if (!$token) {
            throw new DAV\Exception\NotAuthenticated('no token was found in headers');
        }

        $r = DAV\CurlUtil::get("http://api.sso.letv.com/api/checkTicket/tk/".$token);
        if ($r) {
            $result = json_decode($r, $assoc = true);
            if ($result["status"] == 1) {
                $this->uid = $result["bean"]["result"];
            } else {
                throw new DAV\Exception\NotAuthenticated('make sure user has logined');
            }
        } else {
            throw new DAV\Exception\NotAuthenticated('failed to check token');
        }
    }
    
    function getCurrentUser() {
        return $this->uid;
    }
}
