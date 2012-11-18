<?php
if (!defined("dddfd"))
	die("Hacking attempt");
	
function WarKbs() {
	global $content, $pre, $fake_att, $fake_def, $scripturl;
	
	$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

	$filter_kbs = "";
	$filter_link = "";
	$content['filter']['kb_att'] = '';
	if(!empty($_REQUEST['kb_att'])) {
		$content['filter']['kb_att'] = EscapeO(Param('kb_att'));
		$filter_link .= '&amp;kb_att='.EscapeO(Param('kb_att'));
		$filter_kbs .= "AND att LIKE '%".EscapeDB(Param('kb_att'))."%' ";
	}
	$content['filter']['kb_att_ally'] = '';
	if(!empty($_REQUEST['kb_att_ally'])) {
		$content['filter']['kb_att_ally'] = EscapeO(Param('kb_att_ally'));
		$filter_link .= '&amp;kb_att_ally='.EscapeO(Param('kb_att_ally'));
		$filter_kbs .= "AND attally LIKE '%".EscapeDB(Param('kb_att_ally'))."%' ";
	}
	$content['filter']['kb_def'] = '';
	if(!empty($_REQUEST['kb_def'])) {
		$content['filter']['kb_def'] = EscapeO(Param('kb_def'));
		$filter_link .= '&amp;kb_def='.EscapeO(Param('kb_def'));
		$filter_kbs .= "AND def LIKE '%".EscapeDB(Param('kb_def'))."%' ";
	}
	$content['filter']['kb_def_ally'] = '';
	if(!empty($_REQUEST['kb_def_ally'])) {
		$content['filter']['kb_def_ally'] = EscapeO(Param('kb_def_ally'));
		$filter_link .= '&amp;kb_def_ally='.EscapeO(Param('kb_def_ally'));
		$filter_kbs .= "AND defally LIKE '%".EscapeDB(Param('kb_def_ally'))."%' ";
	}
	$content['filter']['kb_start'] = '';
	if(!empty($_REQUEST['kb_start'])) {
		$content['filter']['kb_start'] = EscapeO(Param('kb_start'));
		$filter_link .= '&amp;kb_start='.EscapeO(Param('kb_start'));
		$filter_kbs .= "AND start LIKE '".EscapeDB(Param('kb_start'))."%' ";
	}
	$content['filter']['kb_dst'] = '';
	if(!empty($_REQUEST['kb_dst'])) {
		$content['filter']['kb_dst'] = EscapeO(Param('kb_dst'));
		$filter_link .= '&amp;kb_dst='.EscapeO(Param('kb_dst'));
		$filter_kbs .= "AND dst LIKE '".EscapeDB(Param('kb_dst'))."%' ";
	}
	$content['filter']['att_value'] = '';
	if(!empty($_REQUEST['att_value'])) {
		$content['filter']['att_value'] = EscapeO(Param('att_value'));
		$filter_link .= '&amp;att_value='.EscapeO(Param('att_value'));
		$filter_kbs .= "AND attvalue >= ".intval(str_replace('.', '', Param('att_value')))." ";
	}
	$content['filter']['def_value'] = '';
	if(!empty($_REQUEST['def_value'])) {
		$content['filter']['def_value'] = EscapeO(Param('def_value'));
		$filter_link .= '&amp;def_value='.EscapeO(Param('def_value'));
		$filter_kbs .= "AND defvalue >= ".intval(str_replace('.', '', Param('def_value')))." ";
	}
	
	$now = time();
	$content['wars'] = array();
	$showall = isset($_REQUEST['showall']) && $_REQUEST['showall'] == '1';
	$q_wars = DBQuery("SELECT GROUP_CONCAT(id SEPARATOR ','), name FROM {$pre}wars ".($showall ? '' : "WHERE {$now} BETWEEN begin AND end")." GROUP BY name", __FILE__, __LINE__);
	
	while($row_wars = mysql_fetch_row($q_wars)) {
		$wardata = array(
			'name' => EscapeOU($row_wars[1]),
			'kbs' => array(),
		);
		
		$q = DBQuery("SELECT iwid, hash, timestamp, att, attally, def, defally, attvalue, attloss, defvalue, defloss, raidvalue, bombvalue, attwin, start, dst FROM {$pre}war_kbs WHERE warid IN (".$row_wars[0].") {$filter_kbs} ORDER BY timestamp DESC LIMIT ".($limit*50).",".(($limit+1)*50), __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			$wardata['kbs'][] = array(
				'id' => $row[0],
				'hash' => $row[1],
				'url' => 'http://www.icewars.de/portal/kb/de/kb.php?id='.$row[0].'&amp;md_hash='.$row[1],
				'date' => FormatDate($row[2]),
				'angreiferName' => EscapeOU($row[3]),
				'angreiferAlly' => EscapeOU($row[4]),
				'verteidigerName' => EscapeOU($row[5]),
				'verteidigerAlly' => EscapeOU($row[6]),
				'angreiferWert' => number_format($row[7], 0, ',', '.'),
				'angreiferVerlust' => number_format(-$row[8], 0, ',', '.'),
				'verteidigerWert' => number_format($row[9], 0, ',', '.'),
				'verteidigerVerlust' => number_format(-$row[10], 0, ',', '.'),
				'raidWert' => number_format($row[11], 0, ',', '.'),
				'bombWert' => number_format($row[12], 0, ',', '.'),
				'attWin' => $row[13] == '1',
				'startKoords' => nl2br(EscapeOU($row[14])),
				'zielKoords' => EscapeOU($row[15]),
				'isFake' => $row[7] < $fake_att && $row[9] < $fake_def,
			);
		}

		$content['wars'][] = $wardata;
	}
	$content['hasWars'] = !empty($content['wars']);
	$content['hasPrev'] = $limit > 0;
	$content['prevLink'] = $scripturl.'/index.php?action=war_kbs&amp;limit='.($limit-1).($showall ? '&amp;showall=1' : '').$filter_link;
	$content['nextLink'] = $scripturl.'/index.php?action=war_kbs&amp;limit='.($limit+1).($showall ? '&amp;showall=1' : '').$filter_link;
	$content['showAllLink'] = $scripturl.'/index.php?action=war_kbs&amp;limit='.($limit).'&amp;showall=1'.$filter_link;

	$content['submitUrl'] = $scripturl.'/index.php?action=war_kbs'.($showall ? '&amp;showall=1' : '');
	
	TemplateInit('wars');
	TemplateWarKbs();
}

