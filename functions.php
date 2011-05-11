<?PHP
// -------------------------------------------------------
// Copyright (c) 2007 Jakub Suder <jakub.suder@gmail.com>
// Licensed under MIT license
// -------------------------------------------------------

/* Adds 'http://' to the URL if it hasn't got a protocol part. */
function addHttp($url) {
	if ((strpos($url, "http://") !== 0)
		&& (strpos($url, "ftp://") !== 0)
		&& (strpos($url, "https://") !== 0)) {

		$url = "http://$url";
	}
	return $url;
}

/* Returns the number of links in the specified category and its child categories. If $countHidden
is true, then only links visible for an unregistered user are counted. */
function getLinksInCategory($categoryId, $countHidden) {
	$visible = ($countHidden ? "" : "visible_");
	$sql = db_prepare("SELECT subtree_{$visible}links FROM " . DB_CATEGORIES_TABLE
		. " WHERE id = @categoryId", get_defined_vars());
	return db_scalar($sql);
}

/* Returns the number of categories in the specified category and its child categories. If $countHidden
is true, then only categories visible for an unregistered user are counted. */
function getSubcategoriesInCategory($categoryId, $countHidden) {
	$visible = ($countHidden ? "" : "visible_");
	$sql = db_prepare("SELECT subtree_{$visible}categories FROM " . DB_CATEGORIES_TABLE
		. " WHERE id = @categoryId", get_defined_vars());
	return db_scalar($sql);
}

/* Returns category ID if it is set in the request and is correct, INVALID_CATEGORY if there is no such category,
	or UNSPECIFIED_CATEGORY if the variable is not set. */
function getCategoryId() {
	if (isset($_REQUEST['cat'])) {
		$categoryId = $_REQUEST['cat'];
	} else {
		return UNSPECIFIED_CATEGORY;
	}
	
	$sql = db_prepare("SELECT id FROM " . DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
	$rows = db_query($sql);
	if ($rows != 1) {
		return INVALID_CATEGORY;
	}

	return $categoryId;
}

/* Creates a list of all "ancestors" of the specified category, from root to the category's parent,
	with the category itself at the end of the list. */
function createCategoryPath($cat) {
	$categories = array();
	while ($cat != null) {
		$sql = db_prepare("SELECT id, name, parent, type FROM " . DB_CATEGORIES_TABLE . " WHERE id = @cat", get_defined_vars());
		db_query($sql);
		list($id, $name, $parent, $type) = db_read();
		$type = getCategoryTypeName($type);
		array_unshift($categories, compact("id", "name", "parent", "type"));
		$cat = $parent;
	}
	return $categories;
}

/* Returns the name of the category type. */
function getCategoryTypeName($type) {
	switch ($type) {
		case TYPE_PUBLIC: return "public";
		case TYPE_PRIVATE: return "private";
		default: return "hidden";
	}
}

/* Returns the ID of the category type. */
function getCategoryTypeValue($type) {
	switch ($type) {
		case 'public': return TYPE_PUBLIC;
		case 'private': return TYPE_PRIVATE;
		default: return TYPE_HIDDEN;
	}
}

/* Checks if there is a link with ID $linkId in the database. */
function checkLink($linkId) {
	$sql = db_prepare("SELECT id FROM " . DB_LINKS_TABLE . " WHERE id = @linkId", get_defined_vars());
	$rows = db_query($sql);
	if ($rows === false) {
		echo "Database query failed";
		die();
	}
	return ($rows == 1);
}

/* Checks if there is a category with ID $categoryId in the database. */
function checkCategory($categoryId) {
	$sql = db_prepare("SELECT id FROM " . DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
	$rows = db_query($sql);
	if ($rows === false) {
		echo "Database query failed";
		die();
	}
	return ($rows == 1);
}

/* Tells if this category's subelements should be "invisible", that is, if either this category or one of its ancestors
	has type 'private' or 'hidden'. */
function isHidingElements($categoryId) {
	$sql = db_prepare("SELECT type, invisible FROM " . DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
	$res = db_query($sql);
	if (!$res) {
		echo "Database query failed";
		die();
	}
	list($type, $invisible) = db_read();
	$inv = ($invisible || ($type != TYPE_PUBLIC)) ? 1 : 0;
	return $inv;
}

/* Updates the "invisible" status of all categories in this category and its subcategories. */
function updateInvisibility($id, $inv) {
	$sql = db_prepare("UPDATE " . DB_CATEGORIES_TABLE . " SET invisible = @inv WHERE parent = @id", get_defined_vars());
	$res = db_query($sql);
	if ($res === false) {
		return false;
	}
	
	$sql = db_prepare("SELECT id, type FROM " . DB_CATEGORIES_TABLE . " WHERE parent = @id", get_defined_vars());
	$sub = db_query($sql);
	$res = db_result();
	if ($sub === false) {
		return false;
	}
	
	for ($i=0; $i<$sub; $i++) {
		list($subid, $subtype) = db_read($res);
		$ok = updateInvisibility($subid, ($inv || ($subtype != TYPE_PUBLIC)) ? 1 : 0);
		if (!$ok) return false;
	}
	
	return true;
}

/* For debugging */
function log2file($text) {
	$file = fopen("log.txt", "a");
	fwrite($file, $text . "\n");
	fclose($file);
}

?>
