<?php

if(!defined('dddfd'))
	die("möh");

function RaidOverview() {
	global $content, $pre, $user;
	
	if($user['isRestricted'])
		die("Hacking Attempt");
	
	$q = DBQuery("SELECT iwid, hash, time, angreifer, angrAlly, verteidiger, verteidigerAlly, score, rFe, rSt, rCh, rVv, rEi, rWa, rEn, zFe, zSt, zCh, zVv, zEi, zWa, zEn FROM {$pre}raidberichte ORDER BY time DESC LIMIT 0, 30", __FILE__, __LINE__);
	$content['raids'] = array();
	while($row = mysql_fetch_row($q)) {
		$content['raids'][] = array(
			'id' => $row[0],
			'hash' => $row[1],
			'url' => 'http://www.icewars.de/portal/kb/de/kb.php?id='.$row[0].'&md_hash='.$row[1],
			'date' => FormatDate($row[2]),
			'angreiferName' => EscapeOU($row[3]),
			'angreiferAlly' => EscapeOU($row[4]),
			'verteidigerName' => EscapeOU($row[5]),
			'verteidigerAlly' => EscapeOU($row[6]),
			'score' => number_format($row[7], 0, ',', '.'),
			'rFe' => number_format($row[8], 0, ',', '.'),
			'rSt' => number_format($row[9], 0, ',', '.'),
			'rCh' => number_format($row[10], 0, ',', '.'),
			'rVv' => number_format($row[11], 0, ',', '.'),
			'rEi' => number_format($row[12], 0, ',', '.'),
			'rWa' => number_format($row[13], 0, ',', '.'),
			'rEn' => number_format($row[14], 0, ',', '.'),
			'zFe' => number_format($row[15], 0, ',', '.'),
			'zSt' => number_format($row[16], 0, ',', '.'),
			'zCh' => number_format($row[17], 0, ',', '.'),
			'zVv' => number_format($row[18], 0, ',', '.'),
			'zEi' => number_format($row[19], 0, ',', '.'),
			'zWa' => number_format($row[20], 0, ',', '.'),
			'zEn' => number_format($row[21], 0, ',', '.'),
		);
	}
	
	TemplateInit('kbs');
	TemplateRaidOverview();
}

function KbPassthrough() {
	$id = intval(Param('id'));
	$hash = Param('hash');
	if(strlen($hash) != 32)
		die('fail');
	$allowed_chars=array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
	for($i = 0; $i < 32; ++$i)
		if(!in_array($hash[$i], $allowed_chars))
			die('fail');
	$url = "http://www.icewars.de/portal/kb/de/kb.php?id={$id}&md_hash={$hash}";
	$kbhtml = CacheQuery($url);
	$begin = strpos($kbhtml, '<body bgcolor="#000000">') + strlen('<body bgcolor="#000000">');
	$end = strpos($kbhtml, '</body>')-strlen('  <p>&nbsp;</p>');
	$kbhtml = substr($kbhtml, $begin, $end-$begin);
	$kbhtml = str_replace('../bilder', 'http://www.icewars.de/portal/kb/bilder', $kbhtml);
	echo $kbhtml;
}
?>