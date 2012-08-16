<?php
if (!defined("dddfd"))
	die("Hacking attempt");

function Index() {
	global $content, $pre, $user, $sourcedir, $scripturl, $ID_MEMBER, $warmode;
	
	
	$content['visibleName'] = $user['visibleName'];
	$content['problems'] = array();
	
	$u = $user['igmuser'] != 0;
	if($u) {
		$username = DBQueryOne("SELECT igmname FROM {$pre}igm_data WHERE id=".$user['igmuser'], __FILE__, __LINE__);
		$u = $username !== false && strlen($username) > 0;
	}
	if(!$u && !$user['isRestricted']) {
		$content['problems'][] = array(
				'class' => 'imp',
				'text' => 'Du hast keine Sitterdaten eingetragen!',
				'link' => $scripturl. '/index.php?action=settings',
			);
	}
	if($user['isAdmin']) {
		$dberrors = DBQueryOne("SELECT count(*) FROM {$pre}errors", __FILE__, __LINE__);
		if($dberrors > 0) {
			$content['problems'][] = array(
				'class' => 'imp',
				'text' => 'Es sind '. $dberrors. ' Fehler aufgetreten!',
				'link' => $scripturl. '/index.php?action=errors',
			);
		}
		if(file_exists(dirname($sourcedir)."/errors.txt") && filesize(dirname($sourcedir)."/errors.txt") > 0) {
			$content['problems'][] = array(
				'class' => 'imp',
				'text' => 'Es sind kritische Fehler aufgetreten!',
				'link' => $scripturl. '/index.php?action=errors',
			);
		}
	}
	$browser = IdentifyBrowser();
	if($browser != 'fx') {
		$content['problems'][] = array(
			'class' => 'imp',
			'text' => 'Du benutzt als Browser nicht den Firefox, somit wird der größte Teil der Tool-Parser nicht funktionieren!',
			'link' => '',
		);
	}
	if($warmode) {
		$content['problems'][] = array(
			'class' => 'imp',
			'text' => 'Krieg!',
			'link' => '',
		);
		$content['problems'][] = array(
			'class' => 'simp',
			'text' => 'Es gibt extra viel Sitterzeit!',
			'link' => '',
		);
		$now = time();
		$schedule_slot = $now - ($now % 1800);
		if(0<DBQueryOne("SELECT count(*) FROM {$pre}war_schedule WHERE time={$schedule_slot} AND userid={$ID_MEMBER}", __FILE__, __LINE__)) {
			$content['problems'][] = array(
				'class' => 'simp',
				'text' => 'Du bist aktuell als Sitter eingetragen!',
				'link' => '',
			);
		}
	}
	
	$q = DBQuery("SELECT time, event FROM {$pre}events ORDER BY ID DESC LIMIT 0,20", __FILE__, __LINE__);
	$content['events'] = array();
	while($row = mysql_fetch_row($q)) {
		$content['events'][] = array(
			'time' => FormatDate($row[0]),
			'text' => $row[1], //unescaped, weil da schon Zeug in der DB steht
		);
	}
	
	TemplateInit('main');
	TemplateIndex();
}

function IdentifyBrowser() {
	$s = $_SERVER['HTTP_USER_AGENT'];
	if(strpos($s, 'Firefox/') !== false) {
		return 'fx';
	} elseif(strpos($s, 'SeaMonkey/') !== false) {
		return 'fx';
	} elseif(strpos($s, 'Mozilla/') !== false) {
		return 'ie';
	} elseif(strpos($s, 'Opera/') !== false) {
		return 'op';
	} else {
		return '??';
	}
}

function UnknownAction() {
	
	header("HTTP/1.0 404 Not Found");
	LogError("Unbekannte Action: ".$_REQUEST['action'], __FILE__, __LINE__);
	TemplateInit('main');
	TemplateUnknownAction();
}

function HelpView() {
	global $pre, $content;
	
	$txt = DBQueryOne("SELECT Text FROM {$pre}texts WHERE Name='".EscapeDB(Param('name'))."'", __FILE__, __LINE__);
	if($txt === false || strlen($txt) == 0) {
		$content['text'] = 'Zu diesem Thema ist leider kein Hilfe-Eintrag vorhanden!';
	} else {
		$content['text'] = $txt;
	}
	
	TemplateInit('main');
	TemplateHelp();
}

function HelpPage() {
	global $content;
	$content['text'] = GetText2('help_page');
	TemplateInit('main');
	TemplateBugs();
}
function KbFormat() {
	global $content, $scripturl, $user, $sourcedir;

	$content['result'] = '';
	GenRequestID();
	$_POST['uid'] = $user['igmuser'];
	require_once($sourcedir.'/newscan/main.php');
	ParseScansEx(false, true);
	
	if(isset($_REQUEST['abs'])) {
		$matches = array();
		preg_match_all('~http://www\.icewars\.de/portal/kb/de/kb\.php\?id=\d+&md_hash=[a-z0-9]{32}~', Param('scans'), $matches);
		foreach($matches[0] as $m) {
			$kb = CacheQuery($m.'&typ=bbcode');
			if($_REQUEST['target'] == 'forum')
				$kb = str_replace(array(' colspan=3', ' colspan=4'), array('', ''), $kb);
			$content['result'] .= EscapeOU($kb)."\n\n";
		}
	}
	
	$content['submitUrl'] = $scripturl.'/index.php?action=kbformat';
	$content['target'] = array(
		'forum' => array('desc' => 'Allyforum', 'selected' => isset($_REQUEST['target']) && $_REQUEST['target'] == 'forum'),
		'iwf' => array('desc' => 'IWF',  'selected' => isset($_REQUEST['target']) && $_REQUEST['target'] == 'iwf'),
	);
	TemplateInit('main');
	TemplateKbFormat();
}

?>
