<?php
	if(!defined('dddfd'))
		exit();
	function AdminUtils() {
		global $user, $content, $scripturl;
		if(!$user['isAdmin'])
			exit();
		
		$subs = array(
			'TechTreeLevels' => array('fkt' => 'UpdateTechTreeLevels', 'desc' => 'Aktualisiert die Techtree-Levels'),
			'IrcAutoLoginMasks' => array('fkt' => 'IrcAutoLoginMasksOverview', 'desc' => 'Listet IRC-Autologinmasken auf'), 
			'RequestIDCleanup' => array( 'fkt' => 'CleanupRequestIDs', 'desc' => 'Lösche alte Request-IDs.'),
			'TradeCleanup' => array('fkt' => 'TradeCleanup', 'desc' => 'Löscht erledigte Handelseinträge'),
			'CacheCleanup' => array('fkt' => 'CacheCleanup', 'desc' => 'Entfernt alte Einträge aus dem IW-Cache'),
			'KabaFilterUpdate' => array('fkt' => 'KabaFilterUpdate', 'desc' => 'KabaFilter im Bot aktualisieren'),
			'ServerInfo' => array('fkt' => 'ServerInfo', 'desc' => 'Informationen über den aktuellen Server'),
			'HardReset' => array('fkt' => 'HardReset', 'desc' => 'Setzt das Tool in den Ausgangszustand zurück! (nur der aktuelle Benutzer und der Techtree bleiben übrig)'),
			'WarRefresh' => array('fkt' => 'WarRefresh', 'desc' => 'Berechnet Kriege neu und aktualisiert den Kriegscache ({$pre}wars)'),
		);
		
		$content['msg'] = '';
		$content['result'] = '';
		$sub = isset($_GET['sub']) ? $_GET['sub'] : '';
		if(isset($subs[$sub])) {
			$content['msg'] = call_user_func($subs[$sub]['fkt']);
		}
		foreach($subs as $key => $value) {
			$content['utils'][] = array (
				'name' => $key,
				'desc' => $value['desc'],
				'link' => $scripturl.'/index.php?action=util&amp;sub='.$key,
				'active' => $key == $sub,
			);
		}
		
		TemplateInit('admin');
		TemplateAdminUtils();
	}
	
	function UpdateTechTreeLevels() {
		if(QueryIWDBUtil("techtreedepth", array(), $resp))
			return "Erfolgreich aktualisiert!";
		else
			return "Fehlgeschlagen!";
	}
	
	function IrcAutoLoginMasksOverview() {
		global $content, $pre;
		
		$q = DBQuery("SELECT users.userName, users.visibleName, irc_autologin.access, irc_autologin.mask FROM {$pre}irc_autologin AS irc_autologin LEFT JOIN {$pre}users AS users ON irc_autologin.uid = users.ID", __FILE__, __LINE__);
		$content['result'] .= '<table><tr><th>User</th><th>Access</th><th>Mask</th></tr>';
		while($row = mysql_fetch_row($q)) {
			$content['result'] .= '<tr><td>'.EscapeOU($row[0]).' ('.EscapeOU($row[1]).')</td><td>'.EscapeOU($row[2]).'</td><td>'.EscapeOU($row[3]).'</td></tr>';
		}
		$content['result'] .= '</table>';
	}
	
	function CleanupRequestIDs() {
		global $pre;
		
		$time = time()-172800;
		DBQuery("DELETE FROM {$pre}requestids WHERE time < $time", __FILE__, __LINE__);
		
		return mysql_affected_rows()." Zeilen erfolgreich gelöscht.";
	}
	
	function TradeCleanup() {
		global $pre, $content;
		
		DBQuery("DELETE FROM {$pre}trade_reqs WHERE soll <= ist", __FILE__, __LINE__);
		$ret = 'Trades: '.mysql_affected_rows().'<br />';
		DBQuery("DELETE FROM {$pre}trade_ignores WHERE NOT EXISTS (SELECT rq.id FROM {$pre}trade_reqs AS rq WHERE rq.id = {$pre}trade_ignores.id)", __FILE__, __LINE__);
		$ret .= 'Ignores: '.mysql_affected_rows();
		$content['result'] = $ret;
	}
	
	function CacheCleanup() {
		global $pre, $content;
		
		DBQuery("DELETE FROM {$pre}iw_cache WHERE timestamp < DATE_SUB(NOW(), INTERVAL 1 MONTH)", __FILE__, __LINE__);
		$content['result'] = "Deleted cache entires: ".mysql_affected_rows();
	}
	
	function KabaFilterUpdate() {
		global $pre, $content;
		
		if(QueryIWDBUtil("KabaFilter", array(), $resp))
			return "Erfolgreich aktualisiert!";
		else
			return "Fehlgeschlagen!";
	}
	
	function ServerInfo() {
		global $content, $db_connection;
		
		$res = '<table>';
		
		$res .= '<tr><td>PHP-Version</td><td>'.phpversion().'</td></tr>';
		$res .= '<tr><td>Mysqlclient-Version</td><td>'.phpversion('mysql').'</td></tr>';
		$res .= '<tr><td>Mysqlserver-Version</td><td>'.mysql_get_server_info($db_connection).'</td></tr>';
		$res .= '<tr><td>register_globals</td><td>'.(ini_get('register_globals') ? "<b>an</b>" : "aus").'</td></tr>';
		$res .= '<tr><td>magic_quotes_gpc</td><td>'.get_magic_quotes_gpc().'</td></tr>';
		$res .= '<tr><td>json_encode(array("a"=>123, "b"=>"c"))</td><td>'.(function_exists('json_encode') ? json_encode(array("a"=>123, "b"=>"c")) : '<b>doesnt exist!</b>').'</td></tr>';
		$res .= '<tr><td>hash_algos()</td><td>'.print_r(hash_algos(),true).'</td></tr>';
		foreach(array('session.save_path', 'session.auto_start', 'session.gc_probability', 'session.gc_divisor', 'session.gc_maxlifetime', 'session.use_cookies', 'session.cookie_path', 'session.cookie_domain', 'session.cookie_secure', 'session.cookie_httponly', 'session.hash_function') as $s)
			$res .= '<tr><td>'.$s.'</td><td>'.ini_get($s).'</td></tr>';
		$res .= '</table>';
		$content['result'] = $res;
	}
	
	function HardReset() {
		global $pre, $ID_MEMBER, $user, $content, $scripturl;
		
		if(!isset($_REQUEST['sure'])) {
			$content['result'] = 'Sicher? <a href="'. $scripturl.'/index.php?action=util&amp;sub=HardReset&amp;sure=1">Ja</a>';
			return;
		}
		
		$truncate = array("building","errors","flotten","geoscans","ip_bans","iw_cache","lastest_scans","ressuebersicht","scans","scans_flotten","scans_flotten_schiffe","scans_gebs","sitter","sitterlog","speedlog","techtree_useritems","trade_ignores","trade_reqs","universum","uni_userdata");
		$res = '<table>';
		foreach($truncate as $tbl) {
			DBQuery("TRUNCATE {$pre}".$tbl, __FILE__, __LINE__);
			$res .= '<tr><td>'.$tbl.'</td><td>'.mysql_affected_rows().'</td></tr>';
		}
		DBQuery("DELETE FROM {$pre}users where id != {$ID_MEMBER}", __FILE__, __LINE__);
		$res .= '<tr><td>users</td><td>'.mysql_affected_rows().'</td></tr>';
		DBQuery("DELETE FROM {$pre}igm_data where id != ".$user['igmuser'], __FILE__, __LINE__);
		$res .= '<tr><td>igm_data</td><td>'.mysql_affected_rows().'</td></tr>';
		DBQuery("DELETE FROM {$pre}irc_autologin where uid != {$ID_MEMBER}", __FILE__, __LINE__);
		$res .= '<tr><td>irc_autologin</td><td>'.mysql_affected_rows().'</td></tr>';
		$res .= '</table>';
		$content['result'] =$res;
	}
	function WarRefresh() {
		global $content;
		if(QueryIWDBUtil("WarFilter", array(), $resp)) {
			$content['result'] = nl2br($resp);
			return "Erfolgreich aktualisiert!";
		} else {
			return "Fehlgeschlagen!";
		}
	}
	
?>
