<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?PHP echo PAGE_TITLE . (($pageSubtitle) ? " - $pageSubtitle" : ""); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="stylesheet" href="stylesheets/datalinks.css" type="text/css" />
	<script type="text/javascript" src="javascripts/niftycube.js"></script>
	<script type="text/javascript" src="javascripts/nifty.js"></script>
</head>

<body>

<div id="titleBar"><?PHP echo TOP_TITLE; ?></div>

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
	</div>
</div>

<table id="subcategories"><tr>
	<td class="left">
		<br />
		<table>
			<?PHP if ($parentId) echo '<tr><td colspan="2"><ul><li><a class="parent" href="index.php?cat='
				. $parentId . '">' . $parentLinkName . '</a></li></ul></td></tr>'; ?>
		</table>
		<p class="errorMessage"><?PHP echo $errorMessage; ?></p>
	</td>
	<td class="right">
		<form action="search.php" method="get">
			<div id="searchBox">
				<input type="hidden" name="cat" value="<?PHP echo $categoryId; ?>" />
				Search: <input type="text" id="searchPhrase" name="searchPhrase" />
				<input type="submit" value="Search" class="submit" />
				<br />
			</div>
		</form>
		<?PHP if (!$loggedIn) { ?>
		<form action="index.php" method="post">
			<div id="loginBox">
				<?PHP if ($badPassword) echo '<p class="badPassword">Error - password incorrect.</p>'; ?>
				<input type="hidden" name="cat" value="<?PHP echo $categoryId; ?>" />
				Log in: <input type="password" id="password" name="password" />
				<input type="submit" value="Log in" class="submit" />
				<br />
			</div>
		</form>
		<?PHP } else { ?>
		<form action="index.php" method="get">
			<div id="logoutBox">
				<input type="hidden" name="cat" value="<?PHP echo $categoryId; ?>" />
				<input type="hidden" name="logout" value="1" />
				<input type="submit" value="Log out" class="submit" />
				<br />
			</div>
		</form>
		<?PHP } ?>
	</td>
</tr></table>

</body>
</html>
