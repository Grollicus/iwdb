<?php
if(!defined('dddfd'))
	exit();

function NewscanEx() {
	global $content, $pre;

	ParseScansEx();
	
	$content['desc'] = GetText2('newscan_desc');
	$q = DBQuery("SELECT id, igmname FROM {$pre}igm_data ORDER BY igmname", __FILE__, __LINE__);
	$content['users'] = array();
	GenRequestID();
	$uid = $content['user'];
	while($row = mysql_fetch_row($q)) {
		$content['users'][] = array(
			'igmName' => EscapeOU($row[1]),
			'ID' => $row[0],
			'selected' => ($row[0] == $uid)
		);
	}
	
	TemplateInit('main');
	TemplateNewscanEx();
}

function ApiNewscans() {
	global $ID_MEMBER, $user, $content;
	
	if(!isset($_REQUEST['scans']))
		die("fail.");
	$_POST['uid'] = $user['igmuser'];
	$_POST['abs'] = true;
	
	ParseScansEx(false, false);
	
	echo "ok\n";
	if(!empty($content['msg']))
		echo $content['msg']."\n";
	if(!empty($content['smsg']))
		echo $content['smsg'];
}

function ParseScansEx($store_in_temp = false, $check_reqid = true) {
	global $content, $ID_MEMBER, $user, $pre, $warmode;
	
	flush();
	$content['scans'] = '';
	$search	 = array("Erdbeeren",	"Erdbeermarmelade", "Brause", 			utf8_decode("Erdbeerkonfitüre"), "Vanilleeis", "Eismatsch"		, "Traubenzucker", "Kekse"  , "blubbernde Gallertmasse" );
	$replace = array("Eisen", 		"Stahl", 			"chem. Elemente", 	"VV4A"	 					   , "Eis"  	 , "Wasser"			, "Energie"		 , "Credits", utf8_decode("Bevölkerung"));
	if(isset($_POST['abs'])) {
		$scan = str_replace($search, $replace, Param('scans'));
		$uid = intval($_POST['uid']);
		$content['user'] = $uid;
		if($check_reqid && !CheckRequestID()) {
			$content['scans'] = EscapeO($scan);
			$content['msg'] = "Fehler mit der Request-ID! Wurden diese Scans schon einmal eingetragen?";
			return;
		}
		$str = '';
		if(!empty($scan) && !QueryIWDBUtil('newscan', array($ID_MEMBER, $uid, $_SERVER['HTTP_USER_AGENT'], $warmode?'1':'0', $user['isRestricted'], $scan), $str)) {
			$content['scans'] = EscapeO($scan);
			$content['msg'] = "Verbindung zum Parser fehlgeschlagen!";
		} else {
			$content['submsg'] = $str;
		}
		if($store_in_temp) {
			$arr = array();
			if(isset($content['msg']))
				$arr['msg'] = $content['msg'];
			if(isset($content['submsg']))
				$arr['submsg'] = $content['submsg'];
			$str = serialize($arr);
			DBQuery("INSERT INTO {$pre}temp (value) VALUES ('".EscapeDB($str)."')", __FILE__, __LINE__);
			return mysql_insert_id();
		}
	} else {
		$content['user'] = $user['igmuser'];
	}
	return false;
}

?>
