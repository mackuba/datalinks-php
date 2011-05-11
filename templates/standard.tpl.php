<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?PHP echo PAGE_TITLE . (($pageSubtitle)? " - $pageSubtitle" : ""); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="stylesheet" href="stylesheets/datalinks.css" type="text/css" />
	<script type="text/javascript" src="javascripts/niftycube.js"></script>
	<script type="text/javascript" src="javascripts/nifty.js"></script>
<?PHP if ($editMode) { ?>
	<script type="text/javascript" src="javascripts/prototype.js"></script>
	<script type="text/javascript" src="javascripts/scriptaculous.js?load=effects"></script>
	<script type="text/javascript" src="javascripts/datalinks.js"></script>
<?PHP } ?>

	<!--[if IE]>
	<style type="text/css">@import "stylesheets/msie.css";</style>
	<![endif]-->

<?PHP /* set some variables for the javascript */ ?>

<?PHP if ($editMode) { ?>
<script type="text/javascript">
<!--
scriptName = '<?PHP echo $_SERVER["SCRIPT_NAME"]; ?>';
categoryId = '<?PHP echo $categoryId; ?>';
parentId = '<?PHP echo $parentId; ?>';
singleColumnLimit = <?PHP echo SINGLE_COLUMN_LIMIT; ?>;
//-->
</script>
<?PHP } ?>

</head>

<body>

<div id="titleBar"><?PHP echo TOP_TITLE; ?></div>

<p class="lynx" />


<div id="categoryBar">
	<div>
		<span class="header">Category:</span>
		<span class="path">
		<?PHP
		for ($i=0; $i<sizeof($categoryPath); $i++) {
			if ($i > 0) echo " &raquo; ";
			echo "<a " . (($i == sizeof($categoryPath)-1) ? ' id="currentCategory" ' : '') . ' class="' . $categoryPath[$i]['type']
				. '" href="index.php?cat=' . $categoryPath[$i]['id'] . '">' . $categoryPath[$i]['name'] . '</a>';
			if ($categoryPath[$i]['type'] == 'private') echo ' <span class="lynx">[p]</span> ';
			if ($categoryPath[$i]['type'] == 'hidden') echo ' <span class="lynx">[h]</span> ';
		}
		?>
		</span>

		<?PHP if ($editMode) { ?>
		<a href="index.php#" id="editCategoryLink" onclick="this.blur(); resetEditCategoryDialog();
			showCategoryDialog('editCategoryDialog', this, 'editCategoryName'); return false;" class="action">Edit</a>

			<?PHP if ($canDeleteCategory) { ?>
				<a href="index.php#" id="deleteCategoryLink" onclick="this.blur();
					showCategoryDialog('deleteCategoryDialog', this); return false;" class="action">Delete</a>
				<a href="index.php#" id="moveCategoryLink" onclick="this.blur(); resetMoveCategoryDialog();
					showCategoryDialog('moveCategoryDialog', this); return false;" class="action">
				Move</a>
			<?PHP } ?>
		<?PHP } ?>
	</div>
</div>


