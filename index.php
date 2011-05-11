<?PHP
// -------------------------------------------------------
// Copyright (c) 2007 Jakub Suder <jakub.suder@gmail.com>
// Licensed under MIT license
// -------------------------------------------------------

require_once("common.php");

// check if install is cleaned
if (file_exists("install")) {
	$h2 = "Error";
	$msg = "For security reasons you have to delete the \"install\" directory before you start using your Datalinks.";
	$path = "install/";
	$npath = "javascripts/";
	$class = "red";
	require_once("install/finished.tpl.php");
	die();
}

// check authorization

if ($_REQUEST['password']) {
	if (md5($_REQUEST['password']) == md5(PASSWORD)) {
		$loggedIn = true;
		$_SESSION[PASSWORD_VAR] = md5(PASSWORD);
	} else {
		$badPassword = true;
		$_SESSION[PASSWORD_VAR] = "";
		unset($_SESSION[PASSWORD_VAR]);
		$loggedIn = false;
	}
} else if ($_REQUEST['logout']) {
	$_SESSION[PASSWORD_VAR] = "";
	unset($_SESSION[PASSWORD_VAR]);
	$loggedIn = false;
} else if ($_SESSION[PASSWORD_VAR] == md5(PASSWORD)) {
	$loggedIn = true;
} else {
	$loggedIn = false;
}

$editMode = $loggedIn;
$categoryId = getCategoryId();


// if it's an action called using AJAX, execute it

if ($_REQUEST['action']) {
	if (!$loggedIn) {
		echo "You are not allowed to modify the Datalinks database.";
		die();
	}

	if (($categoryId == UNSPECIFIED_CATEGORY) && ($_REQUEST['action'] != 'editLink')
			&& ($_REQUEST['action'] != 'deleteLink') && ($_REQUEST['action'] != 'moveLink')) {
		echo "Category ID parameter is missing.";
		die();
	} else if ($categoryId == INVALID_CATEGORY) {
		echo "Invalid category ID parameter.";
		die();
	}

	switch ($_REQUEST['action']) {
		case 'addCategory':
			$name = stripslashes($_REQUEST['name']);
			if ($name == "") {
				echo "Category name not set.";
			} else {
				addCategory($name, $categoryId);
			}
			break;

		case 'addLink':
			$name = stripslashes($_REQUEST['name']);
			$url = $_REQUEST['url'];
			$description = stripslashes($_REQUEST['desc']);
			if ($name == "" || $url == "") {
				echo "Link name or url not set.";
			} else {
				$url = addHttp($url);
				addLink($name, $url, $description, $categoryId);
			}
			break;

		case 'deleteCategory':
			$deleteLinks = $_REQUEST['deleteLinks'];
			if ($categoryId == ROOT_CATEGORY) {
				echo "Can't delete root category.";
			} else {
				deleteCategory($categoryId, $deleteLinks);
			}
			break;

		case 'deleteLink':
			$linkId = $_REQUEST['link'];
			if ($linkId == "") {
				echo "Link ID not set.";
			} else {
				deleteLink($linkId);
			}
			break;

		case 'editCategory':
			$name = stripslashes($_REQUEST['name']);
			$type = stripslashes($_REQUEST['type']);
			if ($name == "") {
				echo "Category name or type not set.";
			} else if ($type != 'hidden' && $type != 'public' && $type != 'private') {
				echo "Invalid category type: $type.";
			} else {
				editCategory($categoryId, $name, getCategoryTypeValue($type));
			}
			break;

		case 'editLink':
			$name = stripslashes($_REQUEST['name']);
			$url = $_REQUEST['url'];
			$description = stripslashes($_REQUEST['desc']);
			$linkId = $_REQUEST['link'];
			if ($name == "" || $url == "" || $linkId == "") {
				echo "Link name, url or ID not set.";
			} else {
				$url = addHttp($url);
				editLink($linkId, $name, $url, $description);
			}
			break;
	
		case 'moveCategory':
			$dest = stripslashes($_REQUEST['dest']);
			if ($dest == "") {
				echo "Destination category not set.";
			} else if ($categoryId == ROOT_CATEGORY) {
				echo "Can't move root category.";
			} else {
				moveCategory($categoryId, $dest);
			}
			break;

		case 'moveLink':
			$linkId = $_REQUEST['link'];
			$dest = stripslashes($_REQUEST['dest']);
			if ($dest == "" || $linkId == "") {
				echo "Destination category or link ID not set.";
			} else {
				moveLink($linkId, $dest);
			}
			break;

		case 'showCategoryFrame':
			$show = $_REQUEST['show'];
			if ($show == "") {
				echo "Show parameter not set.";
			} else {
				showDestinationFrame($categoryId, $show, "Category");
			}
			break;

		case 'showLinkFrame':
			$show = $_REQUEST['show'];
			if ($show == "") {
				echo "Show parameter not set.";
			} else {
				showDestinationFrame($categoryId, $show, "Link");
			}
			break;

		default:
			echo "Unknown action '{$_REQUEST['action']}'.";
			break;
	}

	die();
}


