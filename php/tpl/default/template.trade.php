<?php
	if(!defined('dddfd'))
		exit();
	function TemplateTradeList() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '
<div class="content"><h2>Handelsübersicht</h2>
<form action="', $content['submitUrl'], '" method="post">
', ReqID(), '
<table>
	<tr><th>Spieler</th><th>Zeit</th><th>Koordinaten</th><th>Ressource</th><th>Prio</th><th>Soll</th><th>Ist</th><th>Fehlend</th><th>Kommentar</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr>';
		foreach($content['reqs'] as $req) {
			echo '<tr><td>', $req['user'], '</td><td>', $req['zeit'], '</td><td>', $req['coords'], '</td><td>', $req['nameLong'], '</td>
					<td>', $req['priority'], '</td><td>', $req['soll'], '</td><td>', $req['ist'], '</td><td>', $req['diff'], '</td><td>', $req['comment'], '</td><td>', $req['ignored'] , '</td>
					<td><input type="checkbox" name="id[]" value="',$req['id'],'" /></td>
					<td><input type="text" name="anz[', $req['id'], ']" size="5" /></tr>';
		}
		echo '
<tr><td colspan="12"><select name="todo"><option value="done">Erledigt</option><option value="ignore">Ignorieren</option><option value="unignore">Nicht mehr ignorieren</option><option value="delete">Löschen</value></select>&nbsp;<input type="submit" name="update" /></td></tr>
</table></form><br />
<form action="', $content['submitUrl'], '" method="post">
', ReqID(), '
	<table>
		<tr><th colspan="2">Bedarf anmelden</th></tr>
		<tr><td>Ressource</td><td>
			<select name="ressource">';
			foreach($content['ress'] as $short => $long)
				echo '<option value="', $short, '">', $long, '</option>';
			echo '</select>
		</td></tr>
		<tr><td>Priorität</td><td>
			<select name="priority">';
			foreach($content['prioritys'] as $txt => $prio)
				echo '<option value="', $prio, '"', $prio == 0 ? ' selected="selected"' : '', '>', $prio, ' - ', $txt, '</option>';
			echo '</select>
		</td></tr>
		<tr><td>Account</td><td><select name="account" id="account">';
		foreach($content['accounts'] as $acc) {
			echo '<option value="',$acc['val'],'" ', $acc['sel'] ? 'selected="selected"' : '', '>', $acc['text'], '</option>';
		}
		echo '</select></td></tr>
		<tr><td>Koords</td><td><input type="text" name="coords" value="" id="coords" /><select name="planiselect" id="planiselect" onchange="if(this.selectedIndex != 0) getElById(\'coords\').value=this.value;" onkeyup="if(this.selectedIndex != 0) getElById(\'coords\').value=this.value;">';
		foreach($content['planis'] as $plani) 
			echo '<option value="', $plani['text'], '">', $plani['text'], '</option>';
		echo '</select></td></tr>
		<tr><td>Anzahl</td><td><input type="text" name="anz" value="" /></td></tr>
		<tr><td>Kommentar</td><td><input type="text" name="comment" value="" /></td></tr>
		<tr><td colspan="2"><input type="submit" name="new" value="Neu bestellen" /></td></tr>
	</table>
</form><br />
<table>
	<tr><th colspan="42">History</th></tr>';
		foreach($content['history'] as $hline) {
			echo '<tr><td>', $hline['time'], '</td><td>', $hline['type'], '</td><td>', $hline['sender'], '</td><td>', $hline['receiver'], '</td><td>', $hline['dest'], '</td><td>', $hline['ress'], '</td><td>', $hline['diff'], '</td></tr>';
		}
echo '
</table>
<script type="text/javascript"><!-- // --><![CDATA[
function UpdatePlanet() {
	var accSel = getElById("account");
	var igmid = accSel.options[accSel.selectedIndex].value;
	scriptRequest("sitter_planis", "igmid="+igmid, UpdatePlanetCallback);
}
function UpdatePlanetCallback(req) {
	FillSelect("planiselect", req.responseXML, false);
}
OnSelectChanged("account", UpdatePlanet);
// ]]></script>
</div>';
		TemplateFooter();
	}
?>
