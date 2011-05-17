<?php

if(!defined('dddfd') || dddfd != 'script')
	exit();

function TechtreeScriptQuery() {
	if(!isset($_REQUEST['t']) || !isset($_REQUEST['id']))
		StopExecution();
	switch($_REQUEST['t']) {
		default:
		case '0':
			TechtreeShowDetails(intval($_REQUEST['id']));
			break;
		case '1':
			TechtreeShowPathToItem(intval($_REQUEST['id']));
			break;
	}
}

function TechtreeShowDetails($id) {
	global $content, $ID_MEMBER, $pre, $user;
	
	$forschungs_plani = DBQueryOne("SELECT forschungs_plani FROM {$pre}users WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
	
	$q = DBQuery("SELECT techtree_items.Name, techtree_items.Type, IFNULL(techtree_useritems.count, 0) 
FROM ({$pre}techtree_reqs AS techtree_reqs INNER JOIN {$pre}techtree_items AS techtree_items ON techtree_reqs.ItemID = techtree_items.ID) 
LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.itemid AND techtree_useritems.uid=".$user['igmuser']." AND (techtree_useritems.coords = '' OR techtree_useritems.coords = '{$forschungs_plani}')
WHERE techtree_reqs.RequiresID={$id} ORDER BY techtree_items.depth",__FILE__, __LINE__);
	$content['erm'] = array();
	while ($row = mysql_fetch_row($q)) {
		$content['erm'][] = array(
			'name' => $row[0],
			'typ' => $row[1],
			'anz' => $row[2],
			'done' => ($row[2] > 0),
		);
	}
	
	$q = DBQuery("SELECT techtree_items.Name, techtree_items.Type, IFNULL(techtree_useritems.count, 0) 
FROM ({$pre}techtree_reqs AS techtree_reqs INNER JOIN {$pre}techtree_items AS techtree_items ON techtree_reqs.RequiresID = techtree_items.ID ) 
LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.itemid AND techtree_useritems.uid=".$user['igmuser']." AND (techtree_useritems.coords = '' OR techtree_useritems.coords = '{$forschungs_plani}')
WHERE techtree_reqs.ItemID={$id} ORDER BY techtree_items.depth",__FILE__, __LINE__);
	$content['ben'] = array();
	while ($row = mysql_fetch_row($q)) {
		$content['ben'][] = array(
			'name' => $row[0],
			'typ' => $row[1],
			'anz' => $row[2],
			'done' => ($row[2] > 0),
		);
	}
	
	$q = DBQuery("SELECT Stufe, Dauer, bauE, bauS, bauC, bauV, bauEis, bauW, bauEn, bauCr, bauBev, bauFP, E, S, C, V, Eis, W, En, Cr, Bev, FP, Sonstiges FROM {$pre}techtree_stufen WHERE ItemID={$id}", __FILE__, __LINE__);
	$content['stufen'] = array();
	$ress = array('Eisen', 'Stahl', 'Chemie', 'VV4A', 'Eis', 'Wasser', 'Energie', 'Credits', 'Bevölkerung', 'FP');
	$short = array('fe', 'st', 'ch', 'vv', 'ei', 'wa', 'en', 'cr', 'bev', 'fp');
	while($row = mysql_fetch_row($q)) {
		
		$kosten = array();
		for($i = 2; $i < 12; ++$i) {
			if($row[$i] > 0) {
				$kosten[] = array(
					'name' => $ress[$i-2],
					'short' => $short[$i-2],
					'anz' => number_format($row[$i], 0, ',', '.'),
				);
			}
		}
		$bringt = array();
		for($i = 12; $i < 22; ++$i) {
			if($row[$i] > 0) {
				$bringt[] = array(
					'name' => $ress[$i-12],
					'short' => $short[$i-12],
					'anz' => number_format($row[$i], 0, ',', '.'),
				);
			}
		}
		
		$content['stufen'][] = array(
			'stufe' => $row[0],
			'dauer' => $row[1] > 0 ? FormatTime($row[1]) : '',
			'kosten' => $kosten,
			'bringt' => $bringt,
			'bringtext' => $row[22],
		);
	}
	
	$dta = DBQueryOne("SELECT Beschreibung FROM {$pre}techtree_items WHERE ID={$id}", __FILE__, __LINE__);
	$content['beschreibung'] = EscapeOU($dta); 
	
	TemplateInit('techtree');
	sTemplateTechtreeDetails();
}

function TechtreeShowPathToItem($itemid) {
	global $forschungs_plani, $pre, $ID_MEMBER;
	$forschungs_plani = DBQueryOne("SELECT forschungs_plani FROM {$pre}users WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
	$reqs = array();
	
	getReqs(array($itemid), $reqs);
	$gesfp = 0;
	$needfp = 0;
	echo '<b>Forschungsweg zu ', EscapeOU($reqs[$itemid][0]), '</b><br />';
		
	foreach($reqs as $item) {
		PrintItem($item[0], $item[1], $item[3] > 0, $item[2]);
		$gesfp += $item[2];
		if($item[3] == 0)
			$needfp += $item[2];
	}
	echo '<i>Es fehlen noch '.number_format($needfp,0,'','.').' FP (von '.number_format($gesfp,0,'','.').').</i>';
}

function PrintItem($name, $type, $done, $fp) {
	echo '<div class="techtree_',$type, $done ? '_done' : '', '">', EscapeOU($name), $type == 'for' ? ' ('.number_format($fp,0,'','.').' FP)' : '', '</div>';
}

function getReqs($ids, &$ret) {
	global $ID_MEMBER, $pre, $forschungs_plani, $user;
	$q = DBQuery("SELECT RequiresID FROM {$pre}techtree_reqs WHERE ItemID IN ('".implode("', '", $ids). "')", __FILE__, __LINE__);
	$req = array();
	while($row = mysql_fetch_row($q)) {
		$req[] = $row[0];
	}
	if(!empty($req))
		getReqs($req, $ret);
	$idstr = "'".implode("', '", $ids). "'";
	/*$q = DBQuery("(SELECT techtree_items.ID, techtree_items.Name, techtree_items.Type, 0 FROM {$pre}techtree_items AS techtree_items WHERE ID IN ({$idstr})
) UNION DISTINCT (
SELECT techtree_items.ID, techtree_items.Name, techtree_items.Type, 1 FROM {$pre}techtree_items AS techtree_items INNER JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.itemid WHERE ID IN ({$idstr}) AND techtree_useritems.uid = '".$user['igmuser']."')", __FILE__, __LINE__, 2);
*/
	$q = DBQuery("SELECT techtree_items.ID, techtree_items.Name, techtree_items.Type, IFNULL(techtree_useritems.count, 0) AS Stufe, techtree_stufen.bauFP
FROM (({$pre}techtree_items AS techtree_items LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.itemid AND techtree_useritems.uid=".$user['igmuser']." AND (techtree_useritems.coords = '' OR techtree_useritems.coords = '{$forschungs_plani}'))
	LEFT JOIN {$pre}techtree_stufen AS techtree_stufen ON techtree_items.ID=techtree_stufen.ItemID AND Stufe = techtree_stufen.Stufe)
WHERE techtree_items.ID IN ({$idstr}) ORDER BY techtree_items.depth", __FILE__, __LINE__);
	while($row = mysql_fetch_row($q)) {
		$ret[$row[0]] = array($row[1], $row[2], $row[4], $row[3]); //Nicht direkt in einen String schmeissen, so werden Duplikate aussortiert
	}
}

?>
