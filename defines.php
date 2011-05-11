<?PHP
// -------------------------------------------------------
// Copyright (c) 2007 Jakub Suder <jakub.suder@gmail.com>
// Licensed under MIT license
// -------------------------------------------------------

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
