<?php
if (!defined("dddfd"))
	die("Hacking attempt");
	
/*
 * void LoadSession(void) 
 * 	*Imports the old Session or starts a new one
 * 
 * void LoadUser
 * 	*Initialisizes the $user-Array
 * 
 * 
 * void TemplateInit( string templateName );
 * 	*Loads the Templatefile(->templateName) and the default-Templatefile.
 *  
 */
 

function LoadSession()
{
	global $content, $cookie;
	//Re-Import the old Session
	ini_set("session.name", $cookie['name']);
	ini_set("session.cookie_path", $cookie['path']);
	ini_set("session.cookie_domain", $cookie['domain']);
	ini_set("session.cookie_lifetime", 31536000);
	session_start();
	if(isset($_SESSION['ID_MEMBER']) && !$_SESSION['stay_loggedin'] && $_SESSION['lastactive'] < time()-3600) {
		//nicht- dauerhafte Session nach einer Stunde Inaktivität abgelaufen
		$_SESSION = array();
		session_destroy();
		session_start();
	}
	$content['sid'] = session_id();
	$_SESSION['lastactive'] = time();
	//And... thats it ;)
	//If there was something like "DB-Based-Sessions", we would have to do something more in here.
}

function LoadUser()
{
	global $pre, $user, $ID_MEMBER, $content;
	
	if(isset($_SESSION['ID_MEMBER'])) {
		$user['isGuest'] = false;
		$ID_MEMBER = $_SESSION['ID_MEMBER'];
		//Ok, then let's see what the DB says about this User
		$userSettingsArray = DBQueryOne("SELECT 
				visibleName, isAdmin , theme, lastactive, igmuser
			FROM {$pre}users
			WHERE ID = {$ID_MEMBER}",__FILE__,__LINE__,true);
		if($userSettingsArray !== false)
			$user = array_merge($user, $userSettingsArray);
			if(dddfd != "script")
				DBQuery("UPDATE {$pre}users set lastactive=".time()." where ID= {$ID_MEMBER}", __FILE__, __LINE__);
		} else {
			$user['isGuest'] = true;
			$user['theme'] = 'default';
		}
}

function TemplateInit ( $templateName )
{
	global $themedir, $user, $themeurl;

	//First, the Template in the userdefined theme
	if (file_exists($themedir.'/'.$user['theme'].'/template.'.$templateName.'.php'))
		require_once($themedir.'/'.$user['theme'].'/template.'.$templateName.'.php');
	//Doesnt exist? Then fall back to the default one
	elseif (file_exists($themedir.'/default/template.'.$templateName.'.php'))
		require_once($themedir.'/default/template.'.$templateName.'.php');
	else
		LogError ("Error, the part {$templateName} is missing in ".$user['theme'], __FILE__, __LINE__, ERROR_CRITICAL);
		
#The Template is there now, it's time for adding some default
#Stuff might be needed within the template
	if (file_exists($themedir.'/'.$user['theme'].'/template.index.php'))
		require_once($themedir.'/'.$user['theme'].'/template.index.php');
	//Doesnt exist? Then fall back to the default one
	elseif (file_exists($themedir.'/default/template.index.php'))
		require_once($themedir.'/default/template.index.php');
	else
		LogError ("Error, the part index is missing in ".$user['theme'], __FILE__, __LINE__, ERROR_CRITICAL);


#complete $themeurl
	$themeurl .= '/'.$user['theme'];
#Job done
}

function DoMaintenance() {
	global $pre;
	
	$now = time();
	DBQuery("DELETE FROM {$pre}requestids WHERE time < ".($now-172800), __FILE__, __LINE__);
	DBQuery("DELETE FROM {$pre}geoscans WHERE reset is not null and reset < ".$now." AND (SELECT objekttyp='---' FROM {$pre}universum AS universum WHERE universum.id = {$pre}geoscans.id)", __FILE__, __LINE__);
}
?>
