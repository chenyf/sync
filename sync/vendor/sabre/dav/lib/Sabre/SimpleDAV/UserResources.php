<?php

namespace Sabre\SimpleDAV;

use Sabre\DAV;
use Sabre\DAVACL;

/**
 * The UserCalenders class contains all resources associated to one user
 *
 * @copyright Copyright (C) 2007-2013 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class UserResources implements DAV\IExtendedCollection, DAVACL\IACL {

    /**
     * CalDAV backend
     *
     * @var Sabre\CalDAV\Backend\BackendInterface
     */
    protected $resouceBackend;

    /**
     * Principal information
     *
     * @var array
     */
    protected $principalInfo;


    private static $defaultUri = 'default';

    private static $defaultProps = array(
        '{DAV:}displayname' => 'Default calendar',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'Default calendar'
    );

    /**
     * Constructor
     *
     * @param Backend\BackendInterface $resouceBackend
     * @param mixed $userUri
     */
    public function __construct(Backend\BackendInterface $resouceBackend, $principalInfo) {
        $this->resouceBackend = $resouceBackend;
        $this->principalInfo = $principalInfo;
    }

    /**
     * Returns the name of this object
     *
     * @return string
     */
    public function getName() {
        list(,$name) = DAV\URLUtil::splitPath($this->principalInfo['uri']);
        return $name;
    }

    /**
     * Updates the name of this object
     *
     * @param string $name
     * @return void
     */
    public function setName($name) {
        throw new DAV\Exception\Forbidden();
    }

    /**
     * Deletes this object
     *
     * @return void
     */
    public function delete() {
        throw new DAV\Exception\Forbidden();
    }

    /**
     * Returns the last modification date
     *
     * @return int
     */
    public function getLastModified() {
        return null;
    }

    /**
     * Creates a new file under this object.
     *
     * This is currently not allowed
     *
     * @param string $filename
     * @param resource $data
     * @return void
     */
    public function createFile($filename, $data=null) {
        throw new DAV\Exception\MethodNotAllowed('Creating new files in this collection is not supported');
    }

    /**
     * Creates a new directory under this object.
     *
     * This is currently not allowed.
     *
     * @param string $filename
     * @return void
     */
    public function createDirectory($filename) {

        throw new DAV\Exception\MethodNotAllowed('Creating new collections in this collection is not supported');

    }

    /**
     * Returns a single calendar, by name
     *
     * @param string $name
     * @todo needs optimizing
     * @return Calendar
     */
    public function getChild($name) {

        foreach($this->getChildren() as $child) {
            if ($name==$child->getName())
                return $child;

        }
        throw new DAV\Exception\NotFound('Calendar with name \'' . $name . '\' could not be found');

    }

    /**
     * Checks if a calendar exists.
     *
     * @param string $name
     * @todo needs optimizing
     * @return bool
     */
    public function childExists($name) {

        foreach($this->getChildren() as $child) {
            if ($name==$child->getName())
                return true;

        }
        return false;

    }

    /**
     * Returns a list of resources
     *
     * @return array
     */
    public function getChildren() {
        $resources = $this->resouceBackend->getResoourcesForUser($this->principalInfo['uri']);
        $objs = array();
        if(empty($resources)){
            $this->resouceBackend->createResource($this->principalInfo['uri'], self::$defaultUri, self::$defaultProps);
            $resources = $this->resouceBackend->getResourcesForUser($this->principalInfo['uri']);
        }
        foreach($resources as $resource) {
            $objs[] = new Resource($this->resouceBackend, $resource);
        }
        $objs[] = new Schedule\Outbox($this->principalInfo['uri']);
        return $objs;
    }

    /**
     * Creates a new calendar
     *
     * @param string $name
     * @param array $resourceType
     * @param array $properties
     * @return void
     */
    public function createExtendedCollection($name, array $resourceType, array $properties) {
        $isCalendar = false;
        foreach($resourceType as $rt) {
            switch ($rt) {
                case '{DAV:}collection' :
                case '{http://resourceserver.org/ns/}shared-owner' :
                    // ignore
                    break;
                case '{urn:ietf:params:xml:ns:caldav}calendar' :
                    $isCalendar = true;
                    break;
                default :
                    throw new DAV\Exception\InvalidResourceType('Unknown resourceType: ' . $rt);
            }
        }
        if (!$isCalendar) {
            throw new DAV\Exception\InvalidResourceType('You can only create resources in this collection');
        }
        $this->resouceBackend->createResource($this->principalInfo['uri'], $name, $properties);
    }

    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner
     *
     * @return string|null
     */
    public function getOwner() {
        return $this->principalInfo['uri'];
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

        return array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalInfo['uri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->principalInfo['uri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalInfo['uri'] . '/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->principalInfo['uri'] . '/calendar-proxy-write',
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->principalInfo['uri'] . '/calendar-proxy-read',
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
        throw new DAV\Exception\MethodNotAllowed('Changing ACL is not yet supported');
    }

    /**
     * Returns the list of supported privileges for this node.
     *
     * The returned data structure is a list of nested privileges.
     * See Sabre\DAVACL\Plugin::getDefaultSupportedPrivilegeSet for a simple
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

    /**
     * This method is called when a user replied to a request to share.
     *
     * This method should return the url of the newly created calendar if the
     * share was accepted.
     *
     * @param string href The sharee who is replying (often a mailto: address)
     * @param int status One of the SharingPlugin::STATUS_* constants
     * @param string $calendarUri The url to the calendar thats being shared
     * @param string $inReplyTo The unique id this message is a response to
     * @param string $summary A description of the reply
     * @return null|string
     */
    public function shareReply($href, $status, $calendarUri, $inReplyTo, $summary = null) {
        throw new DAV\Exception\NotImplemented('Sharing support is not implemented by this backend.');
    }
}
