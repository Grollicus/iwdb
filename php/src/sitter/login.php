<?php

	if (!defined("dddfd"))
		die("Hacking attempt");

	function SitterDoLogin() {
		global $spiel, $user, $ID_MEMBER, $pre, $warmode;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		$sitter= isset($_REQUEST['sitter']);
		$id = intval($_REQUEST['ID']);
		if(!$sitter && $id != $user['igmuser']) {
			die("nö.");
		}
		
		$victim = DBQueryOne("SELECT igmname, sitterpw, realpw FROM {$pre}igm_data AS igm_data WHERE igm_data.id=".$id, __FILE__, __LINE__);
		if($victim === false)
			die("noe.");
		
		$now = time();
		$login_factor = 1;
		if($warmode) {
			$login_factor = 5;
			$schedule_slot = $now - ($now % 1800);
			if(0<DBQueryOne("SELECT count(*) FROM {$pre}war_schedule WHERE time={$schedule_slot} AND userid={$ID_MEMBER}", __FILE__, __LINE__))
				$login_factor = 20;
		}
		
		$last = DBQueryOne("SELECT lastLogin FROM {$pre}igm_data WHERE ID={$id}", __FILE__, __LINE__);
		DBQuery("UPDATE {$pre}igm_data SET lastLogin={$now} WHERE ID={$id}", __FILE__, __LINE__);
		DBQuery("UPDATE {$pre}users SET sittertime=sittertime+".(($now-$last)*$login_factor)." WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
		
		if($spiel == 'iw')  {
			$loginurl = 'http:///176.9.83.213/index.php?action=login&submit=1';
			if($sitter)
				$loginurl .= '&sitter=1';
			$loginurl .= '&name='.EscapeOU($victim[0]);
			if($sitter)
				$loginurl .= '&pswd='.EscapeO(rawurlencode(utf8_decode($victim[1])));
			else
				$loginurl.= '&pswd='.EscapeO(rawurlencode(utf8_decode($victim[2])));
		} else {
			$loginurl = 'http://www.crystalwars.de/index.php?action=login&submit_data=1';
			if($sitter)
				$loginurl .= '&login_sitter=1';
			$loginurl .= '&login_name='.EscapeOU($victim[0]);
			if($sitter)
				$loginurl .= '&login_pswd='.EscapeOU(rawurlencode(utf8_decode($victim[1])));
			else
				$loginurl.= '&login_pswd='.EscapeO(rawurlencode(utf8_decode($victim[2])));
		}
		$sitterdata = DBQueryOne("SELECT sitterskin, ipsecurity FROM {$pre}users WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
		if($sitterdata[0] != 0)
			$loginurl .= '&serverskin=1&serverskin_typ='.$sitterdata[0];
		if($sitterdata[1] == 0)
			$loginurl .= '&ip_change=1';
		Redirect($loginurl);
	}
		
	function SitterLogin() {
		global $ID_MEMBER, $pre, $content, $scripturl, $spiel, $sourcedir, $user;

		if($user['isRestricted'])
			die("Hacking Attempt");
		
		require_once($sourcedir.'/newscan/main.php');
		$tmpid = ParseScansEx(true); //für fastpaste
		
		$from = EscapeO(Param('from'));
		if(isset($_GET['jobid'])) {
			$jid = intval($_GET['jobid']);
			$now = time();
			$jobdata = DBQueryOne("SELECT igmid FROM {$pre}sitter WHERE ID={$jid} AND done=0 AND time<={$now}", __FILE__, __LINE__);
			if($jobdata === false) { //race condition - andere hat den Auftrag als erledigt markiert während sich hier jemand grade für den Job einloggen will
				Redirect($scripturl. '/index.php?action='. $from.'&msg=sitter_racecondition');
			}
			$content['job'] = $jid;
			$id = $jobdata;
			$params = '&amp;id='.$jid.'&amp;uid='.$id.'&amp;from='.$from;
		} elseif(isset($_GET['id'])) {
			if($_GET['id'] == 'next') {
				$id = DBQueryOne("SELECT ID FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
			} elseif($_GET['id'] == 'idle') {
				$now = time();
				$id = DBQueryOne("SELECT building.uid AS uid FROM {$pre}building AS building INNER JOIN {$pre}igm_data AS igm_data ON building.uid=igm_data.ID WHERE igm_data.ikea=0 OR building.plani=0 GROUP BY building.plani, uid ORDER BY IF(MAX(building.end)<{$now}, 0, MAX(building.end)), igm_data.lastLogin LIMIT 0,1", __FILE__, __LINE__);
			} else {
				$id = intval($_GET['id']);
			}
			$content['job'] = 0;
			$params = '&amp;uid='.$id.'&amp;from='.$from;
		} else {
			return;
		}
		$now = time();
		$lastloginid = DBQueryOne("SELECT userid FROM {$pre}sitterlog WHERE victimid={$id} AND userid<>{$ID_MEMBER} AND type='login' AND time >= ".(time()-300), __FILE__, __LINE__);
		
		LogAction($id, 'login', '');
		$victim = DBQueryOne("SELECT igmname, lastLogin FROM {$pre}igm_data WHERE ID=".$id, __FILE__, __LINE__);
		if($victim === false)
			return;
		$params .= '&amp;lastLogin='.$victim[1];
		
		$loginurl = $scripturl.'/index.php?action=sitter_dologin&amp;sitter=1&amp;ID='.$id;
		$content['leftUtil'] = $scripturl.'/index.php?action=sitterutil_job'.$params.'&amp;pos=left';
		$content['rightUtil'] = $scripturl.'/index.php?action=sitterutil_newscan'.$params.'&amp;pos=right';
		if($tmpid !== false)
			$content['rightUtil'] .= '&amp;tmpid='.$tmpid;
		$content['accName'] = EscapeOU($victim[0]);
		$content['loginUrl'] = $loginurl;
		$content['loginWarning'] = $lastloginid !== false;
		$content['loginLastUser'] = $lastloginid === false ? '' : DBQueryOne("SELECT visibleName FROM {$pre}users WHERE ID=".$lastloginid, __FILE__, __LINE__);
		
		TemplateInit('sitter');
		TemplateSitterLogin();
	}
	
	function MainLogin() {
		global $content, $pre, $ID_MEMBER, $scripturl, $user, $spiel, $sourcedir;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		$dta = DBQueryOne("SELECT igmname, lastLogin FROM {$pre}igm_data WHERE ID=".$user['igmuser'], __FILE__, __LINE__);
		$params = '&amp;id=0&amp;uid='.$user['igmuser'].'&amp;from='.EscapeO(Param('from')).'&amp;lastLogin='.$dta[1];
		
		$content['accName'] = EscapeOU($dta[0]);
		$content['leftUtil'] = $scripturl.'/index.php?action=sitterutil_trade'.$params.'&amp;pos=left';
		$content['rightUtil'] = $scripturl.'/index.php?action=sitterutil_newscan'.$params.'&amp;pos=right';
		$content['loginUrl'] = $scripturl.'/index.php?action=sitter_dologin&amp;ID='.$user['igmuser'];
		$content['loginWarning'] = false;
		
		TemplateInit('sitter');
		TemplateSitterLogin();
	}
	
	function SitterUtilPrepare() {
		global $content, $pre, $scripturl, $params, $user;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		$lastLogin = intval($_REQUEST['lastLogin']);
		$content['nextLoginColor'] = LastLoginColor($lastLogin);
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$uid = intval($_REQUEST['uid']);
		$content['uid'] = $uid;
		$from = EscapeO(Param('from'));
		$pos = $_GET['pos'] == 'left' ? 'left' : 'right';
		$content['position'] = $pos;
		$params = "&amp;id={$id}&amp;uid={$uid}&amp;from={$from}&amp;pos={$pos}&amp;lastLogin={$lastLogin}";
		$content['params'] = $params;
		$content['hasExitLink'] = ($pos == 'left');
		
		if($_REQUEST['pos'] == 'right') {
			$content['nextLoginLink'] = $scripturl."/index.php?action=sitter_login&amp;from={$from}&amp;id=next";
			$content['idleLoginLink'] = $scripturl."/index.php?action=sitter_login&amp;from={$from}&amp;id=idle";
			
			$q = DBQuery("SELECT id, igmname FROM {$pre}igm_data ORDER BY igmname", __FILE__, __LINE__);
			$content['users'] = array();
			while($row = mysql_fetch_row($q)) {
				$content['userLogins'][] = array(
					'name' => EscapeOU($row[1]),
					'value' => $scripturl."/index.php?action=sitter_login&amp;from={$from}&amp;id=".$row[0],
					'isSelected' => $row[0] == $uid, 
				);
			}
			
		} else {
			$content['exitLink'] = $scripturl. '/index.php?action='. $from;
			$infos = DBQueryOne("SELECT accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data WHERE ID={$uid}", __FILE__, __LINE__);
			$accTypes = array(
				'fle' => array('<b>F</b>', 'Dieser Account ist ein Fleeter-Account'),
				'bud' => array('B', 'Dieser Account ist ein Buddler-Account'),
				'mon' => array('M', 'Dieser Account ist ein Monarch-Account'),
				'all' => array('A', 'Dieser Account ist ein Allrounder-Account'),
			);
			$content['accountInfo'] = array(
				'rawType' => $infos[0],
				'type' => $accTypes[$infos[0]][0],
				'typeDesc' => $accTypes[$infos[0]][1],
				'ikea' => $infos[1] != 0,
				'mdp' => $infos[2] !=0,
				'iwsa' => $infos[3] != 0,
			);
		}
	}
	
	function SitterUtilJob() {
		global $user;
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		if(isset($_REQUEST['done'])) {
			SitterUtilJobDone();
			return;
		}
		if(isset($_REQUEST['move'])) {
			SitterUtilJobMove();
			return;
		}
		SitterUtilJobView();
	}
	
	function SitterUtilJobDone() {
		global $pre, $ID_MEMBER, $content, $user;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		$id = intval($_REQUEST['id']);
		$job = DBQueryOne("SELECT sitter.ID, sitter.done, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.ID={$id}", __FILE__, __LINE__);
		if($job[1] != 0) { //Auftrag schon erledigt, Formular nur nochmal aufgerufen - warum auch immer
			$_GET['id'] = 0;
			SitterUtilJobView();
			return;
		}
		
		if(isset($_REQUEST['bauschleife'])) {
			$coords = $job[9].':'.$job[10].':'.$job[11];
			$bs = ParseIWBuildingQueue(Param('bauschleife'), $coords);
			if($bs === false || count($bs) == 0) {
				$content['msg'] = 'Konnte mit der Bauschleife nichts anfangen!';
				SitterUtilJobView();
				return;
			}
			$q = DBQuery("SELECT sitter.ID, sitter.usequeue FROM {$pre}sitter AS sitter WHERE FollowUpTo={$id}", __FILE__, __LINE__);
			while($row = mysql_fetch_row($q)) {
				if(count($bs) == 0) {
					$time = $job[5];
				} else {
					if($row[1] == '1') {
						$time = $bs[0];
					} else {
						$time = end($bs);
					}
				}
				DBQuery("UPDATE {$pre}sitter SET time={$time}, FollowUpTo=0 WHERE ID=".$row[0], __FILE__, __LINE__);
			}
		}
		require_once dirname(__FILE__)."/view.php";
		$types = array(
			'Geb' => 'Bauauftrag',
			'For' => 'Forschungsauftrag',
			'Sch' => 'Schiffbauauftrag',
			'Sonst' => 'sonstiger Auftrag',
		);
		$text = '<b>Erledigt</b><br />';
		$text .= FormatDate($job[5]).'<br />';
		$text .= '['.$job[9]. ':'. $job[10]. ':'. $job[11].'] '.EscapeOU($job[12])."<br />";
		$text .= '<b>'.$types[$job[6]].'</b><br />';
		$text .= EscapeDBU(SitterText($job));
		
		DBQuery("UPDATE {$pre}sitter SET done=1 WHERE id=".$id, __FILE__, __LINE__);
		//DBQuery("INSERT INTO {$pre}sitterlog (userid, victimid, type, time, text) VALUES ({$ID_MEMBER}, ".$job[3].", 'auftrag', ".time().", '{$text}')", __FILE__, __LINE__);
		LogAction($job[3], 'auftrag', $text);
		if($job[3] != $user['igmuser'])
			DBQuery("UPDATE {$pre}users SET sitterpts=sitterpts+1 WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
		
		$_GET['id'] = 0;
		SitterUtilJobView();
	}
	
	function SitterUtilJobMove() {
		global $pre, $user, $scripturl, $content, $user, $ID_MEMBER;
		if($user['isRestricted'])
			die("Hacking Attempt");
		$id = intval($_REQUEST['id']);
		$uid = intval($_REQUEST['uid']);
		$from = EscapeO(Param('from'));
		$pos = $_GET['pos'] == 'left' ? 'left' : 'right';
		$lastLogin = intval($_REQUEST['lastLogin']);
		$params = "&amp;id={$id}&amp;uid={$uid}&amp;from={$from}&amp;pos={$pos}&amp;lastLogin={$lastLogin}";
		$content['params'] = $params;
		$content['position'] = $pos;
		
		if(isset($_REQUEST['abs']) && CheckRequestID()) {
			$update = '';
			$time = false;
			if(!empty($_REQUEST['zeit1'])) {
				$time = ParseTime($_REQUEST['zeit1']);
			}
			if(!empty($_REQUEST['bauschleife'])) {
				$c = DBQueryOne("SELECT sitter.time, sitter.usequeue, universum.gala, universum.sys, universum.pla, sitter.igmid
	FROM ({$pre}sitter AS sitter LEFT JOIN {$pre}universum AS universum ON sitter.planID = universum.ID)
	WHERE sitter.ID = {$id}", __FILE__, __LINE__);
				$coords = $c[2].':'.$c[3].':'.$c[4];
				$bs = ParseIWBuildingQueue(Param('bauschleife'), $coords);
				if(count($bs) == 0 || $bs === false) {
					$time = $c[0];
					$content['msg'] = 'Konnte mit der angegebenen Bauschleife nix anfangen!';
				} else {
					if($c[1] == '1') {
						$time = $bs[0];
					} else {
						$time = end($bs);
					}
				}
			}
			if($time !== false) {
				$dta = DBQueryOne("SELECT sitter.time, sitter.igmid
FROM ({$pre}sitter AS sitter LEFT JOIN {$pre}universum AS universum ON sitter.planID = universum.ID)
WHERE sitter.ID = {$id}", __FILE__, __LINE__);
				if(($time-$dta[0]) > 300 && $dta[1] != $user['igmuser'] && $time > time()) {
					DBQuery("UPDATE {$pre}users SET sitterpts=sitterpts+1 WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
				}
				
				$update .= "time={$time}, ";
			}
			if(!empty($_REQUEST['kommentar'])) {
				$text = "\nKommentar von ".$user['visibleName'].": ".EscapeDB($_REQUEST['kommentar']);
				$update .= "notes=CONCAT(notes,'{$text}'), ";
			}
			if(strlen($update) > 0) {
				$update = substr($update, 0, -2);
				DBQuery("UPDATE {$pre}sitter SET {$update} WHERE ID={$id}", __FILE__, __LINE__);
				
				$job = DBQueryOne("SELECT sitter.ID, sitter.done, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.ID={$id}", __FILE__, __LINE__);
				require_once dirname(__FILE__)."/view.php";//need: SitterText
				$types = array(
                  		      'Geb' => 'Bauauftrag',
                        		'For' => 'Forschungsauftrag',
                        		'Sch' => 'Schiffbauauftrag',
                        		'Sonst' => 'sonstiger Auftrag',
                		);
				$text = '<b>Aktualisiert</b><br />';
				$text .= FormatDate($job[5]).'<br />';
				$text .= '['.$job[9]. ':'. $job[10]. ':'. $job[11].'] '.EscapeOU($job[12])."<br />";
				$text .= '<b>'.$types[$job[6]].'</b><br />';
				$text .= EscapeDBU(SitterText($job));
				LogAction($job[3], 'auftrag', $text);
				
				$_GET['id']=0;
				SitterUtilJobView();
				StopExecution();
			}
		}
		
		$content['zeit1'] = FormatDate(DBQueryOne("SELECT time FROM {$pre}sitter WHERE ID={$id}", __FILE__, __LINE__));
		$content['submitLink'] = $scripturl.'/index.php?action=sitterutil_job&amp;sub=move'.$params;
		$content['backLink'] = $scripturl.'/index.php?action=sitterutil_job&amp;sub=view'.$params;
		GenRequestID();
		TemplateInit('sitter');
		TemplateSitterUtilJobMove();
	}
	
	function SitterUtilJobView() {
		global $content, $ID_MEMBER, $pre, $scripturl, $params, $user;
		if($user['isRestricted'])
			die("Hacking Attempt");
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if($id == 0) {
			$uid = intval($_REQUEST['uid']);
			$id = DBQueryOne("SELECT ID FROM {$pre}sitter WHERE igmid={$uid} AND done=0 AND time <= ".time()." ORDER BY time ASC LIMIT 1", __FILE__, __LINE__);
			if($id === false) {
				$id = 0;
			} else {
				$content['smsg'] = 'Für den Account ist ein Sitterauftrag offen!';
				$_GET['id'] = $id; //Ekliger Hack, um die Auftrags-ID in den Prepare() rein zu kriegen
			}
		}
		SitterUtilPrepare();
		
		if($id != 0) { //Benutzer bearbeitet grade einen Sitterauftrag
			$job = DBQueryOne("SELECT sitter.ID, sitter.uid, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) LEFT JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.ID={$id}", __FILE__, __LINE__);
			if($job !== false) {
				require_once dirname(__FILE__)."/view.php";//need: SitterText
				$types = array(
					'Geb' => 'Bauauftrag',
					'For' => 'Forschungsauftrag',
					'Sch' => 'Schiffbauauftrag',
					'Sonst' => 'sonstiger Auftrag',
				);
				$content['jobid'] = $id;
				$content['time'] = FormatDate($job[5]);
				$content['text'] = SitterText($job);
				$content['longType'] = $types[$job[6]];
				$content['coords'] = $job[9]. ':'. $job[10]. ':'. $job[11];
				$content['planiName'] = EscapeOU($job[12]);
				
				$content['formAction'] = $scripturl.'/index.php?action=sitterutil_job'.$params;
				
				$followUps = DBQueryOne("SELECT COUNT(*) FROM {$pre}sitter WHERE FollowUpTo={$id}", __FILE__, __LINE__);
				$content['hasFollowUp'] = ($followUps > 0);
			}
		}
		
		$content['hasjob'] = $id != 0;
		
		TemplateInit('sitter');
		TemplateSitterUtilJobView();
	}
	
	function SitterUtilNewscan() {
		global $content, $sourcedir, $scripturl, $pre, $params, $user;
		if($user['isRestricted'])
			die("Hacking Attempt");
			
		require($sourcedir.'/newscan/main.php');
		ParseScansEx();
		
		if(isset($_REQUEST['tmpid'])) {
			$tmpid = intval($_REQUEST['tmpid']);
			$tmp = DbQueryOne("SELECT value FROM {$pre}temp WHERE ID=".$tmpid, __FILE__, __LINE__);
			if($tmp !== false) {
				DBQuery("DELETE FROM {$pre}temp WHERE ID={$tmpid}", __FILE__, __LINE__);
				$arr = unserialize(utf8_decode($tmp));
				if(isset($arr['msg']))
					if(isset($content['msg']))
						$content['msg'] .= '<br />'.$arr['msg'];
					else
						$content['msg'] = '<br />'.$arr['msg'];
				if(isset($arr['submsg']))
					if(isset($content['submsg']))
						$content['submsg'] .= '<br />'.$arr['submsg'];
					else
						$content['submsg'] = '<br />'.$arr['submsg'];
			}
		}
		
		GenRequestID();
		SitterUtilPrepare();

		$from = EscapeO(Param('from'));
		$content['fastPasteTarget'] = $scripturl.'/index.php?action=sitter_login&from='.$from.'&id=next';
		$content['idlePasteTarget'] = $scripturl.'/index.php?action=sitter_login&from='.$from.'&id=idle';
		
		TemplateInit('sitter');
		TemplateSitterUtilNewscan();
	}

	function SitterUtilTrade() {
		global $content, $ID_MEMBER, $scripturl, $pre, $params, $user;

		if($user['isRestricted'])
			die("Hacking Attempt");
		
		SitterUtilPrepare();
		$content['submitUrl'] = $scripturl. '/index.php?action=sitterutil_trade'.$params;
		
		if(isset($_REQUEST['ignore']) && CheckRequestID()) {
			DBQuery("INSERT INTO {$pre}trade_ignores (id, uid, end) VALUES (".intval($_REQUEST['rid']).", {$ID_MEMBER}, ".(time()+604800).") ON DUPLICATE KEY UPDATE end=VALUES(end)", __FILE__, __LINE__);
		} elseif(isset($_REQUEST['fullDone']) && CheckRequestID()) {
			$id = intval($_REQUEST['rid']);
			$now = time();
			$row = DBQueryOne("SELECT uid,ziel,ress,schiffid,soll-ist FROM {$pre}trade_reqs WHERE id=".$id, __FILE__, __LINE__);
			DBQuery("UPDATE {$pre}trade_reqs SET ist=soll WHERE id=".$id, __FILE__, __LINE__);
			DBQuery("INSERT INTO {$pre}trade_history (time, type, sender, receiver, dst, ress, schiffid, resscnt) VALUES ({$now}, 'edit', {$ID_MEMBER}, ".intval($row[0]).", '".EscapeDB($row[1])."', '".EscapeDB($row[2])."', ".intval($row[3]).", ".intval($row[4]).")" , __FILE__, __LINE__);
		} elseif(isset($_REQUEST['partDone']) && CheckRequestID()) {
			$id = intval($_REQUEST['rid']);
			$now = time();
			$anz = intval(str_replace('k', '000',$_REQUEST['cnt']));
			DBQuery("UPDATE {$pre}trade_reqs SET ist=ist+{$anz} WHERE id=".$id, __FILE__, __LINE__);
			$row = DBQueryOne("SELECT uid,ziel,ress,schiffid FROM {$pre}trade_reqs WHERE id=".$id, __FILE__, __LINE__);
			DBQuery("INSERT INTO {$pre}trade_history (time, type, sender, receiver, dst, ress, schiffid, resscnt) VALUES ({$now}, 'edit', {$ID_MEMBER}, $row[0], '".EscapeDB($row[1])."', '".EscapeDB($row[2])."', ".intval($row[3]).", ".$anz.")", __FILE__, __LINE__);
		}
		
		$req = DBQueryOne("SELECT trade_reqs.id, trade_reqs.time, trade_reqs.priority, trade_reqs.ress, trade_reqs.ziel, trade_reqs.soll, trade_reqs.ist, trade_reqs.comment, igm_data.igmname, techtree_items.Name
FROM (({$pre}trade_reqs AS trade_reqs INNER JOIN {$pre}igm_data AS igm_data ON trade_reqs.uid = igm_data.ID)
	LEFT JOIN {$pre}trade_ignores AS trade_ignores ON trade_reqs.ID = trade_ignores.ID AND trade_ignores.uid={$ID_MEMBER})
	LEFT JOIN {$pre}techtree_items AS techtree_items ON trade_reqs.SchiffID=techtree_items.ID
	WHERE (ISNULL(trade_ignores.ID) OR trade_ignores.end <= ".time().") AND soll > ist ORDER BY priority, time LIMIT 0,1", __FILE__, __LINE__);
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
		$prioritys = array(
			-20 => 'sehr Dringend',
			-10 => 'Wichtig',
			  0 => 'Normal',
			 10 => 'Nicht so dringend',
			 20  => '(fast) total irrelevant',
		);
		if($req === false) {
			$content['hasReq'] = false;
		} else {
			$content['hasReq'] = true;
			$content['req'] = array(
				'id' => $req[0],
				'time' => FormatDate($req[1]),
				'priority' => $prioritys[$req[2]],
				'nameLong' => $req[3] == 'schiff' ? $req[9] : $ress[$req[3]],
				'ziel' => EscapeOU($req[4]),
				'soll' => number_format($req[5], 0, ',', '.'),
				'ist' => number_format($req[6], 0, ',', '.'),
				'fehl' => number_format($req[5]-$req[6], 0, ',', '.'),
				'user' => EscapeOU($req[8]),
				'comment' => EscapeOU($req[7]),
			);
		}
		GenRequestID();
		TemplateInit('sitter');
		TemplateSitterUtilTrade();
	}

	function SitterUtilLog() {
		global $content, $pre, $scripturl, $user;
		if($user['isRestricted'])
			die("Hacking Attempt");
			
		$uid = intval($_REQUEST['uid']);
		SitterUtilPrepare();
		
		$q = DBQuery("SELECT users.visibleName, sitterlog.type, sitterlog.time, sitterlog.Text 
FROM ({$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON sitterlog.userid = users.ID)
WHERE sitterlog.victimid=".$uid." ORDER BY time DESC LIMIT 0, 6", __FILE__, __LINE__);
		$content['ownlog'] = array();
		$types = array(
			'login' => 'L',
			'auftrag' => 'A',
		);
		while($row = mysql_fetch_row($q)) {
			$content['log'][] = array(
				'user' => EscapeOU($row[0]),
				'type' => $types[$row[1]],
				'time' => FormatDate($row[2]),
				'text' => $row[3], //Nicht escaped, weil der Text schon escaped in die Datenbank eingetragen wird
			);
		}
		TemplateInit('sitter');
		TemplateSitterUtilLog();
	}
	
	function SitterUtilRess() {
		global $content, $pre, $scripturl, $user, $params;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		SitterUtilPrepare();
		$uid = intval($_REQUEST['uid']);
		$content['submitUrl'] = $scripturl.'/index.php?action=sitterutil_ress'.$params;
		
		
		$ress = array(
			'fe' => 'Eisen',
			'st' => 'Stahl',
			'vv' => 'VV4A',
			'ch' => 'Chemie',
			'ei' => 'Eis',
			'en' => 'Energie',
			'wa' => 'Wasser',
			'bev' => 'Bevölkerung',
			'cr' => 'Credits',
		);
		if(isset($_REQUEST['ress'])) {
			$r = Param('ress');
			if(!isset($ress[$r]))
				$r = 'fe';
		} else {
			$r = 'fe';
		}
		
		$content['ress'] = array();
		foreach($ress as $short => $name) {
			$content['ress'][] = array(
				'value' => $short,
				'name' => $name,
				'selected' => ($short == $r),
			);
		}
		$q = DBQuery("SELECT id, igmname FROM {$pre}igm_data", __FILE__, __LINE__);
		$content['users'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['users'][] = array(
				'id' => $row[0],
				'name' => EscapeOU($row[1]),
				'selected' => $row[0] == $uid,
			);
		}
		
		$show_lager = in_array($r, array('ch', 'ei', 'wa', 'ch', 'en'));
		$q = DBQuery("SELECT {$r}, v{$r}, universum.gala, universum.sys, universum.pla, universum.planiname".($show_lager ? ", l{$r}" : '')." 
FROM {$pre}ressuebersicht AS ressuebersicht LEFT JOIN {$pre}universum AS universum ON ressuebersicht.planid = universum.ID
WHERE uid={$uid} AND universum.objekttyp IN ('Kolonie', 'Sammelbasis')", __FILE__, __LINE__);
		$content['data'] = array();
		while($row = mysql_fetch_row($q)) {
			if($row[1] < 0 || ($row[1] != 0 && $show_lager)) {
				$h = (-1)*$row[0]/$row[1]*100;
				if($show_lager) {
					$l = abs(($row[6] - $row[0])/$row[1]*100);
					if($row[1]>0 && ($h > 0 && $l < $h || $h < 0 && $l < abs($h)))
						$h = $l;
				}
			} else {
				$h = -1;
			}
			$content['data'][] = array(
				'coords' => $row[2].':'.$row[3].':'.$row[4],
				'name' => $row[5],
				'ress' => number_format($row[0], 0, ',', '.'),
				'prod' => number_format($row[1]/100, 2, ',', '.'),
				'haelt' => $h >= 1000 ? 'lang' : ($h > 0 ? number_format($h, 2, ',', '.').' h' : ''),
				'lager' => $show_lager ? number_format($row[6], 0, ',', '.') : '',
			);
		}
		
		TemplateInit('sitter');
		TemplateSitterUtilRess();
	}
?>
