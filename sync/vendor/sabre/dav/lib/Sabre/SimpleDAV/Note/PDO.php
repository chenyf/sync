<?php

namespace Sabre\SimpleDAV\Note;

use Sabre\SimpleDAV\Backend;

class PDO extends Backend\AbstractBackend {

    /**
     * PDO connection
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * The PDO table name used to store Notes
     */
    protected $notesTableName;

    /**
     * The PDO table name used to store NoteItem
     */
    protected $noteItemTableName;

    /**
     * Sets up the object
     *
     * @param \PDO $pdo
     * @param string $notesTableName
     * @param string $noteItemTableName
     */
    public function __construct(\PDO $pdo, $notesTableName = 'Notes', $noteItemTableName = 'NoteItem') {
        $this->pdo = $pdo;
        $this->notesTableName = $notesTableName;
        $this->noteItemTableName = $noteItemTableName;
    }

    public function getNote($uid) {
        $stmt = $this->pdo->prepare('SELECT id, userId, cTag, createTime, updateTime from '.$this->notesTableName.' where userId = ?');
        $stmt->execute(array($uid));
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createNote($uid) {
        $values = array(
            'userId' => $uid,
            'cTag' => 1,
        );

        $stmt = $this->pdo->prepare('INSERT INTO '.$this->notesTableName.' SET userId = :userId, cTag = :cTag, createTime = now(), updateTime = now()');
        $stmt->execute($values);
        return $this->pdo->lastInsertId();
    } 

    public function getNoteItems($noteid) {
        $stmt = $this->pdo->prepare('SELECT uri FROM '.$this->noteItemTableName.' where noteid = ?');
        $stmt->execute(array($noteid));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getNoteItem($noteid, $uri) {
        $values = array(
            'noteid' => $noteid,
            'uri' => $uri,
        );
        $stmt = $this->pdo->prepare('SELECT id, noteid, data, iscol, uri, name, createTime, updateTime from '.$this->noteItemTableName.' where uri = :uri and noteid = :noteid');
        $stmt->execute($values);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createNoteItem($noteid, $uri, $data) {
        $values = array(
            'noteid' => $noteid,
            'uri' => $uri,
            'iscol' => 0,
            'name' => $uri,
            'data' => $data,
        );

        $stmt = $this->pdo->prepare('INSERT INTO '.$this->noteItemTableName.' SET uri = :uri, noteid = :noteid, iscol = :iscol, name = :name, data = :data, createTime = now(), updateTime = now()');
        $stmt->execute($values);
        if (is_string($data)) {
            return 'W/"'.md5($data).'"';
        } else {
            return null;
        }
    }

    public function updateNoteItem($noteid, $uri, $data) {
        $stmt = $this->pdo->prepare('UPDATE '.$this->notesTableName.' SET cTag = cTag + 1, updateTime = now() WHERE id = ?');
        $stmt->execute(array($noteid));

        $values = array(
            'noteid' => $noteid,
            'uri' => $uri,
            'data' => $data,
        );
        $stmt = $this->pdo->prepare('UPDATE '.$this->noteItemTableName.' SET data = :data, createTime = now(), updateTime = now() where uri = :uri and noteid = :noteid');
        $stmt->execute($values);
        if (is_string($data)) {
            return 'W/"'.md5($data).'"';
        } else {
            return null;
        }
    }

    public function removeNoteItem($noteid, $uri) {
        $values = array(
            "noteid" => $noteid,
            "uri" => $uri,
        );
        $stmt = $this->pdo->prepare('DELETE FROM '.$this->noteItemTableName.' WHERE uri = :uri and noteid = :noteid');
        $stmt->execute($values);
    }
}
