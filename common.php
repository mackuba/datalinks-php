<?PHP
// -------------------------------------------------------
// Copyright (c) 2007 Jakub Suder <jakub.suder@gmail.com>
// Licensed under MIT license
// -------------------------------------------------------

// initialize
@ini_set('session.gc_maxlifetime', 3600 * 8);
session_name("Datalinks" . getmyuid());
session_start();
require_once("config.php");
require_once("defines.php");
require_once("functions.php");
require_once("actions.php");

// load external libraries
require_once("lib/sdba.inc");

// check if datalinks is installed
if ($dbuser == "") {
	header('Location: install/install.php');
	die();
}

// initialize database
db_execute("SET CHARACTER SET utf8");
db_execute("SET NAMES utf8");
?>
