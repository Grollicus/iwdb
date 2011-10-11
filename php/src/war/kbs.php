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
	
	$filter_scans = "";
	$content['filter']['scan_type'] = array(
		'*' => array('name' => '*', 'selected' => !isset($_REQUEST['scan_type']) || ($_REQUEST['scan_type'] != 'geb' && $_REQUEST['scan_type'] != 'schiff')),
		'geb' => array('name' => 'geb', 'selected' => (isset($_REQUEST['scan_type']) && $_REQUEST['scan_type'] == 'geb')),
		'schiff' => array('name' => 'schiff', 'selected' => (isset($_REQUEST['scan_type']) && $_REQUEST['scan_type'] == 'schiff')),
	);
	if(isset($_REQUEST['scan_type'])) {
		switch($_REQUEST['scan_type']) {
			case 'schiff':
				$filter_scans .= "AND typ='schiff' ";
				$filter_link .= '&amp;scan_type=schiff';
				break;
			case 'geb':
				$filter_scans .= "AND typ='geb' ";
				$filter_link .= '&amp;scan_type=geb';
				break;
		}
	}
	$content['filter']['scan_coords'] = '';
	if(!empty($_REQUEST['scan_coords'])) {
		$content['filter']['scan_coords'] = EscapeO(Param('scan_coords'));
		$filter_link .= '&amp;scan_coords='.EscapeO(Param('scan_coords'));
		$c = explode(':', Param('scan_coords'));
		if(count($c) > 0)
			$filter_scans .= 'AND gala='.intval($c[0]).' ';
		if(count($c) > 1)
			$filter_scans .= 'AND sys='.intval($c[1]).' ';
		if(count($c) > 2)
			$filter_scans .= 'AND pla='.intval($c[2]).' ';
	}
	$content['filter']['scan_owner'] = '';
	if(!empty($_REQUEST['scan_owner'])) {
		$content['filter']['scan_owner'] = EscapeO(Param('scan_owner'));
		$filter_link .= '&amp;scan_owner='.EscapeO(Param('scan_owner'));
		$filter_scans .= "AND ownername LIKE '%".EscapeDB(Param('scan_owner'))."%'";
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
		
		$q = DBQuery("SELECT iwid, hash, timestamp, att, attally, def, defally, attvalue, attloss, defvalue, defloss, raidvalue, bombvalue, start, dst FROM {$pre}war_kbs WHERE warid IN (".$row_wars[0].") {$filter_kbs} ORDER BY timestamp DESC LIMIT ".($limit*50).",".(($limit+1)*50), __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			$wardata['kbs'][] = array(
				'id' => $row[0],
				'hash' => $row[1],
				'url' => 'http://www.icewars.de/portal/kb/de/kb.php?id='.$row[0].'&md_hash='.$row[1],
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
				'startKoords' => nl2br(EscapeOU($row[13])),
				'zielKoords' => EscapeOU($row[14]),
				'isFake' => $row[7] < $fake_att && $row[9] < $fake_def,
			);
		}
		
		$wardata['scans'] = array();
		$q = DBQuery("SELECT id, iwid, iwhash, time, gala, sys, pla, typ, planityp, objekttyp, ownername, ownerally, ressScore, score FROM {$pre}scans WHERE warid IN (".$row_wars[0].") {$filter_scans} ORDER BY time DESC LIMIT ".($limit*100).",".(($limit+1)*100), __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			$wardata['scans'][] = array(
				'id' => $row[0],
				'url' => 'http://www.icewars.de/portal/kb/de/sb.php?id='.$row[1].'&md_hash='.$row[2],
				'date' => FormatDate($row[3]),
				'coords' => $row[4].':'.$row[5].':'.$row[6],
				'typ' => EscapeOU($row[7]),
				'planityp' => EscapeOU($row[8]),
				'objekttyp' =>  EscapeOU($row[9]),
				'ownerName' => EscapeOU($row[10]),
				'ownerAlly' => EscapeOU($row[11]),
				'ress' => number_format($row[12], 0, ',', '.'),
				'score' => number_format($row[13], 0, ',', '.'),
			);
		}
		$content['wars'][] = $wardata;
	}
	$content['hasWars'] = !empty($content['wars']);
	$content['hasPrev'] = $limit > 0;
	$content['prevLink'] = $scripturl.'/index.php?action=war_data&amp;limit='.($limit-1).($showall ? '&amp;showall=1' : '').$filter_link;
	$content['nextLink'] = $scripturl.'/index.php?action=war_data&amp;limit='.($limit+1).($showall ? '&amp;showall=1' : '').$filter_link;
	$content['showAllLink'] = $scripturl.'/index.php?action=war_data&amp;limit='.($limit).'&amp;showall=1.$filter_link';

	$content['submitUrl'] = $scripturl.'/index.php?action=war_data'.($showall ? '&amp;showall=1' : '');
	
	TemplateInit('wars');
	TemplateWarKbs();
}

function ScanPrint() {
	global $pre, $scan, $user, $content;
	$id = intval(Param('id'));
	
	$content['hasGebs'] = false;
	$content['hasShips'] = false;

	$row = DBQueryOne("SELECT typ, fe, st, vv, ch, ei, wa, en FROM {$pre}scans WHERE ID={$id}", __FILE__, __LINE__);
	
	$content['scan'] = array(
		'fe' => number_format($row[1], 0, ',', '.'),
		'st' => number_format($row[2], 0, ',', '.'),
		'vv' => number_format($row[3], 0, ',', '.'),
		'ch' => number_format($row[4], 0, ',', '.'),
		'ei' => number_format($row[5], 0, ',', '.'),
		'wa' => number_format($row[6], 0, ',', '.'),
		'en' => number_format($row[7], 0, ',', '.'),
	);
	
	if($row[0] == 'geb') {
		$content['hasGebs'] = true;
		$q = DBQuery("SELECT techtree_items.Name, scans_gebs.anzahl FROM {$pre}scans_gebs AS scans_gebs LEFT JOIN {$pre}techtree_items AS techtree_items ON scans_gebs.gebid=techtree_items.ID WHERE scans_gebs.scanid={$id}", __FILE__, __LINE__);
		$content['scan']['gebs'] = array();
		while($row = mysql_fetch_row($q))
			$content['scan']['gebs'][] = array('name' => EscapeOU($row[0]), 'cnt' => $row[1]);
	} else {
		$content['hasShips'] = true;
		$qry = DBQuery("SELECT id, owner, typ FROM {$pre}scans_flotten WHERE scanid={$id}", __FILE__, __LINE__);
		while($row = mysql_fetch_row($qry)) {
			$q = DBQuery("SELECT techtree_items.Name, schiffe.anz FROM {$pre}scans_flotten_schiffe AS schiffe LEFT JOIN {$pre}techtree_items AS techtree_items ON schiffe.schid = techtree_items.ID WHERE schiffe.flid=".$row[0], __FILE__, __LINE__);
			$s = array();
			while($r = mysql_fetch_row($q)) {
				$s[] = array('name' => EscapeOU($r[0]), 'cnt' => $r[1]);
			}
			$content['scan']['flotten'][] = array(
				'typ' => $row[2],
				'owner' => EscapeOU($row[1]),
				'ships' => $s,
			);
		}
	}
	
	TemplateInit('wars');
	TemplateScanPrint();
	
}
?>
