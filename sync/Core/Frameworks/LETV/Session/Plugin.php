<?php

namespace LETV\Session;
use Sabre\DAV;

/**
 * This plugin provides Authentication for a WebDAV server.
 *
 * It relies on a Backend object, which provides user information.
 *
 * Additionally, it provides support for:
 *  * {DAV:}current-user-principal property from RFC5397
 *  * {DAV:}principal-collection-set property from RFC3744
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Plugin extends DAV\ServerPlugin {

    /**
     * Reference to main server object
     *
     * @var Sabre\DAV\Server
     */
    protected $server;


    protected $sessopBackend;

    private static $pluginMap = array(
        'carddav'   =>  '1',
        'caldav'    =>  '3'
    );

    const AUTH_TYPE = 'session';


    public function __construct(PDOSessionOperationBackend $sessopBackend) {

        $this->sessopBackend = $sessopBackend;

    }

    /**
     * Initializes the plugin. This function is automatically called by the server
     *
     * @param DAV\Server $server
     * @return void
     */
    public function initialize(DAV\Server $server) {

        $this->server = $server;
        $this->server->subscribeEvent('afterMethod',array($this,'afterMethod'));

    }

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using DAV\Server::getPlugin
     *
     * @return string
     */
    public function getPluginName() {

        return 'sessionOperation';

    }



    /**
     * This method is called before any HTTP method and forces users to be authenticated
     *
     * @param string $method
     * @param string $uri
     * @throws Sabre\DAV\Exception\NotAuthenticated
     * @return bool
     */
    public function afterMethod() {
        $httpMethod = $this->server->httpRequest->getMethod();
        $sessionId = $this->getSessionToken();
        foreach($this->server->getPlugins() as $plugin){
            $name = $plugin->getPluginName();
            if(array_key_exists($name, self::$pluginMap)){
                $this->sessopBackend->saveOperationIfNeeded($sessionId, self::$pluginMap[$name], $httpMethod);
            }
        }
        $this->sessopBackend->updateSession($sessionId);


    }

    public function getSessionToken() {

        //$auth = $this->server->httpRequest->getHeader('Authorization');
	$auth = $this->server->httpRequest->getHeader('sessionid');
        if (!$auth) return false;
	return $auth;
        if (strpos(strtolower($auth),self::AUTH_TYPE)!==0) return false;
        return substr($auth, strlen(self::AUTH_TYPE)+1);

    }

}
