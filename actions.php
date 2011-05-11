<?PHP
/****************************************************************************
 *   Copyright by Jakub Suder                                               *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 ****************************************************************************/

/* Auxilliary function for most actions, which updates all "ancestors" of a given category with the specified UPDATE sql query. */
function updateAncestors($chlinks, $chcats, $chvlinks, $chvcats, $categoryId) {
	$sql = "UPDATE " . DB_CATEGORIES_TABLE . " SET type = type";
	if ($chlinks != "") $sql .= ", subtree_links = subtree_links $chlinks";
	if ($chcats != "") $sql .= ", subtree_categories = subtree_categories $chcats";
	if ($chvlinks != "") $sql .= ", subtree_visible_links = subtree_visible_links $chvlinks";
	if ($chvcats != "") $sql .= ", subtree_visible_categories = subtree_visible_categories $chvcats";
	$sql .= " WHERE id = @categoryId";

	$hidden = false;

	while ($categoryId != null) {
		$query = db_prepare($sql, get_defined_vars());
		db_execute($query);
		$query = db_prepare("SELECT type, parent FROM " . DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
		db_query($query);
		list($type, $categoryId) = db_read();
		if (!$hidden && ($type != TYPE_PUBLIC)) {
			$sql = "UPDATE " . DB_CATEGORIES_TABLE . " SET type = type";
			if ($chlinks != "") $sql .= ", subtree_links = subtree_links $chlinks";
			if ($chcats != "") $sql .= ", subtree_categories = subtree_categories $chcats";
			$sql .= " WHERE id = @categoryId";
			$hidden = true;
		}
	}
}

/* Adds a category with specified name to the database as a subcategory of category 'categoryId'. */
function addCategory($name, $categoryId) { 
	$inv = isHidingElements($categoryId);
	$sql = db_prepare("INSERT INTO " . DB_CATEGORIES_TABLE . " (name, parent, invisible, subtree_categories, subtree_links, subtree_visible_links, "
		. "subtree_visible_categories) VALUES (@name, @categoryId, @inv, 0, 0, 0, 0)", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		updateAncestors("", "+ 1", "", "+ 1", $categoryId);
		echo "OK $res";
	} else {
		echo "Database insert failed";
	}
}

/* Adds a link with specified name, url and description to the database as a member of category 'categoryId'. */
function addLink($name, $url, $description, $categoryId) {
	$sql = db_prepare("INSERT INTO " . DB_LINKS_TABLE . " (title, url, category, description) "
		. "VALUES (@name, @url, @categoryId, @description)", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		updateAncestors("+ 1", "", "+ 1", "", $categoryId);
		echo "OK $res";
	} else {
		echo "Database insert failed";
	}
}

/* Deletes category 'categoryId' and all its subcategories from the database. */
function deleteCategory($categoryId, $deleteLinks) {

	function deleteOne($parent, $id, $deleteLinks) {
		$sql = db_prepare("SELECT id FROM " . DB_CATEGORIES_TABLE . " WHERE parent = @id", get_defined_vars());
		$sub = db_query($sql);
		$res = db_result();
		if ($sub === false) {
			return false;
		}
		
		for ($i=0; $i<$sub; $i++) {
			list($subid) = db_read($res);
			$ok = deleteOne($id, $subid, $deleteLinks);
			if (!$ok) return false;
		}

		if ($deleteLinks) {
			$sql = db_prepare("DELETE FROM " . DB_LINKS_TABLE . " WHERE category = @id", get_defined_vars());
		} else {
			$sql = db_prepare("UPDATE " . DB_LINKS_TABLE . " SET category = @parent WHERE category = @id", get_defined_vars());
		}
		$res = db_query($sql);
		if ($res === false) {
			return false;
		}

		$sql = db_prepare("DELETE FROM " . DB_CATEGORIES_TABLE . " WHERE id = @id", get_defined_vars());
		$res = db_query($sql);
		if ($res === false) {
			return false;
		}
		
		return true;
	}

	$sql = db_prepare("SELECT parent, type, subtree_links, subtree_visible_links, subtree_categories, subtree_visible_categories FROM "
		. DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
	db_query($sql);
	list($parent, $type, $subLinks, $subVLinks, $subCats, $subVCats) = db_read();
	$ok = deleteOne($parent, $categoryId, $deleteLinks);

	if ($ok) {
		if ($deleteLinks) {
			updateAncestors("- $subLinks", "- $subCats - 1", (($type == TYPE_PUBLIC) ? "- $subVLinks" : ""),
				(($type == TYPE_PUBLIC) ? "- $subVCats " : "") . (($type != TYPE_HIDDEN) ? "- 1" : ""), $parent);
		} else {
			updateAncestors("", "- $subCats - 1", (($type == TYPE_PUBLIC) ? "+ $subLinks - $subVLinks" : "+ $subLinks"),
				(($type == TYPE_PUBLIC) ? "- $subVCats " : "") . (($type != TYPE_HIDDEN) ? "- 1" : ""), $parent);
		}
		echo "OK";
	} else {
		echo "Database update failed";
	}
}

/* Deletes link 'linkId' from the database. */
function deleteLink($linkId) {
	if (!checkLink($linkId)) {
		echo "Link not found.";
		return;
	}

	$sql = db_prepare("SELECT category FROM " . DB_LINKS_TABLE . " WHERE id = @linkId", get_defined_vars());
	$categoryId = db_scalar($sql);

	$sql = db_prepare("DELETE FROM " . DB_LINKS_TABLE . " WHERE id = @linkId", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		updateAncestors("- 1", "", "- 1", "", $categoryId);
		echo "OK";
	} else {
		echo "Database update failed";
	}
}

/* Updates category 'categoryId' in the database. */
function editCategory($categoryId, $name, $type) {
	$sql = db_prepare("SELECT parent, type, invisible, subtree_visible_links, subtree_visible_categories FROM "
		. DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
	db_query($sql);
	list($parent, $old_type, $invisible, $vlinks, $vcats) = db_read();
	$inv = ($invisible || ($type != TYPE_PUBLIC)) ? 1 : 0;

	$sql = db_prepare("UPDATE " . DB_CATEGORIES_TABLE . " SET name = @name, type = @type WHERE id = @categoryId", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		// public -> private: hide all children
		// public -> hidden: hide all children and this one
		// private -> public: show all children
		// private -> hidden: hide this one
		// hidden -> public: show all children and this one
		// hidden -> private: show this one
		$chthis = ($old_type == TYPE_HIDDEN) ? "+ 1" : (($type == TYPE_HIDDEN) ? "- 1" : "");

		if ($old_type == TYPE_PUBLIC) {
			$chlinks = "- $vlinks";
			$chcats = "- $vcats";
		} else if ($type == TYPE_PUBLIC) {
			$chlinks = "+ $vlinks";
			$chcats = "+ $vcats";
		} else {
			$chlinks = "";
			$chcats = "";
		}

		updateAncestors("", "", $chlinks, "$chcats $chthis", $parent);

		if (($chlinks == "") || ($invisible)) {
			echo "OK";
		} else {
			$ok = updateInvisibility($categoryId, $inv);
			if ($ok) {
				echo "OK";
			} else {
				echo "Database update failed";
			}
		}
	} else {
		echo "Database update failed";
	}
}

/* Updates link 'linkId' in the database. */
function editLink($linkId, $name, $url, $description) {
	if (!checkLink($linkId)) {
		echo "Link not found.";
		return;
	}

	$sql = db_prepare("UPDATE " . DB_LINKS_TABLE . " SET title = @name, url = @url, description = @description "
		. "WHERE id = @linkId", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		echo "OK";
	} else {
		echo "Database update failed";
	}
}

/* Moves category 'categoryId' from its parent category to the category 'destination'. */
function moveCategory($categoryId, $destination) {
	if (!checkCategory($destination)) {
		echo "Destination category not found.";
		return;
	}

	$categoryPath = createCategoryPath($destination);
	foreach ($categoryPath as $category) {
		if ($category["id"] == $categoryId) {
			echo "Can't move a category into one of its children.";
			return;
		}
	}

	$sql = db_prepare("SELECT type, parent, subtree_links, subtree_visible_links, subtree_categories, subtree_visible_categories FROM "
		. DB_CATEGORIES_TABLE . " WHERE id = @categoryId", get_defined_vars());
	db_query($sql);
	list($type, $parent, $subLinks, $subVLinks, $subCats, $subVCats) = db_read();

	$sql = db_prepare("UPDATE " . DB_CATEGORIES_TABLE . " SET parent = @destination WHERE id = @categoryId", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		$inv = isHidingElements($destination);
		$sql = db_prepare("UPDATE " . DB_CATEGORIES_TABLE . " SET invisible = @inv WHERE id = @categoryId", get_defined_vars());
		$res = db_query($sql);

		if ($res !== false) {
			$inv = isHidingElements($categoryId);
			$ok = updateInvisibility($categoryId, $inv);
			if ($ok) {
				updateAncestors("- $subLinks", "- $subCats - 1", (($type == TYPE_PUBLIC) ? "- $subVLinks" : ""),
					(($type == TYPE_PUBLIC) ? "- $subVCats " : "") . (($type != TYPE_HIDDEN) ? "- 1" : ""), $parent);
				updateAncestors("+ $subLinks", "+ $subCats + 1", (($type == TYPE_PUBLIC) ? "+ $subVLinks" : ""),
					(($type == TYPE_PUBLIC) ? "+ $subVCats " : "") . (($type != TYPE_HIDDEN) ? "+ 1" : ""), $destination);

				echo "OK";
			} else {
				echo "Database update failed";
			}
		} else {
			echo "Database update failed";
		}
	} else {
		echo "Database update failed";
	}
}

/* Moves link 'linkId' from its parent category to the category 'destination'. */
function moveLink($linkId, $destination) {
	if (!checkLink($linkId)) {
		echo "Link not found.";
		return;
	}

	if (!checkCategory($destination)) {
		echo "Destination category not found.";
		return;
	}

	$sql = db_prepare("SELECT category FROM " . DB_LINKS_TABLE . " WHERE id = @linkId", get_defined_vars());
	$categoryId = db_scalar($sql);
	$was_inv = isHidingElements($categoryId);

	$sql = db_prepare("UPDATE " . DB_LINKS_TABLE . " SET category = @destination WHERE id = @linkId", get_defined_vars());
	$res = db_query($sql);
	if ($res !== false) {
		updateAncestors("- 1", "", "- 1", "", $categoryId);
		updateAncestors("+ 1", "", "+ 1", "", $destination);
		echo "OK";
	} else {
		echo "Database update failed";
	}
}

/* Prints the contents of an internal frame of moveCategoryDialog or moveLinkDialog. */
function showDestinationFrame($categoryId, $show, $object) {
	// prepare the category path bar
	$categoryPath = createCategoryPath($show);
	$parentId = $categoryPath[count($categoryPath) - 1]["parent"];

	// prepare the subcategory list
	$sql = db_prepare("SELECT id, name, type FROM " . DB_CATEGORIES_TABLE . " WHERE parent = @show ORDER BY name");
	$rows = db_query($sql);
	$rowlist = db_result();

	if ($rows === false) {
		echo "Database query failed";
		die();
	}

	$subcategories = array();
	for ($i=0; $i<$rows; $i++) {
		list($id, $name, $type) = db_read($rowlist);
		$amount = getLinksInCategory($id, true);
		$type = getCategoryTypeName($type);
		$name = htmlspecialchars($name, ENT_NOQUOTES);
		array_push($subcategories, compact("id", "name", "amount", "type"));
	}

	// print the template
	echo "OK";
	require_once("templates/frame.tpl.php");
}

?>