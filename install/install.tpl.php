<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Datalinks <?PHP echo DATALINKS_VERSION; ?> - Installation</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="stylesheet" href="../stylesheets/datalinks.css" type="text/css" />
	<link rel="stylesheet" href="install.css" type="text/css" />
	<script type="text/javascript" src="niftycube.js"></script>

	<script type="text/javascript">
	<!--
		window.onload = function() {Nifty("div#installBox");}
	-->
	</script>
	<?PHP function quot($s) {return str_replace('"', '&quot;', $s);} ?>
</head>

<body>
	<div id="titleBar">Datalinks <?PHP echo DATALINKS_VERSION; ?> - Installation</div>

	<div id="installBox">
		<h2>Enter installation data:</h2>
		<form action="install.php" method="post">
			<table>
				<col class="left" /> <col class="right" />

				<?PHP if ($errorMessage) echo '<tr><td colspan="2" class="error">' . $errorMessage . '</td></tr>'; ?>

				<tr><td>Database server address:</td>
				<td><input type="text" name="dbHost" value="<?PHP echo quot($dbhost); ?>" size="25" /></td></tr>
	
				<tr><td>Database name:</td>
				<td><input type="text" name="dbName" value="<?PHP echo quot($dbname); ?>" size="20" /></td></tr>
	
				<tr><td>Database user login:</td>
				<td><input type="text" name="dbUser" value="<?PHP echo quot($dbuser); ?>" size="20" /></td></tr>
	
				<tr><td>Database user password:</td>
				<td><input type="password" name="dbPass" value="" size="20" /></td></tr>
	
				<tr><td>Table prefix:</td>
				<td><input type="text" name="prefix" value="<?PHP echo quot($prefix); ?>" size="20" /><br />
				<p class="fieldDescription">Your Datalinks installation will use tables &lt;prefix&gt;_categories
					and &lt;prefix&gt;_links.</p>
				</td></tr>
	
				<tr><td colspan="2" class="line"><br /></td></tr>
	
				<tr><td>Page title:</td>
				<td><input type="text" name="pageTitle" value="<?PHP echo quot($pageTitle); ?>" size="40" /><br />
				<p class="fieldDescription">The title that will be visible in the title bar of the browser.</p>
				</td></tr>
	
				<tr><td>Top bar title:</td>
				<td><input type="text" name="topTitle" value="<?PHP echo quot($topTitle); ?>" size="40" /><br />
				<p class="fieldDescription">The title that will be visible in the blue bar on the top of the page.</p>
				</td></tr>
	
				<tr><td>Datalinks admin password:</td>
				<td><input type="password" name="password" value="" size="20" /><br />
				<p class="fieldDescription">The password that will be required to make changes in your Datalinks.</p>
				</td></tr>
	
				<tr><td><input type="hidden" name="visited" value="1" /></td>
				<td class="submit"><input type="submit" value="Install..." /></td></tr>
			</table>
		</form>
	</div>

</body>
</html>
