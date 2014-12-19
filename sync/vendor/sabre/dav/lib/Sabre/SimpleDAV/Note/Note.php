<?php

namespace Sabre\DAV\CustomExt\NoteExt;

use Sabre\DAV\CustomExt\Backend;
use Sabre\DAV;

class Note extends DAV\Node implements DAV\ICollection {

    protected $noteBackend;
    protected $noteid;

    public function __construct(Backend\BackendInterface $noteBackend) {
        $this->noteBackend = $noteBackend;
        $this->noteid = null;
    }
    
    public function setRootNodeAttr($uid) {
        $note = $this->noteBackend->getNote($uid);
        if ($note) {
            $this->noteid = $note["id"];
        } else {
            $this->noteid = $this->noteBackend->createNote($uid); 
        }
    }

    public function getName() {
        return "";
    }

    public function createFile($name, $data = null) {
        if (is_resource($data)) {
            $data = stream_get_contents($data); 
        }
        return $this->noteBackend->createNoteItem($this->noteid, $name, $data);
    }

    public function createDirectory($name) {
        throw new DAV\Exception\MethodNotAllowed('Mkcol is not yet supported');
    }

    public function getChild($name) {
        $data = $this->noteBackend->getNoteItem($this->noteid, $name);
        return new NoteItem(array(
            "noteBackend" => $this->noteBackend, 
            "noteid" => $this->noteid,            
            "name" => $data["uri"],
        ));
    }

    public function getChildren() {

        $nodes = array();
        foreach($this->noteBackend->getNoteItems($this->noteid) as $data) {
            $nodes[] = new NoteItem(array(
                "noteBackend" => $this->noteBackend, 
                "noteid" => $this->noteid,
                "name" => $data["uri"],
            ));

        }
        return $nodes;
    }

    public function childExists($name) {
        if ($this->noteBackend->getNoteItem($this->noteid, $name) === false)
            return false;
        return true;
    }

    public function delete() {
        throw new DAV\Exception\MethodNotAllowed('delete collection is not yet supported');
    }

}
