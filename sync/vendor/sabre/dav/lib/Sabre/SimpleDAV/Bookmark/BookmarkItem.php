<?php

namespace Sabre\DAV\CustomExt\BookmarkExt;

use Sabre\DAV;

class BookmarkItem extends DAV\Node implements DAV\IFile {

    public function __construct(array $ctx) {
        $this->backend = $ctx["backend"];
        $this->bookid = $ctx["bookid"];
        $this->name = $ctx["name"];
    }

    public function getName() {
        return $this->name;
    }
    
    public function put($data) {
        if (is_resource($data)) {
            $data = stream_get_contents($data); 
        }
        return $this->backend->updateBookmarkItem($this->bookid, $this->name, $data); 
    }

    public function get() {
        $result = $this->backend->getBookmarkItem($this->bookid, $this->name);
        return $result["data"];
    }

    public function delete() {
        $this->backend->removeBookmarkItem($this->bookid, $this->name); 
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

