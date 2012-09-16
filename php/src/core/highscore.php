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
	
	$q = DBQuery("SELECT verteidiger, SUM(score) AS sum FROM {$pre}raidberichte GROUP BY verteidiger ORDER BY sum DESC LIMIT 0,{$cnt}", __FILE__, __LINE__);
	$arr = array();
	while($row = mysql_fetch_row($q))
		$arr[] = array('name' => EscapeOU($row[0]), 'value' => number_format($row[1], 0, ',', '.'));
	$content['hs'][] = array('title' => 'Raidopfer', 'data' => $arr);
	
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

function Inactives() {
	global $pre, $content, $scripturl;
	
	if(isset($_REQUEST['del'])) {
		DBQuery("DELETE FROM {$pre}highscore_inactive WHERE name='".EscapeDB(Param('del'))."'", __FILE__, __LINE__);
		return;
	}
	
	$q = DBQuery("SELECT name, since, until, gebp FROM {$pre}highscore_inactive WHERE since<>until or 1=1 ORDER BY until-since DESC, gebp DESC LIMIT 0,100", __FILE__, __LINE__);
	$content['inactives'] = array();
	$i = 0;
	while($row = mysql_fetch_row($q)) {
		$content['inactives'][] = array(
			'name' => EscapeOU($row[0]),
			'link' => $scripturl.'/index.php?action=uni_view&amp;gala_min=&amp;gala_max=&amp;sys_min=&amp;sys_max=&amp;pla_min=&amp;pla_max=&amp;spieler='.EscapeOU(rawurlencode($row[0])).'&amp;tag=&amp;objekttyp[]=Kolonie&amp;objekttyp[]=Sammelbasis&amp;objekttyp[]=Kampfbasis&amp;objekttyp[]=Artefaktbasis&amp;planiname=&amp;geo_ch_min=&amp;geo_ch_max=&amp;geo_fe_min=&amp;geo_fe_max=&amp;geo_ei_min=&amp;geo_ei_max=&amp;geo_gravi_min=&amp;geo_gravi_max=&amp;geo_lb_min=&amp;geo_lb_max=&amp;geo_fmod_min=&amp;geo_fmod_max=&amp;geo_gebd_min=&amp;geo_gebd_max=&amp;geo_gebk_min=&amp;geo_gebk_max=&amp;geo_schd_min=&amp;geo_schd_max=&amp;geo_schk_min=&amp;geo_schk_max=&amp;scan_geb=&amp;spalten[]=coords&amp;spalten[]=owner&amp;spalten[]=types&amp;spalten[]=planiname&amp;spalten[]=important_specials&amp;sortby[]=coords&amp;orders[]=0',
			'span' => FormatTime($row[2]-$row[1]),
			'age' => ActualityColor($row[2]),
			'pts' => number_format($row[3], 2, ',', '.'),
			'num' => ++$i,
			'delid' => EscapeJSU($row[0]),
		);
	}
	$content['dellink'] = EscapeJS($scripturl.'/index.php?action=highscore_inactives');
	
	TemplateInit('main');
	TemplateInactives();
}
 
 ?>