<table id="subcategories"><tr>
	<td class="left">
		<?PHP
			if ($searchMode) echo '<p class="header">Categories found:</p>';
			else echo '<p class="header">Subcategories:</p>';
		?>
		
		<table>
			<?PHP if ($parentId) echo '<tr><td colspan="2"><ul><li><a class="parent" href="index.php?cat='
				. $parentId . '">Parent category</a></li></ul></td></tr>'; ?>
			<tr><td id="subcategoryTdLeft">
			<?PHP if (sizeof($subcategories) > 0) { ?>
				<ul id="subcategoryListLeft">
	
					<?PHP
						for ($i=0; $i<sizeof($subcategories); $i++) {
							if ($i == $half) echo '</ul></td><td id="subcategoryTdRight"><ul id="subcategoryListRight">';
							echo '<li>';
							switch ($subcategories[$i]['type']) {
								case 'public':
									echo '<a href="index.php?cat=' . $subcategories[$i]['id'] . '">' . $subcategories[$i]['name'] . '</a>';
									break;
								case 'private':
									if ($loggedIn) echo '<a href="index.php?cat=' . $subcategories[$i]['id'] . '" class="private">' . $subcategories[$i]['name']
										. '</a> <span class="lynx">[private]</span>';
									else echo '<span class="private">' . $subcategories[$i]['name'] . '</span> <span class="lynx">[private]</span>';
									break;
								case 'hidden':
									echo '<a href="index.php?cat=' . $subcategories[$i]['id'] . '" class="hidden">' . $subcategories[$i]['name']
										. '</a> <span class="lynx">[hidden]</span>';
							}
							echo ' <span class="amount">(' . $subcategories[$i]['amount'] . ')</span> ';
							if ($subcategories[$i]['parentId']) echo '- <a href="index.php?cat=' . $subcategories[$i]['parentId']
								. '" class="noimage">(' . $subcategories[$i]['parentName'] . ')</a>';
							echo "</li>\n";
						}
					?>
	
				</ul>
			<?PHP }
			if ((sizeof($subcategories) == 0) && ($searchMode)) echo "<ul><li>No results.</li></ul>";
			?>
			</td>
			<?PHP if ($half == -1) echo '<td id="subcategoryTdRight"></td>'; ?>
		</tr>
		</table>

		<?PHP if ($editMode) {
			/* We need to scan the category lists into memory for easier management, but only when the lists are printed */	?>
			<script type="text/javascript">
			<!--
			examineLists();
			//-->
			</script>

			<p class="addButton addCategory">
			<a href="index.php#" onclick="this.blur(); showAddDialog('addCategoryDialog', 'addCategoryName'); return false;">
			Add new subcategory</a>
			</p>
			<div id="addCategoryDialog" style="display: none;">
				<img src="images/triangle.gif" width="15" height="30" alt="" class="dialogTriangle" />
				<p><label for="addCategoryName">Name:</label>
				<input type="text" class="text" id="addCategoryName" maxlength="50" onkeypress="if (wasEnterPressed(event)) addCategory();" /></p>
				<p id="addCategoryInfo" style="display: none;"><span class="throbberLabelInfo">Adding...</span>
				<img id="addCategoryThrobber" class="throbber" src="images/throbber_blue.gif"
					width="16" height="16" alt="" style="display: none;" /></p>
				<p><button id="addCategoryAdd" onclick="addCategory();">Add</button>
				<button id="addCategoryCancel" onclick="showAddDialog('addCategoryDialog');">Cancel</button></p>
			</div>
		<?PHP } ?>
		<br />
	</td>

	<!-- menu on the right - search box and login/logout box -->
	<td class="right">
		<form action="search.php" method="get">
			<div id="searchBox">
				<input type="hidden" name="cat" value="<?PHP echo $categoryId; ?>" />
				<label for="searchPhrase">Search:</label> <input type="text" id="searchPhrase" name="searchPhrase" maxlength="50" />
				<input type="submit" value="Search" class="submit" /> <br />
			</div>
		</form>
		<?PHP if (!$loggedIn) { ?>
		<form action="index.php" method="post">
			<div id="loginBox">
				<?PHP if ($badPassword) echo '<p class="badPassword">Error - password incorrect.</p>'; ?>
				<input type="hidden" name="cat" value="<?PHP echo $categoryId; ?>" />
				<label for="password">Log in:</label> <input type="password" id="password" name="password" />
				<input type="submit" value="Log in" class="submit" /> <br />
			</div>
		<?PHP } else { ?>
		<form action="index.php" method="get">
			<div id="logoutBox">
				<input type="hidden" name="cat" value="<?PHP echo $categoryId; ?>" />
				<input type="hidden" name="logout" value="1" />
				<input type="submit" value="Log out" class="submit" /> <br />
			</div>
		<?PHP } ?>
			<div id="statsBox">
				<p><img src="images/link.gif" width="16" height="16" alt="" /><span id="linkStats"><?PHP echo $nLinks; ?>
					link<?PHP echo ($nLinks == 1 ? "" : "s"); ?></span></p>
				<p><img src="images/folder_blue.gif" width="16" height="16" alt="" /><span id="categoryStats"><?PHP echo $nCategories; ?>
					categor<?PHP echo ($nCategories == 1 ? "y" : "ies"); ?></span></p>
			</div>
		</form>
	</td>
</tr></table>

<hr />

<?PHP
	if ($searchMode) echo '<p class="pages">Links found:</p>';
	else echo '<p class="pages">Links in this category:</p>';
?>