if ($categoryId == UNSPECIFIED_CATEGORY) {
	$categoryId = ROOT_CATEGORY;
}

$canDeleteCategory = ($categoryId != ROOT_CATEGORY) && $loggedIn;


// check if the category isn't hidden or invalid

$sql = db_prepare("SELECT type, invisible FROM " . DB_CATEGORIES_TABLE . " WHERE id = @categoryId");
$result = db_query($sql);
if ($result != 0) {
	list($ctype, $invisible) = db_read();
}

if (($categoryId == ROOT_CATEGORY) && (!$loggedIn) && ($ctype != TYPE_PUBLIC)) {
	// print a special error page if the root category is not public
	
	$pageSubtitle = "";
	$categoryPath = createCategoryPath(ROOT_CATEGORY);
	$errorMessage = "This is a private Datalinks directory. Please log in to access the database.";
	require_once("templates/error.tpl.php");
	die();
}

if (($categoryId == INVALID_CATEGORY) || (!$loggedIn && ($ctype == TYPE_HIDDEN || $invisible))) {
	// print the error page
	
	$pageSubtitle = "Error";
	$categoryPath = createCategoryPath(ROOT_CATEGORY);
	$parentLinkName = "Back to main category";
	$errorMessage = "Error: the requested category is missing - either you have entered an invalid category ID, "
		. "or the category has been deleted.";
	require_once("templates/error.tpl.php");
	die();
}


// prepare the category path bar

$categoryPath = createCategoryPath($categoryId);
$parentId = $categoryPath[count($categoryPath) - 1]["parent"];
$pageSubtitle = ($categoryId == ROOT_CATEGORY) ? "" : $categoryPath[count($categoryPath) - 1]["name"];


// check if the category isn't private

if (!$loggedIn && ($ctype == TYPE_PRIVATE)) {
	// print the error page
	$parentLinkName = "Parent category";
	$errorMessage = "Error: to view the requested category you have to be logged in.";
	require_once("templates/error.tpl.php");
	die();
}

if (!$loggedIn) {
	// enable HTTP page caching if the user is not logged in
	header('Cache-Control: private, pre-check=0, post-check=0, max-age=14400');
}


// prepare the subcategory list

$hide = (!$loggedIn) ? (" AND type != " . TYPE_HIDDEN . " ") : "";
$sql = db_prepare("SELECT id, name, type FROM " . DB_CATEGORIES_TABLE . " WHERE parent = @categoryId " . $hide . " ORDER BY name");
$rows = db_query($sql);
$rowlist = db_result();

if ($rows > SINGLE_COLUMN_LIMIT) {
	$half = $rows - (int)($rows / 2);
} else {
	$half = -1;
}

$subcategories = array();
for ($i=0; $i<$rows; $i++) {
	list($id, $name, $type) = db_read($rowlist);
	$amount = (($type == TYPE_PRIVATE) && (!$loggedIn)) ? "-" : getLinksInCategory($id, $loggedIn);
	$type = getCategoryTypeName($type);
	$name = htmlspecialchars($name, ENT_NOQUOTES);
	array_push($subcategories, compact("id", "name", "amount", "type"));
}


// prepare the link list

$links = array();
$sql = db_prepare("SELECT id, url, title, description FROM " . DB_LINKS_TABLE . " WHERE category = @categoryId ORDER BY title");
$rows = db_query($sql);

for ($i=0; $i<$rows; $i++) {
	list($id, $url, $title, $description) = db_read();
	$title = htmlspecialchars($title, ENT_NOQUOTES);
	$url = htmlspecialchars($url, ENT_NOQUOTES);
	$description = htmlspecialchars($description, ENT_NOQUOTES);
	array_push($links, compact("id", "url", "title", "description"));
}

// prepare statistics
$nLinks = getLinksInCategory(ROOT_CATEGORY, $loggedIn);
$nCategories = getSubcategoriesInCategory(ROOT_CATEGORY, $loggedIn);

// print the template
require_once('templates/standard.tpl.php');
?>
