<?php
if (!defined("dddfd"))
	die("Hacking attempt");
	
function WarKbs() {
	global $content, $pre, $fake_att, $fake_def;
	
	$limit = isset($_REQUEST['limit']) ? intval($_REQUEST['limit']) : 0;
	
	$now = time();
	$content['wars'] = array();
	$q_wars = DBQuery("SELECT GROUP_CONCAT(id SEPARATOR ','), name FROM {$pre}wars WHERE {$now} BETWEEN begin AND end GROUP BY name", __FILE__, __LINE__);
	
	while($row_wars = mysql_fetch_row($q_wars)) {
		$wardata = array(
			'name' => EscapeOU($row_wars[1]),
			'kbs' => array(),
		);
		$wardata['kbs'] = array();
		$q = DBQuery("SELECT iwid, hash, timestamp, att, attally, def, defally, attvalue, attloss, defvalue, defloss, raidvalue, bombvalue, start, dst FROM {$pre}war_kbs WHERE warid IN (".$row_wars[0].") ORDER BY timestamp DESC LIMIT ".($limit*50).",".(($limit+1)*50), __FILE__, __LINE__);
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
		$q = DBQuery("SELECT id, iwid, iwhash, time, gala, sys, pla, typ, planityp, objekttyp, ownername, ownerally, fe+2*st+4*vv+1.5*ch+2*ei+4*wa+en FROM {$pre}scans WHERE warid IN (".$row_wars[0].") ORDER BY time DESC LIMIT ".($limit*100).",".(($limit+1)*100), __FILE__, __LINE__);
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
			);
		}
		$content['wars'][] = $wardata;
	}
	$content['hasWars'] = !empty($content['wars']);
	
	TemplateInit('wars');
	TemplateWarKbs();
}

function ScanPrint() {
	global $pre, $scan, $user, $content;
	$id = intval(Param('id'));
	$user['theme'] = 'default';
	
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