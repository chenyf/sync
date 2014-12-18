<?php

namespace Sabre\DAV\CustomExt\BookmarkExt;

use Sabre\DAV;

class BookmarkItem extends DAV\Node implements DAV\IFile {

    public function __construct(array $ctx) {
        $this->bookmarkBackend = $ctx["bookmarkBackend"];
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
               
        return $this->bookmarkBackend->updateBookmarkItem($this->bookid, $this->getName(), $data); 
    }

    public function get() {
        $result = $this->bookmarkBackend->getBookmarkItem($this->bookid, $this->getName());
        return $result["data"];
    }

    public function delete() {
        $this->bookmarkBackend->removeBookmarkItem($this->bookid, $this->getName()); 
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

