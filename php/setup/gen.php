<?php

require "../Settings.php";
require "$sourcedir/Commons.php";

$get_data = array('iwdb_users', 'iwdb_sittergroup', 'iwdb_nebel', 'iwdb_besonderheiten');

global $db_connection;

$db_connection = mysql_connect($db_host, $db_user, $db_pass) or die("MySQL Connect failed: ".mysql_errno());
mysql_select_db($db_name) or die("MySQL SelectDB failed: ".mysql_errno());
$f = fopen('install.sql', 'wb');

$q = DBQuery("SHOW TABLES", __FILE__, __LINE__);
while($tbl = mysql_fetch_row($q)) {
	if(!beginsWith($tbl[0], $db_prefix))
		continue;
	$qry = DBQuery("SHOW CREATE TABLE ".$tbl[0], __FILE__, __LINE__);
	$row = mysql_fetch_row($qry);
	fwrite($f, str_replace("CREATE TABLE `$db_prefix", "CREATE TABLE `pre_", $row[1]));
	if(!in_array($tbl[0], $ignore_data)) {
		$name = "pre_".substr($tbl[0], strlen($db_prefix));
		fwrite($f, ";\n");
		$qry = DBQuery("SELECT * FROM ".$tbl[0], __FILE__, __LINE__);
		while($row = mysql_fetch_assoc($qry)) {
			$keys = '';
			$vals = '';
			foreach($row as $key => $value) {
				$keys .= $key.', ';
				$vals .= "'".$value."', ";
			}
			$keys = substr($keys, 0, -2);
			$vals = substr($vals, 0, -2);
			fwrite($f, "INSERT INTO ".$name." ({$keys}) VALUES ({$vals});\n");
		}
	}
	fwrite($f, "\n\n");
}
fclose($f);

?>