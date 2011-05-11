<?php
/* My modifications: function db_result(), db_execute() */

$db_config = array(
	user => $dbuser,
	password => $dbpass,
	database => $dbname,
	host => $dbhost
);

////////////////////////////////////////////
// Simple DataBase Abstraction (SDBA)
////////////////////////////////////////////
// Email: akrymski@gmail.com
// Website: http://www.identicli.com/sdba
////////////////////////////////////////////
// License: LGPL
////////////////////////////////////////////
			
$db_c = NULL; // connection handle
$db_r = NULL; // result set handle

function db_prepare($sql, $values=NULL) {
	global $_DB_PREPARE_VALUES;
	if ($values == NULL) $values = $GLOBALS;
	$_DB_PREPARE_VALUES = $values;
	$p = '|@(\w+)|';
	return preg_replace_callback($p, 'db__cb', $sql);
}

function db__cb($matches) {
	global $_DB_PREPARE_VALUES;
	return db_sqlval($_DB_PREPARE_VALUES[$matches[1]]);
}

function db_sqlval($value) {
	global $db_c;
	if ($db_c == NULL) db_open();
	if (is_array($value)) {
		$in = array();
		foreach ($value as $v) $in[] = db_sqlval($v);
		return implode(',', $in);
	}
	if (is_numeric($value)) return $value;
	if ($value == NULL) return 'NULL';
	return ("'" . mysql_real_escape_string($value) . "'");
}

function db_scalar($sql) {
	global $db_c;
	if ($db_c == NULL) db_open();
	$r = mysql_query($sql);
	if ((!$r) || (mysql_num_rows($r) <= 0)) return FALSE;
	$v = mysql_result($r, 0, 0);
	mysql_free_result($r);
	return $v;
}

function db_execute($sql) {
	global $db_c;
	if ($db_c == NULL) db_open();
	$r = mysql_query($sql);
	if (!$r) return FALSE;
	return TRUE;
}

// $sql  : statement to execute
// $page : if paging is needed, the page number to get
// $size : if page is specified, rows per page
// RETURNS:
// 1. UPDATE or DELETE: number of rows affected
// 2. INSERT: last insert ID
// 3. SELECT: number of rows returned
// 4. SELECT with paging: array(total, number of pages the query returns)
// 
function db_query($sql, $page=FALSE, $size=10) {
	global $db_c, $db_r;
	if ($db_c == NULL) db_open();

	if ($page !== FALSE) { // do paging
		$sql = 'SELECT SQL_CALC_FOUND_ROWS' . substr($sql, 6);
		$from = ($page - 1) * $size;
		$sql .= " LIMIT $from,$size";
	}

	$r = mysql_query($sql);
	if (!$r) return FALSE;

	$c = strtoupper($sql{0});
	if ($c == 'S') {
		$db_r = $r;
		if ($page === FALSE) return mysql_num_rows($r);
		$total = db_scalar('SELECT FOUND_ROWS();');
		return array($total,ceil($total / $size));
	}
	// if its an insert query return autoincrement number
	if ($c == 'I') return mysql_insert_id();
	// if its an update or delete return number of rows affected
	if (($c == 'U') || ($c == 'D')) return mysql_affected_rows();
	// for other queries
	return $r;
}

function db_read($r=FALSE) {
	global $db_r;
	if ($r === FALSE) $r = $db_r;
	$row = mysql_fetch_row($r);
	if ($row === FALSE) mysql_free_result($r);
	return $row;
}

function db_open() {
	global $db_c, $db_config;
	extract($db_config);
	$db_c = mysql_connect($host, $user, $password) or db_error(1);
	mysql_select_db($database) or db_error(2);
}

function db_close() {
	global $db_c;
	mysql_close();
	$db_c = NULL;
}

function db_error($type=NULL) {
	$e = mysql_error();
	if ($type == 1) die("Error connecting to database server: $e");
	if ($type == 2) die("Error selecting database: $e");
	if ($type == NULL) die("Error executing query: $e");
}

function db_result() {
	global $db_r;
	return $db_r;
}

?>
