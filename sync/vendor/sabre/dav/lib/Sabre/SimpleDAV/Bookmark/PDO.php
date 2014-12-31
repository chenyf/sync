<?php

namespace Sabre\SimpleDAV\Bookmark;

use Sabre\SimpleDAV\Backend;

class PDO extends Backend\AbstractBackend {

    /**
     * PDO connection
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The PDO table name used to store Bookmarks
     */
    protected $bookmarksTableName;

    /**
     * The PDO table name used to store BookmarkItem
     */
    protected $bookmarkItemTableName;

    /**
     * Sets up the object
     *
     * @param \PDO $pdo
     * @param string $bookmarksTableName
     * @param string $bookmarkItemTableName
     */
    public function __construct(\PDO $pdo, $bookmarksTableName = 'Bookmarks', $bookmarkItemTableName = 'BookmarkItem') {
        $this->pdo = $pdo;
        $this->bookmarksTableName = $bookmarksTableName;
        $this->bookmarkItemTableName = $bookmarkItemTableName;
    }

    public function getBookmark($uid) {
        $stmt = $this->pdo->prepare('SELECT id, userId, cTag, createTime, updateTime from '.$this->bookmarksTableName.' where userId = ?');
        $stmt->execute(array($uid));
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createBookmark($uid) {
        $values = array(
            'userId' => $uid,
            'cTag' => 1,
        );

        $stmt = $this->pdo->prepare('INSERT INTO '.$this->bookmarksTableName.' SET userId = :userId, cTag = :cTag, createTime = now(), updateTime = now()');
        $stmt->execute($values);
        return $this->pdo->lastInsertId();
    } 

    public function getBookmarkItems($bookid) {
        $stmt = $this->pdo->prepare('SELECT uri FROM '.$this->bookmarkItemTableName.' where pid = 0 and bookid = ?');
        $stmt->execute(array($bookid));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getBookmarkItem($bookid, $uri) {
        $values = array(
            'bookid' => $bookid,
            'uri' => $uri,
        );
        $stmt = $this->pdo->prepare('SELECT id, bookid, data, iscol, uri, name, pid, createTime, updateTime from '.$this->bookmarkItemTableName.' where uri = :uri and bookid = :bookid');
        $stmt->execute($values);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createBookmarkItem($bookid, $uri, $data) {
        $values = array(
            'bookid' => $bookid,
            'uri' => $uri,
            'iscol' => 0,
            'name' => $uri,
            'data' => $data,
        );

        $stmt = $this->pdo->prepare('INSERT INTO '.$this->bookmarkItemTableName.' SET uri = :uri, bookid = :bookid, iscol = :iscol, name = :name, data = :data, createTime = now(), updateTime = now()');
        $stmt->execute($values);
        if (is_string($data)) {
            return 'W/"'.md5($data).'"';
        } else {
            return null;
        }
    }

    public function updateBookmarkItem($bookid, $uri, $data) {
        $stmt = $this->pdo->prepare('UPDATE '.$this->bookmarksTableName.' SET cTag = cTag + 1, updateTime = now() WHERE id = ?');
        $stmt->execute(array($bookid));

        $values = array(
            'bookid' => $bookid,
            'uri' => $uri,
            'data' => $data,
        );
        $stmt = $this->pdo->prepare('UPDATE '.$this->bookmarkItemTableName.' SET data = :data, createTime = now(), updateTime = now() where uri = :uri and bookid = :bookid');
        $stmt->execute($values);
        if (is_string($data)) {
            return 'W/"'.md5($data).'"';
        } else {
            return null;
        }
    }

    public function removeBookmarkItem($bookid, $uri) {
        $values = array(
            'bookid' => $bookid,
            "uri" => $uri,
        );
        $stmt = $this->pdo->prepare('DELETE FROM '.$this->bookmarkItemTableName.' WHERE uri = :uri and bookid = :bookid');
        $stmt->execute($values);
    }
}

