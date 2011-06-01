<?php
/***************************************************************************
*                   This File belongs to the dddfd-Game                    *
* Filename: LoginOut.php                                                   *
* Created: 06.10.2005 14:23:54                                             *
* This file implements the Login-/Logoutsystem                             *
***************************************************************************/

/*
 * void Login ( void )
 * 	*Views a Loginbox
 * void Login2 ( void )
 * 	*Does the actually logging in
 * void Logout( void )
 * 	*Clears the $_SESSION and redirects to the index
 * 
 */

if (!defined("dddfd"))
	die("Hacking attempt");

function Login()
{
	global $content, $scripturl;
	//Initialisize the Logintemplate	
	TemplateInit('login');
	if (!isset($content['message']))
		$content['message'] = "Einloggen bitte!";
	$content['user'] = '';
	$content['pw'] = '';
	$url = $scripturl.'/index.php?';
	$a = array();
	SerializeReq($_GET, '', $a);
	foreach($a as $k => $v) {
		$url .= $k.'='.$v.'&';
	}
	$content['submitUrl'] = substr($url, 0, -1);
	
	$p = array();
//	foreach($_POST as $k => $v) {
//		if(!in_array($k, array('login_user', 'login_pass', 'login'))) {
//			if(get_magic_quotes_gpc())
//				$p[EscapeOU($k)] = EscapeOU(stripslashes($v));
//			else
//				$p[EscapeOU($k)] = EscapeOU($v);
//		}
//	}
	SerializeReq($_POST, '', $p);
	$content['postdata'] = $p;
	
	Template_Login();
}

function SerializeReq($a, $prefix, &$ret) {
	foreach($a as $k => $v) {
		if(in_array($k, array('login_user', 'login_pass', 'login')))
			continue;
			
		if($prefix == '')
			$key = EscapeOU($k);
		else
			$key = $prefix.'['.EscapeOU($k).']';
			
		if(is_array($v))
			SerializeReq($v, $key, $ret);
		else if(get_magic_quotes_gpc())
			$ret[$key] = EscapeOU(stripslashes($v));
		else
			$ret[$key] = EscapeOU($v);
	}
}

function Login2()
{
	global $pre, $content, $scripturl;
	
	if (!isset($_POST['login_user']))
		die("Hacking attempt");
	if (!isset($_POST['login_pass']))
		die("Hacking attempt");
		
	$ban = DBQueryOne("SELECT exceeds FROM {$pre}ip_bans WHERE ip='".$_SERVER['REMOTE_ADDR']."'", __FILE__, __LINE__);
	if($ban !== false && $ban > time())
		die("banned.");
	
	$memberInfo = DBQueryOne("SELECT ID FROM {$pre}users WHERE userName='".
		EscapeDB(Param('login_user')).
		"' AND pwmd5='".md5($_POST['login_pass'])."'",__FILE__,__LINE__);
		
	$max = ini_get('max_execution_time');
	if($max != 0 && $max < 60)
		ini_set('max_execution_time', 60);

//if they are wrong( no results => $memberId === false ), review the login box
	if ($memberInfo === false) 
	{
		
		DBQuery("INSERT INTO {$pre}ip_bans (ip, exceeds) VALUES ('".$_SERVER['REMOTE_ADDR']."', ".(time()+10).")
						ON DUPLICATE KEY UPDATE exceeds=VALUES(exceeds)", __FILE__, __LINE__);
		sleep(10);
		$content['message'] = "Benutzername oder Passwort falsch!";
		$content['user'] = EscapeO(Param('login_user'));
		$content['pw'] = EscapeO(Param('login_pass'));
		$url = $scripturl.'/index.php?';
		foreach($_GET as $k => $v) {
			$url .= EscapeOU($k).'='.EscapeOU($v).'&';
		}
		$content['submitUrl'] = substr($url, 0, .1);
		
		$p = array();
		foreach($_POST as $k => $v) {
			if(!in_array($k, array('login_user', 'login_pass', 'login'))) {
				if(get_magic_quotes_gpc())
					$p[EscapeOU($k)] = EscapeOU(stripslashes($v));
				else
					$p[EscapeOU($k)] = EscapeOU($v);
			}
		}
		$content['postdata'] = $p;
		TemplateInit('login');
		Template_Login();
		StopExecution();
	} else {
#Init the Session
		//UserId
		$_SESSION['ID_MEMBER'] = $memberInfo;
			
		//Useragent-check
		$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		
		$_SESSION['stay_loggedin'] = isset($_REQUEST['login_stay']);
		//Redirect($scripturl.'/index.php?action=index');
	}
}

function Logout()
{
	global $scripturl;
	
	$_SESSION = array();
	session_destroy();
	Redirect($scripturl);
}
?>
