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
			'ServerInfo' => array('fkt' => 'ServerInfo', 'desc' => 'Informationen über den aktuellen Server'),
			'HardReset' => array('fkt' => 'HardReset', 'desc' => 'Setzt das Tool in den Ausgangszustand zurück! (nur der aktuelle Benutzer und Techtree bleiben übrig)'),
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
	
	function ServerInfo() {
		global $content, $db_connection;
		
		$res = '<table>';
		
		$res .= '<tr><td>PHP-Version</td><td>'.phpversion().'</td></tr>';
		$res .= '<tr><td>Mysqlclient-Version</td><td>'.phpversion('mysql').'</td></tr>';
		$res .= '<tr><td>Mysqlserver-Version</td><td>'.mysql_get_server_info($db_connection).'</td></tr>';
		$res .= '<tr><td>register_globals</td><td>'.(ini_get('register_globals') ? "<b>an</b>" : "aus").'</td></tr>';
		$res .= '<tr><td>magic_quotes_gpc</td><td>'.get_magic_quotes_gpc().'</td></tr>';
		
		$res .= '</table>';
		$content['result'] = $res;
	}
	
	function HardReset() {
		global $pre, $ID_MEMBER, $user, $content;
		
		$truncate = array("building","errors","flotten","flottenerinnerungen","gebs","geoscans","ip_bans","iw_cache","lastest_scans","ressuebersicht","scans","scans_flotten","scans_flotten_schiffe","scans_gebs","schiffe","sitter","sitterlog","speedlog","techtree_useritems","trade_ignores","trade_reqs","universum","uni_userdata");
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
	
?>
