<?PHP
// -------------------------------------------------------
// Copyright (c) 2007 Jakub Suder <jakub.suder@gmail.com>
// Licensed under MIT license
// -------------------------------------------------------

require_once("../defines.php");

// read variables from the form
$dbname = stripslashes($_POST['dbName']);
$dbhost = stripslashes($_POST['dbHost']);
$dbuser = stripslashes($_POST['dbUser']);
$dbpass = stripslashes($_POST['dbPass']);
$password = stripslashes($_POST['password']);
$prefix = stripslashes($_POST['prefix']);
$pageTitle = stripslashes($_POST['pageTitle']);
$topTitle = stripslashes($_POST['topTitle']);
$visited = $_POST['visited'];

function dquot($s) {return str_replace("\"", "\\\"", $s);}

// check if nothing is missing
if ($visited && ($dbname == "" || $dbhost == "" || $dbpass == "" || $dbuser == ""
		|| $prefix == "" || $pageTitle == "" || $topTitle == "" || $password == "")) {
	$errorMessage = "All fields have to be filled.";

} else if ($visited) {
	// all fields filled, let's install

	require_once("../lib/sdba.inc");
	db_execute("SET CHARACTER SET utf8");
	db_execute("SET NAMES utf8");

	// create table for links
	$res = db_execute("CREATE TABLE " . $prefix . DB_LINKS_SUFFIX . " ("
		. "id int(11) NOT NULL auto_increment, "
		. "title varchar(250), "
		. "url tinytext NOT NULL, "
		. "category int(11) NOT NULL, "
		. "description text, "
		. "PRIMARY KEY (id) "
		. ") CHARACTER SET utf8 COLLATE utf8_unicode_ci;");

	if ($res === false) {
		$errorMessage = "Error: Can't create table " . $prefix . DB_LINKS_SUFFIX . ".";
	} else {

		// create table for categories
		$res = db_execute("CREATE TABLE " . $prefix . DB_CATEGORIES_SUFFIX . " ("
			. "id int(11) NOT NULL auto_increment, "
			. "name varchar(100) NOT NULL, "
			. "parent int(11) default NULL, "
			. "type int(1) default '0', "
			. "invisible int(1) default '0', "
			. "subtree_links int default '0', "
			. "subtree_categories int default '0', "
			. "subtree_visible_links int default '0', "
			. "subtree_visible_categories int default '0', "
			. "PRIMARY KEY (id) "
			. ") CHARACTER SET utf8 COLLATE utf8_unicode_ci;");
		if ($res === false) {
			$errorMessage = "Error: Can't create table " . $prefix . DB_CATEGORIES_SUFFIX . ".";
		} else {

			// create root category
			$res = db_execute("INSERT INTO " . $prefix . DB_CATEGORIES_SUFFIX . " (id, name) VALUES (1, 'Datalinks');");
			if ($res === false) {
				$errorMessage = "Error: Can't initialize table " . $prefix . DB_CATEGORIES_SUFFIX . ".";
			} else {

				// update config.php file
				$config = file_get_contents("../config.php");
				$config = str_replace('define("PAGE_TITLE", "");', 'define("PAGE_TITLE", "' . dquot($pageTitle) . '");', $config);
				$config = str_replace('define("TOP_TITLE", "");', 'define("TOP_TITLE", "' . dquot($topTitle) . '");', $config);
				$config = str_replace('define("DB_TABLE_PREFIX", "");', 'define("DB_TABLE_PREFIX", "' . dquot($prefix) . '");', $config);
				$config = str_replace('define("PASSWORD", "");', 'define("PASSWORD", base64_decode("' . base64_encode($password) . '"));', $config);
				$config = str_replace('$dbuser = "";', '$dbuser = "' . dquot($dbuser) . '";', $config);
				$config = str_replace('$dbpass = "";', '$dbpass = base64_decode("' . base64_encode($dbpass) . '");', $config);
				$config = str_replace('$dbname = "";', '$dbname = "' . dquot($dbname) . '";', $config);
				$config = str_replace('$dbhost = "";', '$dbhost = "' . dquot($dbhost) . '";', $config);
				
				$file = @fopen("../config.php", "w");
				if ($file === FALSE) {
					$errorMessage = "Error: config.php file is not writable. Change its permissions (just for the installation) and try again.";
					db_execute("DROP TABLE " . $prefix . DB_CATEGORIES_SUFFIX);
					db_execute("DROP TABLE " . $prefix . DB_LINKS_SUFFIX);
				} else {
					fwrite($file, $config);
					fclose($file);
	
					// show a message
					$h2 = "Installation successful!";
					$msg = 'You can now delete the install/ directory and go to your <a href="../index.php">Datalinks</a>.';
					require_once("finished.tpl.php");
					die();
				}
			}
		}
	}
}

// if the form hasn't been submitted, or not all fields were filled, print it

if (!$visited) {
	// default values
	$prefix = "datalinks";
	$topTitle = $pageTitle = "My Datalinks";
}

// output the template
require_once("install.tpl.php");
?>
