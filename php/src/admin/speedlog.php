<?php
	if(!defined('dddfd') || !$user['isAdmin'])
		die();
		
	function Speedlogview() {
		global $content, $pre;
		
		if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 1)
			DBQuery("TRUNCATE TABLE {$pre}speedlog", __FILE__, __LINE__);
		$q = DBQuery("SELECT action, script, sub, COUNT(*), AVG(runtime) as runt FROM {$pre}speedlog GROUP BY action, sub, script ORDER BY runt DESC",__FILE__, __LINE__);
		$content['logentries'] = array();
		while($row = mysql_fetch_row($q))
			$content['logentries'][] = array('action' => $row[0], 'script' => $row[1], 'sub' => $row[2], 'count' => $row[3],  'time' => $row[4]);
			print_r($row);
		TemplateInit('admin');
		TemplateSpeedlogView();
	}

?>
