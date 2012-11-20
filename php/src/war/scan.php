<?php
if (!defined("dddfd"))
	die("Hacking attempt");

function WarScans() {
	global $content, $pre, $scripturl;
	
	$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;

	$filter_link = "";
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
		$filter_scans .= "AND ownername LIKE '%".EscapeDB(Param('scan_owner'))."%' ";
	}
	$content['filter']['scan_ally'] = '';
	if(!empty($_REQUEST['scan_ally'])) {
		$content['filter']['scan_ally'] = EscapeO(Param('scan_ally'));
		$filter_link .= '&amp;scan_ally='.EscapeO(Param('scan_ally'));
		$filter_scans .= "AND ownerally LIKE '%".EscapeDB(Param('scan_ally'))."%' ";
	}
	$content['filter']['scan_ress_score'] = '';
	if(!empty($_REQUEST['scan_ress_score'])) {
		$content['filter']['scan_ress_score'] = EscapeO(Param('scan_ress_score'));
		$filter_link .= '&amp;scan_ress_score='.EscapeO(Param('scan_owner'));
		$filter_scans .= "AND ressScore >= ".intval(Param('scan_owner')).' ';
	}
	$content['filter']['scan_ress_fe'] = '';
	if(!empty($_REQUEST['scan_ress_fe'])) {
		$content['filter']['scan_ress_fe'] = EscapeO(Param('scan_ress_fe'));
		$filter_link .= '&amp;scan_ress_fe='.EscapeO(Param('scan_ress_fe'));
		$filter_scans .= "AND fe >= ".intval(Param('scan_ress_fe')).' ';
	}
	$content['filter']['scan_ress_st'] = '';
	if(!empty($_REQUEST['scan_ress_st'])) {
		$content['filter']['scan_ress_st'] = EscapeO(Param('scan_ress_st'));
		$filter_link .= '&amp;scan_ress_st='.EscapeO(Param('scan_ress_st'));
		$filter_scans .= "AND st >= ".intval(Param('scan_ress_st')).' ';
	}
	$content['filter']['scan_ress_ch'] = '';
	if(!empty($_REQUEST['scan_ress_ch'])) {
		$content['filter']['scan_ress_ch'] = EscapeO(Param('scan_ress_ch'));
		$filter_link .= '&amp;scan_ress_ch='.EscapeO(Param('scan_ress_ch'));
		$filter_scans .= "AND ch >= ".intval(Param('scan_ress_ch')).' ';
	}
	$content['filter']['scan_ress_vv'] = '';
	if(!empty($_REQUEST['scan_ress_vv'])) {
		$content['filter']['scan_ress_vv'] = EscapeO(Param('scan_ress_vv'));
		$filter_link .= '&amp;scan_ress_vv='.EscapeO(Param('scan_ress_vv'));
		$filter_scans .= "AND vv >= ".intval(Param('scan_ress_vv')).' ';
	}
	$content['filter']['scan_ress_ei'] = '';
	if(!empty($_REQUEST['scan_ress_ei'])) {
		$content['filter']['scan_ress_ei'] = EscapeO(Param('scan_ress_ei'));
		$filter_link .= '&amp;scan_ress_ei='.EscapeO(Param('scan_ress_ei'));
		$filter_scans .= "AND ei >= ".intval(Param('scan_ress_ei')).' ';
	}
	$content['filter']['scan_ress_wa'] = '';
	if(!empty($_REQUEST['scan_ress_wa'])) {
		$content['filter']['scan_ress_wa'] = EscapeO(Param('scan_ress_wa'));
		$filter_link .= '&amp;scan_ress_wa='.EscapeO(Param('scan_ress_wa'));
		$filter_scans .= "AND wa >= ".intval(Param('scan_ress_wa')).' ';
	}
	$content['filter']['scan_ress_en'] = '';
	if(!empty($_REQUEST['scan_ress_en'])) {
		$content['filter']['scan_ress_en'] = EscapeO(Param('scan_ress_en'));
		$filter_link .= '&amp;scan_ress_en='.EscapeO(Param('scan_ress_en'));
		$filter_scans .= "AND en >= ".intval(Param('scan_ress_en')).' ';
	}
	
	$now = time();
	$content['wars'] = array();
	$showall = isset($_REQUEST['showall']) && $_REQUEST['showall'] == '1';
	$q_wars = DBQuery("SELECT GROUP_CONCAT(id SEPARATOR ','), name FROM {$pre}wars ".($showall ? '' : "WHERE {$now} BETWEEN begin AND end")." GROUP BY name", __FILE__, __LINE__);
	
	while($row_wars = mysql_fetch_row($q_wars)) {
		$wardata = array(
			'name' => EscapeOU($row_wars[1]),
			'scans' => array(),
		);
		
		$q = DBQuery("SELECT id, iwid, iwhash, time, gala, sys, pla, typ, planityp, objekttyp, ownername, ownerally, fe,st,vv,ch,ei,wa,en, ressScore, score FROM {$pre}scans WHERE warid IN (".$row_wars[0].") {$filter_scans} ORDER BY time DESC LIMIT ".($limit*100).",".(($limit+1)*100), __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			$wardata['scans'][] = array(
				'id' => $row[0],
				'url' => 'http://www.icewars.de/portal/kb/de/sb.php?id='.$row[1].'&amp;md_hash='.$row[2],
				'date' => FormatDate($row[3]),
				'coords' => $row[4].':'.$row[5].':'.$row[6],
				'typ' => EscapeOU($row[7]),
				'planityp' => EscapeOU($row[8]),
				'objekttyp' =>  EscapeOU($row[9]),
				'ownerName' => EscapeOU($row[10]),
				'ownerAlly' => EscapeOU($row[11]),
				'ress' => array(
						'fe' => number_format($row[12], 0, ',', '.'),
						'st' => number_format($row[13], 0, ',', '.'),
						'vv' => number_format($row[14], 0, ',', '.'),
						'ch' => number_format($row[15], 0, ',', '.'),
						'ei' => number_format($row[16], 0, ',', '.'),
						'wa' => number_format($row[17], 0, ',', '.'),
						'en' => number_format($row[18], 0, ',', '.'),
					),
				'ressScore' => number_format($row[19], 0, ',', '.'),
				'score' => number_format($row[20], 0, ',', '.'),
			);
		}
		$content['wars'][] = $wardata;
	}
	$content['hasWars'] = !empty($content['wars']);
	$content['hasPrev'] = $limit > 0;
	$content['prevLink'] = $scripturl.'/index.php?action=war_scans&amp;limit='.($limit-1).($showall ? '&amp;showall=1' : '').$filter_link;
	$content['nextLink'] = $scripturl.'/index.php?action=war_scans&amp;limit='.($limit+1).($showall ? '&amp;showall=1' : '').$filter_link;
	$content['showAllLink'] = $scripturl.'/index.php?action=war_scans&amp;limit='.($limit).'&amp;showall=1'.$filter_link;
	$content['submitUrl'] = $scripturl.'/index.php?action=war_scans'.($showall ? '&amp;showall=1' : '');
	
	TemplateInit('wars');
	TemplateWarScans();
}

function ScanPrint() {
	global $pre, $scan, $user, $content;
	$id = intval(Param('id'));
	
	$content['hasGebs'] = false;
	$content['hasShips'] = false;

	$row = DBQueryOne("SELECT typ FROM {$pre}scans WHERE ID={$id}", __FILE__, __LINE__);
	
	$content['scan'] = array();
	
	if($row == 'geb') {
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