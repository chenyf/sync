<?php
##############################################################################
#
#	Copyright notice
#
#	(c) 2014 Jérôme Schneider <mail@jeromeschneider.fr>
#	All rights reserved
#
#	http://baikal-server.com
#
#	This script is part of the Baïkal Server project. The Baïkal
#	Server project is free software; you can redistribute it
#	and/or modify it under the terms of the GNU General Public
#	License as published by the Free Software Foundation; either
#	version 2 of the License, or (at your option) any later version.
#
#	The GNU General Public License can be found at
#	http://www.gnu.org/copyleft/gpl.html.
#
#	This script is distributed in the hope that it will be useful,
#	but WITHOUT ANY WARRANTY; without even the implied warranty of
#	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
#	GNU General Public License for more details.
#
#	This copyright notice MUST APPEAR in all copies of the script!
#
##############################################################################

##############################################################################
# System configuration
# Should not be changed, unless YNWYD
#
# RULES
#	0. All folder pathes *must* be suffixed by "/"
#	1. All URIs *must* be suffixed by "/" if pointing to a folder
#

# PATH to SabreDAV
define("BAIKAL_PATH_SABREDAV", PROJECT_PATH_FRAMEWORKS . "SabreDAV/lib/Sabre/");

# If you change this value, you'll have to re-generate passwords for all your users
define("BAIKAL_AUTH_REALM", 'BaikalDAV');

# Should begin and end with a "/"
define("BAIKAL_CARD_BASEURI", PROJECT_BASEURI . "carddav/");

# Should begin and end with a "/"
define("BAIKAL_CAL_BASEURI", PROJECT_BASEURI . "caldav/");

define("BAIKAL_WEBDAV_BASEURI", PROJECT_BASEURI . "webdav/");

# Define path to Baïkal Database SQLite file
define("PROJECT_SQLITE_FILE", PROJECT_PATH_SPECIFIC . "db/db.sqlite");

# MySQL > Use MySQL instead of SQLite ?
define("PROJECT_DB_MYSQL", TRUE);

# MySQL > Host, including ':portnumber' if port is not the default one (3306)
define("PROJECT_DB_MYSQL_HOST", '127.0.0.1');

# MySQL > Database name
define("PROJECT_DB_MYSQL_DBNAME", 'sync');

# MySQL > Username
define("PROJECT_DB_MYSQL_USERNAME", '*');

# MySQL > Password
define("PROJECT_DB_MYSQL_PASSWORD", '*');

# MySQL > Host, including ':portnumber' if port is not the default one (3306)
define("PROJECT_DB_MYSQL_SYNC_HOST", '127.0.0.1');

# MySQL > Database name
define("PROJECT_DB_MYSQL_SYNC_DBNAME", 'sync');

# MySQL > Username
define("PROJECT_DB_MYSQL_SYNC_USERNAME", '*');

# MySQL > Password
define("PROJECT_DB_MYSQL_SYNC_PASSWORD", '*');

# A random 32 bytes key that will be used to encrypt data
define("BAIKAL_ENCRYPTION_KEY", '4be738c7f6425103ead63fc5fce853f4');

# The currently configured Baïkal version
define("BAIKAL_CONFIGURED_VERSION", '0.2.7');

define("SYNC_PUSH_URL", 'http://push.scloud.letv.com/sync/message');
define("SYNC_PUSH_APPID", 'id_edd5e5960c8f4197820d0da2fdf43213');
define("SYNC_PUSH_NOTE_ENABLE", TRUE);
define("SYNC_PUSH_NOTE_SENDID", "note");
define("SYNC_PUSH_BOOKMARK_ENABLE", TRUE);
define("SYNC_PUSH_BOOKMARK_SENDID", "bookmark");
define("SYNC_PUSH_CAL_ENABLE", TRUE);
define("SYNC_PUSH_CAL_SENDID", "calendar");
define("SYNC_PUSH_CARD_ENABLE", TRUE);
define("SYNC_PUSH_CARD_SENDID", "contact");

