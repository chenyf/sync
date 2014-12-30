<?php

namespace Sabre\SimpleDAV\Bookmark;

use Sabre\DAV;
use Sabre\SimpleDAV;
use LETV\CLog;

class Plugin extends SimpleDAV\Plugin {

    public function prepPushData($data = array()) {
        if (SYNC_PUSH_BOOKMARK_ENABLE) { 
            $this->pushData = array_merge($data, array(
                "sendid" => SYNC_PUSH_BOOKMARK_SENDID,
            ));
        }
    }

    public function syncPush() {
        if (SYNC_PUSH_BOOKMARK_ENABLE) {
            $r = DAV\PushUtil::syncPush($this->pushData);
            if (!$r) {
                CLog\CLog::warning("failed to sync data with push service. request: ".json_encode($this->pushData));
            } else {
                CLog\CLog::notice("request: ".json_encode($this->pushData)." response: ".$r);
            }
        }
    }
}
