<?php
if(!defined('dddfd'))
	exit();

function KBPrint() {
	$kbid = intval($_GET['ID']);
	echo '<table border="1" width="100%">';
	
	//Beteiligte Schiffe
	//TODO: Anflugzeit berechnen
	$qatt = DBQuery("SELECT ID, name, ally FROM atters WHERE kbid=".$kbid, __FILE__, __LINE__);
	$qdef = DBQuery("SELECT ID, name, ally FROM deffers WHERE kbid=".$kbid, __FILE__, __LINE__);
	$attrow = mysql_fetch_row($qatt);
	$defrow = mysql_fetch_row($qdef);
	echo '<tr><td colspan="2" align="center"><b>beteiligte Schiffe</b></td></tr>';
	while($attrow || $defrow) {
		echo '<tr><td width="50%">';
		if($attrow != false) {
			echo '<table border="0" width="100%">';
			echo '<tr><td colspan="4"><u>', $attrow[1], ' (', $attrow[2],')</u></td></tr>';
			$q = DBQuery("SELECT shipnames.name, start, ende, verlust FROM attships INNER JOIN shipnames ON attships.shipid = shipnames.ID WHERE atterid=".$attrow[0], __FILE__, __LINE__);
			while($row = mysql_fetch_row($q))
				echo '<tr><td>', $row[0], '</td><td align="right">', number_format($row[1], 0, '', '.'), '</td><td align="right">', number_format($row[3], 0, '', '.'), '</td><td align="right">', number_format($row[2], 0, '', '.'), '</td></tr>';
			echo '</table>';
		} else
			echo '&nbsp;';
		echo '</td><td width="50%">';
		if($attrow != false) {
			echo '<table border="0" width="100%">';
			echo '<tr><td colspan="4"><u>', $defrow[1], ' (', $defrow[2],')</u></td></tr>';
			$q = DBQuery("SELECT shipnames.name, start, ende, verlust FROM defships INNER JOIN shipnames ON defships.shipid = shipnames.ID WHERE defferid=".$defrow[0], __FILE__, __LINE__);
			while($row = mysql_fetch_row($q))
				echo '<tr><td>', $row[0], '</td><td align="right">', number_format($row[1], 0, '', '.'), '</td><td align="right">', number_format($row[3], 0, '', '.'), '</td><td align="right">', number_format($row[2], 0, '', '.'), '</td></tr>';
			echo '</table>';
		} else
			echo '&nbsp;';
		echo '</td></tr>';
		$attrow = mysql_fetch_row($qatt);
		$defrow = mysql_fetch_row($qdef);
	}
	//geraidete Ress | stationäre Def
	$qraid = DBQuery("SELECT ressnames.ressname, raidress.amount FROM raidress INNER JOIN ressnames ON raidress.ressID = ressnames.ressid WHERE raidress.kbid = ".$kbid, __FILE__, __LINE__);
	$qdef = DBQuery("SELECT deffers.name, deffers.ally, defdef.start, defdef.ende, defdef.verlust, defnames.name FROM (deffers INNER JOIN defdef ON deffers.ID = defdef.defferid) INNER JOIN defnames ON defdef.defid = defnames.ID WHERE deffers.kbid = ".$kbid, __FILE__, __LINE__);
	if(mysql_num_rows($qraid) > 0 || mysql_num_rows($qdef) > 0) {
		echo '<tr><td><b>geraidete Ress</b></td><td><b>station&auml;re Def</b></td></tr>';
		echo '<tr><td width="50%">';
		if(mysql_num_rows($qraid) == 0)
			echo '&nbsp;';
		else {
			echo '<table border="0" width=100%>';
			while($row = mysql_fetch_row($qraid))
				echo '<tr><td>', $row[0], '</td><td align="right">', number_format($row[1], 0, '', '.'), '</td></tr>';
			echo '</table>';
		}
		echo '</td><td>';
		if(mysql_num_rows($qdef) == 0)
			echo '&nbsp;';
		else {
			echo '<table border="0" width="100%">';
			$first = true;
			while($row = mysql_fetch_row($qdef)) {
				if($first) {
					$first = false;
					echo '<tr><td colspan="4"><u>', $row[0], ' ', $row[1],'</u></td></tr>';
				}
				echo '<tr><td>', $row[5], '</td><td align="right">', number_format($row[2], 0, '', '.'), '</td><td align="right">', number_format($row[4], 0, '', '.'), '</td><td align="right">', number_format($row[3], 0, '', '.'), '</td><td>';
			}
			echo '</table>';
		}
		echo '</td></tr>';
	}
	//Ressverluste
	$row = mysql_fetch_row(DBQuery("SELECT count(1) FROM resslost WHERE kbid=".$kbid, __FILE__, __LINE__));
	if($row[0] != 0) {
		$q = DBQuery("SELECT ressname, amount FROM resslost INNER JOIN ressnames ON resslost.ressid = ressnames.ressid WHERE kbid=".$kbid." AND side='att'", __FILE__, __LINE__);
		echo '<tr><td colspan="2" align="center"><b>Ressverluste</b></td></tr>';
		echo '<tr><td><table border="0" width="100%">';
		if(mysql_num_rows($q) == 0)
			echo '<tr><td>&nbsp;</td></tr>';
		else
			while($row = mysql_fetch_row($q))
				echo '<tr><td>', $row[0], '</td><td align="right">', number_format($row[1], 0, '', '.'), '</td></tr>';
		echo '</table></td><td><table border="0" width="100%">';
		$q = DBQuery("SELECT ressname, amount FROM resslost INNER JOIN ressnames ON resslost.ressid = ressnames.ressid WHERE kbid=".$kbid." AND side='def'", __FILE__, __LINE__);
		if(mysql_num_rows($q) == 0)
			echo '<tr><td>&nbsp;</td></tr>';
		else
			while($row = mysql_fetch_row($q))
				echo '<tr><td>', $row[0], '</td><td align="right">', number_format($row[1], 0, '', '.'), '</td></tr>';
		echo '</table></td></tr>';
	}
	//externer Link
	$q = DBQuery("SELECT kbid, kbhash FROM kampfberichte WHERE ID=".$kbid, __FILE__, __LINE__);
	$row = mysql_fetch_row($q);
	echo '<tr><td colspan="2"><a href="http://www.icewars.de/portal/kb.php?id=', $row[0], '&md_hash=', $row[1], '" target="_blank">http://www.icewars.de/portal/kb.php?id=', $row[0], '&md_hash=', $row[1], '</a>';
	echo '</table>';
}
?>