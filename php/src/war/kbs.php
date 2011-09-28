<?php
	if (!defined("dddfd"))
		die("Hacking attempt");
		
	function WarKbs() {
		global $content, $pre, $fake_att, $fake_def;
		
		$now = time();
		$content['wars'] = array();
		$q_wars = DBQuery("SELECT id, name FROM {$pre}wars WHERE {$now} BETWEEN begin AND end", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q_wars)) {
			$wardata = array(
				'name' => EscapeOU($row[1]),
				'kbs' => array(),
			);
			$wardata['kbs'] = array();
			$q = DBQuery("SELECT iwid, hash, timestamp, att, attally, def, defally, attvalue, attloss, defvalue, defloss, raidvalue, bombvalue, start, dst FROM {$pre}war_kbs WHERE warid=".$row[0]." ORDER BY timestamp DESC", __FILE__, __LINE__);
			while($row = mysql_fetch_row($q)) {
				$wardata['kbs'][] = array(
					'id' => $row[0],
					'hash' => $row[1],
					'url' => 'http://www.icewars.de/portal/kb/de/kb.php?id='.$row[0].'&md_hash='.$row[1],
					'date' => FormatDate($row[2]),
					'angreiferName' => EscapeOU($row[3]),
					'angreiferAlly' => EscapeOU($row[4]),
					'verteidigerName' => EscapeOU($row[5]),
					'verteidigerAlly' => EscapeOU($row[6]),
					'angreiferWert' => number_format($row[7], 0, ',', '.'),
					'angreiferVerlust' => number_format(-$row[8], 0, ',', '.'),
					'verteidigerWert' => number_format($row[9], 0, ',', '.'),
					'verteidigerVerlust' => number_format(-$row[10], 0, ',', '.'),
					'raidWert' => number_format($row[11], 0, ',', '.'),
					'bombWert' => number_format($row[12], 0, ',', '.'),
					'startKoords' => nl2br(EscapeOU($row[13])),
					'zielKoords' => EscapeOU($row[14]),
					'isFake' => $row[7] <= $fake_att,
				);
			}
			$content['wars'][] = $wardata;
		}
		
		TemplateInit('wars');
		TemplateWarKbs();
	}
?>