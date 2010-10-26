<?php
if(!defined('dddfd'))
	exit();

function UniWhosIn() {
	global $content, $pre;
	
	if(isset($_REQUEST['gal'])) {
		$content['gal'] = intval($_REQUEST['gal']);
		$q = DBQuery("SELECT {$pre}uni_userdata.allytag FROM {$pre}universum INNER JOIN {$pre}uni_userdata ON {$pre}universum.ownername = {$pre}uni_userdata.name
				WHERE {$pre}universum.gala = ".intval($_REQUEST['gal'])." AND {$pre}uni_userdata.allytag <> '' GROUP BY {$pre}uni_userdata.allytag ORDER BY {$pre}uni_userdata.allytag", __FILE__, __LINE__);
		$content['allys'] = array();
		while($row = mysql_fetch_row($q))
			$content['allys'][] = $row[0];
		$content['allynum'] = mysql_num_rows($q);
	} else {
		$content['gal'] = '';
	}
	TemplateInit('uni');
	TemplateUniWhosIn();
}

function UniAllyAt() {
	global $content, $pre;
	
	if(isset($_REQUEST['allytag'])) {
		$content['allytag'] = EscapeO($_REQUEST['allytag']);
		$q = DBQuery("SELECT {$pre}universum.gala FROM {$pre}uni_userdata INNER JOIN {$pre}universum ON {$pre}universum.ownername = {$pre}uni_userdata.name
				WHERE {$pre}uni_userdata.allytag LIKE '%".EscapeDB(Param('allytag'))."%' GROUP BY {$pre}universum.gala ORDER BY {$pre}universum.gala", __FILE__, __LINE__);
		$content['allygals'] = array();
		while($row = mysql_fetch_row($q))
			$content['allygals'][] = $row[0];
		$content['allynum'] = mysql_num_rows($q);
	} else {
		$content['allytag'] = '';
	}
	TemplateInit('uni');
	TemplateUniAllyAt();
}

//NOTICE: Dies ist einer der aufwendigsten Querys in Sachen Uni. Optimieren? ;)
function UniAllyOverview() {
	global $content, $pre;
	
	$q = DBQuery("SELECT uni_userdata.allytag, GROUP_CONCAT(DISTINCT universum.gala SEPARATOR ', '), count(distinct universum.ownername), count(universum.id) 
		FROM {$pre}uni_userdata AS uni_userdata INNER JOIN {$pre}universum AS universum ON universum.ownername = uni_userdata.name WHERE uni_userdata.allytag <> '' GROUP BY uni_userdata.allytag ORDER BY uni_userdata.allytag", __FILE__, __LINE__);
	//$q = DBQuery("SELECT uni_userdata.allytag, ((SELECT GROUP_CONCAT(universum.gala, count(*)) from {$pre}universum as universum left join {$pre}uni_userdata as tag on universum.ownername = tag.name WHERE tag.allytag = uni_userdata.allytag) SEPARATOR ', ') 
	//FROM {$pre}uni_userdata as uni_userdata where uni_userdata.allytag <> '' group by uni_userdata.allytag", __FILE__, __LINE__);
	
	$content['uniallydata'] = array();
	while($row = mysql_fetch_row($q))
		$content['uniallydata'][] = array(
			'ally' => $row[0],
			'galas' => $row[1],
			'members' => $row[2],
			'planis' => $row[3],
		);
	
	TemplateInit('uni');
	TemplateUniAllyOverview();
}
?>
