<?PHP
/****************************************************************************
 *   Copyright by Jakub Suder                                               *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 ****************************************************************************/

// title of the page visible on the title bar of a window (<title> tag) 
define("PAGE_TITLE", "");

// title of the page visible in the bar on the top of the page
define("TOP_TITLE", "");

// database connection data
$dbuser = "";
$dbpass = "";
$dbname = "";
$dbhost = "";

define("DB_TABLE_PREFIX", "");

// if the number of subcategories is higher than this limit, they will be printed in
// two columns, otherwise there will be just one column
define("SINGLE_COLUMN_LIMIT", 5);

// the password that you use to log in to Datalinks to add new categories and links
define("PASSWORD", "");
?>
