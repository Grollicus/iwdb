<?php
if(!defined('dddfd'))
	die("Hacking attempt!");

/* 
 * ressource DBQuery( string query, __FILE__, __LINE__ );
 * 	*Queries the Database and handles errors if occur
 * 
 * mixed DBQueryOne( string query, string filename, int line, bool assoc = false )
 * 	*Returns an Array if the query's result has more than one element, else it returns the element
 * void DBError ( string query, __FILE__, __LINE__ );
 * 	*Reacts on Errors when Querying sth from the Database
 *  *Automatically called by DBQuery
 * 	*Exits at the Moment, but shall be able to catch errors and react on them later on
 * bool Checklogin ()
 * 	*Checks if the user is logged in and returns true if yes, if he is now logging in it returns false
 * 	*If the User isnt logged in and isnt logging it, it exit()s
 * void Redirect( $url )
 * 	*Redirects to $url and exits
 * string FormatTime( int secs )
 *  *Returns a formated relative time (Like 3 days, 3 h, 2 min, 1s)
 * string FormatDate( int unixtimestamp )
 * 	*Returns a formatted string representing the unixtimestamp
 * int ParseTime( string time )
 * 	*Returns the a UNIX-Timestamp equal to time 
 * void LogError( string description, string filename, int line, boolean critical )
 * 	*Logs an error into DB/File
 * bool QueryIWDBUtil( string action, array requestArgs, string $response )
 * 	*Queries the external Util using the given action and additional arguments given in requestArgs
 * 	*Returns a String, containing the response of the external Util 
 * string EscapeO ( string toEscape )
 * 	*Escapes toEscape so it can be safely printed to a XHTML-Website
 * string EscapeDB ( string toEscape )
 * 	*Escapes toEscape so it can be safely used in a string entity in a database query 
 * string GetText2( string name )
 * 	*Gets a predefined Text message from the database
 * 
 */

//namespace Source;

/**
 * @name DBQuery
 * @param String $query SQL Query to execute
 * @param String $file Filename where DBQuery is called (used to be __FILE__)
 * @param Integer $line Line in $file where DBQuery is called (used to be __LINE__)
 * @param Integer $flags Special flags to change the behavior of DBQuery: 1 = don't die on error, 2 = allow UNION
 * @return FALSE if failed or a RessourceID identifying the Result Set
 */
function DBQuery($query,$file,$line, $flags = 0)
{
	global $db_connection, $use_transactions, $debug, $sql_log;
	#Let's do some Stuff for preventing execution of malicious SQL 
		//We don't use UNION, but it's useful for hacking
		if (strpos($query,"UNION") !== false && (($flags & 2) == 0))
			die("Hacking attempt");
			
		//We don't set passwords
		if (strpos($query,"set password") !== false)
			die("Hacking attempt");
		
	  //Trying to slow us down?
	  if (strpos($query,"benchmark") !== false)
			die("Hacking attempt");
	#Handling transactions...
		//At first, lets look if the var exists - it not, hell, what to do then?!
		if(!isset($use_transactions))
			$use_transactions = false;
			
		//Is the Query a Transaction? If yes, remember...
		if (strpos($query,"START TRANSACTION") !== false)
			$use_transactions = true;
		
		//Maybe we're using transactions and want to stop it now...
		if ($use_transactions && (strpos($query,"COMMIT") !== false || strpos($query,"ROLLBACK") !== false))
			$use_transactions = false;
		
	#Ok, the Query seems to be ok - then lets ask the Database...
		$q = mysql_query($query,$db_connection);
	#some Logging
		if($debug >= 1) {
			$sql_log[] = $query;
		}
		if ($q === false && (($flags & 1) == 0))
			DBError($query,$file,$line);
		return $q;
}

