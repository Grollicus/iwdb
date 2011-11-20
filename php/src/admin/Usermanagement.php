<?php
	if(!defined('dddfd'))
		die('foul');
	
	function Useradmin_Main() {
		global $content, $pre, $scripturl, $user;

		if(!$user['isAdmin'])
			die('Epic fail.');
		
		if(isset($_GET['new'])) {
			if(isset($_REQUEST['guest_submit'])) {
				DBQuery("INSERT INTO {$pre}users (userName, igmuser, isRestricted) VALUES ('".EscapeDB(Param('guest'))."', 0, 1)", __FILE__, __LINE__);
			} else {
				DBQuery("INSERT INTO {$pre}igm_data (lastLogin) VALUES (".time().")", __FILE__, __LINE__);
				$igmid = mysql_insert_id();
				DBQuery("INSERT INTO {$pre}users (userName, igmuser) VALUES ('".EscapeDB(Param('name'))."', {$igmid})", __FILE__, __LINE__);
			}
		}
		if(isset($_GET['del'])) {
			$delid = intval($_GET['del']);
			$igmid = DBQueryOne("SELECT igmuser FROM {$pre}users where ID={$delid}", __FILE__, __LINE__); 
			DBQuery("DELETE FROM {$pre}users WHERE ID=".$delid, __FILE__, __LINE__);
			if(0 == DBQueryOne("SELECT count(*) FROM {$pre}users WHERE igmuser=$igmid", __FILE__, __LINE__))
				DBQuery("DELETE FROM {$pre}igm_data where id={$igmid}", __FILE__, __LINE__);
		}
			
		$q = DBQuery("SELECT users.ID, userName, visibleName, isAdmin, isRestricted, lastactive, igm_data.igmname, users.igmuser, pwmd5<>'' 
FROM {$pre}users AS users LEFT JOIN {$pre}igm_data AS igm_data ON users.igmuser=igm_data.id", __FILE__, __LINE__);
		$content['users'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['users'][] = array(
				'id' => $row[0],
				'name' => EscapeOU($row[1]),
				'visibleName' => EscapeOU($row[2]),
				'isAdmin' => $row[3] ? '<b>Ja</b>' : 'Nein',
				'isRestricted' => $row[4] ? '<b>Ja</b>' : 'Nein',
				'lastactive' => FormatDate($row[5]),
				'igmName' => EscapeOU($row[6]),
				'igmid' => $row[7],
				'hasPW' => $row[8] ? 'Ja' : '<b>Nein</b>',
				'editlink' => $scripturl.'/index.php?action=settingsex&amp;ID='.$row[0],
				'dellink' => $scripturl.'/index.php?action=useradmin&amp;del='.$row[0],
			);
		}
		$content['addurl'] = $scripturl.'/index.php?action=useradmin&amp;new=1';
		
		TemplateInit('admin');
		TemplateUseradminList();
	}
?>