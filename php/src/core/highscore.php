<?php

if(!defined('dddfd'))
	die('uerks');

function HighScore() {
	global $pre, $content, $user;
	
	if($user['isRestricted'])
		die("Hacking Attempt");
	
	$cnt = isset($_REQUEST['cnt']) ? intval($_REQUEST['cnt']) : 5;
	if($cnt < 0)
		$cnt = 0;
	$content['cnt'] = $cnt;
	$content['hs'] = array();
	
	$q = DBQuery("SELECT visibleName, sitterpts FROM {$pre}users ORDER BY sitterpts DESC LIMIT 0,{$cnt}", __FILE__, __LINE__);
	$arr = array();
	while($row = mysql_fetch_row($q))
		$arr[] = array('name' => EscapeOU($row[0]), 'value' => $row[1]);
	$content['hs'][] = array('title' => 'Sitterpunkte', 'data' => $arr);
	
	$q = DBQuery("SELECT visibleName, sittertime FROM {$pre}users ORDER BY sittertime DESC LIMIT 0,{$cnt}", __FILE__, __LINE__);
	$arr = array();
	while($row = mysql_fetch_row($q))
		$arr[] = array('name' => EscapeOU($row[0]), 'value' => number_format($row[1]/86400, 2, ',', '.').' d');
	$content['hs'][] = array('title' => 'Sitterzeit', 'data' => $arr);
	
	$q = DBQuery("SELECT angreifer, SUM(score) AS sum FROM {$pre}raidberichte GROUP BY angreifer ORDER BY sum DESC LIMIT 0,{$cnt}", __FILE__, __LINE__);
	$arr = array();
	while($row = mysql_fetch_row($q))
		$arr[] = array('name' => EscapeOU($row[0]), 'value' => number_format($row[1], 0, ',', '.'));
	$content['hs'][] = array('title' => 'Raids', 'data' => $arr);
	
	
	
	$qs = array(
		'Eisen' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vFe) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'Stahl' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vSt) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'VV4A' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vVv) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'Chemie' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vCh) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'Eis' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vEi) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'Wasser' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vWa) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'Energie' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vEn) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'FP' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(fp) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
		'Credits' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vCr) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,{$cnt}"),
	);
	
	foreach($qs as $title => $qry) {
		$arr = array();
		$q = DBQuery($qry['qry'], __FILE__, __LINE__);
		while($row = mysql_fetch_row($q))
			$arr[] = array('name' => EscapeOU($row[0]), 'value' => number_format($row[1]*$qry['fact'], 2, ',', '.'));
		$content['hs'][] = array('title' => $title, 'data' => $arr);
	}
	
	TemplateInit('main');
	TemplateHighscore();
	
}
 
 ?>
