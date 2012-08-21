<?php
	if (!defined("dddfd"))
		die("Hacking attempt");
	
	function TemplateSitterEdit() {
		global $content;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
	<h2>', $content['heading'], '</h2>
	<form action="', $content['submitAction'], '" method="post">
	<table>
				<tr>';
		foreach($content['pages'] as $page) {
			echo '<th', $page['active'] ? ' style="font-style: italic;"' : '', '><a href="', $page['link'], '">', $page['desc'], '</a></th>';
		}
		echo '</tr>
	</table><br />';
		if(!empty($content['errors'])) {
			echo '<div class="imp">';
			foreach($content['errors'] as $err) {
				echo $err.'<br />';
			}
			echo '</div>';
		}
		echo '<table>
		<tr><th>', $content['subHeading'], '</th><th>&nbsp;</th></tr>';
		foreach($content['mods'] as $n => $mod) {
			if(!$mod['hidden']) {
				echo '
		<tr><td>', $mod['name'], '<br />', !empty($mod['desc']) ? '<i>'.$mod['desc'].'</i>' : '', '</td><td>';
				switch($n) {
					case 'notes':
					case 'bauschleife':
						echo '<textarea name="', $n, '" rows="4" cols="30"', isset($content['readonly_'.$n]) ? ' readonly="readonly"' : '', '>', $content[$n], '</textarea>';
						break;
					case 'zeit':
					case 'anzahl':
						echo '<input type="text" name="', $n, '" value="', $content[$n], '"', isset($content['readonly_'.$n]) ? ' readonly="readonly"' : '', ' />';
						break;
					case 'account':
					case 'planet':
					case 'forschung':
					case 'schiff':
						echo '<select name="', $n, '" id="', $n, '">';
						foreach($content[$n] as $acc) {
							echo '<option value="', $acc['id'], '"', $acc['selected'] ? ' selected="selected"' : '', '>', $acc['name'], '</option>';
						}
						echo '</select>';
						break;
					case 'use_bauschleife':
						echo '<input type="checkbox" name="use_bauschleife" value="1"', $content['use_bauschleife'] ? ' checked="checked"' : '', ' />';
						break;
					case 'gebaeude':
						echo '<select name="gebaeude" id="gebaeude">';
						foreach($content['gebaeude'] as $geb) {
							echo '<option value="', $geb['id'], '"', $geb['selected'] ? ' selected="selected"' : '', '>', $geb['name'], '</option>';
						}
						echo '</select>&nbsp;<select name="stufe" id="stufen">';
						foreach($content['stufe'] as $s) {
							echo '<option value="', $s['id'], '"', $s['selected'] ? ' selected="selected"' : '', '>', $s['name'], '</option>';
						}
						echo '</select>';
						break;
					case 'angehaengtAn':
						echo '<input type="hidden" name="angehaengtAn" value="', $content['angehaengtAn'], '" />', $content['angehaengtAn'] != 0 ? '<span class="imp">Sitterauftrag mit der Nummer '.$content['angehaengtAn'].'</span>' : '-';
						break;
				}
				echo '</td></tr>';
			}
		}
		echo '
		<tr><td colspan="2"><button onclick="window.location=\'',$content['backLink'],'\';return false;" >Zurück</button><div style="float:right;"><input type="submit" name="submit" value="Absenden" /></div></td></tr>
	</table>';
		foreach($content['mods'] as $n => $mod) {
			if($mod['hidden']) {
				echo '
		<input type="hidden" name="', $mod['name'], '" value="', $content[$mod['name']], '" />';
			}
		}
		echo '
	</form>
</div><script type="text/javascript"><!-- // --><![CDATA[
', isset($content['mods']['planet']) ? '
	function UpdatePlanet() {
		var accSel = getElById("account");
		var igmid = accSel.options[accSel.selectedIndex].value;
		scriptRequest("sitter_planis", "igmid="+igmid, UpdatePlanetCallback);
	}
	function UpdatePlanetCallback(req) {
		FillSelect("planet", req.responseXML);
		'.(isset($content['mods']['gebaeude']) ? 'UpdateGebs();' : '').'
		'.(isset($content['mods']['forschung']) ? 'UpdateForschungen();' : '').'
	}
	OnSelectChanged("account", UpdatePlanet);' : '', '
', isset($content['mods']['gebaeude']) ? '
	function UpdateGebs() {
		var planiSel = getElById("planet");
		var planid = planiSel.options[planiSel.selectedIndex].value;
		var accSel = getElById("account");
		var igmid = accSel.options[accSel.selectedIndex].value;
		scriptRequest("sitter_gebs", "igmid="+igmid+"&planid="+planid, UpdateGebsCallback);
	}
	function UpdateGebsCallback(req) {
		FillSelect("gebaeude", req.responseXML);
		UpdateStufen();
	}
	function UpdateStufen() {
		var planiSel = getElById("planet");
		var planid = planiSel.options[planiSel.selectedIndex].value;
		var gebSel = getElById("gebaeude");
		var gebid = gebSel.options[gebSel.selectedIndex].value;
		scriptRequest("sitter_stufen", "planid="+planid+"&itemid="+gebid, UpdateStufenCallback);
	}
	function UpdateStufenCallback(req) {
		FillSelect("stufen", req.responseXML);
	}
	OnSelectChanged("planet", UpdateGebs);
	OnSelectChanged("gebaeude", UpdateStufen);
' : '', '
', isset($content['mods']['forschung']) ? '
	function UpdateForschungen() {
		var accSel = getElById("account");
		var igmid = accSel.options[accSel.selectedIndex].value;
		scriptRequest("sitter_forschungen", "igmid="+igmid, UpdateForschungenCallback);
	}
	function UpdateForschungenCallback(req) {
		FillSelect("forschung", req.responseXML);
	}' : '', '
// ]]></script>';
		TemplateFooter();
	}
?>