<?php
	if (!defined("dddfd"))
		die("Hacking attempt");
	
	
	function SitterView() {
		global $pre, $ID_MEMBER, $user, $content, $scripturl;
				
		$types = array(
			'Geb' => 'Bauauftrag',
			'For' => 'Forschungsauftrag',
			'Sch' => 'Schiffbauauftrag',
			'Sonst' => 'sonstiger Auftrag',
		);
		$msgs = array(
			'sitter_racecondition' => 'Sorry, den Sitterauftrag hat schon jemand erledigt!',
		);
		if(isset($_REQUEST['msg']) && isset($msgs[$_REQUEST['msg']])) {
			$content['msg'] = $msgs[$_REQUEST['msg']];
		}
		$now = time();
		
		$q = DBQuery("SELECT sitter.ID, sitter.uid, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.done=0 AND followUpTo=0 AND sitter.time <= {$now} ORDER BY time", __FILE__, __LINE__);
		$content['sitternow'] = array();
		
		while($row = mysql_fetch_row($q)) {
			$content['sitternow'][] = array(
				'time' => FormatDate($row[5]),
				'typeLong' => $types[$row[6]],
				'typeShort' => $row[6],
				'text' => SitterText($row),
				'igmName' => EscapeOU($row[4]),
				'userName' => EscapeOU($row[2]),
				'coords' => $row[9]. ':'. $row[10]. ':'. $row[11],
				'planiName' => EscapeOU($row[12]),
				'loginLink' => $scripturl.'/index.php?action=sitter_login&amp;from=sitter_view&amp;jobid='.$row[0],
				'ownershipState' => $row[1] == $ID_MEMBER ? 'own' : ($row[3] == $user['igmuser'] ? 'account' : 'none'),
			);
			/*if($a['hasOwnerLinks']) {
				$a['editLink'] = $scripturl.'/index.php?action=sitter_ownex&amp;mod='.$row[6].'&amp;ID='.$row[0];
				//TODO: $a['delLink']
				if($row[6] == 'Geb') {
					$a['canAppend'] = true;
					$a['appendLink'] = $scripturl.'/index.php?action=sitter_ownex&amp;mod='.$row[6].'&amp;angehaengtAn='.$row[0];
				} else {
					$a['canAppend'] = false;
				}
			}*/
		}
		
		$q = DBQuery("SELECT sitter.ID, sitter.uid, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.done=0 AND sitter.time > {$now} ORDER BY time LIMIT 0,10", __FILE__, __LINE__);
		$content['sittersoon'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['sittersoon'][] = array(
				'time' => FormatDate($row[5]),
				'typeLong' => $types[$row[6]],
				'typeShort' => $row[6],
				'text' => SitterText($row),
				'igmName' => EscapeOU($row[4]),
				'userName' => EscapeOU($row[2]),
				'coords' => $row[9]. ':'. $row[10]. ':'. $row[11],
				'planiName' => EscapeOU($row[12]),
				'ownershipState' => $row[1] == $ID_MEMBER ? 'own' : ($row[3] == $user['igmuser'] ? 'account' : 'other'),
			);
		}
		TemplateInit('sitter');
		TemplateSitterTaskList();
	}
	function SitterText($row) {
		$ret = '';
		$item = '';
		if($row[14] != 0) {
			$item .= $row[14].'x ';
		}
		if($row[7] != null) {
			$item .= EscapeOU($row[7]);
		}
		if($row[8] != 0) {
			$item .= ' Stufe '.$row[8];
		}
		if(!empty($item))
			$ret .= '<u>'.$item.'</u>';
		if($row[13] == 1) {
			$ret .= (!empty($ret) ? '<br/>' : '').'[Bauschleife]';
		}
		if(!empty($row[15])) {
			$ret .= (!empty($ret) ? '<br/>' : '').'<i>'.nl2br(EscapeOU($row[15])).'</i)';
		}
		return $ret;
	}
	function SitterFeindlFlottenUebersicht() {
		global $content, $pre, $scripturl;
		$q = DBQuery("SELECT startuni.gala, startuni.sys, startuni.pla, startuni.planiname, startuni.ownername, start_userdata.allytag, zieluni.gala, zieluni.sys, zieluni.pla, zieluni.planiname, zieluni.ownername, ziel_userdata.allytag, flotten.action, flotten.ankunft, flotten.id, igm_data.id
FROM (((({$pre}flotten AS flotten INNER JOIN {$pre}universum AS startuni ON flotten.startid = startuni.ID) 
	INNER JOIN {$pre}universum AS zieluni ON flotten.zielid=zieluni.ID)
	LEFT JOIN {$pre}uni_userdata AS start_userdata ON startuni.ownername = start_userdata.name)
	LEFT JOIN {$pre}uni_userdata AS ziel_userdata ON zieluni.ownername = ziel_userdata.name)
	INNER JOIN {$pre}igm_data AS igm_data ON zieluni.ownername=igm_data.igmname
WHERE flotten.action IN ('Angriff', 'Sondierung (Gebäude/Ress)','Sondierung (Schiffe/Def/Ress)')
ORDER BY flotten.ankunft ASC", __FILE__, __LINE__);
		$users = array();
		$u = '';
		while($row = mysql_fetch_row($q)) {
			if($row[10] != $u) {
				$u = $row[10];
				$c = $row[6].':'.$row[7].':'.$row[8];
			}
			if(isset($users[$u])) {
				if(isset($users[$u][$c])) {
					$users[$u][$c][] = $row;
				} else {
					$users[$u][$c] = array($row);
				}
			} else {
				$users[$u] = array($c => array($row));
			}
		}
		$content['users'] = array();
		foreach($users as $n => $u) {
			$p = array();
			$att = false;
			foreach($u as $c => $arr) {
				foreach($arr as $v) {
					$b = array(
						'startkoords' => $v[0].':'.$v[1].':'.$v[2],
						'startname' => EscapeOU($v[3]),
						'startowner' => EscapeOU($v[4]),
						'startally' => EscapeOU($v[5]),
						'zielkoords' => $c,
						'zielname' => EscapeOU($v[9]),
						'zielowner' => EscapeOU($n),
						'zielally' => EscapeOU($v[11]),
						'bewegung' => EscapeOU($v[12]),
						'ankunft' => FormatDate($v[13]),
						'ID' => $v[14],
						'uid' => $v[15],
						'gefahrenLevel' => $v[12] == 'Angriff' ? 1 : 0,
					);
					$p[] = $b;
					if(!$att && $b['gefahrenLevel'] > 0)
						$att = true;
				}
			}
			$content['users'][] = array(
				'name' => EscapeOU($n),
				'planis' => $p,
				'ersteAnkunft' => $p[0]['ankunft'],
				'loginLink' => $scripturl.'/index.php?action=sitter_login&amp;from=sitter_flotten&amp;id='.$p[0]['uid'],
				'uid' => $p[0]['uid'],
				'gefahrenLevel' => $att ? '1' : '0',
			);
		}
		TemplateInit('sitter');
		TemplateFeindlFlottenUebersicht();
	}
	function SitterOwn() {
		global $content, $pre, $ID_MEMBER, $user, $scripturl;
		
		if(isset($_REQUEST['del'])) {
			$id = intval($_REQUEST['del']);
			DBQuery("DELETE FROM {$pre}sitter WHERE ID=$id AND (done=0 AND (uid={$ID_MEMBER} OR igmid=".$user['igmuser']."))", __FILE__, __LINE__);
			if(mysql_affected_rows() >0) {
				$content['msg'] = 'Auftrag gelöscht!';
			}
		}
		
		$types = array(
			'Geb' => 'Bauauftrag',
			'For' => 'Forschungsauftrag',
			'Sch' => 'Schiffbauauftrag',
			'Sonst' => 'sonstiger Auftrag',
		);
		
		$q = DBQuery("SELECT sitter.ID, sitter.uid, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.done=0 AND (sitter.uid={$ID_MEMBER} OR sitter.igmid=".$user['igmuser'].") ORDER BY time", __FILE__, __LINE__);
		$content['jobs'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['jobs'][] = array(
				'time' => FormatDate($row[5]),
				'typeLong' => $types[$row[6]],
				'typeShort' => $row[6],
				'text' => SitterText($row),
				'igmName' => EscapeOU($row[4]),
				'userName' => EscapeOU($row[2]),
				'coords' => $row[9]. ':'. $row[10]. ':'. $row[11],
				'planiName' => EscapeOU($row[12]),
				'editLink' => $scripturl.'/index.php?action=sitter_edit&amp;page='.$row[6].'&amp;ID='.$row[0],
				'delLink' => $scripturl.'/index.php?action=sitter_own&amp;del='.$row[0],
				'hasAppendLink' => $row[6] == 'Geb',
				'appendLink' => $scripturl.'/index.php?action=sitter_edit&amp;page='.$row[6].'&amp;angehaengtAn='.$row[0],
				'ownershipState' => $row[1] == $ID_MEMBER ? 'own' : ($row[3] == $user['igmuser'] ? 'account' : 'none'),
			);
		}
		$content['pages'] = array(
			array(
				'link' => $scripturl.'/index.php?action=sitter_edit&amp;page=For',
				'desc' => 'Neuer Forschungsauftrag',
			),
			array(
				'link' => $scripturl.'/index.php?action=sitter_edit&amp;page=Geb',
				'desc' => 'Neuer Gebäudeauftrag',
			),
			array(
				'link' => $scripturl.'/index.php?action=sitter_edit&amp;page=Sch',
				'desc' => 'Neuer Schiffbauauftrag',
			),
			array(
				'link' => $scripturl.'/index.php?action=sitter_edit&amp;page=Sonst',
				'desc' => 'Neuer sonstiger Auftrag',
			),
		);
		TemplateInit('sitter');
		TemplateSitterOwn();
	}
	
	function SitterScriptCnt() {
		global $pre;
		
		echo '<data><setValue elementId="sitter_job_cnt">';
		$cnt = DBQueryOne("SELECT count(*) FROM {$pre}sitter AS sitter WHERE sitter.done=0 AND followUpTo=0 AND sitter.time <= ".time(), __FILE__, __LINE__);
		if($cnt > 0)
			echo '('.$cnt.')';
		echo '</setValue></data>';
	}
?>
