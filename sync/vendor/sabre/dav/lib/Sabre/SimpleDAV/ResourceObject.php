<?php

namespace Sabre\SimpleDAV;

/**
 * The CalendarObject represents a single VEVENT or VTODO within a Calendar.
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class ResourceObject extends \Sabre\DAV\File implements IResourceObject, \Sabre\DAVACL\IACL {

    /**
     * Sabre\CalDAV\Backend\BackendInterface
     *
     * @var Sabre\CalDAV\Backend\AbstractBackend
     */
    protected $realBackend;

    /**
     * Array with information about this CalendarObject
     *
     * @var array
     */
    protected $objectData;

    /**
     * Array with information about the containing calendar
     *
     * @var array
     */
    protected $resourceInfo;

    /**
     * Constructor
     *
     * @param Backend\BackendInterface $realBackend
     * @param array $resourceInfo
     * @param array $objectData
     */
    public function __construct(Backend\BackendInterface $realBackend,array $resourceInfo,array $objectData) {

        $this->realBackend = $realBackend;

        if (!isset($objectData['resourceid'])) {
            throw new \InvalidArgumentException('The objectData argument must contain a \'resourceid\' property');
        }
        if (!isset($objectData['uri'])) {
            throw new \InvalidArgumentException('The objectData argument must contain an \'uri\' property');
        }

        $this->resourceInfo = $resourceInfo;
        $this->objectData = $objectData;

    }

    /**
     * Returns the uri for this object
     *
     * @return string
     */
    public function getName() {
        return $this->objectData['uri'];
    }

    /**
     * Returns the ICalendar-formatted object
     *
     * @return string
     */
    public function get() {

        // Pre-populating the 'resourcedata' is optional, if we don't have it
        // already we fetch it from the backend.
        if (!isset($this->objectData['resourcedata'])) {
            $this->objectData = $this->realBackend->getCalendarObject($this->objectData['resourceid'], $this->objectData['uri']);
        }
        return $this->objectData['resourcedata'];

    }

    /**
     * Updates the ICalendar-formatted object
     *
     * @param string|resource $resourceData
     * @return string
     */
    public function put($resourceData) {
        if (is_resource($resourceData)) {
            $resourceData = stream_get_contents($resourceData);
        }
        $etag = $this->realBackend->updateResourceObject($this->resourceInfo['id'],$this->objectData['uri'],$resourceData);
        $this->objectData['resourcedata'] = $resourceData;
        $this->objectData['etag'] = $etag;
        return $etag;
    }

    /**
     * Deletes the calendar object
     *
     * @return void
     */
    public function delete() {
        $this->realBackend->deleteResourceObject($this->resourceInfo['id'],$this->objectData['uri']);
    }

    /**
     * Returns the mime content-type
     *
     * @return string
     */
    public function getContentType() {
        return 'text/letv-resource; charset=utf-8';
    }

    /**
     * Returns an ETag for this object.
     *
     * The ETag is an arbitrary string, but MUST be surrounded by double-quotes.
     *
     * @return string
     */
    public function getETag() {
        if (isset($this->objectData['etag'])) {
            return $this->objectData['etag'];
        } else {
            return '"' . md5($this->get()). '"';
        }
    }

    /**
     * Returns the last modification date as a unix timestamp
     *
     * @return int
     */
    public function getLastModified() {
        return $this->objectData['lastmodified'];
    }

    /**
     * Returns the size of this object in bytes
     *
     * @return int
     */
    public function getSize() {
        if (array_key_exists('size',$this->objectData)) {
            return $this->objectData['size'];
        } else {
            return strlen($this->get());
        }
    }

    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    public function getOwner() {
        return $this->resourceInfo['principaluri'];
    }

    /**
     * Returns a group principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    public function getGroup() {
        return null;
    }

    /**
     * Returns a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to
     *      be updated.
     *
     * @return array
     */
    public function getACL() {
        // An alternative acl may be specified in the object data.
        if (isset($this->objectData['acl'])) {
            return $this->objectData['acl'];
        }

        // The default ACL
        return array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->resourceInfo['principaluri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->resourceInfo['principaluri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->resourceInfo['principaluri'] . '/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->resourceInfo['principaluri'] . '/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->resourceInfo['principaluri'] . '/calendar-proxy-read',
                'protected' => true,
            ),

        );

    }

    /**
     * Updates the ACL
     *
     * This method will receive a list of new ACE's.
     *
     * @param array $acl
     * @return void
     */
    public function setACL(array $acl) {
        throw new \Sabre\DAV\Exception\MethodNotAllowed('Changing ACL is not yet supported');
    }

    /**
     * Returns the list of supported privileges for this node.
     *
     * The returned data structure is a list of nested privileges.
     * See \Sabre\DAVACL\Plugin::getDefaultSupportedPrivilegeSet for a simple
     * standard structure.
     *
     * If null is returned from this method, the default privilege set is used,
     * which is fine for most common usecases.
     *
     * @return array|null
     */
    public function getSupportedPrivilegeSet() {
        return null;
    }
}

