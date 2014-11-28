<?php
/**
 * Created by PhpStorm.
 * User: wanghao1
 * Date: 14-6-23
 * Time: 上午11:33
 */
namespace LETV\Auth;

use Sabre\DAV\Auth\Backend\BackendInterface;
use Sabre\DAV;
use Sabre\HTTP;

class PDOSessionAuthBackend implements BackendInterface{

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

    /**
     * Authentication user
     * @var string
     */
    private $userId;


    /**
     * Creates the backend object.
     *
     * If the filename argument is passed in, it will parse out the specified file fist.
     *
     * @param PDO $pdo
     * @param string $tableName The PDO table name to use
     */
    public function __construct(\PDO $pdo, $authRealm, $tableName = 'SyncSession') {

        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->authRealm = $authRealm;
    }

    /**
     * @param DAV\Server $server
     * @param string $realm
     * @return bool
     * @throws \Sabre\DAV\Exception\NotAuthenticated
     */
    function authenticate(\Sabre\DAV\Server $server,$realm) {

        $auth = new SessionAuth;
        $auth->setHTTPRequest($server->httpRequest);
        $auth->setHTTPResponse($server->httpResponse);
        $auth->setRealm($realm);

        $sessionId = $auth->getSession();
        if (!$sessionId) {
            $auth->requireLogin();
            throw new DAV\Exception\NotAuthenticated('No basic authentication headers were found');
        }

        // Authenticates the user
        if (!$this->validateUserSession($sessionId, $auth->getLoginName())) {

        }
        return true;
    }
    /**
     * Returns information about the currently logged in username.
     *
     * If nobody is currently logged in, this method should return null.
     *
     * @return string|null
     */
    function getCurrentUser() {
        return $this->userId;
    }

    function validateUserSession($sessionId, $loginName){
        $sql = 'SELECT id, uid, username, lastUpdateTime FROM '.$this->tableName.' WHERE sessionCode = ? and active = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($sessionId, 'Y'));
        $result = $stmt->fetch();


        if (!count($result)) {
            throw new DAV\Exception\SessionExpired('Invalid Session, session timeout or not exist!');
        };

        if (empty($loginName) || $result['username'] != $loginName) {
            throw new DAV\Exception\SessionExpired('Session authorization failed.');
        }

        $userID = $result['uid'];
        if( !empty($userID) ){
            $this->userId = $userID;
            return true;
        }
        return false;
    }

}