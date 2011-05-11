<?PHP
/****************************************************************************
 *   Copyright by Jakub Suder                                               *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 ****************************************************************************/

require_once("common.php");

// check if install is cleaned
if (file_exists("install")) {
	$h2 = "Error";
	$msg = "For security reasons you have to delete the \"install\" directory before you start using your Datalinks.";
	$path = "install/";
	$npath = "scripts/";
	$class = "red";
	require_once("install/finished.tpl.php");
	die();
}

// check authorization
if ($_SESSION[PASSWORD_VAR] == md5(PASSWORD)) {
	$loggedIn = true;
} else {
	$loggedIn = false;
}

$searchMode = true;
$categoryId = getCategoryId();
$searchPhrase = $_REQUEST['searchPhrase'];
$sqlSearchPhrase = "%$searchPhrase%";




// prepare the category path bar

$categoryPath = createCategoryPath($categoryId);


// prepare the category list

$hide = (!$loggedIn) ? (" AND cat.type != " . TYPE_HIDDEN . " AND cat.invisible != 1 ") : "";
$sql = db_prepare("SELECT cat.id, cat.name, cat.type, parent.id, parent.name FROM " . DB_CATEGORIES_TABLE . " cat LEFT JOIN "
	. DB_CATEGORIES_TABLE . " parent ON (cat.parent = parent.id) WHERE cat.name LIKE @sqlSearchPhrase " . $hide . " ORDER BY cat.name");
$rows = db_query($sql);
$rowlist = db_result();

if ($rows > SINGLE_COLUMN_LIMIT) {
	$half = $rows - (int)($rows / 2);
} else {
	$half = -1;
}

$subcategories = array();
for ($i=0; $i<$rows; $i++) {
	list($id, $name, $type, $parentId, $parentName) = db_read($rowlist);
	$amount = (($type == TYPE_PRIVATE) && (!$loggedIn)) ? "-" : getLinksInCategory($id, $loggedIn);
	$type = getCategoryTypeName($type);
	if ($searchPhrase != "") {
		$name = htmlspecialchars($name, ENT_NOQUOTES);
		$parentName = htmlspecialchars($parentName, ENT_NOQUOTES);
		$name = eregi_replace('(' . quotemeta($searchPhrase) . ')', "<em>\\1</em>", $name);
	}
	array_push($subcategories, compact("id", "name", "amount", "type", "parentId", "parentName"));
}


// prepare the link list

$hide = (!$loggedIn) ? (" AND category.invisible != 1 AND category.type = " . TYPE_PUBLIC) : "";
$links = array();
$sql = db_prepare("SELECT link.id, link.url, link.title, link.description, category.id, category.name, category.type, category.invisible FROM "
	. DB_LINKS_TABLE . " link LEFT JOIN " . DB_CATEGORIES_TABLE . " category ON (link.category = category.id) "
	. "WHERE (title LIKE @sqlSearchPhrase OR url LIKE @sqlSearchPhrase OR description LIKE @sqlSearchPhrase) "
	. $hide . " ORDER BY link.title");
$rows = db_query($sql);

for ($i=0; $i<$rows; $i++) {
	list($id, $url, $title, $description, $linkCategoryId, $linkCategoryName) = db_read();
	if ($searchPhrase != "") {
		$title = htmlspecialchars($title, ENT_NOQUOTES);
		$url = htmlspecialchars($url, ENT_NOQUOTES);
		$description = htmlspecialchars($description, ENT_NOQUOTES);
		$linkCategoryName = htmlspecialchars($linkCategoryName, ENT_NOQUOTES);
		$visibleUrl = eregi_replace('(' . quotemeta($searchPhrase) . ')', "<em>\\1</em>", $url);
		$title = eregi_replace('(' . quotemeta($searchPhrase) . ')', "<em>\\1</em>", $title);
		$description = eregi_replace('(' . quotemeta($searchPhrase) . ')', "<em>\\1</em>", $description);
	}
	
	array_push($links, compact("id", "url", "visibleUrl", "title", "description", "linkCategoryId", "linkCategoryName"));
}

// prepare statistics
$nLinks = getLinksInCategory(ROOT_CATEGORY, $loggedIn);
$nCategories = getSubcategoriesInCategory(ROOT_CATEGORY, $loggedIn);

// print the template
require_once('templates/standard.tpl.php');
$pageSubtitle = "Search: $searchPhrase";
?>
