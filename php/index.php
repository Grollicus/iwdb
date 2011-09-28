<?php

//Note: LoneStar

define("dddfd","std");
error_reporting(E_ALL|E_STRICT);
ini_set('variables_order','GP');
date_default_timezone_set('Europe/Berlin');
Header('Content-Type: text/html; charset=utf-8');
Header('Accept-Charset: UTF-8');

$starttime = microtime(true);
$link_parameters = array();

//Load default config settings
require(dirname(__FILE__)."/Settings.default.php");
//Read the User Configuration
require(dirname(__FILE__)."/Settings.php");
if($debug > 0) {
	ini_set("display_errors", "1");
	$sql_log = array();
}

//Mainteance-Mode = 2 => Exit
if($mainteance == 2)
	die('<h2>'.$mainteance_title.'</h2>'.$mainteanceBody);

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

if(mt_rand(0, 99) == 0) {
	DoMaintenance();
}

//Lets Execute the Main-Function, got the Name from main()
call_user_func(Main());

StopExecution();

function Main()
{
	global $sourcedir, $ID_MEMBER, $action, $content, $mainteance, $user, $debug, $pre;
	
//If there will be something else to do then sesison_start(), it'll be done in LoadSession()	
	LoadSession();

//Load user-specific stuff
	LoadUser();

//Check if the User is logged in
	CheckLogin();


//### Comment for automatically adding new _!!User initialisation!!_ functions
//	}

//Now, lets see what to do now.
	//At first, all the Possibilities, as '$_REQUEST['action']' => array('includefile','Function to call')
	$actionArr = array(
	'errors' => array('admin/errors.php', 'ListErrors'),
	'flotten' => array('sitter/view.php', 'SitterFeindlFlottenUebersicht'),
	'help' => array('core/main.php', 'HelpView'),
	'help_page' => array('core/main.php', 'HelpPage'),
	'hs' => array('core/highscore.php', 'HighScore'),
	'ircmask' => array('core/irc.php', 'IrcMask'),
	'index' => array('core/main.php', 'Index'),
	'iwlogin' => array('sitter/login.php', 'MainLogin'),
	'kbformat' => array('core/main.php', 'KbFormat'),
	'logout' => array('core/LoginOut.php','Logout'),
	'mysql' => array('admin/mysql.php', 'MySQLClient'),
	'newscanex' => array('newscan/main.php', 'NewscanEx'),
	'raids' => array('kbs/raids.php', 'RaidOverview'),
	'ressuserlist' => array('ress/list.php', 'RessUserList'),
	'settingsex' => array('core/UserSettingsEx.php', 'UserSettingsEx'),
	'scans_view' => array('scans/view.php', 'ScansView'), //scheint nicht verwendet zu werden
	'shipdata' => array('admin/shipdata.php', 'EditShipData'),
	'sitter_flotten' => array('sitter/view.php', 'SitterFeindlFlottenUebersicht'),
	'sitter_login' => array('sitter/login.php', 'SitterLogin'),
	'sitter_dologin' => array('sitter/login.php', 'SitterDoLogin'),
	'sitter_edit' => array('sitter/edit.php', 'SitterEdit'),
	'sitter_view' => array('sitter/view.php', 'SitterView'),
	'sitter_history' => array('sitter/history.php', 'SitterHistory'),
	'sitter_list' => array('sitter/list.php', 'SitterList'),
	'sitter_own' => array('sitter/view.php', 'SitterOwn'),
	'sitterutil_job' => array('sitter/login.php', 'SitterUtilJob'),
	'sitterutil_log' => array('sitter/login.php', 'SitterUtilLog'),
	'sitterutil_newscan' => array('sitter/login.php', 'SitterUtilNewscan'),
	'sitterutil_ress' => array('sitter/login.php', 'SitterUtilRess'),
	'sitterutil_trade' => array('sitter/login.php', 'SitterUtilTrade'),
	'speedlog' => array('admin/speedlog.php', 'Speedlogview'),
	'techtree' => array('techtree/view.php', 'TechtreeView'),
	'techtree_missing' => array('techtree/view.php', 'TechtreeListMissing'),
	'texts' => array('admin/texts.php', 'TextsList'),
	'texts_edit' => array('admin/texts.php', 'TextsEdit'),
	'trade_list' => array('trade/list.php', 'TradeList'),
	'transporte' => array('ress/transporte.php', 'TransporteList'),
	'uni_allyat' => array ('uni/ally.php','UniAllyAt'),
	'uni_allyoverview' => array ('uni/ally.php','UniAllyOverview'),
	'uni_map' => array('uni/view.php', 'ShowUniMap'),
	'uni_view' => array('uni/viewex.php', 'ViewFilteredUniEx'),
	'uni_whosat' => array ('uni/ally.php','UniWhosIn'),
	'util' => array('admin/util.php', 'AdminUtils'),
	'unknownAction' => array('core/main.php', 'UnknownAction'),
	'useradmin' => array('admin/Usermanagement.php', 'Useradmin_Main'),
	
	//### Comment for automatically adding new _!!Actions!!_ here
	);
	
	//defaultaction - if none exists or we dont know this action..
	if (!isset($_REQUEST['action']))
		$action = 'index';
	elseif (!isset($actionArr[$_REQUEST['action']]))
		$action = 'unknownAction';
	else //view what the user wants uto
		$action = $_REQUEST['action'];
	
//if we're in mainteance-mode and only admins should log in - but the user isnt an admin - die
	if($mainteance == 1 && !$user['isAdmin'] && !isset($_REQUEST['login'])) {
		require_once($sourcedir.'/core/LoginOut.php');
		Login();
		StopExecution();
	}
	
	//tell the template were we are atm - maybe it'll need this
	$content['action'] = $action;
	SetLinkParam('action', $action);
	$content['debug_mode'] = $debug > 1 || $user['isAdmin'] ? $debug : 0;
	$content['sitter_job_cnt'] = DBQueryOne("SELECT count(*) FROM {$pre}sitter AS sitter WHERE sitter.done=0 AND followUpTo=0 AND sitter.time <= ".time(), __FILE__, __LINE__);
	$content['users_online'] = array();
	
	$q = DBQuery("SELECT visibleName FROM {$pre}users WHERE lastactive>".(time()-300), __FILE__, __LINE__);
	while($row = mysql_fetch_row($q))
		$content['users_online'][] = $row[0];
	
	//Actually do
	require_once($sourcedir.'/'.$actionArr[$action][0]);
	return $actionArr[$action][1];
}

function StopExecution()
{
	global $starttime, $pre, $action, $debug;
	if($debug >= 1) {
		//Ok, thats it - Execution done  - just a bit for logging
		DBQuery("INSERT INTO {$pre}speedlog (action, sub, runtime) VALUES (
		'".EscapeDB($action)."',
		'".(isset($_GET['sub']) ? EscapeDB(Param('sub')) : '')."',
		".((microtime(true)-$starttime)*1000000).")", __FILE__, __LINE__);
	}
	exit();
}

?>