<div id="links">

	<?PHP if ($editMode) { ?>
		<p class="addButton addLink"><a href="index.php#" onclick="this.blur(); showAddDialog('addLinkDialog', 'addLinkURL'); return false;">
		Add new link</a></p>
		<div id="addLinkDialog" style="display: none;">
			<img src="images/triangle.gif" width="15" height="30" alt="" class="dialogTriangle" />
			<p><label for="addLinkURL">URL:</label>
			<input type="text" class="url" maxlength="225" id="addLinkURL" onkeypress="if (wasEnterPressed(event)) $('addLinkName').focus();" /><br />
			<label for="addLinkName">Title:</label>
			<input type="text" class="text" maxlength="175" id="addLinkName" onkeypress="if (wasEnterPressed(event)) $('addLinkDescription').focus();" /><br />
			<label for="addLinkDescription">Description:</label><br />
			<textarea class="description" id="addLinkDescription" rows="3" cols="70"></textarea><br />
			<button id="addLinkAdd" onclick="addLink();">Add link</button>
			<button id="addLinkCancel" onclick="showAddDialog('addLinkDialog');">Cancel</button>
			<span class="throbberLabelInfo" id="addLinkInfo" style="display: none;">Adding...</span>
			<img id="addLinkThrobber" class="throbber" src="images/throbber_blue.gif"
				width="16" height="16" alt="" style="display: none;" /></p>
		</div>
	<?PHP }

	if (sizeof($links) > 0) {
		echo '<ol id="linksOl">';
		for ($i=0; $i<sizeof($links); $i++) {
			echo '<li class="entry" id="link' . $links[$i]['id'] . '">';
			echo '<a href="' . $links[$i]['url'] . '" class="title">' . $links[$i]['title'] . '</a><br />';
			echo '<div class="description">' . $links[$i]['description'] . '</div>';
			echo '<a href="' . $links[$i]['url'] . '" class="url">'
				. (($links[$i]['visibleUrl']) ? $links[$i]['visibleUrl'] : $links[$i]['url']) . '</a>';
			if ($editMode) {
				echo ' - <a href="index.php#" onclick="this.blur(); showLinkDialog(\'editLinkDialog\', this, ' . $links[$i]['id'] . ', \'editLinkURL\');'
					. 'resetEditLinkDialog(); return false;" class="linkact">Edit</a>';
				echo ' - <a href="index.php#" onclick="this.blur(); showLinkDialog(\'deleteLinkDialog\', this, ' . $links[$i]['id'] . '); return false;" '
					. 'class="linkact">Delete</a>';
				echo ' - <a href="index.php#" onclick="this.blur(); resetMoveLinkDialog(); showLinkDialog(\'moveLinkDialog\', this, ' . $links[$i]['id']
					. '); return false;" class="linkact">Move</a>';
			}
			if ($links[$i]['linkCategoryId']) echo '- <a href="index.php?cat=' . $links[$i]['linkCategoryId']
				. '">(' . $links[$i]['linkCategoryName'] . ')</a>';
			echo '<br class="lynx" /><br class="lynx" /></li>';
		}
		echo "</ol>\n";
	}

	if ($searchMode && sizeof($links) == 0) echo '<ol id="linksOl"><li class="entry" style="list-style-type: none;">No results.</li></ol>';
	?>
</div>

<hr />
<div class="bottomBar">Powered by <a href="http://nexus.vrak.pl/datalinks">Datalinks <?PHP echo DATALINKS_VERSION; ?></a></div>

<hr class="lynx" />