function WarStats() {
	global $content, $pre, $scripturl;
	
	$content['resp'] = '';
	if(isset($_REQUEST['refresh'])) {
		$msg = QueryIWDBUtil('warstats', array(intval($_REQUEST['refresh'])), $resp) ? 'ok' : 'fail';
		Redirect($scripturl.'/index.php?action=war_stats&msg='.$msg);
	}
	
	if(isset($_REQUEST['msg'])) {
		$messages = array(
			'ok' => 'Erfolgreich aktualisiert!',
			'fail' => 'MEH :( Refresh fehlgeschlagen',
		);
		$content['resp'] = $messages[$_REQUEST['msg']];
	}
	
	$q = DBQuery("SELECT wars.id, wars.name, stats.stats FROM {$pre}wars AS wars LEFT JOIN {$pre}war_stats AS stats ON wars.id=stats.id", __FILE__, __LINE__);
	$content['stats'] = array();
	while($row = mysql_fetch_row($q)) {
		$content['stats'][] = array(
			'id' => intval($row[0]),
			'name' => EscapeOU($row[1]),
			'refreshLink' => $scripturl.'/index.php?action=war_stats&amp;refresh='.intval($row[0]),
			'stats' => $row[2], //unescaped weil der Bot da html einträgt
		);
	}
	
	TemplateInit('wars');
	TemplateWarStats();
	
}
?>