function DBError($query,$file,$line, $con = false)
{
	//NOTICE: Couldn't this be done by fatal_error anyhow?
	
	global $db_connection, $use_transactions;
	if($con === false)
		$con = $db_connection;
	#Uuh, bad - a database-query went wrong. Let's see what we can do.
		$errstr = mysql_error($con);
		//Are we using a transaction? Lets try to roll it back
		if($use_transactions) {
			@mysql_query("ROLLBACK");
			$use_transactions = false;
		}
	#We'll Print the error - thats only for debugging.
		//Where the error happened
		echo "<hr/><h3>Database Error in <i>{$file}</i> around Line <i>{$line}</i></h3><br />";
		//The query that caused the error
		echo "<pre>".$query	."</pre><br />";
		//And Mysql's error-description
		echo $errstr;
		
		//Log it baby..
		LogError($query."\n".$errstr, $file, $line);
		
		//Thats it - bye...
		exit();
}

/**
 * @name DBQueryOne
 * @param String $query SQL Query to execute
 * @param String $file Filename where DBQuery is called (used to be __FILE__)
 * @param Integer $line Line in $file where DBQuery is called (used to be __LINE__)
 * @param Integer $assoc Return an associative array or a numeric
 * @return FALSE if failed, one Element if only one row/col is queried or an array with the values of all columns
 */
function DBQueryOne($query,$file,$line,$assoc = false)
{
	$q = DBQuery($query,$file, $line);
	
	if (mysql_num_rows($q) == 0)
		return false;

	if ($assoc)
		$stuff = mysql_fetch_assoc($q);
	else
		$stuff = mysql_fetch_row($q);
	mysql_free_result($q);
			
	if (count($stuff) > 1)
		return $stuff;
	else
		return current($stuff); //Return the first element of $stuff
}

function CheckLogin()
{
	global $sourcedir;
	
	//If the User is logged in, ID_MEMBER is set
	if (isset($_SESSION['ID_MEMBER']) && $_SESSION['user_agent'] == $_SERVER['HTTP_USER_AGENT'])
		return true;
	//Maybe he's logging in...
	elseif (isset($_REQUEST['login'])) {
		require($sourcedir.'/core/LoginOut.php');
		Login2();
		LoadUser();
		return true;
	} else { //Ok, he isnt logged in and doesnt do it atm. Let's view a loginbox and die :)	
		require($sourcedir.'/core/LoginOut.php');
		Login();
		StopExecution();
	}
}

function Redirect($url)
{
	if (!headers_sent())
		header("Location: ".$url);
	else
		echo "Should have redirected to: <a href=\"{$url}\">{$url}</a>, but headers already sent.";
	StopExecution();
}

function FormatTime($secs)
{
	$days = ($secs-($secs % 86400)) / 86400;
	$secs -= $days*86400;
	
	$hours = ($secs-($secs % 3600)) / 3600;
	$secs -= $hours*3600;
	
	$mins = ($secs-($secs % 60)) / 60;
	$secs -= $mins*60;
	
	if ($days > 1)
		$ret = $days.' Tage ';
	elseif ($days == 1)
		$ret = $days.' Tag ';
	else
		$ret = '';
	
	$ret .= sprintf("%02d:%02d:%02d", $hours, $mins, $secs);

	return $ret;
}

function FormatDate($secs) {
	return date("d.m.Y H:i", $secs);
}

/**
 * @name ParseTime
 * @param string IcewarsTimeString String containing sth like '15.11.2006 01:58'
 * @return int Seconds since January 1 1970 00:00:00 GMT or false if it didnt match
 */
function ParseTime($str) {
	$matches = array();
	if(!empty($str) && preg_match('~(\d+)\.(\d+)\.(\d+)\s(\d+)\:(\d+)~', $str, $matches))
		return strtotime($matches[3].'-'.$matches[2].'-'.$matches[1].' '.$matches[4].':'.$matches[5]);
	else
		return false;
}

/**
 * @name LogError
 * @param int flags ERROR_CRITICAL = critical error - die on error, ERROR_FILE_ONLY - dont try to log to the DB, just put it into the file
 */
