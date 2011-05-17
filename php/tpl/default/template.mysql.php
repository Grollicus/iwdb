<?php
if(!defined('dddfd') || !$user['isAdmin'])
	exit();

function TemplateMySQLQueryForm() {
	global $content, $scripturl;
	
	TemplateHeader();
	TemplateMenu();
	echo '<div class="content">
			<form action="', $scripturl, '/index.php?action=mysql" method="post" name="Form1">
			<table align="center" border="0">
				<tr><th align="center" colspan="2">Ausf&uuml;hrer f&uuml;r Mysql-Befehle</th></tr>
				<tr><td>Server:</td><td><input name="address" type="text" value="', $content['server'],'" /></td></tr>
				<tr><td>User:</td><td><input name="username" type="text" value="', EscapeO($content['user']), '" /></td></tr>
				<tr><td>PW:</td><td><input name="password" type="password" value="', EscapeO($content['pw']), '" /></td></tr>
				<tr><td>DBName:</td><td><input name="DB" type="text" value="', $content['db'], '" /></td></tr>
				<tr><th align="center" colspan="2">Befehl</th></tr>
				<tr><td colspan="2" align="center"><textarea name="befehl" cols="40" rows="20">', EscapeO($content['cmd']), '</textarea></td></tr>
				<tr><td colspan="2">Tipp: <i>$pre</i> in Datenbankanfragen wird an dieser Stelle automatisch<br /> durch das aktuelle Tabellenprefix ersetzt! (Spart u.U. Tipparbeit)</td></tr>
				<tr><td align="center" colspan="2"><input type="submit" name="submit" value="Query" /></td></tr>
			</table></form>';
	echo '</div>';
	TemplateFooter();
}

function TemplateMySQLQueryResult() {
	global $content, $scripturl;
	
	TemplateHeader();
	TemplateMenu();
	echo '<div class="content">
			<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th align="center" colspan="', $content['cols'], '"></th></tr>';
	if(isset($content['msg'])) {
		echo '<tr><td class="imp">'.$content['msg'].'</td></tr>';
	} else {
		if($content['numrows'] > 0)
			echo '<tr><td colspan="', $content['cols'], '">Rows: ', $content['numrows'], '</td></tr>';
		if($content['affected'] > 0)
			echo '<tr><td colspan="', $content['cols'], '">Affected Rows: ', $content['affected'], '</td></tr>';
		echo '<tr><td colspan="', $content['cols'], '">&nbsp;</td></tr>';
		echo '<tr>';
		foreach($content['colnames'] as $name)
			echo '<th>', $name, '</th>';
		echo '</tr>';
		foreach($content['result'] as $row) {
			echo '<tr>';
			foreach($row as $field)
				echo '<td>', nl2br(EscapeO($field)), '</td>';
			echo '</tr>';
		}
	}
	echo '<tr>
			<td colspan="', $content['cols'], '">
				<form action="', $scripturl, '/index.php?action=mysql" method="post">
					<input type="hidden" name="address" value="', EscapeO($content['server']), '" />
					<input type="hidden" name="username" value="', EscapeO($content['user']), '" />
					<input type="hidden" name="password" value="', EscapeO($content['pw']), '" />
					<input type="hidden" name="DB" value="', EscapeO($content['db']), '" />
					<input type="hidden" name="befehl" value="', EscapeO($content['cmd']), '" />
					<input type="submit" name="submit" value="Neuer Befehl" />
				</form>
			</td>
		  </tr>';
	echo 	'</table>';
	echo '</div>';
	TemplateFooter();
}
?>
