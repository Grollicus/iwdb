<?php

if(!defined('dddfd'))
	die('uerks');

function HighScore() {
	global $pre, $content;
	
	$content['hs'] = array();
	
	$q = DBQuery("SELECT visibleName, sitterpts FROM {$pre}users ORDER BY sitterpts DESC LIMIT 0,5", __FILE__, __LINE__);
	$arr = array();
	while($row = mysql_fetch_row($q))
		$arr[] = array('name' => EscapeOU($row[0]), 'value' => $row[1]);
	$content['hs'][] = array('title' => 'Sitterpunkte', 'data' => $arr);
	
	$q = DBQuery("SELECT visibleName, sittertime FROM {$pre}users ORDER BY sittertime DESC LIMIT 0,5", __FILE__, __LINE__);
	$arr = array();
	while($row = mysql_fetch_row($q))
		$arr[] = array('name' => EscapeOU($row[0]), 'value' => number_format($row[1]/86400, 2, ',', '.').' d');
	$content['hs'][] = array('title' => 'Sitterzeit', 'data' => $arr);
	
	$qs = array(
		'Eisen' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vFe) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'Stahl' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vSt) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'VV4A' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vVv) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'Chemie' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vCh) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'Eis' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vEi) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'Wasser' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vWa) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'Energie' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vEn) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'FP' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, fp AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
		'Credits' => array('fact' => 0.01, 'qry' => "SELECT igm_data.igmname, SUM(vCr) AS sum FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id GROUP BY igm_data.igmname ORDER BY sum DESC LIMIT 0,5"),
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