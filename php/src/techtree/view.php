<?php
if(!defined('dddfd'))
	exit();

function TechtreeView() {
	global $pre, $content, $ID_MEMBER, $user;
	
	if(isset($_POST['filter']) && !empty($_POST['filter'])) {
		$filter = Param('filter');
		$filterstr = "WHERE NAME LIKE '%".str_replace("*", "%", EscapeDB($filter))."%'";
		$content['filterstr'] = EscapeO($filter);
	} else {
		$filterstr = "";
		$content['filterstr'] = '';
	}
	
	$forschungs_plani = DBQueryOne("SELECT forschungs_plani FROM {$pre}users WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
	
	$q = DBQuery("SELECT ID, Name, Type, global, Class
FROM {$pre}techtree_items AS techtree_items LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.itemid AND techtree_useritems.uid=".$user['igmuser']." AND (techtree_useritems.coords = '' OR techtree_useritems.coords = '{$forschungs_plani}')
 ".$filterstr. " ORDER BY depth, Name LIMIT 0, 30", __FILE__, __LINE__);

	$content['items'] = array();
	while($row = mysql_fetch_row($q)) {
		$content['items'][] = array(
			'id' => $row[0],
			'name' => EscapeOU($row[1]),
			'typ' => $row[2],
			'isGlobal' => $row[3],
			'klasse' => $row[4],
			'done' => true,
		);
	}
	TemplateInit('techtree');
	TemplateTechtreeView();
}

function TechtreeListMissing() {
	global $content, $pre;
	
	$q = DBQuery("SELECT Name, type FROM {$pre}techtree_items WHERE Beschreibung IS NULL", __FILE__, __LINE__);
	$content['missing'] = array();
	while($row = mysql_fetch_row($q)) {
		$content['missing'][] = array(
			'name' => EscapeOU($row[0]),
			'typ' => $row[1],
		);
	}
	TemplateInit('techtree');
	TemplateTechtreeListMissing();
}

?>
