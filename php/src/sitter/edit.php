<?php
	function SitterEdit() {
		global $content, $pre, $scripturl, $ID_MEMBER;
		$modules = array(
//name => arr(
//	'name' => Name,
//	'desc' => Desc,
//	'hidden' => boolean,
//	'col' => Datenbankspalte,
//	'isValid' => Callback,
//	'prepare' => Callback,
//	'evaluate' => Callback
//)
			'account' => array(
				'name' => 'Account',
				'desc' => 'Ingame-Account, für den der Sitterauftrag erstellt wird',
				'col' => 'igmid',
				'prepare' => 'CbPrepareAccount',
				'evaluate' => 'CbEvaluateAccount',
			),
			'anzahl' => array(
				'name' => 'Anzahl',
				'desc' => 'Wie oft das Schiff gebaut werden soll',
				'col' => 'anzahl',
				'isValid' => 'CbValidateAnzahl',
				'prepare' => 'CbPrepareAnzahl',
				'evaluate' => 'CbEvaluateAnzahl',
			),
			'bauschleife' => array(
				'name' => 'Bauschleife',
				'desc' => 'Alternative zum Eintragen der Zeiten von Hand: einfach die aktuelle Bauschleife (Bau- oder Hauptseite) in das Textfeld kopieren und dann wird der Kram automatisch ausgelesen',
				//'col'
				//'prepare'
				'isValid' => 'CbValidateBauschleife',
				'evaluate' => 'CbEvaluateBauschleife',
				'prepare' => 'CbPrepareBauschleife',
			),
			'angehaengtAn' => array(
				'name' => 'Angehängt an',
				'desc' => 'Wenn ein Sitterauftrag an einen anderen angehängt werden soll steht hier der Vorgängerauftrag',
				'col' => 'FollowUpTo',
				'prepare' => 'CbPrepareAngehaengtAn',
				'evaluate' => 'CbEvaluateAngehaengtAn',
			),
			'forschung' => array(
				'name' => 'Forschung',
				'desc' => '',
				'col' => 'itemid',
				'isValid' => 'CbValidateForschung',
				'evaluate' => 'CbEvaluateForschung',
				'prepare' => 'CbPrepareForschung',
			),
			'gebaeude' => array(
				'name' => 'Gebäude &amp; Stufe',
				'desc' => '',
				'col' => 'itemid, stufe',
				'isValid' => 'CbValidateGebaeude',
				'evaluate' => 'CbEvaluateGebaeude',
				'prepare' => 'CbPrepareGebaeude',
			),
			'ID' => array(
				'name' => 'ID',
				'desc' => '',
				'col' => 'ID',
				'hidden' => true,
				//isValid
				'prepare' => 'CbPrepareID',
				//'evaluate'
			),
			'notes' => array(
				'name' => 'Notitzen',
				'desc' => 'Zusätzlich angezeigter Text',
				'col' => 'notes',
				//'isValid'
				'prepare' => 'CbPrepareNotes',
				'evaluate' => 'CbEvaluateNotes',
			),
			'planet' => array(
				'name' => 'Planet',
				'desc' => 'auf dem der Sitterauftrag ausgeführt werden soll',
				'col' => 'planID',
				'prepare' => 'CbPreparePlanet',
				'evaluate' => 'CbEvaluatePlanet',
				'isValid' => 'CbValidatePlanet',
			),
			'schiff' => array(
				'name' => 'Schiff',
				'desc' => 'Welches Schiff gebaut werden soll',
				'col' => 'itemid',
				'prepare' => 'CbPrepareSchiff',
				'evaluate' => 'CbEvaluateSchiff',
				'isValid' => 'CbValidateSchiff',
			),
			'use_bauschleife' => array(
				'name' => 'Bauschleife verwenden',
				'desc' => '',
				'col' => 'usequeue',
				'prepare' => 'CbPrepareUseBauschleife',
				'evaluate' => 'CbEvaluateUseBauschleife',
			),
			'zeit' => array(
				'name' => 'Zeit',
				'desc' => 'Zeit, ab der der Auftrag angezeigt wird<br />Bsp: 15.11.2006 01:58',
				
				'col' => 'time',
				'isValid' => 'CbValidateZeit',
				'prepare' => 'CbPrepareZeit',
				'evaluate' => 'CbEvaluateZeit',
			),
		);
		$pages = array(
			'For' => array(
				'name' => 'Forschungsauftrag',
				'mods' => array('account', 'zeit', 'planet', 'ID', 'forschung', 'notes'),
			),
			'Geb' => array(
				'name' => 'Gebäudeauftrag',
				'mods' => array('angehaengtAn', 'account', 'zeit', 'bauschleife', 'planet', 'use_bauschleife', 'ID', 'gebaeude', 'notes'),
			),
			'Sch' => array(
				'name' => 'Schiffbauauftrag',
				'mods' => array('account', 'zeit', 'planet', 'ID', 'schiff', 'anzahl', 'notes'),
			),
			'Sonst' => array(
				'name' => 'sonstiger Auftrag',
				'mods' => array('account', 'zeit', 'planet', 'ID', 'notes'),
			),
		);
		
		$currentPage = Param('page');
		$modsActive = array();
		foreach($pages[$currentPage]['mods'] as $n) {
			$modsActive[$n] = $modules[$n];
		}
		$content['errors'] = array();
		
		if(isset($_REQUEST['submit'])) {
			$valid = true;
			foreach($modsActive as $mod) {
				if(isset($mod['isValid']))
					$valid &= (call_user_func($mod['isValid']));
			}
			if($valid) {
				$data = array('uid' => $ID_MEMBER, 'type' => EscapeDB($currentPage));
				$p = array(&$data);
				foreach($modsActive as $mod) {
					if(isset($mod['evaluate']))
						call_user_func_array($mod['evaluate'], $p);
				}
				if(isset($_REQUEST['ID']) && $_REQUEST['ID'] != '0') {
					$upd = '';
					foreach($data as $col => $val) {
						$upd .= $col."='".$val."', ";
					}
					$upd = substr($upd, 0, -2);
					DBQuery("UPDATE {$pre}sitter SET {$upd} WHERE ID=".intval($_REQUEST['ID']), __FILE__, __LINE__);
					$content['successmsg'] = 'Sitterauftrag erfolgreich aktualisiert!';
				} else {
					$cols = '';
					$vals = '';
					foreach($data as $col => $val) {
						$cols .= $col.', ';
						$vals .= "'$val', ";
					}
					$cols = substr($cols, 0, -2);
					$vals = substr($vals, 0, -2);
					DBQuery("INSERT INTO {$pre}sitter ($cols) VALUES ($vals)", __FILE__, __LINE__);
					$content['successmsg'] = 'Sitterauftrag erfolgreich eingetragen!';
				}
			}
		}
		
		$cols = '';
		foreach($modsActive as $mod) {
			if(isset($mod['col']))
				$cols .= $mod['col'].', ';
		}
		$cols = substr($cols, 0, -2);
		
		if(!isset($_REQUEST['submit']) && isset($_REQUEST['ID']) && $_REQUEST['ID'] != '0') {
			$row = DBQueryOne("SELECT {$cols} FROM {$pre}sitter WHERE ID=".intval($_REQUEST['ID']), __FILE__, __LINE__, true);
		} else {
			$row = false;
		}
		$content['mods'] = array();
		foreach($modsActive as $n => $mod) {
			$content['mods'][$n] = array('name' => $mod['name'], 'desc' => $mod['desc'], 'hidden' => isset($mod['hidden']) && $mod['hidden']);
			if(isset($mod['prepare'])) {
				call_user_func($mod['prepare'], $row);
			}
		}
		$content['pages'] = array();
		foreach($pages as $n => $p) {
			$content['pages'][] = array(
				'desc' => 'Neuer '.$p['name'],
				'link' => $scripturl.'/index.php?action=sitter_edit&amp;page='.$n,
				'active' => ($n == $currentPage),
			);
		}
		$content['heading'] = (isset($_REQUEST['ID']) && $_REQUEST['ID'] != '0') ? 'Sitterauftrag bearbeiten' : 'Neuer Sitterauftrag';
		$content['subHeading'] = $pages[$currentPage]['name'];
		$content['submitAction'] = $scripturl.'/index.php?action=sitter_edit&amp;page='.EscapeO($currentPage);
		$content['action'] = 'sitter_own';
		TemplateInit('sitter_own');
		TemplateSitterEdit();
	}

	function CbPrepareNotes($row) {
		global $content;
		if(isset($_REQUEST['notes'])) {
			$content['notes'] = EscapeO(Param('notes'));
		} else {
			if($row === false)
				$content['notes'] = '';
			else
				$content['notes'] = EscapeOU($row['notes']);
		}
	}
	function CbEvaluateNotes(&$data) {
		 $data['notes'] = EscapeDB(Param('notes'));
	}
	function CbPrepareID() {
		global $content;
		$content['ID'] = isset($_REQUEST['ID']) ? intval($_REQUEST['ID']) : '0';
	}
	function CbValidateZeit() {
		global $valid_zeit1;
		$t = ParseTime($_REQUEST['zeit']);
		$valid_zeit1 = ($t !== false);
		if ($t === false && empty($_REQUEST['bauschleife'])) {
			global $content;
			$content['errors'][] = 'Keine Zeit angegeben oder fehlerhaft formatiert!';
			return false;
		}
		return true;
	}
	function CbPrepareZeit($row) {
	global $content;
		if(isset($_REQUEST['zeit'])) {
			$content['zeit'] = EscapeO(Param('zeit'));
		} else {
			if($row === false)
				$content['zeit'] = '';
			else
				$content['zeit'] = FormatDate($row['time']);
		}
	}
	function CbEvaluateZeit(&$data) {
		$data['time'] = ParseTime($_REQUEST['zeit']);
	}
	function CbPrepareAccount($row) {
		global $pre, $content, $user;
		if(isset($_REQUEST['account'])) {
			$active = $_REQUEST['account'];
		} else {
			$active = $row === false ? $user['igmuser'] : $row['igmid'];
		}
		$q = DBQuery("SELECT id, igmname FROM {$pre}igm_data ORDER BY igmname", __FILE__, __LINE__);
		$content['account'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['account'][] = array('id' => $row[0], 'name' => EscapeOU($row[1]), 'selected' => $row[0] == $active); 
		}
	}
	function CbEvaluateAccount(&$data) {
		$data['igmid'] = intval($_REQUEST['account']);
	}
	function CbValidatePlanet() {
		if($_REQUEST['planet'] == '0') {
			global $content;
			$content['errors'][] = 'Kein Planet ausgewählt!';
			return false;
		}
		return true;
	}
	function CbPreparePlanet($row) {
		global $pre, $content, $user;
		if(isset($_REQUEST['planet'])) {
			$active = $_REQUEST['planet'];
		} else {
			$active = $row === false ? 0 : $row['planID'];
		}
		if(isset($_REQUEST['account'])) {
			$uid = intval($_REQUEST['account']);
		} else {
			$uid = $row === false ? $user['igmuser'] : $row['igmid'];
		}
		$q = DBQuery("SELECT universum.id, universum.gala, universum.sys, universum.pla, planiname FROM {$pre}universum AS universum INNER JOIN {$pre}igm_data AS igm_data ON universum.ownername=igm_data.igmname WHERE igm_data.ID={$uid} ORDER BY CASE objekttyp WHEN 'Kolonie' THEN 1 ELSE 0 END, universum.gala, universum.sys, universum.pla", __FILE__, __LINE__);
		$content['planet'] = array(array('id' => '0', 'name' => 'Plani auswählen!', 'selected' => false));
		while($row = mysql_fetch_row($q)) {
			$content['planet'][] = array('id' => $row[0], 'name' => "({$row[1]}:{$row[2]}:{$row[3]}) ".EscapeOU($row[4]), 'selected' => $row[0] == $active); 
		}
	}
	function CbEvaluatePlanet($data) {
		$data['planID'] = intval($_REQUEST['planet']);
	}
	function CbValidateBauschleife() {
		global $bq, $pre, $valid_zeit1, $content;
		$bq = false;
		$c = DBQueryOne("SELECT gala, sys, pla FROM {$pre}universum WHERE ID=".intval($_REQUEST['planet']), __FILE__, __LINE__);
		$coords = "$c[0]:$c[1]:$c[2]"; 
		$bq = ParseIWBuildingQueue($_REQUEST['bauschleife'], $coords);
		if($bq === false && !$valid_zeit1) {
			$content['errors'][] = 'Keine Zeit angegeben oder fehlerhaft formatiert!';
		}
		return $bq !== false || $valid_zeit1;
	}
	function CbEvaluateBauschleife(&$data) {
		global $bq, $pre;
		if($bq === false)
			return;
		if(count($bq) <= 1) {
			$data['time'] = time();
			return;
		}
		if(isset($_REQUEST['use_bauschleife'])) {
			$data['time'] = $bq[0];
		} else {
			$data['time'] = end($bq);
		}
	}
	function CbPrepareBauschleife($row) {
		global $content;
		$content['bauschleife'] = isset($_REQUEST['bauschleife']) ? EscapeO(Param('bauschleife')) : '';
	}
	function CbPrepareUseBauschleife($row) {
		global $content;
		if(isset($_REQUEST['submit']))
			$content['use_bauschleife'] = isset($_REQUEST['use_bauschleife']);
		elseif($row !== false)
			$content['use_bauschleife'] = $row['usequeue'];
		else
			$content['use_bauschleife'] = true;
	}
	function CbEvaluateUseBauschleife(&$data) {
		$data['usequeue'] = isset($_REQUEST['use_bauschleife']) ? '1' : '0';
	}
	function CbValidateGebaeude() {
		if(!isset($_REQUEST['gebaeude'])) {
			global $content;
			$content['errors'][] = 'Kein Gebäude ausgewählt!';
			return false;
		}
		if(!isset($_REQUEST['stufe'])) {
			global $content;
			$content['errors'][] = 'Keine Gebäudestufe ausgewählt!';
			return false;
		}
		return true;
	}
	function CbEvaluateGebaeude(&$data) {
		global $pre;
		$data['itemid'] = intval($_REQUEST['gebaeude']);
		$data['stufe'] = intval($_REQUEST['stufe']);
	}
	function CbPrepareGebaeude($row) {
		global $content, $pre;
		
		if(isset($_REQUEST['gebaeude'])) {
			$g = intval($_REQUEST['gebaeude']);
		} elseif($row !== false) {
			$g = $row['itemid'];
		} else {
			$g = 0;
		}
		if(isset($_REQUEST['stufe'])) {
			$s = intval($_REQUEST['stufe']);
		} elseif($row !== false) {
			$s = $row['stufe'];
		} else {
			$s = 0;
		}
		if(isset($_REQUEST['planet']) && $_REQUEST['planet'] != '0') {
			$planid = intval($_REQUEST['planet']);
			$igmid = intval($_REQUEST['account']);
			$c = DBQueryOne("SELECT gala, sys, pla FROM {$pre}universum WHERE ID={$planid}", __FILE__, __LINE__);
			$plani = "{$c[0]}:{$c[1]}:{$c[2]}";

			$content['gebaeude'] = array(array('name' => 'Gebäude auswählen!', 'id' => 0, 'selected' => false));
			
			$q = DBQuery("SELECT techtree_items.ID, techtree_items.Name
FROM ({$pre}techtree_items AS techtree_items LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.ItemID AND techtree_useritems.uid={$igmid} AND techtree_useritems.coords = '{$plani}')
	LEFT JOIN {$pre}techtree_stufen AS techtree_stufen ON techtree_items.ID = techtree_stufen.ItemID AND ((techtree_items.Class IN (1,2) AND (techtree_stufen.Stufe=IFNULL(techtree_useritems.count,0)+1)) OR (techtree_items.Class=0 AND techtree_stufen.Stufe=1))
	WHERE techtree_items.Type='geb' AND (techtree_items.MaxLevel = 0 OR IFNULL(techtree_useritems.count,0)+1 <= techtree_items.MaxLevel)
	ORDER BY techtree_items.Name", __FILE__, __LINE__);
			while($row = mysql_fetch_row($q)) {
				$content['gebaeude'][] = array(
					'name' => EscapeOU($row[1]),
					'id' => $row[0],
					'selected' => $row[0] == $g,
				);
			}
			if($g != 0) {
				$q = DBQuery("SELECT Stufe, Dauer FROM {$pre}techtree_stufen WHERE ItemID={$g}", __FILE__, __LINE__);
				if(mysql_num_rows($q) != 1)
					$content['stufe'] = array(array('name' => 'Stufe auswählen!', 'id' => 0, 'selected' => false));
				while($row = mysql_fetch_row($q)) {
					$content['stufe'][] = array(
						'name' => sprintf("%02s", $row[0]).' ['.FormatTime($row[1]).']',
						'id' => $row[0],
						'selected' => $row[0] == $s,
					);
				}
			} else {
				$content['stufe'] = array(array('name' => 'Stufe auswählen!', 'id' => 0, 'selected' => false));
			}
		} else {
			$content['stufe'] = array(array('name' => 'Gebäude auswählen!', 'id' => 0, 'selected' => false));
			$content['gebaeude'] = array(array('name' => 'Plani auswählen!', 'id' => 0, 'selected' => false));
		}
	}
	function CbValidateForschung() {
		if($_REQUEST['forschung'] == '0') {
			global $content;
			$content['errors'][] = 'Keine Forschung ausgewählt!';
			return false;
		}
		return true;
	}
	function CbEvaluateForschung(&$data) {
		$data['itemid'] = intval($_REQUEST['forschung']);
	}
	function CbPrepareForschung($row) {
		global $content, $pre, $user;
		if(isset($_REQUEST['submit']))
			$f = intval($_REQUEST['forschung']);
		elseif($row !== false)
			$f = $row['itemid'];
		else
			$f = 0;
		if(isset($_REQUEST['account'])) {
			$uid = intval($_REQUEST['account']);
		} else {
			$uid = $row === false ? $user['igmuser'] : $row['igmid'];
		}
		$q = DBQuery("SELECT techtree_items.ID, techtree_items.Name 
FROM {$pre}techtree_items AS techtree_items LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_useritems.uid={$uid} AND techtree_items.ID=techtree_useritems.itemid 
WHERE techtree_items.type='For' AND techtree_useritems.count IS NULL OR techtree_useritems.count = 0 ORDER BY techtree_items.Name", __FILE__, __LINE__);
		$content['forschung'] = array(array('name' => 'Forschung auswählen!', 'id' => 0, 'selected' => false));
		while($row = mysql_fetch_row($q)) {
			$content['forschung'][] = array(
				'name' => EscapeOU($row[1]),
				'id' => $row[0],
				'selected' => $row[0] == $f,
			);
		}
	}
	function CbPrepareSchiff($row) {
		global $content, $pre;
		if(isset($_REQUEST['submit']))
			$s = intval($_REQUEST['schiff']);
		elseif($row !== false)
			$s = $row['itemid'];
		else
			$s = 0;
		$q = DBQuery("SELECT ID, Name FROM {$pre}techtree_items WHERE Type='schiff'", __FILE__, __LINE__);
		$content['schiff'] = array(array('name' => 'Schiff auswählen!', 'id' => 0, 'selected' => false));
		while($row = mysql_fetch_row($q)) {
			$content['schiff'][] = array(
				'name' => EscapeOU($row[1]),
				'id' => $row[0],
				'selected' => $row[0] == $s,
			);
		}
	}
	function CbEvaluateSchiff(&$data) {
		$data['itemid'] = intval($_REQUEST['schiff']);
	}
	function CbValidateSchiff() {
		if($_REQUEST['schiff'] == '0') {
			global $content;
			$content['errors'][] = 'Kein Schiff ausgewählt!';
			return false;
		}
		return true;
	}
	function CbValidateAnzahl() {
		if($_REQUEST['anzahl'] <= 0) {
			global $content;
			$content['errors'][] = 'Fehlerhafte Anzahl!';
			return false;
		}
		return true;
	}
	function CbPrepareAnzahl($row) {
		global $content;
		if(isset($_REQUEST['anzahl'])) {
			$content['anzahl'] = EscapeO($_REQUEST['anzahl']);
		} else {
			if($row !== false) {
				$content['anzahl'] = $row['anzahl'];
			} else {
				$content['anzahl'] = '';
			}
		}
	}
	function CbEvaluateAnzahl(&$data) {
		$data['anzahl'] = intval($_REQUEST['anzahl']);
	}
	function CbPrepareAngehaengtAn($row) {
		global $content, $pre;
		if(isset($_REQUEST['angehaengtAn'])) {
			$id = intval($_REQUEST['angehaengtAn']);
		} else {
			if($row !== false) {
				$id = $row['FollowUpTo'];
			} else {
				$id = '0';
			}
		}
		$content['angehaengtAn'] = $id;
		if($id != 0) {
			$_REQUEST['zeit'] = FormatDate(DBQueryOne("SELECT sitter.time+IFNULL(techtree_stufen.Dauer,0) FROM {$pre}sitter as sitter left join {$pre}techtree_stufen as techtree_stufen on sitter.itemid=techtree_stufen.ItemID and sitter.stufe=techtree_stufen.Stufe WHERE sitter.ID={$id}", __FILE__, __LINE__));
			$_REQUEST['bauschleife'] = '';
			$content['readonly_bauschleife'] = true;
			$content['readonly_zeit'] = true;
			$content['readonly_zeit2'] = true;
		}
	}
	function CbEvaluateAngehaengtAn(&$data) {
		if($_REQUEST['angehaengtAn'] != '0') {
			$data['FollowUpTo'] = intval($_REQUEST['angehaengtAn']);
		}
	}
	
	function SitterScriptListPlanis() {
		global $pre;
		$uid = intval($_REQUEST['igmid']);
		echo '<data><option><value>0</value><description>Plani auswählen!</description></option>';
		$q = DBQuery("SELECT universum.id, universum.gala, universum.sys, universum.pla, planiname FROM {$pre}universum AS universum INNER JOIN {$pre}igm_data AS igm_data ON universum.ownername=igm_data.igmname WHERE igm_data.ID={$uid} ORDER BY CASE objekttyp WHEN 'Kolonie' THEN 1 ELSE 0 END, universum.gala, universum.sys, universum.pla", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			echo '<option><value>', $row[0], '</value><description>', "({$row[1]}:{$row[2]}:{$row[3]}) ", EscapeOU($row[4]), '</description></option>';
		}
		echo '</data>';
	}
	function SitterScriptListGebs() {
		global $pre;
		
		$planid = intval($_REQUEST['planid']);
		$igmid = intval($_REQUEST['igmid']);
		$c = DBQueryOne("SELECT gala, sys, pla FROM {$pre}universum WHERE ID={$planid}", __FILE__, __LINE__);
		$plani = "{$c[0]}:{$c[1]}:{$c[2]}";
		
		$q = DBQuery("SELECT techtree_items.ID, techtree_items.Name
FROM ({$pre}techtree_items AS techtree_items LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_items.ID = techtree_useritems.ItemID AND techtree_useritems.uid={$igmid} AND techtree_useritems.coords = '{$plani}')
	LEFT JOIN {$pre}techtree_stufen AS techtree_stufen ON techtree_items.ID = techtree_stufen.ItemID AND ((techtree_items.Class IN (1,2) AND (techtree_stufen.Stufe=IFNULL(techtree_useritems.count,0)+1)) OR (techtree_items.Class=0 AND techtree_stufen.Stufe=1))
	WHERE techtree_items.Type='geb' AND (techtree_items.MaxLevel = 0 OR IFNULL(techtree_useritems.count,0)+1 <= techtree_items.MaxLevel)
	ORDER BY techtree_items.Name", __FILE__, __LINE__);
		echo '<data><option><value>0</value><description>Gebäude auswählen!</description></option>';
		while($row = mysql_fetch_row($q)) {
			echo '<option><value>', $row[0], '</value><description>', EscapeOU($row[1]), '</description></option>';
		}
		echo '</data>';
	}
	function SitterScriptListStufen() {
		global $pre;
		
		$planid = intval($_REQUEST['planid']);
		$c = DBQueryOne("SELECT gala, sys, pla FROM {$pre}universum WHERE ID={$planid}", __FILE__, __LINE__);
		$plani = "{$c[0]}:{$c[1]}:{$c[2]}";
		$itemid = intval($_REQUEST['itemid']);
		
		$q = DBQuery("SELECT Stufe, Dauer FROM {$pre}techtree_stufen WHERE ItemID={$itemid}", __FILE__, __LINE__);
		echo '<data>';
		if(mysql_num_rows($q) != 1)
			echo '<option><value>0</value><description>Stufe auswählen!</description></option>';
		while($row = mysql_fetch_row($q)) {
			echo '<option><value>', $row[0], '</value><description>', sprintf("%02s", $row[0]), ' [', FormatTime($row[1]),']</description></option>';
		}
		echo '<select>';
		$stufe = DBQueryOne("SELECT count FROM {$pre}techtree_useritems WHERE itemid={$itemid} AND coords='{$plani}'", __FILE__, __LINE__);
		echo ($stufe === false) ? '0' : $stufe;
		echo '</select></data>';
	}
	function SitterScriptListForschungen() {
		global $pre;
		
		$igmid = intval($_REQUEST['igmid']);
		$q = DBQuery("SELECT techtree_items.ID, techtree_items.Name 
FROM {$pre}techtree_items AS techtree_items LEFT JOIN {$pre}techtree_useritems AS techtree_useritems ON techtree_useritems.uid={$igmid} AND techtree_items.ID=techtree_useritems.itemid 
WHERE techtree_items.type='For' AND techtree_useritems.count IS NULL OR techtree_useritems.count = 0", __FILE__, __LINE__);
		$content['forschung'] = array();
		echo '<data><option><value>0</value><description>Forschung auswählen!</description></option>';
		while($row = mysql_fetch_row($q)) {
			echo '<option><value>', $row[0], '</value><description>', EscapeOU($row[1]), '</description></option>';
		}
		echo '</data>';
	}
?>