define('ERROR_CRITICAL', 1);
define('ERROR_FILE_ONLY', 2);
function LogError($str, $file, $line, $flags = 0) {
	global $pre, $ID_MEMBER, $sourcedir;
	
	if(!isset($ID_MEMBER))
		$ID_MEMBER = 0;
		
	$trace = '';
	foreach(debug_backtrace() as $val) {
		$trace .= (isset($val['class'],$val['type']) ? $val['class'].$val['type'] : '').$val['function'].'(';
		if(isset($val['args'])) {
			foreach($val['args'] as $arg) {
				$trace .= var_export($arg, true).',';
			}
			$trace = substr($trace, 0, -1);
		}
		$trace.= ')'.(isset($val['file'], $val['line']) ? "\n [".$val ['file'].':'.$val['line'].']' : '')."\n\n";
	}
	$request = '';
	$toStore = array('Get' => $_GET, 'Post' => $_POST, 'Cookie' => $_COOKIE, 'Server' => $_SERVER);
	foreach($toStore as $k => $v) { 
		ob_start();
		$request .= "<============ ".$k." ============>\n";
		var_dump($v);
		$request .= ob_get_contents()."\n";
		ob_end_clean();
	}
	
	if(($flags & ERROR_FILE_ONLY) != ERROR_FILE_ONLY) {
		$q = mysql_query("INSERT INTO {$pre}errors (time, user, file, line, msg, stacktrace, request) VALUES (".time().", {$ID_MEMBER}, '{$file}', {$line}, '".EscapeDBU($str)."', '".EscapeDBU($trace)."', '".EscapeDBU($request)."')");
		if($q === false) {
			LogError("Mysql error logging failed: ".mysql_error(), __FILE__, __LINE__, ERROR_FILE_ONLY);
			$flags |= ERROR_FILE_ONLY;
		}
	}
	if(($flags & ERROR_FILE_ONLY) == ERROR_FILE_ONLY) {
		$file = str_replace(array(',', '|^|'), array('', ''),  $file);
		$trace = str_replace(array(',', '|^|'), array('', ''),  $trace);
		$str = str_replace('|^|', '',  $str);
		
		$res = @fopen(dirname($sourcedir)."/errors.txt", 'ab');
		@fwrite($res, time().", {$ID_MEMBER}, {$file}, {$line}, {$trace}, {$str}|^|\n");
		@fclose($res);
	}
	if($flags & ERROR_CRITICAL)
		die($str);
}

function QueryIWDBUtil($action, array $req, &$response) {
	global $util_host, $util_port;
	$req = str_replace(array("\r\n", "\r", " \t", "\t"), array("\n", "\n", " ", " "), $req);
	$sock = @fsockopen($util_host, $util_port, $err, $errstr, 5);
	if($sock === false) {
		LogError("Fehler beim Verbinden mit dem IWDBUtil: {$errstr} ($err)", __FILE__, __LINE__);
		return false;
	}
	stream_set_timeout($sock, 5);
	fwrite($sock, $action."\0");
	foreach($req as $part) {
		if(strlen($part) == 0) {
			fclose($sock);
			return false;
		}
		fwrite($sock, utf8_encode($part));
		fwrite($sock, "\0");
	}
	fwrite($sock, "\0");
	$str = '';
	while(!feof($sock)) {
		$str .= @fread($sock, 100);
	}
	if(strlen($str) >= 3 && ($str[0] == "\xEF" && $str[1] == "\xBB" && $str[2] == "\xBF" || $str[0] == "\xBF" && $str[1] == "\xBB" && $str[2] == "\xEF"))
		$str = substr($str, 3);
	$response = $str;
	return true;
}

/**
 * @name EscapeO
 * @param $text The Text to escape
 * @return A escaped version of $text so it can be safely printed to a XHTML-Website. 
  */
function EscapeO($text) {
	return utf8_encode(EscapeOU($text));
}
function EscapeOU($text) {
	return str_replace(array('&', '<', '>', '"'), array('&amp;', '&lt;', '&gt;', '&quot;'), $text);
}

