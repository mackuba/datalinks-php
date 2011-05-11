<?PHP
/****************************************************************************
 *   Copyright by Jakub Suder                                               *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 ****************************************************************************/

require_once("config.php");

define("DB_CATEGORIES_SUFFIX", "_categories");
define("DB_LINKS_SUFFIX", "_links");

define("DB_CATEGORIES_TABLE", DB_TABLE_PREFIX . DB_CATEGORIES_SUFFIX);
define("DB_LINKS_TABLE", DB_TABLE_PREFIX . DB_LINKS_SUFFIX);

define("ROOT_CATEGORY", 1);
define("INVALID_CATEGORY", -1);
define("UNSPECIFIED_CATEGORY", -2);

define("TYPE_PUBLIC", 0);
define("TYPE_PRIVATE", 1);
define("TYPE_HIDDEN", 2);

define("PASSWORD_VAR", DB_TABLE_PREFIX . "Password");
define("DATALINKS_VERSION", "2.0");
?>
