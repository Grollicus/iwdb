<?php
/// Das Ding scheint nicht verwendet zu werden..
	function ScansView() {
		global $content, $pre;
		
		$q = DBQuery("SELECT * FROM (SELECT universum.gala, universum.sys, universum.pla, universum.planiname, universum.ownername, universum.objekttyp, universum.planityp, scans.basistyp, scans.id, scans.fe, scans.st, scans.vv, scans.ch, scans.ei, scans.wa, scans.en, scans.time
FROM {$pre}universum AS universum INNER JOIN {$pre}scans AS scans ON universum.gala=scans.gala AND universum.sys=scans.sys AND universum.pla=scans.pla
WHERE universum.ownername='".EscapeDB(Param('owner'))."' AND (scans.typ IS NULL OR scans.typ='geb') ORDER BY scans.time) AS blub GROUP BY gala, sys, pla", __FILE__, __LINE__);
		$content['planis'] = array();
		$content['accountName'] = EscapeO(Param('owner'));
		while($row = mysql_fetch_row($q)) {
			$p = array(
				'coords' => "{$row[0]}:{$row[1]}:{$row[2]}",
				'name' => EscapeOU($row[3]),
				'besitzer' => EscapeOU($row[4]),
				'objekttyp' => $row[7] != '-' ? EscapeOU($row[7]) : EscapeOU($row[5]),
				'planityp' => EscapeOU($row[6]),
				'zeit' => FormatDate($row[16]),
				'aktualitaet' => ActualityColor($row[16]),
				'fe' => number_format($row[9], 0, ',', '.'),
				'st' => number_format($row[10], 0, ',', '.'),
				'vv' => number_format($row[11], 0, ',', '.'),
				'ch' => number_format($row[12], 0, ',', '.'),
				'ei' => number_format($row[13], 0, ',', '.'),
				'wa' => number_format($row[14], 0, ',', '.'),
				'en' => number_format($row[15], 0, ',', '.'),
			);
			$scanid = $row[8];
			$qGeb = DBQuery("SELECT gebs.name, scans_gebs.anzahl FROM {$pre}scans_gebs AS scans_gebs INNER JOIN {$pre}gebs AS gebs ON scans_gebs.gebid=gebs.id WHERE scans_gebs.scanid={$scanid}", __FILE__, __LINE__);
			$gebs = array();
			while($row = mysql_fetch_row($qGeb)) {
				$gebs[] = array(
					'name' => EscapeOU($row[0]),
					'anz' => $row[1],
				);
			}
			$p['gebs'] = $gebs;
			$content['planis'][] = $p;
		}
		
		TemplateInit('scans');
		TemplateAccountOverview();
	}
?>