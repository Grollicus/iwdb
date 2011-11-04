<?php
	if (!defined("dddfd"))
		die("Hacking attempt");

	function SitterHistory() {
		global $ID_MEMBER, $pre, $content, $user;
		
		$longTypes = array(
			'login' => 'Sitterlogin',
			'auftrag' => 'Sitterauftrag',
			'scan' => 'Bericht eingelesen',
		);
		
		//Was andere bei dem User gemacht haben
		$q = DBQuery("SELECT users.visibleName, sitterlog.type, sitterlog.time, sitterlog.Text 
FROM ({$pre}sitterlog AS sitterlog INNER JOIN {$pre}users AS users ON sitterlog.userid = users.ID)
WHERE sitterlog.victimid=".$user['igmuser']." ORDER BY time DESC LIMIT 0, 30", __FILE__, __LINE__);
		$content['ownlog'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['ownlog'][] = array(
				'user' => EscapeOU($row[0]),
				'type' => $longTypes[$row[1]],
				'time' => FormatDate($row[2]),
				'text' => nl2br($row[3]), //nicht escaped, weil wird escaped in die DB eingetragen
			);
		}
		
		//Was der User bei anderen gemacht hat
		$q = DBQuery("SELECT igm_data.igmname, sitterlog.type, sitterlog.time, sitterlog.Text 
FROM ({$pre}sitterlog AS sitterlog INNER JOIN {$pre}igm_data AS igm_data ON sitterlog.victimid = igm_data.id)
WHERE sitterlog.userid={$ID_MEMBER} ORDER BY time DESC LIMIT 0, 30", __FILE__, __LINE__);
		$content['otherlog'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['otherlog'][] = array(
				'igmAccount' => EscapeOU($row[0]),
				'type' => $longTypes[$row[1]],
				'time' => FormatDate($row[2]),
				'text' => nl2br($row[3]), //nicht escaped, weil wird escaped in die DB eingetragen
			);
		}
		
		TemplateInit('sitter');
		TemplateSitterHistory();
	}
	
	function SitterGlobalHistory() {
		global $pre, $content;
		
		
		$longTypes = array(
			'login' => 'Sitterlogin',
			'auftrag' => 'Sitterauftrag',
			'scan' => 'Bericht eingelesen',
		);
		
		$q = DBQuery("SELECT users.visibleName, igm_data.igmName, sitterlog.type, sitterlog.time, sitterlog.Text 
FROM ({$pre}sitterlog AS sitterlog LEFT JOIN {$pre}users AS users ON sitterlog.userid = users.ID) LEFT JOIN {$pre}igm_data AS igm_data ON sitterlog.victimid = igm_data.id 
ORDER BY time DESC LIMIT 0, 300", __FILE__, __LINE__);
		$content['log'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['log'][] = array(
				'user' => EscapeOU($row[0]),
				'victim' => EscapeOU($row[1]),
				'type' => $longTypes[$row[2]],
				'time' => FormatDate($row[3]),
				'text' => nl2br($row[4]), //nicht escaped, weil wird escaped in die DB eingetragen
			);
		}
		
		TemplateInit('sitter');
		TemplateSitterGlobalHistory();
	}

?>
