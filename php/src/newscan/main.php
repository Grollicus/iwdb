<?php
if(!defined('dddfd'))
	exit();

function NewscanEx() {
	global $content, $pre;

	ParseScansEx();
	
	$content['desc'] = GetText('newscan_desc');
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

function ParseScansEx() {
	global $content, $ID_MEMBER, $user;
	
	
	$content['scans'] = '';
	$search	 = array("Erdbeeren",	"Erdbeermarmelade", "Brause", 			utf8_decode("Erdbeerkonfitüre"), "Vanilleeis", "Eismatsch"		, "Traubenzucker", "Kekse"  , "blubbernde Gallertmasse" );
	$replace = array("Eisen", 		"Stahl", 			"chem. Elemente", 	"VV4A"	 					   , "Eis"  	 , "Wasser"			, "Energie"		 , "Credits", utf8_decode("Bevölkerung"));
	if(isset($_POST['abs'])) {
		$scan = str_replace($search, $replace, Param('scans'));
		$uid = intval($_POST['uid']);
		$content['user'] = $uid;
		if(!CheckRequestID()) {
			$content['scans'] = EscapeO($scan);
			$content['msg'] = "Fehler mit der Request-ID! Wurden diese Scans schon einmal eingetragen?";
			return;
		}
		if(!QueryIWDBUtil('newscan', array($ID_MEMBER, $uid, $_SERVER['HTTP_USER_AGENT'], $scan), $str)) {
			$content['scans'] = EscapeO($scan);
			$content['msg'] = "Verbindung zum Parser fehlgeschlagen!";
		} else {
			$content['submsg'] = $str;
		}
	} else {
		$content['user'] = $user['igmuser'];
	}
}

?>
