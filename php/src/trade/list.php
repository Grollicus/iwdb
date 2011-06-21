<?php
	if(!defined('dddfd'))
		exit();
	function TradeList() {
		global $content, $pre, $scripturl, $ID_MEMBER, $user;
		if(isset($_REQUEST['new']) && CheckRequestID()) {
			$res = Param('ressource');
			if(is_numeric($res)) {
				$schiff = $res;
				$res = 'schiff';
			} else {
				$schiff = 0;
				$res = EscapeDB($res);
			}
			DBQuery("INSERT INTO {$pre}trade_reqs (uid, time, priority, ress, ziel, soll, ist, SchiffID, comment) 
VALUES (".$user['igmuser'].", ".time().", ".intval($_REQUEST['priority']).", '$res', '".EscapeDB(Param('coords'))."', ".intval(Param('anz')).", 0, $schiff, '".EscapeDB(Param('comment'))."')", __FILE__, __LINE__);
		} elseif(isset($_REQUEST['update']) && (isset($_REQUEST['id']) && CheckRequestID() || $_REQUEST['todo'] == 'done')) {
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
					DBQuery("DELETE FROM {$pre}trade_reqs WHERE uid={$ID_MEMBER} AND id IN ({$ids})", __FILE__, __LINE__);
					break;
				case 'done':
					$ids_done = array();
					foreach($_REQUEST['anz'] as $id => $anz) {
						if(!empty($anz)) {
							$id = intval($id);
							DBQuery("UPDATE {$pre}trade_reqs SET ist=ist+".intval($anz)." WHERE id=".$id, __FILE__, __LINE__);
							$ids_done[] = $id;
						}
					}
					$ids = '';
					if(isset($_REQUEST['id'])) {
						foreach($_REQUEST['id'] as $id) {
							$i = intval($id);
							if(!in_array($i, $ids_done))
								$ids .= $i.', ';
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
				'soll' => $row[6],
				'ist' => $row[7],
				'diff' => $row[6]-$row[7],
				'comment' => EscapeOU($row[8]),
				'ignored' => $row[10] ? 'ignoriert' : '',
			);
		}
		GenRequestID();
		$content['submitUrl'] = $scripturl.'/index.php?action=trade_list';
		TemplateInit('trade');
		TemplateTradeList();
	}
?>