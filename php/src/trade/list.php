<?php
	if(!defined('dddfd'))
		exit();
	function TradeList() {
		global $content, $pre, $scripturl, $ID_MEMBER, $user;
		if($user['isRestricted'])
			die("hacking attempt");
		if(isset($_REQUEST['new']) && CheckRequestID()) {
			$res = Param('ressource');
			if(is_numeric($res)) {
				$schiff = $res;
				$res = 'schiff';
			} else {
				$schiff = 0;
				$res = EscapeDB($res);
			}
			$anz = intval(str_replace('k', '000', Param('anz')));
			DBQuery("INSERT INTO {$pre}trade_reqs (uid, time, priority, ress, ziel, soll, ist, SchiffID, comment) 
VALUES (".intval(Param('account')).", ".time().", ".intval($_REQUEST['priority']).", '$res', '".EscapeDB(Param('coords'))."', ".$anz.", 0, $schiff, '".EscapeDB(Param('comment'))."')", __FILE__, __LINE__);
			$now = time();
			DBQuery("INSERT INTO {$pre}trade_history (time, type, sender, receiver, dst, ress, schiffid, resscnt) VALUES ({$now}, 'new', {$ID_MEMBER}, ".intval(Param('account')).", '".EscapeDB(Param('coords'))."', '$res', {$schiff}, -".intval(Param('anz')).")", __FILE__, __LINE__);
		} elseif(isset($_REQUEST['update']) && (isset($_REQUEST['id']) && CheckRequestID() || $_REQUEST['todo'] == 'done')) {
			$now = time();
			switch (Param('todo')) {
				case 'ignore':
					$end = time()+604800;
					$str = "INSERT INTO {$pre}trade_ignores (id, uid, end) VALUES ";
					foreach($_REQUEST['id'] as $id) {
						$str .= "(".intval($id).", {$ID_MEMBER}, ".$end."), ";
					}
					$str = substr($str, 0, -2);
					DBQuery($str." ON DUPLICATE KEY UPDATE end=VALUES(end)", __FILE__, __LINE__);
				break;
				case 'unignore':
					$ids = "";
					foreach($_REQUEST['id'] as $id) {
						$ids .= intval($id).', ';
					}
					$ids = substr($ids, 0, -2);
					DBQuery("DELETE FROM {$pre}trade_ignores WHERE uid={$ID_MEMBER} AND id IN ({$ids})", __FILE__, __LINE__);
				break;
				case 'delete':
					$ids = "";
					foreach($_REQUEST['id'] as $id) {
						$ids .= intval($id).', ';
					}
					$ids = substr($ids, 0, -2);
					$q = DBQuery("SELECT uid, ziel, ress, schiffid, soll-ist FROM {$pre}trade_reqs WHERE uid=".$user['igmuser']." AND id IN({$ids})", __FILE__, __LINE__);
					while($row = mysql_fetch_row($q)) {
						DBQuery("INSERT INTO {$pre}trade_history (time, type, sender, receiver, dst, ress, schiffid, resscnt) VALUES ({$now}, 'del', {$ID_MEMBER}, ".intval($row[0]).", '".EscapeDB($row[1])."', '".EscapeDB($row[2])."', ".intval($row[3]).", ".intval($row[4]).")" , __FILE__, __LINE__);
					}
					DBQuery("DELETE FROM {$pre}trade_reqs WHERE uid=".$user['igmuser']." AND id IN ({$ids})", __FILE__, __LINE__);
					break;
				case 'done':
					$ids_done = array();
					foreach($_REQUEST['anz'] as $id => $anz) {
						if(!empty($anz)) {
							$id = intval($id);
							$anza = intval(str_replace('k', '000', $anz));
							DBQuery("UPDATE {$pre}trade_reqs SET ist=ist+".$anza." WHERE id=".$id, __FILE__, __LINE__);
							$ids_done[] = $id;
							$row = DBQueryOne("SELECT uid,ziel,ress,schiffid FROM {$pre}trade_reqs WHERE id=".$id, __FILE__, __LINE__);
							DBQuery("INSERT INTO {$pre}trade_history (time, type, sender, receiver, dst, ress, schiffid, resscnt) VALUES ({$now}, 'edit', {$ID_MEMBER}, $row[0], '".EscapeDB($row[1])."', '".EscapeDB($row[2])."', ".intval($row[3]).", ".$anza.")", __FILE__, __LINE__);
						}
					}
					$ids = '';
					if(isset($_REQUEST['id'])) {
						foreach($_REQUEST['id'] as $id) {
							$i = intval($id);
							if(!in_array($i, $ids_done)) {
								$ids .= $i.', ';
								$row = DBQueryOne("SELECT uid,ziel,ress,schiffid,soll-ist FROM {$pre}trade_reqs WHERE id=".$id, __FILE__, __LINE__);
								DBQuery("INSERT INTO {$pre}trade_history (time, type, sender, receiver, dst, ress, schiffid, resscnt) VALUES ({$now}, 'edit', {$ID_MEMBER}, ".intval($row[0]).", '".EscapeDB($row[1])."', '".EscapeDB($row[2])."', ".intval($row[3]).", ".intval($row[4]).")" , __FILE__, __LINE__);
							}
						}
						if($ids != '') {
							$ids = substr($ids, 0, -2);
							DBQuery("UPDATE {$pre}trade_reqs SET ist=soll WHERE id IN ($ids)", __FILE__, __LINE__);
						}
					}
					break;
			}
		}
		
		$ress = array(
			'eisen' => 'Eisen',
			'stahl' => 'Stahl',
			'chem' => 'Chemie',
			'vv4a' => 'VV4A',
			'eis' => 'Eis',
			'wasser' => 'Wasser',
			'energie' => 'Energie',
			'credits' => 'Credits',
			'bev' => 'Bevölkerung',
		);
		$q = DBQuery("SELECT ID, Name FROM {$pre}techtree_items WHERE Type='schiff'", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			$ress[$row[0]] = EscapeOU($row[1]);
		}
		$content['ress'] = $ress;
		$content['prioritys'] = array(
			'sehr Dringend' => -20,
			'Wichtig' => -10,
			'Normal' =>  0,
			'Nicht so dringend' => 10,
			'(fast) total irrelevant' => 20,
		);
		$q = DBQuery("SELECT universum.gala, universum.sys, universum.pla, planiname FROM {$pre}universum AS universum INNER JOIN {$pre}igm_data AS igm_data ON universum.ownername=igm_data.igmname WHERE igm_data.ID=".$user['igmuser']." ORDER BY CASE objekttyp WHEN 'Kolonie' THEN 1 ELSE 0 END, universum.gala, universum.sys, universum.pla", __FILE__, __LINE__);
		$content['planis'] = array(array('text' => 'Plani-Schnellauswahl'));
		while($row = mysql_fetch_row($q)) {
			$content['planis'][] = array('text' => "({$row[0]}:{$row[1]}:{$row[2]}) ".EscapeOU($row[3])); 
		}
		$q = DBQuery("SELECT id, igmname FROM {$pre}igm_data", __FILE__, __LINE__);
		$content['accounts'] = array();
		$accsel = isset($_REQUEST['account']) ? intval($_REQUEST['account']) : $user['igmuser'];
		while($row = mysql_fetch_row($q)) {
			$content['accounts'][] = array(
				'val' => $row[0],
				'text' => EscapeOU($row[1]),
				'sel' => $row[0] == $accsel,
			);
		}
		
		$prioritys = array(
			-20 => '++',
			-10 => '+',
			  0 => '0',
			 10 => '-',
			 20 => '--',
		);
		
		
		$q = DBQuery("SELECT trade_reqs.id,
	igm_data.igmname, trade_reqs.time, trade_reqs.priority, ress, ziel, soll, ist, trade_reqs.comment, techtree_items.Name, (trade_ignores.ID IS NOT NULL AND trade_ignores.end > ".time().")
FROM (({$pre}trade_reqs AS trade_reqs INNER JOIN {$pre}igm_data AS igm_data ON trade_reqs.uid=igm_data.id)
	LEFT JOIN {$pre}trade_ignores AS trade_ignores ON trade_reqs.ID = trade_ignores.ID AND trade_ignores.uid={$ID_MEMBER}) 
	LEFT JOIN {$pre}techtree_items AS techtree_items ON trade_reqs.SchiffID=techtree_items.ID
		where soll > ist
		ORDER BY trade_reqs.priority, trade_reqs.time ASC", __FILE__, __LINE__);
		$content['reqs'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['reqs'][] = array(
				'id' => $row[0],
				'user' => EscapeOU($row[1]),
				'zeit' => FormatDate($row[2]),
				'priority' => $prioritys[$row[3]],
				'nameLong' => $row[4] == 'schiff' ? EscapeOU($row[9]) : $ress[$row[4]],
				'coords' => EscapeOU($row[5]),
				'soll' => number_format($row[6], 0, ',', '.'),
				'ist' => number_format($row[7], 0, ',', '.'),
				'diff' => number_format($row[6]-$row[7], 0, ',', '.'),
				'comment' => EscapeOU($row[8]),
				'ignored' => $row[10] ? 'ignoriert' : '',
			);
		}
		
		
		$q = DBQuery("SELECT hist.time, hist.type, users.visibleName, igm_data.igmname, hist.dst, hist.ress, hist.SchiffID, hist.resscnt FROM ({$pre}trade_history AS hist INNER JOIN {$pre}igm_data AS igm_data ON hist.receiver = igm_data.ID) INNER JOIN {$pre}users AS users ON hist.sender=users.ID ORDER BY hist.time DESC LIMIT 0,80", __FILE__, __LINE__);
		$content['history'] = array();
		$historyTypes = array(
			'new' => 'Neu',
			'del' => 'Del',
			'edit' => '',
		);
		while($row = mysql_fetch_row($q)) {
			$content['history'][] = array(
				'time' => FormatDate($row[0]),
				'type' => $historyTypes[$row[1]],
				'sender' => EscapeOU($row[2]),
				'receiver' => EscapeOU($row[3]),
				'dest' => EscapeOU($row[4]),
				'ress' => $row[5] == 'schiff' ? $ress[$row[6]] : EscapeOU($ress[$row[5]]),
				'diff' => number_format($row[7],0, ',', '.'),
			);
		}
		
		GenRequestID();
		$content['submitUrl'] = $scripturl.'/index.php?action=trade_list';
		TemplateInit('trade');
		TemplateTradeList();
	}
?>
