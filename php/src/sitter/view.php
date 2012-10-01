<?php
	if (!defined("dddfd"))
		die("Hacking attempt");
	
	
	function SitterView() {
		global $pre, $ID_MEMBER, $user, $content, $scripturl, $user;
		if($user['isRestricted'])
			die("hacking attempt");
		
		if(isset($_REQUEST['del'])) {
			$id = intval($_REQUEST['del']);
			DBQuery("DELETE FROM {$pre}sitter WHERE ID=$id ".($user['isAdmin'] ? "" : "AND (done=0 AND (uid={$ID_MEMBER} OR igmid=".$user['igmuser']."))"), __FILE__, __LINE__);
			if(mysql_affected_rows() > 0)
				$content['msg'] = 'Auftrag gelöscht!';
		}
		
		$types = array(
			'Geb' => 'Bauauftrag',
			'For' => 'Forschungsauftrag',
			'Sch' => 'Schiffbauauftrag',
			'Sonst' => 'sonstiger Auftrag',
		);
		$msgs = array(
			'sitter_racecondition' => 'Sorry, den Sitterauftrag hat schon jemand erledigt!',
			'job_update' => 'Sitterauftrag erfolgreich aktualisiert!',
			'job_insert' => 'Sitterauftrag erfolgreich eingetragen!',
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
				'hasPlani' => !is_null($row[9]),
				'coords' => is_null($row[9]) ? '' : ('['.$row[9]. ':'. $row[10]. ':'. $row[11].']'),
				'planiName' => is_null($row[9]) ? 'Alle Planeten' : EscapeOU($row[12]),
				'loginLink' => $scripturl.'/index.php?action=sitter_login&amp;from=sitter_view&amp;jobid='.$row[0],
				'hasEditLinks' => $row[1] == $ID_MEMBER || $row[3] == $user['igmuser'] || $user['isAdmin'],
				'editLink' => $scripturl.'/index.php?action=sitter_edit&amp;page='.$row[6].'&amp;ID='.$row[0],
				'delLink' => $scripturl.'/index.php?action=sitter_view&amp;del='.$row[0],
				'hasAppendLink' => $row[6] == 'Geb',
				'appendLink' => $scripturl.'/index.php?action=sitter_edit&amp;page='.$row[6].'&amp;angehaengtAn='.$row[0],
				'ownershipState' => $row[1] == $ID_MEMBER ? 'own' : ($row[3] == $user['igmuser'] ? 'account' : 'none'),
			);
		}
		
		$q = DBQuery("SELECT sitter.ID, sitter.uid, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.done=0 AND sitter.time > {$now} ORDER BY time", __FILE__, __LINE__, 2);
		$content['sittersoon'] = array();
		$foreign=0;
		while($row = mysql_fetch_row($q)) {
			if($row[1] != $ID_MEMBER && $row[3] != $user['igmuser']) {
				if($foreign >= 10)
					continue;
				$foreign++;
			}
			$content['sittersoon'][] = array(
				'time' => FormatDate($row[5]),
				'typeLong' => $types[$row[6]],
				'typeShort' => $row[6],
				'text' => SitterText($row),
				'igmName' => EscapeOU($row[4]),
				'userName' => EscapeOU($row[2]),
				'hasPlani' => !is_null($row[9]),
				'coords' => is_null($row[9]) ? '' : ('['.$row[9]. ':'. $row[10]. ':'. $row[11].']'),
				'planiName' => is_null($row[9]) ? 'Alle Planeten' : EscapeOU($row[12]),
				'hasEditLinks' => $row[1] == $ID_MEMBER || $row[3] == $user['igmuser'] || $user['isAdmin'],
				'editLink' => $scripturl.'/index.php?action=sitter_edit&amp;page='.$row[6].'&amp;ID='.$row[0],
				'delLink' => $scripturl.'/index.php?action=sitter_view&amp;del='.$row[0],
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
		TemplateSitterTaskList();
	}
	function SitterText($row) {
		$ret = '';
		$item = '';
		if($row[14] != 0) {
			$item .= $row[14].'x ';
		}
		if($row[7] == null) {
			switch($row[6]) {
				case 'Geb':
					$item .= 'Gebäude nach Postit';
					break;
				case 'For':
					$item .= 'Forschung nach Postit';
					break;
				case 'Sch':
					$item .= 'SchiffbauAll';
					break;
			}
		} else {
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
			$ret .= (!empty($ret) ? '<br/>' : '').'<i>'.nl2br(EscapeOU($row[15])).'</i>';
		}
		return $ret;
	}
	
	function SitterScriptCnt() {
		global $pre, $user;
		if($user['isRestricted'])
			die("hacking attempt");
		echo '<data><setValue elementId="sitter_job_cnt">';
		$cnt = DBQueryOne("SELECT count(*) FROM {$pre}sitter AS sitter WHERE sitter.done=0 AND followUpTo=0 AND sitter.time <= ".time(), __FILE__, __LINE__);
		if($cnt > 0)
			echo '('.$cnt.')';
		echo '</setValue></data>';
	}
?>
