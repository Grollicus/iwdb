<?php
//TODO: Beim Login für Sitterauftrag: Sitterauftrag-Fenster anzeigen
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
		$now = time();
		if(!isset($_SESSION['iwlogins']))
			$_SESSION['iwlogins'] = array();
		$_SESSION['iwlogins'][] = $now;
		if(count($_SESSION['iwlogins']) > 4) {
			$wait = $now-array_shift($_SESSION['iwlogins']);
			if($wait < 11)
				sleep(11-$wait);
		}
		
		
		
		$victim = DBQueryOne("SELECT igmname, sitterpw, realpw FROM {$pre}igm_data AS igm_data WHERE igm_data.id=".$id, __FILE__, __LINE__);
		if($victim === false)
			die("noe.");
		$now = time(); //nochmal gesetzt wegen potentiellem sleep()
		DBQuery("UPDATE {$pre}igm_data SET lastLogin={$now} WHERE ID={$id}", __FILE__, __LINE__);
		if($sitter)
			LogAction($id, 'login', '');
		
		if($spiel == 'iw')  {
			$loginurl = 'http://176.9.83.213/index.php?action=login&submit=1';
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
		
	function SitterLogin($fulllogin = false) {
		global $ID_MEMBER, $pre, $content, $scripturl, $spiel, $sourcedir, $user;

		if($user['isRestricted'])
			die("Hacking Attempt");

		$accTypes = array(
			'fle' => array('<b>F</b>', 'Dieser Account ist ein Fleeter-Account'),
			'bud' => array('B', 'Dieser Account ist ein Buddler-Account'),
			'mon' => array('M', 'Dieser Account ist ein Monarch-Account'),
			'all' => array('A', 'Dieser Account ist ein Allrounder-Account'),
		);
			
		$now = time();
		if(isset($_REQUEST['nextid'])) {
			$acc = DBQueryOne("SELECT ID, igmname, lastParsed, accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
			$ll = DBQueryOne("SELECT users.visibleName FROM {$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON users.ID=sitterlog.userid AND sitterlog.victimid={$acc[0]} AND sitterlog.userid<>{$ID_MEMBER} AND sitterlog.type='login' AND sitterlog.time >= ".($now-300)." ORDER BY sitterlog.time DESC LIMIT 0,1", __FILE__, __LINE__);
			echo EscapeJSU(array("uid" => $acc[0], "name" => $acc[1], "loginwarning" => $ll, "act" => LastLoginColor($acc[2]), "acc" => array(
				'rawType' => $acc[3],
				'type' => $accTypes[$acc[3]][0],
				'typeDesc' => $accTypes[$acc[3]][1],
				'ikea' => $acc[4] != 0,
				'mdp' => $acc[5] !=0,
				'iwsa' => $acc[6] != 0,
			)));
			return;
		}
		if(isset($_REQUEST['idleid'])) {
			$acc = DBQueryOne("SELECT building.uid AS uid, igm_data.igmname, igm_data.lastParsed, igm_data.accounttyp, igm_data.ikea, igm_data.mdp, igm_data.iwsa FROM {$pre}building AS building INNER JOIN {$pre}igm_data AS igm_data ON building.uid=igm_data.ID WHERE igm_data.ikea=0 OR building.plani=0 GROUP BY building.plani, uid ORDER BY IF(MAX(building.end)<{$now}, 0, MAX(building.end)), igm_data.lastParsed LIMIT 0,1", __FILE__, __LINE__);
			if($acc === false)
				$acc = DBQueryOne("SELECT ID, igmname, lastParsed, accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
			$ll = DBQueryOne("SELECT users.visibleName FROM {$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON users.ID=sitterlog.userid AND sitterlog.victimid={$acc[0]} AND sitterlog.userid<>{$ID_MEMBER} AND sitterlog.type='login' AND sitterlog.time >= ".($now-300)." ORDER BY sitterlog.time DESC LIMIT 0,1", __FILE__, __LINE__);
			echo EscapeJSU(array("uid" => $acc[0], "name" => $acc[1], "loginwarning" => $ll, "act" => LastLoginColor($acc[2]), "acc" => array(
				'rawType' => $acc[3],
				'type' => $accTypes[$acc[3]][0],
				'typeDesc' => $accTypes[$acc[3]][1],
				'ikea' => $acc[4] != 0,
				'mdp' => $acc[5] !=0,
				'iwsa' => $acc[6] != 0,
			)));
			return;
		}
		if(isset($_REQUEST['idinfo'])) {
			$acc = DBQueryOne("SELECT ID, igmname, lastParsed, accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data WHERE ID=".intval($_REQUEST['idinfo']), __FILE__, __LINE__);
			$ll = DBQueryOne("SELECT users.visibleName FROM {$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON users.ID=sitterlog.userid AND sitterlog.victimid={$acc[0]} AND sitterlog.userid<>{$ID_MEMBER} AND sitterlog.type='login' AND sitterlog.time >= ".($now-300)." ORDER BY sitterlog.time DESC LIMIT 0,1", __FILE__, __LINE__);
			echo EscapeJSU(array("uid" => $acc[0], "name" => $acc[1], "loginwarning" => $ll, "act" => LastLoginColor($acc[2]), "acc" => array(
				'rawType' => $acc[3],
				'type' => $accTypes[$acc[3]][0],
				'typeDesc' => $accTypes[$acc[3]][1],
				'ikea' => $acc[4] != 0,
				'mdp' => $acc[5] !=0,
				'iwsa' => $acc[6] != 0,
			)));
			return;
		}
		
		$jid = 0;
		if(isset($_GET['jobid'])) {
			$jid = intval($_GET['jobid']);
			$jobdata = DBQueryOne("SELECT igmid FROM {$pre}sitter WHERE ID={$jid} AND done=0 AND time<={$now}", __FILE__, __LINE__);
			if($jobdata === false) { //race condition - andere hat den Auftrag als erledigt markiert während sich hier jemand grade für den Job einloggen will
				Redirect($scripturl. '/index.php?action=sitter_view&msg=sitter_racecondition');
			}
			$id = $jobdata;
		} elseif(isset($_GET['id'])) {
			if($_GET['id'] == 'next') {
				$id = DBQueryOne("SELECT ID FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
			} elseif($_GET['id'] == 'idle') {
				$id = DBQueryOne("SELECT building.uid AS uid FROM {$pre}building AS building INNER JOIN {$pre}igm_data AS igm_data ON building.uid=igm_data.ID WHERE igm_data.ikea=0 OR building.plani=0 GROUP BY building.plani, uid ORDER BY IF(MAX(building.end)<{$now}, 0, MAX(building.end)), igm_data.lastParsed LIMIT 0,1", __FILE__, __LINE__);
				if($id === false)
					$id = DBQueryOne("SELECT ID FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
			} else {
				$id = intval($_GET['id']);
			}
		} elseif($fulllogin) {
			$id = $user['igmuser'];
		} else {
			return;
		}
		$lastloginid = DBQueryOne("SELECT userid FROM {$pre}sitterlog WHERE victimid={$id} AND userid<>{$ID_MEMBER} AND type='login' AND time >= ".(time()-300), __FILE__, __LINE__);
		
		$victim = DBQueryOne("SELECT igmname, lastParsed, accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data WHERE ID=".$id, __FILE__, __LINE__);
		if($victim === false)
			return;
		$content['accName'] = EscapeOU($victim[0]);
		$content['actuality_color'] = LastLoginColor($victim[1]);
		$content['accountInfo'] = array(
			'rawType' => $victim[2],
			'type' => $accTypes[$victim[2]][0],
			'typeDesc' => $accTypes[$victim[2]][1],
			'ikea' => $victim[3] != 0,
			'mdp' => $victim[4] !=0,
			'iwsa' => $victim[5] != 0,
		);
		$content['id'] = EscapeJS($id);
		$content['jid'] = EscapeJS($jid);
		$content['sitter'] = EscapeJS(!$fulllogin);
		$content['show_save'] = EscapeJS(isset($_REQUEST['show_save']));
		
		$q = DBQuery("SELECT id, igmname FROM {$pre}igm_data ORDER BY igmname", __FILE__, __LINE__);
		$content['users'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['userLogins'][] = array(
				'name' => EscapeOU($row[1]),
				'value' => $row[0],
				'isSelected' => $row[0] == $id,
			);
		}
		
		$content['exitLink'] = $scripturl. '/index.php?action='.EscapeO(Param('from'));
		$content['jsonLink'] = EscapeJS($scripturl."/index.php?action=sitter_login");
		
		$content['loginBase'] = EscapeJS($scripturl.'/index.php?action=sitter_dologin&sitter=1');
		$content['loginUrl'] = $scripturl.'/index.php?action=sitter_dologin&amp;ID='.$id.($fulllogin ? '' : '&amp;sitter=1');
		$content['loginWarning'] = $lastloginid !== false;
		$content['loginLastUser'] = $lastloginid === false ? '' : EscapeJS(DBQueryOne("SELECT visibleName FROM {$pre}users WHERE ID=".$lastloginid, __FILE__, __LINE__));
		
		TemplateInit('sitter');
		TemplateSitterLogin();
	}
	
	function MainLogin() {
		SitterLogin(true);
	}
	
	function SitterUtilJobEx() {
		global $content, $pre, $scripturl, $ID_MEMBER, $user;
		
		if(isset($_REQUEST['done'])) {
			$resp = array('success' => false, 'msg' => array());
			$id = $_REQUEST['jid'];
			
			$job = DBQueryOne("SELECT sitter.ID, sitter.done, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) INNER JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.ID={$id}", __FILE__, __LINE__);
			if($job[1] != 0) { //Auftrag schon erledigt, Formular nur nochmal aufgerufen - warum auch immer
				$resp['success'] = true;
				$resp['msg'][] = 'Den Auftrag hat schon jemand erledigt!';
				if(!empty($resp['msg']))
					$resp['msg'] = implode("<br/>", $resp['msg']);
				echo EscapeJSU($resp);
				return;
			}
			if(isset($_REQUEST['bauschleife'])) {
				$coords = is_null($job[9]) ? 'all' : $job[9].':'.$job[10].':'.$job[11];
				$bs = ParseIWBuildingQueue(Param('bauschleife'), $coords, $job[6]);
				if($bs === false || count($bs) == 0) {
					$resp['msg'][] = 'Konnte mit der Bauschleife nichts anfangen!';
					if(!empty($resp['msg']))
						$resp['msg'] = implode("<br/>", $resp['msg']);
					echo EscapeJSU($resp);
					return;
				}
				$q = DBQuery("SELECT sitter.ID, sitter.usequeue, sitter.type FROM {$pre}sitter AS sitter WHERE FollowUpTo={$id}", __FILE__, __LINE__);
				while($row = mysql_fetch_row($q)) {
					if(count($bs) == 0) {
						$time = $job[5];
					} else {
						if($row[1] == '1' || $row[2] == 'Sch') {
							$time = $bs[0];
						} else {
							$time = end($bs);
						}
					}
					DBQuery("UPDATE {$pre}sitter SET time={$time}, FollowUpTo=0 WHERE ID=".$row[0], __FILE__, __LINE__);
				}
			}
			require_once dirname(__FILE__)."/view.php"; // SitterText
			$types = array(
				'Geb' => 'Bauauftrag',
				'For' => 'Forschungsauftrag',
				'Sch' => 'Schiffbauauftrag',
				'Sonst' => 'sonstiger Auftrag',
			);
			$text = '<b>Erledigt</b><br />';
			$text .= FormatDate($job[5]).'<br />';
			$text .= is_null($job[9]) ? 'Alle Planeten<br />' : ('['.$job[9]. ':'. $job[10]. ':'. $job[11].'] '.EscapeOU($job[12])."<br />");
			$text .= '<b>'.$types[$job[6]].'</b><br />';
			$text .= EscapeDBU(SitterText($job));
			
			DBQuery("UPDATE {$pre}sitter SET done=1 WHERE id=".$id, __FILE__, __LINE__);
			LogAction($job[3], 'auftrag', $text);
			if($job[3] != $user['igmuser']) {
				DBQuery("UPDATE {$pre}users SET sitterpts=sitterpts+1 WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
				$resp['msg'][] = "[+1]";
			}
			$resp['success'] = true;
			if(!empty($resp['msg']))
				$resp['msg'] = implode("<br/>", $resp['msg']);
			echo EscapeJSU($resp);
			return;
		}
		
		if(isset($_REQUEST['move'])) {
			$id = intval($_REQUEST['jid']);
			$update = '';
			$time = false;
			$resp = array('msg' => array(), 'success' => false);
			if(!empty($_REQUEST['zeit'])) {
				$time = ParseTime($_REQUEST['zeit']);
				if($time === false)
					$time=0;
			}
			if(!empty($_REQUEST['bauschleife'])) {
				$c = DBQueryOne("SELECT sitter.time, sitter.usequeue, universum.gala, universum.sys, universum.pla, sitter.igmid, sitter.type
	FROM ({$pre}sitter AS sitter LEFT JOIN {$pre}universum AS universum ON sitter.planID = universum.ID)
	WHERE sitter.ID = {$id}", __FILE__, __LINE__);
				$coords = is_null($c[2]) ? 'all' : $c[2].':'.$c[3].':'.$c[4];
				$bs = ParseIWBuildingQueue(Param('bauschleife'), $coords, $c[6]);
				if(count($bs) == 0 || $bs === false) {
					$time = $c[0];
					$resp['msg'][] = 'Konnte mit der angegebenen Bauschleife nix anfangen!';
				} else {
					if($c[1] == '1' || $c[6] == 'Sch') {
						$time = $bs[0];
					} else {
						$time = end($bs);
					}
				}
			}
			if($time !== false) {
				if($time == 0) {
					$resp['msg'][] = "Konnte mit der angegebenen Zeit nix anfangen!";
				} else {
					$dta = DBQueryOne("SELECT sitter.time, sitter.igmid
	FROM ({$pre}sitter AS sitter LEFT JOIN {$pre}universum AS universum ON sitter.planID = universum.ID)
	WHERE sitter.ID = {$id}", __FILE__, __LINE__);
					if(($time-$dta[0]) > 300 && $dta[1] != $user['igmuser'] && $time > time()) {
						DBQuery("UPDATE {$pre}users SET sitterpts=sitterpts+1 WHERE ID={$ID_MEMBER}", __FILE__, __LINE__);
						$resp['msg'][] = "[+1]";
					}
					$update .= "time={$time}, ";
				}
			}
			if(!empty($_REQUEST['kommentar'])) {
				$text = "\nKommentar von ".$user['visibleName'].": ".EscapeDB($_REQUEST['kommentar']);
				$update .= "notes=CONCAT(notes,'{$text}'), ";
			}
			if(!empty($update)) {
				$update = substr($update, 0, -2);
				DBQuery("UPDATE {$pre}sitter SET {$update} WHERE ID={$id}", __FILE__, __LINE__);
				$resp['success'] = true;
			}
			if(!empty($resp['msg']))
				$resp['msg'] = implode("<br/>", $resp['msg']);
		}

		
		$jid = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$uid = intval($_REQUEST['uid']);
		$now = time();
		$q = DBQuery("SELECT sitter.ID, sitter.uid, users.visibleName, sitter.igmid, igm_data.igmname, 
		sitter.time, sitter.type, techtree_items.Name, sitter.stufe, universum.gala, universum.sys, universum.pla,
		universum.planiname, sitter.usequeue, sitter.anzahl, sitter.notes
	FROM (((({$pre}sitter AS sitter) LEFT JOIN ({$pre}users AS users) ON sitter.uid = users.ID)
		LEFT JOIN {$pre}igm_data AS igm_data ON sitter.igmid = igm_data.id)
		LEFT JOIN ({$pre}universum AS universum) ON sitter.planID = universum.ID)
		LEFT JOIN ({$pre}techtree_items AS techtree_items) ON sitter.itemid = techtree_items.ID
	WHERE sitter.done=0 AND followUpTo=0 AND sitter.igmid={$uid} AND sitter.time <= {$now} order by sitter.time", __FILE__, __LINE__);

		require_once dirname(__FILE__)."/view.php";//need: SitterText
		$types = array(
			'Geb' => 'Bauauftrag',
			'For' => 'Forschungsauftrag',
			'Sch' => 'Schiffbauauftrag',
			'Sonst' => 'sonstiger Auftrag',
		);
		$content['jobs'] = array();
		$specific_job = false;
		while($row = mysql_fetch_row($q)) {
			$content['jobs'][] = array(
				'id' => $row[0],
				'time' => FormatDate($row[5]),
				'text' => SitterText($row),
				'longType' => $types[$row[6]],
				'hasPlani' => !is_null($row[9]),
				'coords' => is_null($row[9]) ? '' : ('['.$row[9]. ':'. $row[10]. ':'. $row[11].']'),
				'planiName' => is_null($row[9]) ? 'Alle Planeten' : EscapeOU($row[12]),
				'hasFollowUp' => (DBQueryOne("SELECT COUNT(*) FROM {$pre}sitter WHERE FollowUpTo={$row[0]}", __FILE__, __LINE__) > 0),
				'url' => $scripturl.'/index.php?action=sitterutil_jobex',
			);
			if($jid == $row[0])
				$specific_job = true;
		}
		if(isset($_REQUEST['json'])) {
			if(isset($resp)) {
				$resp['jobs'] = $content['jobs'];
				echo EscapeJSU($resp);
				return;
			}
			echo EscapeJSU($content['jobs']);
			return;
		}
		$content['updateUrl'] = $scripturl.'/index.php?action=sitterutil_jobex&json=1';
		$content['has_specific'] = $specific_job;
		$content['specific_job'] = EscapeJS($jid);
		TemplateInit('sitter');
		TemplateSitterUtilJobEx();
	}
	
	function SitterUtilNewscan() {
		global $content, $sourcedir, $scripturl, $pre, $user, $ID_MEMBER;
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		if(isset($_POST['abs'])) {
			require($sourcedir.'/newscan/main.php');
			ParseScansEx(false, false);
			$now = time();
			$accTypes = array(
				'fle' => array('<b>F</b>', 'Dieser Account ist ein Fleeter-Account'),
				'bud' => array('B', 'Dieser Account ist ein Buddler-Account'),
				'mon' => array('M', 'Dieser Account ist ein Monarch-Account'),
				'all' => array('A', 'Dieser Account ist ein Allrounder-Account'),
			);
			$resp = array();
			if(isset($content['msg']))
				$resp['err'] = $content['msg'];
			if(isset($content['submsg']))
				$resp['msg'] = $content['submsg'];
			if(isset($_REQUEST['next'])) {
				$acc = DBQueryOne("SELECT ID, igmname, lastParsed, accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
				$ll = DBQueryOne("SELECT users.visibleName FROM {$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON users.ID=sitterlog.userid AND sitterlog.victimid={$acc[0]} AND sitterlog.userid<>{$ID_MEMBER} AND sitterlog.type='login' AND sitterlog.time >= ".($now-300)." ORDER BY sitterlog.time DESC LIMIT 0,1", __FILE__, __LINE__);
				$resp['nextid'] = array("uid" => $acc[0], "name" => $acc[1], "loginwarning" => $ll, "act" => LastLoginColor($acc[2]), "acc" => array(
					'rawType' => $acc[3],
					'type' => $accTypes[$acc[3]][0],
					'typeDesc' => $accTypes[$acc[3]][1],
					'ikea' => $acc[4] != 0,
					'mdp' => $acc[5] !=0,
					'iwsa' => $acc[6] != 0,
				));
			}
			if(isset($_REQUEST['idle'])) {
				$acc = DBQueryOne("SELECT building.uid AS uid, igm_data.igmname, igm_data.lastParsed, igm_data.accounttyp, igm_data.ikea, igm_data.mdp, igm_data.iwsa FROM {$pre}building AS building INNER JOIN {$pre}igm_data AS igm_data ON building.uid=igm_data.ID WHERE igm_data.ikea=0 OR building.plani=0 GROUP BY building.plani, uid ORDER BY IF(MAX(building.end)<{$now}, 0, MAX(building.end)), igm_data.lastParsed LIMIT 0,1", __FILE__, __LINE__);
				if($acc === false)
					$acc = DBQueryOne("SELECT ID, igmname, lastParsed, accounttyp, ikea, mdp, iwsa FROM {$pre}igm_data ORDER BY lastLogin LIMIT 0,1", __FILE__, __LINE__);
				$ll = DBQueryOne("SELECT users.visibleName FROM {$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON users.ID=sitterlog.userid AND sitterlog.victimid={$acc[0]} AND sitterlog.userid<>{$ID_MEMBER} AND sitterlog.type='login' AND sitterlog.time >= ".($now-300)." ORDER BY sitterlog.time DESC LIMIT 0,1", __FILE__, __LINE__);
				$resp['nextid'] = array("uid" => $acc[0], "name" => $acc[1], "loginwarning" => $ll, "act" => LastLoginColor($acc[2]), "acc" => array(
					'rawType' => $acc[3],
					'type' => $accTypes[$acc[3]][0],
					'typeDesc' => $accTypes[$acc[3]][1],
					'ikea' => $acc[4] != 0,
					'mdp' => $acc[5] !=0,
					'iwsa' => $acc[6] != 0,
				));
			}
			echo EscapeJSU($resp);
			return;
		}
		$content['submitUrl'] = $scripturl.'/index.php?action=sitterutil_newscan';
		TemplateInit('sitter');
		TemplateSitterUtilNewscan();
	}

	function SitterUtilTrade() {
		global $content, $ID_MEMBER, $scripturl, $pre, $user;

		if($user['isRestricted'])
			die("Hacking Attempt");
		
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
		$content['submitUrl'] = $scripturl. '/index.php?action=sitterutil_trade';
		GenRequestID();
		TemplateInit('sitter');
		TemplateSitterUtilTrade();
	}

	function SitterUtilLog() {
		global $content, $pre, $scripturl, $user;
		if($user['isRestricted'])
			die("Hacking Attempt");
			
		$uid = intval($_REQUEST['uid']);
		
		$q = DBQuery("SELECT users.visibleName, sitterlog.type, sitterlog.time, sitterlog.Text 
FROM ({$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON sitterlog.userid = users.ID)
WHERE sitterlog.victimid=".$uid." ORDER BY time DESC LIMIT 0, 6", __FILE__, __LINE__);
		$content['log'] = array();
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
		global $content, $pre, $scripturl, $user;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		$uid = intval($_REQUEST['uid']);
		$content['submitUrl'] = $scripturl.'/index.php?action=sitterutil_ress';
		
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

	function SitterUtilFlotten() {
		global $content, $pre, $scripturl, $user, $ID_MEMBER;
		if($user['isRestricted'])
			die("Hacking Attempt");
		if(isset($_REQUEST['safe']) || isset($_REQUEST['dont_save'])) {
			$flid = intval($_REQUEST['flid']);
			if(isset($_REQUEST['safe']))
				DBQuery("UPDATE {$pre}flotten SET safe=".($_REQUEST['safe'] == '1'?'1':'0')." WHERE dont_save=0 and id={$flid}", __FILE__, __LINE__);
			elseif($_REQUEST['dont_save'] == '1')
				DBQuery("UPDATE {$pre}flotten SET safe=0, dont_save={$ID_MEMBER} WHERE id={$flid}", __FILE__, __LINE__);
			else
				DBQuery("UPDATE {$pre}flotten SET dont_save=0 WHERE id={$flid}", __FILE__, __LINE__);
			$fl = DBQueryOne("SELECT safe, users.visibleName FROM {$pre}flotten AS flotten LEFT JOIN {$pre}users AS users ON flotten.dont_save=users.id where flotten.id={$flid}", __FILE__, __LINE__);
			echo EscapeJSU(array('safe' => $fl[0] == '1', 'dont_save' => !is_null($fl[1]), 'dont_save_user' => $fl[1]));
			return;
		}
		$uid = intval($_REQUEST['uid']);
		$q = DBQuery("SELECT startuni.gala, startuni.sys, startuni.pla, startuni.planiname, startuni.ownername, zieluni.gala, zieluni.sys, zieluni.pla, zieluni.planiname, flotten.id, flotten.action, flotten.ankunft, flotten.safe, users.visibleName
FROM ((({$pre}flotten AS flotten INNER JOIN {$pre}universum AS startuni ON flotten.startid = startuni.ID) 
	INNER JOIN {$pre}universum AS zieluni ON flotten.zielid=zieluni.ID)
	INNER JOIN {$pre}igm_data AS igm_data ON zieluni.ownername=igm_data.igmname)
	LEFT JOIN {$pre}users AS users ON flotten.dont_save=users.ID
WHERE flotten.action IN ('Angriff', 'Sondierung (Gebäude/Ress)','Sondierung (Schiffe/Def/Ress)') AND igm_data.id={$uid}
ORDER BY flotten.ankunft", __FILE__, __LINE__);
		$flotten = array();
		while($row  = mysql_fetch_row($q)) {
			$flotten[] = array(
				'src_coords' => EscapeOU($row[0].':'.$row[1].':'.$row[2]),
				'src_plani' => EscapeOU($row[3]),
				'src_owner' => EscapeOU($row[4]),
				'dst_coords' => EscapeOU($row[5].':'.$row[6].':'.$row[7]),
				'dst_plani' => EscapeOU($row[8]),
				'id' => intval($row[9]),
				'action' => EscapeOU($row[10]),
				'time' => FormatPreciseDate($row[11]),
				'safe' => $row[12] == '1',
				'dont_save' => !is_null($row[13]),
				'dont_save_user' => EscapeOU($row[13]),
			);
		}
		$content['flotten'] = EscapeJSU($flotten);
		$content['requesturl'] = EscapeJS($scripturl."/index.php?action=sitterutil_flotten");
		TemplateInit('sitter');
		TemplateSitterUtilFlotten();
	}
?>
