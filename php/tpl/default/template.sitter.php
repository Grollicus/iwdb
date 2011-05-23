<?php
	if (!defined("dddfd"))
		die("Hacking attempt");

	function TemplateSitterTaskList() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		echo '
		<div class="content">
			<h2>Sitterauftr&auml;ge</h2>
			',isset($content['msg']) ? '<div class="imp">'.$content['msg'].'</div><br />' : '','
			<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th colspan="5">Sitterauftr&auml;ge offen</th></tr>
				<tr><th style="width:120px;">Zeit</th><th>Bei</th><th>Koordinaten</th><th>Auftrag</th><th style="width:50px;"></th></tr>';
		foreach ($content['sitternow'] as $line) {
			echo '
				<tr class="sitterjob_', $line['ownershipState'], '">
					<td>', $line['time'], '</td>
					<td>', $line['igmName'], '<br /><i style="fonz-size:smaller;">(', $line['userName'], ')</i></td>
					<td>[', $line['coords'], '] ', $line['planiName'], '</td>
					<td><b>', $line['typeLong'], '</b><br />', $line['text'], '</td>
					<td align="center"><a href="', $line['loginLink'], '">Login</a></td>
				</tr>';
		}
		echo '<tr><th colspan="5">Farbenlegende: <span class="sitterjob_own">Eigener Auftrag</span> <span class="sitterjob_account">Fremder Auftrag für eigenen Account</span></th></tr></table><br /><br /><br />
			<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th colspan="5">Kommende Sitterauftr&auml;ge</th></tr>
				<tr><th style="width:120px;">Zeit</th><th>Bei</th><th>Koordinaten</th><th>Auftrag</th><th>&nbsp;</th></tr>';
		foreach ($content['sittersoon'] as $line) {
			echo '
				<tr class="sitterjob_', $line['ownershipState'], '">
					<td>', $line['time'], '</td>
					<td>', $line['igmName'], '<br /><i style="fonz-size:smaller;">(', $line['userName'], ')</i></td>
					<td>[', $line['coords'], '] ', $line['planiName'], '</td>
					<td><b>', $line['typeLong'], '</b><br />', $line['text'], '</td>
					<td>&nbsp;</td>
				</tr>';
		}
		echo '<tr><th colspan="5">Farbenlegende: <span class="sitterjob_own">Eigener Auftrag</span> <span class="sitterjob_account">Fremder Auftrag für eigenen Account</span></th></tr>
		</table></div>';
		TemplateFooter();
	}
	
	function TemplateSitterHistory() {
		global $content, $scripturl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">';
		//Sitter für User erledigt
		echo '<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th colspan="4">Was andere bei dir gemacht haben</th></tr>';
		foreach($content['ownlog'] as $line) {
			echo '
			<tr>
				<td style="font-size:smaller;width:120px;">', $line['time'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['user'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['type'], '</td>
				<td style="font-size:smaller;">', $line['text'], '</td>
			</tr>';
		}
		echo '</table><br />';

		//User für andere erledigt
		echo '<table width="99%" cellpadding="0" cellspacing="0" border="0">';
		echo '<tr><th colspan="4">Was du bei anderen gemacht hast</th></tr>';
		foreach($content['otherlog'] as $line) {
			echo '
			<tr>
				<td style="font-size:smaller;width:120px;">', $line['time'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['igmAccount'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['type'], '</td>
				<td style="font-size:smaller;">', $line['text'], '</td>
			</tr>';
		}
		echo '</table>';
		
		echo '</div>';
		TemplateFooter();
	}
	
	function TemplateSitterLogin() {
		global $scripturl, $content, $spiel;
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="StonedSheep - IWDB" />
	<title>', $spiel == 'iw' ? 'IW' : 'CW', ' - ', $content['accName'], ' - StonedSheep-DB</title>
</head>
';
		
		if($content['loginWarning'])
			echo '
			<script type="text/javascript"><!-- // --><![CDATA[
				alert("Achtung, in diesem Account hat sich innerhalb der letzten Minuten schon ', $content['loginLastUser'], ' eingeloggt!");
			// ]]></script>';
		echo '
		<frameset rows="*,150">
			<frame src="', $content['loginUrl'], '" name="IW-Frame"/>
			<frameset cols="50%, 50%">
				<frame src="', $content['leftUtil'], '" name="LeftUtil" />
				<frame src="', $content['rightUtil'], '" name="RightUtil" />
			</frameset>
		</frameset>
		<noframes>
			<body>Tsjoa, du hast ein Problem - dein Browser unterst&uuml;tzt keine Frames.</body> 
		</noframes>';
		TemplateHtmlFooter();
	}
	
	function TemplateSitterList() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		
		echo '
			<div class="content">
				<table width="99%" cellpadding="0" cellspacing="0" border="0">
					<tr><th>Igmname</th><th title="Zeitpunkt, wann die erste Bauschleife/Forschungsschleife ausläuft">Bau/Forschung bis', HelpLink('sitter_bauschleifen_auslauf'), '</th><th title="Nächste angreifende Flotte / Scan">nächste  feindl. Flotte</th><th>Login</th></tr>';
		foreach($content['list'] as $item) {
			echo '<tr class="', $item['actuality'], '"><td>', $item['igmName'], '<br /><i style="font-size:smaller;">',$item['accountTyp'] , '@', $item['squad'], '</i>',
				$item['hasIkea'] ? ', <i style="font-size:smaller;">Ikea</i>' : '',
				$item['hasMdP'] ? ', <i style="font-size:smaller;">MdP</i>' : '',
			 '</td><td>', $item['bauEnde'], '</td><td>', $item['angriffAnkunft'], '</td><td><a href="', $scripturl, '/index.php?action=sitter_login&amp;from=sitter_list&amp;id=', $item['ID'], '">[Jetzt einloggen]</a></td></tr>';
		}
		echo '
				<tr><th colspan="4">Die Farben zeigen, wann sich zuletzt jemand eingeloggt hat:';
		foreach($content['time_stages'] as $k => $t)
			echo '&nbsp;<span class="act_',$k,'">', $t, '</span>';
			echo '<span class="act_5">+</span></th></tr>
				</table>
			</div>';
		
		TemplateFooter();
	}

	function TemplateSitterUtilLinks() {
		global $content, $scripturl;
		echo '
			<div class="sitterutil_links" style="text-align: ', $content['position'] ,';">
				', $content['hasExitLink'] ? '<a target="_top" href="'.$content['exitLink'].'">Zur&uuml;ck</a> --' : ' <span title="Wie lange der Account nicht mehr gesittet wurde" class="'.$content['nextLoginColor'].'" style="left:0px; width:30px; position:absolute;">&nbsp;</span>', '
				<a href="'.$scripturl.'/index.php?action=sitterutil_job'.$content['params'].'">Sitteraufträge</a> --
				<a href="'.$scripturl.'/index.php?action=sitterutil_newscan'.$content['params'].'">Scans einlesen</a> --
				<a href="'.$scripturl.'/index.php?action=sitterutil_trade'.$content['params'].'">Handel</a> -- 
				<a href="'.$scripturl.'/index.php?action=sitterutil_log'.$content['params'].'">Log</a> --
				<a href="'.$scripturl.'/index.php?action=sitterutil_ress'.$content['params'].'">Ress</a>
				', !$content['hasExitLink'] 
					? ' -- <a title="In den nächsten Account mit Leerlauf einloggen" target="_top" href="'.$content['idleLoginLink'].'">LeerlfAcc</a> -- <a title="In den Account einloggen, der am längsten nicht mehr gesittet wurde" target="_top" href="'.$content['nextLoginLink'].'">NxtAcc</a>' 
					: ' <span title="Wie lange der Account nicht mehr gesittet wurde" class="'.$content['nextLoginColor'].'" style="left:480px; right:0px; position:absolute;text-align:right;">&nbsp;<span title="'.$content['accountInfo']['typeDesc'].'">'.$content['accountInfo']['type'].'</span>'.($content['accountInfo']['ikea'] ? '&nbsp;<span title="Ikea-Account">I</span>':'').($content['accountInfo']['mdp'] ? '&nbsp;<span title="Meister der Peitschen-Account">M</span>':'').'</span>',
				 '
			</div>';
	}
	
	function TemplateSitterUtilJobView() {
		global $content;
		
		TemplateHtmlHeader();
		echo '<body><div class="sitterutil_box">';
		if(isset($content['msg'])) {
			echo '<div class="simp">', $content['msg'], '</div>';
		}
		if($content['hasjob']) {
			echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			function checkSubmit() {
				var bs = getElById("bauschleife");
				if(bs) {
					if(bs.value == "") {
						alert("Bauschleife nicht angegeben!");
						return false;
					}
				}
				return confirm("Sitterauftrag wirklich erledigt?");
			}
		// ]]></script>
		<h2>', $content['longType'], '</h2><form action="', $content['formAction'], '" method="post"><table border="1" align="center" width="100%">
			<tr align="left"><th>Zeit:</th><td>', $content['time'], '</td></tr>
			<tr align="left"><th>Planet:</th><td>[', $content['coords'], '] ', $content['planiName'], '</td></tr>
			<tr align="left"><th>Auftrag:</th><td><b>', $content['longType'], '</b><br />', $content['text'], '</td></tr>';
			if($content['hasFollowUp']) {
				echo '<tr><th>Bauschleife</th><td><textarea name="bauschleife" id="bauschleife"></textarea></td></tr>';
			}
			echo '
				<tr><td colspan="2" align="center">
					<input type="submit" name="done" value="Erledigt"  onclick="return checkSubmit();" /> -- 
					<input type="submit" name="move" value="Verschieben" />
				</td></tr>
			</table></form>';
		} else {
			echo '<h2 style="vertical-align:middle;">Kein Sitterauftrag!</h2>';
		}
		echo '</div>';
		TemplateSitterUtilLinks();
		echo '</body>';
		TemplateHtmlFooter();
	}
	
	function TemplateSitterUtilJobMove() {
		global $content;
		
		TemplateHtmlHeader();
		echo '
	<body><div class="sitterutil_box"><form action="', $content['submitLink'], '" method="post">
		<table border="1" align="center">
			<tr><th>Zeit</th><td><input type="text" name="zeit1" value="', $content['zeit1'], '" /></td></tr>
			<tr><th>oder Bauschleife</th><td><textarea name="bauschleife" cols="36" rows="1"></textarea></td></tr>
			<tr><th>Kommentar</th><td><textarea name="kommentar" cols="36" rows="1"></textarea></td></tr>
			<tr><td colspan="2" align="center"><input type="submit" name="abs" value="Verschieben!" /> <button type="button" onclick="window.location.href = \'', $content['backLink'], '\';">Zurück</button></td></tr>
		</table>
	<input type="hidden" name="move" value="1" />
	', ReqID(), '
	</form></div></body>';
		TemplateHtmlFooter();
	}
	
	function TemplateSitterUtilNewscan() {
		global $content, $scripturl;
		TemplateHtmlHeader();
		
		echo '
<body>
	<script type="text/javascript"><!-- // --><![CDATA[
		function FastPasteSubmit() {
			var frm = getElById("newscan");
			frm.target="_parent";
			frm.action="', $content['fastPasteTarget'],'";
			var btn = getElById("newscan_submit");
			frm.submit();
		}
		function IdlePasteSubmit() {
			var frm = getElById("newscan");
			frm.target="_parent";
			frm.action="', $content['idlePasteTarget'],'";
			var btn = getElById("newscan_submit");
			frm.submit();
		}
	// ]]></script>
	<div class="sitterutil_box">
		', !empty($content['submsg']) ? '' : '<h2>Neuer Bericht</h2>', 
		!empty($content['msg']) ? '<div class="imp">'.$content['msg'].'</div>' : '', '
		<div><form id="newscan" method="post" action="', $scripturl, '/index.php?action=sitterutil_newscan', $content['params'], '">
			<textarea name="scans" rows="4" cols="60">', $content['scans'], '</textarea><br/>
			<input type="hidden" name="uid" value="', $content['uid'], '" />
			', ReqID(), '
			<input type="hidden" name="abs" value="Einlesen" />
			<input type="submit" value="Einlesen" id="newscan_submit" />
			<button type="button" onclick="FastPasteSubmit();" title="Den Scan einlesen und danach direkt weiter zu dem am längsten nicht gesitteten Account">Einl. & Nächster</button>
			<button type="button" onclick="IdlePasteSubmit();" title="Den Scan einlesen und danach direkt weiter zum nächsten Account mit Leerlauf!">Einl. & N. + Leerlauf</button>
		</form></div>
		', !empty($content['submsg']) ? '<div class="simp">'.$content['submsg'].'</div>' : '', '
	</div>';
		TemplateSitterUtilLinks();
		echo '
</body>';
		
		TemplateHtmlFooter();
	}
	
	function TemplateSitterUtilTrade() {
		global $content;
		TemplateHtmlHeader();
		echo '
<body>
	<div class="sitterutil_box">
		<h2>Handel</h2>';
		if($content['hasReq']) {
			echo '
		<form action="', $content['submitUrl'], '" method="post">
		<table border="1" width="100%">
			<tr><th>Zeit</th><td>', $content['req']['time'], '</td></tr>
			<tr><th>Ziel</th><td>', $content['req']['ziel'], ' (bei ', $content['req']['user'], ')</td></tr>
			<tr><th>Priorität</th><td>', $content['req']['priority'], '</td></tr>
			<tr><th>Bedarf</th><td>(', $content['req']['soll'], '-', $content['req']['ist'], '=)', $content['req']['fehl'], 'x ', $content['req']['nameLong'], '</td></tr>
			<tr><th>Kommentar</th><td>', $content['req']['comment'], '</td></tr>
			<tr><td colspan="2"><input value="Done" name="fullDone" type="submit" /> -- <input type="text" name="cnt" size="6" /><input type="submit" value="Teilw." name="partDone" /> -- <input type="submit" name="ignore" value="Ignorieren" /></td></tr>
		</table>
		<input type="hidden" name="rid" value="', $content['req']['id'],'" />
		', ReqID(), '
		</form>';
		} else { 
			echo 'Kein weiterer Bedarf vorhanden!';
		}
		echo '
	</div>';
		TemplateSitterUtilLinks();
		echo '
</body>';
		TemplateHtmlFooter();
	}

	function TemplateSitterUtilLog() {
		global $content;
		TemplateHtmlHeader();
		echo '
<body>
	<div class="sitterutil_box">
		<h2>Sitterlog</h2>
		<table width="99%" cellpadding="0" cellspacing="0" border="1">';
		foreach($content['log'] as $line) {
			echo '
			<tr>
				<td style="width:120px;">', $line['time'], '</td>
				<td style="width:80px;">', $line['user'], '</td>',
				$line['type'] == 'L' ? '<td style="width:15px;" title="Login">L</td>' : '<td style="width:15px;" title="Auftrag">A</td>',
				'<td align="left">', $line['text'], '</td>
			</tr>';
		}
		echo '</table>
	</div>';
		TemplateSitterUtilLinks();
		echo '
</body>';
		TemplateHtmlFooter();
	}	

	function TemplateSitterUtilRess() {
		global $content;
		TemplateHtmlHeader();
		echo '
<body>
	<div class="sitterutil_box">
		<h2>Ressübersicht</h2>
		<form action="', $content['submitUrl'], '" method="post">
			<select name="ress">';
		foreach($content['ress'] as $r) {
			echo '
				<option value="', $r['value'], '"', $r['selected'] ? ' selected="selected"' : '', '>', $r['name'], '</option>';
		}
		echo '
			</select>bei <select name="uid">';
		foreach($content['users'] as $user) {
			echo '
				<option value="', $user['id'], '"', $user['selected'] ? ' selected="selected"' : '', '>', $user['name'], '</option>';
		}
		echo '
			</select>
			<input type="submit" name="submit" value="Anz." />
		</form>
		<table width="99%" cellpadding="0" cellspacing="0" border="1"><tr align="center">';
		echo '<th>&nbsp;</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['name'], '<br />(', $line['coords'], ')</td>';
		}
		echo '</tr><tr>';
		echo '<th title="rumfliegende Ress">R</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['ress'], '</td>';
		}
		echo '</tr><tr>';
		echo '<th title="Produktion / h">P</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['prod'], '</td>';
		}
		echo '</tr><tr>';
		echo '<th title="Lagerplatz/-inhalt h&auml;lt noch..">H</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['haelt'], '</td>';
		}
		echo '</tr><tr>';
		echo '<th title="Lagergr&ouml;&szlig;e">L</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['lager'], '</td>';
		}
		echo '</tr>
		</table>
	</div>';
		TemplateSitterUtilLinks();
		echo '
