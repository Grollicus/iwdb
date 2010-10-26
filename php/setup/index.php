<?php
define('dddfd', true);

if(!isset($_GET['do'])) {
	echo '<html><head><title>Installation</title></head>
	<body>
		<h2>Installation der StonedSheepDB</h2>';
	
	if(!file_exists("../Settings.php") || @file_get_contents("../Settings.php") === false) {
		die('<font style="color: #FF0000; font-weight: bold;">Konfigurationsdatei fehlt oder Berechtigungen falsch gesetzt!</font></body></html>');
	}
	
	include "../Settings.php";

	if(!file_exists($sourcedir."/Commons.php") || @file_get_contents($sourcedir."/Commons.php") === false) {
		die('<font style="color: #FF0000; font-weight: bold;">Sourcedir nicht richtig gesetzt oder Berechtigungen falsch!</font></body></html>');
	}
	if(!file_exists($themedir."/default/template.index.php") || @file_get_contents($themedir."/default/template.index.php") === false) {
		die('<font style="color: #FF0000; font-weight: bold;">Themedir nicht richtig gesetzt oder Berechtigungen falsch!</font></body></html>');
	}
	
	
	$db = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
	if($db === false) {
		die('<font style="color: #FF0000; font-weight: bold;">MySQL-Verbindung fehlgeschlagen: '.mysqli_connect_error().'</font></body></html>');
	}
	
	echo '<a href="', $_SERVER['PHP_SELF'], '?do=1">Installieren</a>';
	echo '</body></html>';
	
	exit();
}

require "../Settings.php";
require "$sourcedir/Commons.php";


$file = @file_get_contents("install.sql");

if($file === false) {
	die('<font style="color: #FF0000; font-weight: bold;">install.sql fehlt oder Bereichtigungen falsch!</font></body></html>');
}

$file = str_replace(array("CREATE TABLE `pre_", "INSERT INTO pre_"), array("CREATE TABLE `{$db_prefix}", "INSERT INTO {$db_prefix}"), $file);

$db = mysqli_connect($db_host, $db_user, $db_pass, $db_name) or die(mysql_error());

if (mysqli_multi_query($db, $file)) {
    do {
        if ($result = mysqli_use_result($db)) {
            mysqli_free_result($result);
        } else {
        	echo "Query fehlgeschlagen!<br />";
        }
    } while (mysqli_next_result($db));
}
?>