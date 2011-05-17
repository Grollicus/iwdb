<?php
	if(!defined('dddfd'))
		exit();
	
	//Anmerkung: die Spalte "universum.aktuell" ist > 0, wenn der entsprechede Plani NICHT MEHR aktuell ist!
	//			 (was auch immer ich mir dabei gedacht habe..)	
	
	 
	function ShowUniMap() {
		global $content, $pre, $default_gala;
		
		if(!isset($_REQUEST['gala']))
			$gal = $default_gala; //Defaultgala
		else
			$gal = intval($_REQUEST['gala']);
		$sysperline = 20; //Systeme, die in einer Zeile der Karte ausgegeben werden
		
		$content['gala'] = $gal;
		$content['sysperline'] = $sysperline;
		$q = DBQuery("SELECT sys, MIN(inserttime), SUM(aktuell), planityp FROM {$pre}universum WHERE gala={$gal} GROUP BY sys ORDER BY sys", __FILE__, __LINE__);
		$content['galadatalines'] = array();
		$i = 0;
		$j = 0;
		while($row = mysql_fetch_row($q)) {
			if(($i % $sysperline) == 0) {
				$i = 0;
				$j++;
				$content['galadatalines'][$j] = array();
			}
			$content['galadatalines'][$j][$i] = array(
				'num' => $row[0],
				'age' => $row[3] == 'Stargate' ? 'systemmap_stargate' : $data['act'] = $row[2] > 0 ? 'act_5' : ActualityColor($row[1])
			);
			$i++;
		}
		for(;$i < $sysperline; $i++) {
			$content['galadatalines'][$j][$i] = array();
		}
		TemplateInit('uni');
		TemplateUniMap();
	}
?>
