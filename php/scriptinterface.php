<?php

//Note: LoneStar

define("dddfd","script");
error_reporting(E_ALL|E_STRICT);
ini_set('variables_order','GP');
date_default_timezone_set('Europe/Berlin');
Header('Content-Type: text/html; charset=utf-8');
Header('Accept-Charset: UTF-8');

$starttime = microtime(true);

//Read the User Configuration
require(dirname(__FILE__)."/Settings.php");

//Mainteance-Mode = 2 => Exit
if($mainteance == 2)
	die('Mainteance doesnt allow scripts..');

//Get the Commonly used Functions
require ($sourcedir."/Commons.php");

//Functions for Loading stuff
require ($sourcedir."/Loading.php");

//Setup the Database-Connection
if ($db_persistent == 1)
	$db_connection = @mysql_pconnect($db_host,$db_user,$db_pass);
else
	$db_connection = @mysql_connect($db_host,$db_user,$db_pass);

if (!$db_connection || !@mysql_select_db($db_name,$db_connection))
	LogError(mysql_error(), __FILE__, __LINE__, ERROR_CRITICAL);

//Bring the database-prefix to the right content
$pre = '`'.$db_name.'`.'.$db_prefix;

DBQuery("SET NAMES utf8", __FILE__, __LINE__);

set_error_handler("ErrorHandler");

//Lets Execute the Main-Function, got its Name from Main()
call_user_func(Main());

StopExecution();

function Main()
{
	global $sourcedir, $ID_MEMBER, $action, $content, $mainteance, $user;

//The Session ID must be passed as an arg called sid to scriptinterface.php
	if(!isset($_REQUEST['sid']))
		exit();
	session_id($_REQUEST['sid']);
	
//If there will be something else to do then sessison_start(), it'll be done in LoadSession()	
	LoadSession();

//Load user-specific stuff
	LoadUser();

	if (!CheckLogin())
		exit();
		
//### Comment for automatically adding new _!!User initialisation!!_ functions


//if we're in mainteance-mode and only admins should log in - if the user isnt an admin - die
	if($mainteance == 1 && !$user['isAdmin']) {
		exit();
	}

//Now, lets see what to do now.
	//At first, all the Possibilities, as '$_REQUEST['action']' => array('includefile','Function to call')
	$actionArr = array(
		//'kb_print' => array('kbs/kb_print.php', 'KBPrint'),
		'sittergebs' => array('sitter/own.php', 'SitterScriptListGebs'),
		'sitterstufen' => array('sitter/own.php', 'SitterScriptListGebTimes'),
		'sitter_forschungen' => array('sitter/edit.php', 'SitterScriptListForschungen'),
		'sitter_gebs' => array('sitter/edit.php', 'SitterScriptListGebs'),
		'sitter_planis' => array('sitter/edit.php', 'SitterScriptListPlanis'),
		'sitter_stufen' => array('sitter/edit.php', 'SitterScriptListStufen'),
		'techtree' => array('techtree/script.php', 'TechtreeScriptQuery'),
		'uni_details' => array('uni/view.php', 'UniDetails'), 
	);
	
	//defaultaction - if none exists or we dont know this action..
	if (!isset($_REQUEST['a']) || !isset($actionArr[$_REQUEST['a']]))
		StopExecution();
	else //view what the user wants us to
		$action = $_REQUEST['a'];
	
	//tell the template were we are atm - maybe it'll neex this
	$content['action'] = $action;
	
	//Actually do
	require($sourcedir.'/'.$actionArr[$action][0]);
	return $actionArr[$action][1];
}

function StopExecution()
{
	global $starttime, $pre, $action, $debug;
	if($debug >= 1) {
		//Ok, thats it - Execution done  - just a bit for logging
		DBQuery("INSERT INTO {$pre}speedlog (action, script, sub, runtime) VALUES (
		'".EscapeDB($action)."',
		1,
		'".(isset($_GET['sub']) ? EscapeDB(Param('sub')) : '')."',
		".((microtime(true)-$starttime)*1000000).")", __FILE__, __LINE__);
	}
exit();
}

?>
