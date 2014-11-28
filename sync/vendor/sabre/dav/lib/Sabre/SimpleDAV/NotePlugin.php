<?php

namespace Sabre\SimpleDAV;

use Sabre\DAV;
use Sabre\DAVACL;
use Sabre\VObject;

class NotePlugin extends DAV\ServerPlugin {
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
    }
}