function EscapeDB($text) {
	return mysql_real_escape_string(utf8_encode($text));
}
function EscapeDBU($text) {
	return mysql_real_escape_string($text);
}

function Param($name, $req = null) {
	if($req == null)
		$req = $_REQUEST;
	if(get_magic_quotes_gpc())
		return utf8_decode(stripslashes($req[$name]));
	else
		return utf8_decode($req[$name]);
}

function GetText2($name) {
	global $pre;
	return DBQueryOne("SELECT text FROM {$pre}texts WHERE Name='".$name."'", __FILE__, __LINE__);
}

function ErrorHandler($errno, $errstr, $errfile, $errline) {
	static $err_types = array(
		E_ERROR => "Fatal Error",
		E_WARNING => "Warning",
		E_PARSE => "Parser Error",
		E_NOTICE => "Notice",
		E_CORE_ERROR => "Fatal Core Error",
		E_CORE_WARNING => "Core Warning",
		E_COMPILE_ERROR => "Compiler Error",
		E_COMPILE_WARNING => "Compiler Warning",
		E_USER_ERROR => "User Error",
		E_USER_WARNING => "User Warning",
		E_USER_NOTICE => "User Notice",
		E_STRICT => "Strict Notification",
		E_RECOVERABLE_ERROR => "Fatal Error",
	);
	if(ini_get('error_reporting') == 0)
		return;
	
	LogError($err_types[$errno].': '.$errstr, $errfile, $errline);
	return false;
}

function ParseIWBuildingQueue($str, $koords) {
	if(!QueryIWDBUtil('buildingqueue', array($koords, $str), $resp))
		return false;
	$arr = explode("\n", $resp);
	unset($arr[count($arr)-1]); //Da ist am Ende immer noch ein \n zu viel, das ein leeres Arrayelement produziert.
	if($resp == "err" || count($arr) == 0)
		return false;
	return $arr;
}

function Event($name) {
	switch($name) {
		case 'sitterchanged':
			QueryIWDBUtil('sitterdataupdater', array(), $resp);
			break;
	}
}

function CheckRequestID() {
	global $pre;
	
	if (!isset($_REQUEST['reqid']))
		return false;
	$id = intval($_REQUEST['reqid']);
	$u = DBQueryOne("SELECT used FROM {$pre}requestids WHERE id={$id}", __FILE__, __LINE__);
	if($u === false || $u == 1)
		return false;
	DBQuery("UPDATE {$pre}requestids SET used=1 WHERE id={$id}", __FILE__, __LINE__);
	return true;
}

function GenRequestID() {
	global $content, $pre;
	DBQuery("INSERT INTO {$pre}requestids (time, used) values (".time().", 0)", __FILE__, __LINE__);
	$rid = mysql_insert_id();
	$content['reqid'] = $rid;
	return $rid;
}

function ActualityColor($time) {
	static $stages = array(
		1 => 86400,//1d
		2 => 604800,//1w
		3 => 1209600,//2w
		4 => 1814400,//3w
		5 => 2419200,//4w
	);
	$diff = time()-$time;
	for($pos = 1;isset($stages[$pos]) && $stages[$pos] <= $diff; ++$pos);
	--$pos;
	return 'act_'.$pos;
}

function LastLoginColor($time) {
	static $stages = array(
		1 => 1800, //30 mins
		2 => 3600, //1h
		3 => 7200, //2h
		4 => 10400, //3h
		5 => 14400, //4h
	);
	$diff = time()-$time;
	for($pos = 1;isset($stages[$pos]) && $stages[$pos] <= $diff; ++$pos);
	--$pos;
	return 'act_'.$pos;
}

function SetLinkParam($param, $value) {
	global $link_parameters;
	$link_parameters[$param] = $value;
}
function GenLink($params) {
	global $link_parameters, $scripturl;
	$params = array_merge($link_parameters, $params);
	$ret = $scripturl.'/index.php?';
	foreach($param as $k => $v) {
		$ret .= $k.'='.$v.'&';
	}
	return EscapeO(substr($ret, 0, -1));
}



?>
