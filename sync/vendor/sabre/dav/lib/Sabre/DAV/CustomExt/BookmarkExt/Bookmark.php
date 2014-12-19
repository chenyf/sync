<?php

namespace Sabre\DAV\CustomExt\BookmarkExt;

use Sabre\DAV\CustomExt\Backend;
use Sabre\DAV;

class Bookmark extends DAV\Node implements DAV\ICollection {

    protected $backend;
    protected $bookid;

    public function __construct(Backend\BackendInterface $backend) {
        $this->backend = $backend;
        $this->bookid = null;
    }

    public function setRootNodeAttr($uid) {
        $bookmark = $this->backend->getBookmark($uid);
        if ($bookmark) {
            $this->bookid = $bookmark["id"];
        } else {
            $this->bookid = $this->backend->createBookmark($uid);
        }
    }

    public function getName() {
        return "";
    }

    public function createFile($name, $data = null) {
        if (is_resource($data)) {
            $data = stream_get_contents($data); 
        }
        return $this->backend->createBookmarkItem($this->bookid, $name, $data);
    }

    public function createDirectory($name) {
        throw new DAV\Exception\MethodNotAllowed('Mkcol is not yet supported');
    }

    public function getChild($name) {
        $data = $this->backend->getBookmarkItem($this->bookid, $name);
        return new BookmarkItem(array(
            "backend" => $this->backend, 
            "bookid" => $this->bookid,            
            "name" => $data["uri"],
        ));
    }

    public function getChildren() {
        $nodes = array();
        foreach($this->backend->getBookmarkItems($this->bookid) as $data) {
            $nodes[] = new BookmarkItem(array(
                "backend" => $this->backend, 
                "bookid" => $this->bookid,
                "name" => $data["uri"],
            ));
        }
        return $nodes;
    }

    public function childExists($name) {
        if ($this->backend->getBookmarkItem($this->bookid, $name) === false)
            return false;
        return true;
    }

    public function delete() {
        throw new DAV\Exception\MethodNotAllowed('delete collection is not yet supported');
    }
}

