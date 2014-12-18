<?php

namespace Sabre\DAV\CustomExt;

use Sabre\DAV;

class CustomPlugin extends DAV\ServerPlugin {

    const NS_DAV = "urn:ietf:params:xml:ns:webdav";

    protected $server;

    public function __construct() {
    
    }

    public function initialize(DAV\Server $server) {

        $this->server = $server;
        $this->server->subscribeEvent('beforeMethod',array($this, 'beforeMethod'));
        $this->server->subscribeEvent('report',array($this, 'report'));
        
    }

    public function beforeMethod($method, $uri) { 
        
        $token = $this->server->httpRequest->getHeader("token");
        $r = DAV\CurlUtil::get("http://api.sso.letv.com/api/checkTicket/tk/".$token);
        if ($r) {
            $result = json_decode($r, $assoc = true);
            if ($result["status"] == 1) {
                $uid = $result["bean"]["result"];
                $this->server->tree->getNodeForPath("/")->setRootNodeAttr($uid);
            }
        }

        return true;

    }

    public function report($reportName, $dom, $uri) {

        $pattern = "/{".self::NS_DAV."}.*-multiget/";
        if (preg_match($pattern, $reportName)) {
            $properties = array_keys(DAV\XMLUtil::parseProperties($dom->firstChild));
    
            $hrefElems = $dom->getElementsByTagNameNS('urn:DAV','href');
            $propertyList = array();
    
            foreach($hrefElems as $elem) {
                $uri = $this->server->calculateUri($elem->nodeValue);
                list($propertyList[]) = $this->server->getPropertiesForPath($uri,$properties);
            }
    
            $prefer = $this->server->getHTTPPRefer();
            $this->server->httpResponse->sendStatus(207);
            $this->server->httpResponse->setHeader('Content-Type','application/xml; charset=utf-8');
            $this->server->httpResponse->setHeader('Vary','Brief,Prefer');
            $this->server->httpResponse->sendBody($this->server->generateMultiStatus($propertyList, $prefer['return-minimal']));
            return false;
        }

        return true;

    }
}