<?PHP if ($editMode) { ?>

<div id="deleteLinkDialog" class="popupDialog" style="display: none;">Are you sure?
	<img src="images/triangle_red.gif" width="15" height="30" alt="" class="dialogTriangle" />
	<img src="images/triangleinv_red.gif" width="15" height="30" alt="" class="dialogRightTriangle" style="display: none;" />
	<p><button id="deleteLinkYes" onclick="deleteLink();">Yes</button>
	<button id="deleteLinkNo" onclick="hideDialog('deleteLinkDialog');">No</button>
	<span class="throbberLabelWarning" id="deleteLinkInfo" style="display: none;">Deleting...</span>
	<img id="deleteLinkThrobber" class="throbber" src="images/throbber_red.gif"
		width="16" height="16" alt="" style="display: none;" /></p>
</div>

<div id="editLinkDialog" class="popupDialog" style="display: none;">
	<img src="images/triangle.gif" width="15" height="30" alt="" class="dialogTriangle" />
	<img src="images/triangleinv.gif" width="15" height="30" alt="" class="dialogRightTriangle" style="display: none;" />
	<p><label for="editLinkURL">URL:</label>
	<input type="text" class="url" maxlength="225" id="editLinkURL" onkeypress="if (wasEnterPressed(event)) $('editLinkName').focus();" /><br />
	<label for="editLinkName">Title:</label>
	<input type="text" class="text" maxlength="175" id="editLinkName" onkeypress="if (wasEnterPressed(event)) $('editLinkDescription').focus();" /><br />
	<label for="editLinkDescription">Description:</label><br />
	<textarea class="description" id="editLinkDescription" rows="3" cols="70"></textarea><br />
	<button id="editLinkSave" onclick="editLink();">Save changes</button>
	<button id="editLinkReset" onclick="resetEditLinkDialog();">Reset</button>
	<button id="editLinkCancel" onclick="hideDialog('editLinkDialog');">Cancel</button>
	<span class="throbberLabelInfo" id="editLinkInfo" style="display: none;">Saving...</span>
	<img id="editLinkThrobber" class="throbber" src="images/throbber_blue.gif"
		width="16" height="16" alt="" style="display: none;" /></p>
</div>

<div id="moveLinkDialog" class="popupDialog moveDialog" style="display: none;">
	<img src="images/triangle.gif" width="15" height="30" alt="" class="dialogTriangle" />
	<img src="images/triangleinv.gif" width="15" height="30" alt="" class="dialogRightTriangle" style="display: none;" />
	<p>Select destination category:</p>
	<div id="moveLinkFrame" class="moveFrame"></div>
	<p><button id="moveLinkMove" onclick="moveLink();">Move here</button>
	<button id="moveLinkCancel" onclick="hideDialog('moveLinkDialog');">Cancel</button>
	<span class="throbberLabelInfo" id="moveLinkInfo" style="display: none;">Moving...</span>
	<img id="moveLinkThrobber" class="throbber" src="images/throbber_blue.gif"
		width="16" height="16" alt="" style="display: none;" /></p>
</div>

<div id="editCategoryDialog" class="popupDialog" style="display: none;">
	<img src="images/triangle.gif" width="15" height="30" alt="" class="dialogTriangle" />
	<img src="images/triangleinv.gif" width="15" height="30" alt="" class="dialogRightTriangle" style="display: none;" />
	<p><label for="editCategoryName">Name:</label>
	<input type="text" class="text" id="editCategoryName" maxlength="50" onkeypress="if (wasEnterPressed(event)) editCategory();" /></p>
	<fieldset>
	<legend>Type:</legend>
	<input type="radio" name="editCategoryType" class="editCategoryType" id="typePublic" value="public" />
		<label for="typePublic"> Public <img class="categoryType" src="images/folder_blue.gif"
			width="16" height="16" alt="" /></label><br />
	<input type="radio" name="editCategoryType" class="editCategoryType" id="typePrivate" value="private" />
		<label for="typePrivate"> Private <img class="categoryType" src="images/folder_red.gif"
			width="16" height="16" alt="" /></label><br />
	<input type="radio" name="editCategoryType" class="editCategoryType" id="typeHidden" value="hidden" />
		<label for="typeHidden"> Hidden <img class="categoryType" src="images/folder_grey.gif"
			width="16" height="16" alt="" /></label>
	</fieldset>
	<p><button id="editCategorySave" onclick="editCategory();">Save changes</button>
	<button id="editCategoryReset" onclick="resetEditCategoryDialog();">Reset</button>
	<button id="editCategoryCancel" onclick="hideDialog('editCategoryDialog');
		switchCategoryButton($('editCategoryLink'));">Cancel</button>
	<span class="throbberLabelInfo" id="editCategoryInfo" style="display: none;">Saving...</span>
	<img id="editCategoryThrobber" class="throbber" src="images/throbber_blue.gif" width="16" height="16"
		alt="" style="display: none;" /></p>
</div>

<?PHP if ($canDeleteCategory) { ?>
<div id="deleteCategoryDialog" class="popupDialog" style="display: none;">Are you sure?
	<img src="images/triangle_red.gif" width="15" height="30" alt="" class="dialogTriangle" />
	<img src="images/triangleinv_red.gif" width="15" height="30" alt="" class="dialogRightTriangle" style="display: none;" />
	<p class="radio"> <input type="radio" name="alsoDeleteLinks" id="alsoDeleteLinksNo" value="moveToParent" checked="checked" /> Move links to parent category </p>
	<p class="radio"> <input type="radio" name="alsoDeleteLinks" id="alsoDeleteLinksYes" value="delete" /> Delete links too </p>
	<p>
	<button id="deleteCategoryYes" onclick="deleteCategory();">Yes</button>
	<button id="deleteCategoryNo" onclick="hideDialog('deleteCategoryDialog'); switchCategoryButton($('deleteCategoryLink'));">No</button>
	<span class="throbberLabelWarning" id="deleteCategoryInfo" style="display: none;">Deleting...</span>
	<img id="deleteCategoryThrobber" class="throbber" src="images/throbber_red.gif" width="16" height="16" alt="" style="display: none;" />
	</p>
</div>

<div id="moveCategoryDialog" class="popupDialog moveDialog" style="display: none;">
	<img src="images/triangle.gif" width="15" height="30" alt="" class="dialogTriangle" />
	<img src="images/triangleinv.gif" width="15" height="30" alt="" class="dialogRightTriangle" style="display: none;" />
	<p>Select destination category:</p>
	<div id="moveCategoryFrame" class="moveFrame"></div>
	<p><button id="moveCategoryMove" onclick="moveCategory();">Move here</button>
	<button id="moveCategoryCancel" onclick="hideDialog('moveCategoryDialog');
		switchCategoryButton($('moveCategoryLink'));">Cancel</button>
	<span class="throbberLabelInfo" id="moveCategoryInfo" style="display: none;">Moving...</span>
	<img id="moveCategoryThrobber" class="throbber" src="images/throbber_blue.gif"
		width="16" height="16" alt="" style="display: none;" /></p>
</div>
<?PHP } } ?>

</body>
</html>
