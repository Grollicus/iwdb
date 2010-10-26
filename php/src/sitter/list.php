<?php
if(!defined('dddfd'))
	die("epic fail.");

function SitterList() {
	global $content, $pre;
	
	$q = DBQuery("SELECT igm_data.id, igm_data.igmname, igm_data.accounttyp, igm_data.squad, igm_data.ikea, igm_data.mdp, 0, MIN(flotten.ankunft) AS flottenAnkunft, igm_data.lastLogin
FROM (({$pre}igm_data AS igm_data)
LEFT JOIN {$pre}universum AS universum ON igm_data.igmname = universum.ownername)
LEFT JOIN {$pre}flotten AS flotten ON flotten.action IN ('Angriff', 'Sondierung (Gebäude/Ress)', 'Sondierung (Schiffe/Def/Ress)') AND universum.ID=flotten.zielid
GROUP BY igm_data.id", __FILE__, __LINE__);
	$content['list'] = array();
	$now = time();
	$accountTypen = array(
		'fle' => '<b>Fleeter</b>',
		'bud' => 'Buddler',
		'mon' => 'Monarch',
		'all' => 'Allrounder',
	);
	//Ergebnis noch nach flottenAnkunft sortieren, das geht in SQL nicht ohne zweiten Riesenumstand
	$sortArr = array();
	while($row = mysql_fetch_row($q)) {
		//Hierhin verlagert weil 2 Subqrys da macht MySQL Ärger + als Join umgeformt ists zu langsam
		$row[6] = DBQueryOne("SELECT MIN(blub.end) FROM (SELECT building.uid AS uid, MAX(building.end) AS end FROM {$pre}building AS building WHERE building.uid=".$row[0].($row[3] == 1 ? ' AND building.plani=0' : '')." GROUP BY building.plani) AS blub group by blub.uid", __FILE__, __LINE__);
		$sortArr[] = $row;
	}
	function sortCB($a, $b) {
		if(!is_null($a[7]) && is_null($b[7]) || $a[7] < $b[7]) return -1;
		if(is_null($a[7]) && !is_null($b[7]) || $a[7] > $b[7]) return  1;
		if($a[6] < $b[6]) return -1;
		if($a[6] > $b[6]) return  1;
		return 0;
	}
	usort($sortArr, "sortCB");
	
	foreach($sortArr as $row) {
		$content['list'][] = array(
			'ID' => $row[0],
			'igmName' => EscapeOU($row[1]),
			'accountTyp' => $accountTypen[$row[2]],
			'squad' => EscapeOU($row[3]),
			'hasIkea' => $row[4] == '1',
			'hasMdP' => $row[5] == '1',
			'bauEnde' => ($row[6] == NULL || $row[6] < $now) ? 'Leerlauf?' : FormatDate($row[6]),
			'angriffAnkunft' => ($row[7] == NULL) ? '-' : FormatDate($row[7]),
			'actuality' => LastLoginColor($row[8]),
		);
	}
	TemplateInit('sitter');
	TemplateSitterList();
}

?>
