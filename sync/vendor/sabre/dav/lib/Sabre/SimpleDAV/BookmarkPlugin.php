<?php

namespace Sabre\SimpleDAV;

use Sabre\DAV;
use Sabre\DAVACL;
use Sabre\VObject;

class BookmarkPlugin extends DAV\ServerPlugin {
    protected $server;
    
    public function getHTTPMethods($uri) {
        return array();
    }

    public function getFeatures() {
        return array();
    }

    public function getPluginName() {
        return 'bookmark';
    }

    public function initialize(DAV\Server $server) {
        $this->server = $server;
	$server->subscribeEvent('beforeMethod', array($this,'beforeMethod'));
    }

    public function beforeMethod($method, $path) {
	ok_methods = array(
		'GET',
		'PUT',
		'DELETE',
		'PROPFIND'
	);
	if (!in_array($method, $ok_methods)) {
		return false;
	}	

        try {
            $node = $this->server->tree->getNodeForPath($path);
        } catch (DAV\Exception\NotFound $e) {
            return;
        }
        return true;
    }

}
