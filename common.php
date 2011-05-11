<?PHP
/****************************************************************************
 *   Copyright by Jakub Suder                                               *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 ****************************************************************************/

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
