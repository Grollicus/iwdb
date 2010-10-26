<?php
	function KBWars() {
		$s = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 'view';
		switch($s) {
			case 'view':
			default:
				KbWarsView();
				break;
		}
	}
	function KBWarsView() {
		global $pre, $content;
		
		$q = DBQuery("SELECT ID, name, active, start, end, attmember, defmember FROM {$pre}wars", __FILE__, __LINE__);
		$content['wars'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['wars'][] = array('ID' => $row[0], 'name' => $row[1], 'active' => $row[2],
									'start' => $row[3], 'end' => $row[4], 'attmember' => $row[5],
									'defmember' => $row[6]);
		}
		TemplateInit('kb_wars');
		TemplateKbWarsView();
	}
?>