</body>';
		TemplateHtmlFooter();
	}

	function TemplateFeindlFlottenUebersicht() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '	<script type="text/javascript"><!-- // --><![CDATA[
		var UserRows = new Object();';
		foreach($content['users'] as $user) {
			echo '
			UserRows[', $user['uid'], '] = new Array(';
			foreach($user['planis'] as $p) {
				echo $p['ID'], ', ';
			}
			echo '0);';
		}
		echo '
		function toggleUserRows(uid) {
			var rows = UserRows[uid];
			for(var i = 0; i < rows.length; ++i) {
				var el = rows[i];
				if(el != 0) {
					toggleVisibility(getElById("p_"+el));
				}
			}
		}
	// ]]></script>
	<div class="content"><h2>Übersicht feindliche Flotten</h2>
		<table><tr><th>&nbsp;</th><th colspan="3">Spieler</th><th>Ankunft</th></tr>';
		foreach($content['users'] as $user) {
			echo '
			<tr class="danger_', $user['gefahrenLevel'], '"><td><a href="javascript:toggleUserRows(', $user['uid'], ');">+-</a></td><td colspan="3"><a href="',$user['loginLink'], '">', $user['name'], '</a></td><td>', $user['ersteAnkunft'], '</td></tr>';
			foreach($user['planis'] as $plani) {
				echo '
			<tr style="display:none;" id="p_', $plani['ID'], '" class="danger_', $plani['gefahrenLevel'], '">
				<td>&nbsp;</td>
				<td>(', $plani['startkoords'],')', $plani['startname'], '<br /><i>[', $plani['startally'], ']', $plani['startowner'], '</i></td>
				<td>(', $plani['zielkoords'],')', $plani['zielname'], '<br /><i>[', $plani['zielally'], ']', $plani['zielowner'], '</i></td>
				<td>', $plani['bewegung'], '</td>
				<td>', $plani['ankunft'], '</td>
			</tr>';
			}
		}
		echo '<tr><th colspan="5">Farbenlegende: <span class="danger_1">Angriff INC</span></th></tr></table></div>';
		TemplateFooter();
	}

	function TemplateSitterOwn() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
			<h2>Eigene Sitteraufträge</h2>
			<table width="99%" cellpadding="0" cellspacing="0" border="0"><tr>';
		foreach($content['pages'] as $page) {
			echo '
				<th><a href="', $page['link'], '">', $page['desc'], '</a></th>';
		}
		echo '
			</tr></table><br />
			<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th style="width:120px;">Zeit</th><th>Bei</th><th>Koordinaten</th><th>Auftrag</th><th style="width:50px;"></th></tr>';
		if(isset($content['msg'])) {
			echo '<tr><td colspan="5" class="simp">', $content['msg'], '</td></tr>';
		}
			foreach ($content['jobs'] as $line) {
			echo '
				<tr class="sitterjob_', $line['ownershipState'], '">
					<td>', $line['time'], '</td>
					<td>', $line['igmName'], '<br /><i style="fonz-size:smaller;">(', $line['userName'], ')</i></td>
					<td>[', $line['coords'], '] ', $line['planiName'], '</td>
					<td><b>', $line['typeLong'], '</b><br />', $line['text'], '</td>
					<td align="center"><a href="', $line['editLink'], '">Edit</a>', $line['hasAppendLink'] ? '<br /><a href="'.$line['appendLink'].'">Anhängen</a>' : '', '<br /><a href="', $line['delLink'], '">Del</a></td>
				</tr>';
		}
		echo '</table></div>';
		TemplateFooter();
	}
?>
