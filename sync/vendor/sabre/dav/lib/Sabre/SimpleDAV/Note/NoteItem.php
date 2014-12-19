<?php

namespace Sabre\SimpleDAV\Note;

use Sabre\DAV;

class NoteItem extends DAV\Node implements DAV\IFile {

    public function __construct(array $ctx) {
        $this->backend = $ctx["backend"];
        $this->noteid = $ctx["noteid"];
        $this->name = $ctx["name"];
    }

    public function getName() {
        return $this->name;
    }
    
    public function put($data) {
        if (is_resource($data)) {
            $data = stream_get_contents($data); 
        }
        return $this->backend->updateNoteItem($this->noteid, $this->getName(), $data); 
    }

    public function get() {
        $result = $this->backend->getNoteItem($this->noteid, $this->getName());
        return $result['data'];
    }

    public function delete() {
        $this->backend->removeNoteItem($this->noteid, $this->getName()); 
    }

    public function getSize() {
        return null;
    }

    public function getETag() {
        $data = $this->get();
        if (is_string($data)) {
            return 'W/"'.md5($data).'"';
        } else {
            return null;
        }
    }

    public function getContentType() {
        return null;
    }
}

