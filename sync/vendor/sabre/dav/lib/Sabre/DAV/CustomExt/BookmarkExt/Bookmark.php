<?php

namespace Sabre\DAV\CustomExt\BookmarkExt;

use Sabre\DAV\CustomExt\Backend;
use Sabre\DAV;

class Bookmark extends DAV\Node implements DAV\ICollection {

    protected $bookmarkBackend;
    protected $bookid;

    public function __construct(Backend\BackendInterface $bookmarkBackend) {
        $this->bookmarkBackend = $bookmarkBackend;
        $this->bookid = null;
    }

    public function setRootNodeAttr($uid) {
        $bookmark = $this->bookmarkBackend->getBookmark($uid);
        if ($bookmark) {
            $this->bookid = $bookmark["id"];
        } else {
            $this->bookid = $this->bookmarkBackend->createBookmark($uid);
        }
    }

    public function getName() {
        return "";
    }

    public function createFile($name, $data = null) {
        if (is_resource($data)) {
            $data = stream_get_contents($data); 
        }
        return $this->bookmarkBackend->createBookmarkItem($this->bookid, $name, $data);
    }

    public function createDirectory($name) {
        throw new DAV\Exception\MethodNotAllowed('Mkcol is not yet supported');
    }

    public function getChild($name) {
        $data = $this->bookmarkBackend->getBookmarkItem($this->bookid, $name);
        return new BookmarkItem(array(
            "bookmarkBackend" => $this->bookmarkBackend, 
            "bookid" => $this->bookid,            
            "name" => $data["uri"],
        ));
    }

    public function getChildren() {

        $nodes = array();
        foreach($this->bookmarkBackend->getBookmarkItems($this->bookid) as $data) {
            $nodes[] = new BookmarkItem(array(
                "bookmarkBackend" => $this->bookmarkBackend, 
                "bookid" => $this->bookid,
                "name" => $data["uri"],
            ));

        }
        return $nodes;
    }

    public function childExists($name) {
        if ($this->bookmarkBackend->getBookmarkItem($this->bookid, $name) === false)
            return false;
        return true;
    }

    public function delete() {
        throw new DAV\Exception\MethodNotAllowed('delete collection is not yet supported');
    }

}

