<?php

namespace Sabre\SimpleDAV\Note;

use Sabre\DAV;
use Sabre\SimpleDAV;

class Plugin extends SimpleDAV\Plugin {

    public function prepPushData($data = array()) {
        if (SYNC_PUSH_NOTE_ENABLE) { 
            $this->pushData = array_merge($data, array(
                "sendid" => SYNC_PUSH_NOTE_SENDID,
            ));
        }
    }

    public function syncPush() {
        if (SYNC_PUSH_NOTE_ENABLE) {
            $r = DAV\PushUtil::syncPush($this->pushData);
            if (!$r) {
                \LETV\CLog\CLog::warning("failed to sync data with push service");
            }
        }
    }
}
