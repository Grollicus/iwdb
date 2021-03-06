<?php
	if(!defined('dddfd'))
		die();
		
	function TemplateSpeedlogView() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content"><table>';
		echo '<tr><th>Action</th><th>Sub</th><th>Script?</th><th>Aufrufe</th><th>Durchschn.-Zeit</th></tr>';
		foreach($content['logentries'] as $entry)
			echo '<tr><td>', $entry['action'], '</td><td>', $entry['sub'], '</td><td>', $entry['script'] == 1 ? '<font color="#0000FF">Script</font>' : 'Normal', '</td><td>',$entry['count'],  '</td><td>', $entry['time'], '&micro;s</td></tr>';
		echo '</table><a href="', $scripturl, '/?action=speedlog&amp;delete=1">Log leeren</a></div>';
		TemplateFooter();
	}
	
	function TemplateListErrors() {
		global $content, $scripturl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content"><table width="100%">';
		echo '<tr><th colspan="5">Aufgetretene und in der Datenbank geloggte Fehler (', $content['dberrcount'], ')&nbsp;&nbsp;&nbsp;<a href="', $scripturl, '/?action=errors&amp;sub=delall">clear</a></th></tr>';
		if(isset($content['msg']))
			echo '<tr><td colspan="5"><span class="simp">', $content['msg'], '</span></td></tr>';
		foreach($content['dberrorlines'] as $line) {
			echo '
				<tr>
					<td>', FormatDate($line['time']), '</td>
					<td>', $line['user'], '</td>
					<td>', $line['username'], '</td>
					<td>', $line['file'], ':', $line['line'], '</td>
					<td><a href="', $scripturl, '/?action=errors&amp;sub=del&amp;id=', $line['ID'], '">del</a></td>
				</tr>
				<tr><td colspan="5"><a href="javascript:toggleTableRow(getElById(\'err_st_',$line['ID'],'\'));toggleTableRow(getElById(\'err_req_',$line['ID'],'\'));">', $line['msg'], '</a></td></tr>
				<tr id="err_st_',$line['ID'],'" style="display:none;"><td colspan="5"><b>Stacktrace</b><br />', $line['stacktrace'], '</td></tr>
				<tr id="err_req_',$line['ID'],'" style="display:none;"><td colspan="5">', $line['request'], '</td></tr>';
		}
		echo '</table><br /><table width="100%">';
		echo '<tr><th colspan="4">Aufgetretene und in der Datei geloggte Fehler (', $content['fileerrcount'], ')&nbsp;&nbsp;&nbsp;<a href="', $scripturl, '/?action=errors&amp;sub=fdelall">clear</a></th></tr>';
		foreach($content['fileerrors'] as $err) {
			echo '<tr>
					<td>', $err['time'], '</td>
					<td>', $err['userid'], '</td>
					<td>', $err['file'], '</td>
					<td>', $err['line'], ')</td>
				</tr>
				<tr><td colspan="4">', $err['msg'], '</td></tr>
				<tr><td colspan="4">', $err['stacktrace'], '</td></tr>';
		}
		echo '</table></div>';
		TemplateFooter();
	}

	function TemplateUseradminList() {
		global $content;
		
		TemplateHeader();
		TemplateMenu();
		
		echo '<div class="content">
				<h2>Benutzerliste</h2>
				<form action="', $content['addurl'], '" method="post">
				<table width="100%">
					<tr><th>Benutzername</th><th>angezeigter Name</th><th>Ingamename</th><th>zuletzt aktiv</th><th>Admin</th><th>Gast</th><th>Hat ein PW</th><th>&nbsp;</th></tr>';
		foreach($content['users'] as $user) {
			echo '<tr><td>', $user['name'], ' (', $user['id'], ')</td><td>', $user['visibleName'], '</td>
				<td>', $user['igmName'], ' (', $user['igmid'], ')</td><td>', $user['lastactive'], '</td>
				<td>', $user['isAdmin'], '</td><td>', $user['isRestricted'], '</td><td>', $user['hasPW'], '</td><td><a href="', $user['editlink'], '">Edit</a> <a href="', $user['dellink'], '" onclick="return confirm(\'', $user['name'], ' wirklich löschen?\');">Del</a></td></tr>';
		}
		echo '
					<tr><td><input type="text" name="name" value="Neuer Benutzer" /></td><td colspan="7"><input type="submit" name="user_submit" value="Neuer Benutzer" /></td></tr>
					<tr><td><input type="text" name="guest" value="Neuer Gast" /></td><td colspan="7"><input type="submit" name="guest_submit" value="Neuer Gast" /></td></tr>
				</table>
				</form>
			</div>';
		
		TemplateFooter();
	}

	function TemplateTextsList() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		
		echo '<div class="content"><h2>Texte</h2><table width="99%"><tr><th>Name</th><th>Text</th><th>&nbsp;</th></tr>';
		foreach($content['texts'] as $text) {
			echo '<tr><td>', $text['Name'], '</td><td>', $text['Text'], '</td><td><a href="', $text['editlink'], '">Bearbeiten</a></td></tr>';
		}
		echo '</table></div>';
		
		TemplateFooter();
	}
	
	function TemplateTextsEdit() {
		global $content;
		
		TemplateHeader();
		TemplateMenu();
		echo '
		<div class="content"><form action="', $content['action'], '" method="post">

			<table width="99%">
				<tr><th>Vorschau</th></tr>
				<tr><td>', $content['Preview'], '</td></tr>
			</table><br />

			<table width="99%">
				<tr><th>Bearbeiten von ', $content['Name'], '</th></tr>
				<tr><td><textarea name="text" cols="150" rows="16">', $content['Text'], '</textarea></td></tr>
				<tr><td align="center"><input type="submit" name="submit" value="Absenden" /><input type="submit" name="preview" value="Vorschau" /></td></tr>
			</table>
		<input type="hidden" name="name" value="', $content['Name'], ' " />
		</form></div>';
		
		TemplateFooter();
	}

	function TemplateAdminUtils() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		
		echo '<div class="content"><h2>Admin-Utils</h2><ul>';
		foreach($content['utils'] as $util) {
			echo '<li><a href="', $util['link'], '">', $util['active'] ? '<i>'.$util['name'].'</i>' : $util['name'], '</a> ', $util['desc'], '</li>';
		}
		echo '</ul>';
		
		echo '<div class="simp">', $content['msg'], '</div>', $content['result'], '</div>';
		
		TemplateFooter();
	}
?>
