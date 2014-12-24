<?php

namespace Sabre\SimpleDAV\Note;

use Sabre\DAV;
use Sabre\SimpleDAV\Backend;

class Note extends DAV\Node implements DAV\ICollection {

    protected $backend;
    protected $noteid;

    public function __construct(Backend\BackendInterface $backend) {
        $this->backend = $backend;
        $this->noteid = null;
    }
    
    public function setRootNodeAttr($uid) {
        $note = $this->backend->getNote($uid);
        if ($note) {
            $this->noteid = $note["id"];
        } else {
            $this->noteid = $this->backend->createNote($uid); 
        }
    }

    public function getName() {
        return "";
    }

    public function createFile($name, $data = null) {
        if (is_resource($data)) {
            $data = stream_get_contents($data); 
        }
        return $this->backend->createNoteItem($this->noteid, $name, $data);
    }

    public function createDirectory($name) {
        throw new DAV\Exception\MethodNotAllowed('Mkcol is not yet supported');
    }

    public function getChild($name) {
        $data = $this->backend->getNoteItem($this->noteid, $name);
        return new NoteItem(array(
            "backend" => $this->backend, 
            "noteid" => $this->noteid,            
            "name" => $data["uri"],
        ));
    }

    public function getChildren() {

        $nodes = array();
        foreach($this->backend->getNoteItems($this->noteid) as $data) {
            $nodes[] = new NoteItem(array(
                "backend" => $this->backend, 
                "noteid" => $this->noteid,
                "name" => $data["uri"],
            ));

        }
        return $nodes;
    }

    public function childExists($name) {
        if ($this->backend->getNoteItem($this->noteid, $name) === false)
            return false;
        return true;
    }

    public function delete() {
        throw new DAV\Exception\MethodNotAllowed('delete collection is not yet supported');
    }
}
