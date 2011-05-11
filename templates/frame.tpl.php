<!-- The category bar -->

<p id="frame<?PHP echo $object; ?>Bar" class="pathBar">
<?PHP
for ($i=0; $i<sizeof($categoryPath); $i++) {
	if ($i > 0) echo " &raquo; ";
	echo '<a class="' . $categoryPath[$i]['type'] . '" href="index.php#" onclick="reloadMoveDialog(' . $categoryPath[$i]['id'] . ', \'' . $object
		. '\'); return false;">' . $categoryPath[$i]['name'] . "</a>\n";
	if ($i == sizeof($categoryPath)-1) echo '<a id="frame' . $object . 'CurrentCategory" style="display: none;">' . $categoryPath[$i]['id'] . "</a>\n";
}
?>
</p>



<!-- List of subcategories -->

<p class="header">Subcategories:</p>

<ul>
	<?PHP
	if ($parentId) echo '<li><a class="parent" href="index.php#" onclick="reloadMoveDialog(' . $parentId . ', \'' . $object
		. '\'); return false;">Parent category</a></li>';
	for ($i=0; $i<sizeof($subcategories); $i++) {
		echo '<li><a href="index.php#" onclick="';
		if ($subcategories[$i]['id'] != $categoryId) echo 'reloadMoveDialog(' . $subcategories[$i]['id'] . ', \'' . $object . '\'); ';
		else if ($object == 'Category') echo "alert('You can\\'t move a category into itself.'); ";
		echo 'return false;" class="' . $subcategories[$i]['type'] . '">' . $subcategories[$i]['name'] . '</a><span class="amount">('
			. $subcategories[$i]['amount'] . ')</span></li>';
	}
	?>
</ul>
