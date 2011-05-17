<?php
	function EditShipData() {
		global $pre, $content, $user;
		if(!$user['isAdmin'])
			exit();
			
		$content['url'] = "";
		$content['override'] = false;
		if(isset($_REQUEST['submit'])) {
			$url = $_POST['url'];
			$override = isset($_POST['override']);
			if(strpos($url, 'http://www.icewars.de') === false) {
				$content['msg'] = "Fehlerhafte URL!";
			} else {
				$xmlstr = file_get_contents($url);
				if($xmlstr === false) {
					$content['msg'] = "Konnte URL nicht &ouml;ffnen!";
				} else {
					$xml = new SimpleXMLElement($xmlstr);
					if($override) {
						$str = "INSERT INTO {$pre}ships
						(iwid, name, kosten_fe, kosten_st, kosten_vv, kosten_ch, kosten_ei, kosten_wa, kosten_en, kosten_be, kosten_cr, dauer)
						VALUES ";
					} else {
						$str = "INSERT IGNORE INTO {$pre}ships
						(iwid, name, kosten_fe, kosten_st, kosten_vv, kosten_ch, kosten_ei, kosten_wa, kosten_en, kosten_be, kosten_cr, dauer)
						VALUES ";
					}
					foreach($xml->schiff as $schiff) {
						$str .= "(". $schiff->id['value']. ", '". $schiff->name['value']. "', ". $schiff->kosten->eisen['value']. ', '. $schiff->kosten->stahl['value'];
						$str .= ', '. $schiff->kosten->vv4a['value']. ', '. $schiff->kosten->chemische_elemente['value']. ', '. $schiff->kosten->eis['value'];
						$str .= ', '. $schiff->kosten->wasser['value']. ', '. $schiff->kosten->energie['value']. ', '. $schiff->kosten->bev['value'];
						$str .= ', '. $schiff->kosten->credits['value']. ', '. $schiff->dauer['value']."), ";
					}
					if($override) {
						$str = substr($str, 0, -2);
						$str .= " ON DUPLICATE KEY UPDATE iwid = VALUES(iwid), name = VALUES(name), kosten_fe = VALUES(kosten_fe),
								 kosten_st = VALUES(kosten_st), kosten_vv = VALUES(kosten_vv), kosten_ch = VALUES(kosten_ch),
								 kosten_ei = VALUES(kosten_ei), kosten_wa = VALUES(kosten_wa), kosten_en = VALUES(kosten_en),
								 kosten_be = VALUES(kosten_be), kosten_cr = VALUES(kosten_cr), dauer = VALUES(dauer) ";
					} else {
						$str = substr($str, 0, -2);
					}
					DBQuery($str, __FILE__, __LINE__);
				}
			}
			
		}
		
		$q = DBQuery("SELECT ID, iwid, name, kosten_fe, kosten_st, kosten_vv, kosten_ch, kosten_ei, kosten_wa, kosten_en, kosten_be, kosten_cr, dauer
						FROM {$pre}ships", __FILE__, __LINE__);
		$a = array();
		while($row = mysql_fetch_row($q)) {
			$a[] = array('ID' => $row[0],
						 'iwid' => $row[1],
						 'fe' =>$row[2],
						 'st' => $row[3],
						 'vv' => $row[4],
						 'ch' => $row[5],
						 'ei' => $row[6],
						 'wa' => $row[7],
						 'en' => $row[8],
						 'be' => $row[9],
						 'cr' => $row[10],
						 'dauer' => FormatTime($row[11]));
		}
		$content['shipdata'] = $a;
		TemplateInit('admin');
		TemplateEditShipData();
	}
	
?>