<?php
	function TransporteList() {
		global $pre, $content, $scripturl, $squads;
		
		$q = DBQuery("SELECT igmname, squad FROM {$pre}igm_data", __FILE__, __LINE__);
		$squads = array();
		while($row = mysql_fetch_row($q)) {
			$squads[$row[0]] = EscapeOU($row[1]);
		}
		
		$q = DBQuery("SELECT absender, SUM(eisen), SUM(stahl), SUM(chemie), SUM(vv4a), SUM(eis), SUM(wasser), SUM(energie), SUM(bev) FROM {$pre}bilanz GROUP BY absender", __FILE__, __LINE__);
		$ppl = array();
		while($row = mysql_fetch_row($q)) {
			$ppl[$row[0]] = array(
				'name' => EscapeOU($row[0]), 
				'fe' => intval($row[1]), 
				'st' => intval($row[2]), 
				'ch' => intval($row[3]), 
				'vv' => intval($row[4]), 
				'ei' => intval($row[5]), 
				'wa' => intval($row[6]), 
				'en' => intval($row[7]), 
				'bev' => intval($row[8])
			);
		}
		
		$q = DBQuery("SELECT empfaenger, SUM(eisen), SUM(stahl), SUM(chemie), SUM(vv4a), SUM(eis), SUM(wasser), SUM(energie), SUM(bev) FROM {$pre}bilanz GROUP BY empfaenger", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			if(!isset($ppl[$row[0]]))
				$ppl[$row[0]] = array('name' => EscapeOU($row[0]), 'fe' => 0, 'st' => 0, 'ch' => 0, 'vv' => 0, 'ei' => 0, 'wa' => 0, 'en' => 0, 'bev' => 0);
			$ppl[$row[0]]['fe'] -= $row[1];
			$ppl[$row[0]]['st'] -= $row[2];
			$ppl[$row[0]]['ch'] -= $row[3];
			$ppl[$row[0]]['vv'] -= $row[4];
			$ppl[$row[0]]['ei'] -= $row[5];
			$ppl[$row[0]]['wa'] -= $row[6];
			$ppl[$row[0]]['en'] -= $row[7];
			$ppl[$row[0]]['bev'] -= $row[8];
		}
		
		$content['users'] = array();
		
		global $sort_index, $sort_order;
		$sort_index = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 'name';
		$sort_order = isset($_REQUEST['desc']) ? -1 : 1;
		
		function TransporteSort($a, $b) {
			global $sort_index, $sort_order, $squads;
			
			$cmp = strcasecmp($squads[$a['name']], $squads[$b['name']]);
			if($cmp != 0)
				return $cmp;
			
			if($a[$sort_index] < $b[$sort_index]) {
				return (-1)*$sort_order;
			}
			if($a[$sort_index] > $b[$sort_index]) {
				return $sort_order;
			}
			return 0;
		}
		function TransporteSortName($a, $b) {
			global $sort_order, $squads;
			$cmp = strcasecmp($squads[$a['name']], $squads[$b['name']]);
                        if($cmp != 0)
                                return $cmp;
			return strcasecmp($a['name'], $b['name'])*$sort_order;
		}
		if($sort_index == 'name')
			usort($ppl, 'TransporteSortName');
		else
			usort($ppl, 'TransporteSort');

		$content['users'] = array();
		foreach($ppl as $u) {
			$a = array();
			foreach($u as $k => $v) {
				if($k == 'name') {
					$a[$k] = $v;
				} else {
					$a[$k] = number_format($v, 0, ',', '.');
				}
			}
			if(isset($squads[$a['name']])) {
				$content['users'][$squads[$a['name']]][] = $a;
			} else {
				$content['users']['none'][] = $a;
			}
		}
		$content['headers'] = array(
			'name' =>  array('title' => 'Name', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=name'),
			'fe' =>  array('title' => 'Eisen', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=fe'),
			'st' =>  array('title' => 'Stahl', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=st'),
			'vv' =>  array('title' => 'VV4A', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=vv'),
			'ch' =>  array('title' => 'Chemie', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=ch'),
			'ei' =>  array('title' => 'Eis', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=ei'),
			'wa' =>  array('title' => 'Wasser', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=wa'),
			'en' =>  array('title' => 'Energie', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=en'),
			'bev' =>  array('title' => 'Bevölkerung', 'sort' => false, 'order' => 'asc', 'link' => $scripturl.'/index.php?action=transporte&amp;sortby=bev'),
		);
		if(isset($content['headers'][$sort_index])) {//wg. $sort_index nicht esecaped!
			$content['headers'][$sort_index]['sort'] = true;
			$content['headers'][$sort_index]['order'] = $sort_order == 1 ? 'up' : 'down';
			$content['headers'][$sort_index]['link'] = $scripturl.'/index.php?action=transporte&amp;sortby='.$sort_index.($sort_order==1 ? '&amp;desc=1':'');
		}
		TemplateInit('ress');
		TemplateTransporteList();
	}
?>
