<?php

	function TemplateIndex() {
		global $content, $user, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
//		echo '<div id="content"><table border="0"><tr><th>Ding</th><th>Status</th><th>fertig zu</th></tr>';
//		foreach($content['projektuebersicht'] as $line) {
//			echo '<tr><td>', $line[0], '</td><td>', $line[1], '</td><td>',  $line[2], '%</td>';
//		}
//		echo '</table></div>';
		foreach($content['problems'] as $problem) {
			if(!empty($problem['link']))
				echo '<br /><a href="', $problem['link'], '"><span style="font-size: large;" class="', $problem['class'], '">', $problem['text'], '</span></a>';
			else
				echo '<br /><span style="font-size: large;" class="', $problem['class'], '">', $problem['text'], '</span>';
		}
		echo GetText2('welcomepage');
		echo '<br /><br /><table class="tablesorter"><tr><th colspan="2">Neue Ereignisse</th></tr>';
		
		foreach($content['events'] as $evt) {
			echo '<tr><td style="width:150px;">', $evt['time'], '</td><td>', $evt['text'], '</td></tr>';
		}
		echo '</table>';
		TemplateFooter();
	}
	
	function TemplateNewscanEx() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		echo '<h2>Neuen Bericht einlesen', HelpLink('newscan'), '</h2>
			<form action="', $scripturl, '/index.php?action=newscanex" method="post" accept-charset="UTF-8">
			', ReqID() ,'
			<textarea name="scans" cols="70" rows="12">', $content['scans'], '</textarea><br />';
		
		if(!empty($content['msg']))
			echo '<div class="imp">', $content['msg'], '</div>';
		if(!empty($content['submsg']))
			echo '<div class="simp">', $content['submsg'], '</div>';
		
		echo 'Bericht eintragen für: <select name="uid">';
		foreach($content['users'] as $user) {
			 echo '<option value="', $user['ID'], '"', $user['selected'] ? 'selected="selected"' : '' , '>', $user['igmName'], ' </option>';
		}
		echo '
			</select><br />';
		echo '<input type="submit" name="abs" value="Einlesen" />
			<br />
			', $content['desc'], '
		</form>';
		TemplateFooter();
		
	}
	
	function TemplateUnknownAction() {
		
		TemplateHeader();
		TemplateMenu();
		echo '<h2>Unbekannte Action!</h2>';
		TemplateFooter();
	}

	function TemplateUserSettingsEx() {
		global $content, $user;
		
		TemplateHeader();
		TemplateMenu();
		
		echo '<form method="post" action="', $content['submiturl'], '">
		<input type="hidden" name="ID" value="',$content['settings']['id']['data'],'" />
		<table id="settings1"><thead>
			<tr><th colspan="2">Einstellungen</th></tr>';
		if(!empty($content['errors'])) {
			echo '<tr><td colspan="2"><span class="imp">';
			echo implode('<br />', $content['errors']);
			echo '</span></tr>';
		}
		if(!empty($content['msg'])) {
			echo '<tr><td colspan="2"><span class="simp">';
			echo $content['msg'];
			echo '</span></tr>';
		}
		echo '</thead><tbody>';
		foreach($content['settings'] as $name => $mod) {
			if($name == 'id') {
				continue;
			}
			echo '<tr><td>', $mod['name'], '<br /><span style="font-size:smaller;font-style:italic;">', $mod['desc'], '</span></td>';
			switch($name) {
				case 'username':
					echo '<td><input type="text" name="', $name, '" value="', $mod['data']['value'], '" ', $mod['data']['editable'] ? '' : 'disabled="disabled"' ,' /></td>';
					break;
				case 'pw':
				case 'pw2':
				case 'sitterpw':
				case 'realpw':
				case 'currentPW':
					echo '<td><input type="password" name="', $name, '" value="', $mod['data'], '" /></td>';
					break;
				case 'visibleName':
				case 'email':
				case 'igmname':
				case 'squad':
					echo '<td><input type="text" name="', $name, '" value="', $mod['data'], '" /></td>';
					break;
				case 'ipsec':
				case 'ikea':
				case 'mdp':
				case 'iwsa':
					echo '<td><input type="checkbox" name="', $name, '" value="1" ', $mod['data'] ? 'checked="checked"' : '' ,' /></td>';
					break;
				case 'isAdmin':
				case 'isRestricted':
					echo '<td><input type="checkbox" name="', $name, '" value="1" ', $mod['data']['value'] ? 'checked="checked"' : '' , $mod['data']['editable'] ? '' : 'disabled="disabled"', ' /></td>';
					break;
				case 'sitterskin':
				case 'accounttyp':
					echo '<td><select name="', $name, '">';
					foreach($mod['data'] as $val => $desc) {
						echo '<option value="', $val, '" ',$desc['selected'] ? 'selected="selected" ' : '','>', $desc['text'], '</option>';
					}
					echo '</select></td>';
					break;
				case 'tsdTrennZeichen':
				case 'Komma':
					echo '<td><input type="text" size="3" name="', $name, '" value="', $mod['data'], '" /></td>';
					break;
				case 'token':
					echo '<td>', $mod['data'], '</td>';
					break;
				default:
					echo $name, ' => ', var_dump($mod), '<br />';
			}
			echo '</tr>';
			if($name == 'currentPW')
				echo '<tr><td colspan="2"><input name="submit" value="Absenden" type="submit" /></td></tr></tbody></table><br /><table><thead><tr><th colspan="2">Ingame-Einstellungen</th></tr></thead><tbody>';
		}
			
		echo '<tr><td colspan="2"><input name="submit" value="Absenden" type="submit" /></td></tr></tbody></table></form>';
		if(!$user['isRestricted']) {
			echo '
		<br /><table cellpadding="0" cellspacing="0" border="0"><tr><th colspan="2">IRC-Autologinmasken</th></tr>';
		foreach($content['ircAutoLogin'] as $line) {
			echo '<tr><td>', $line['mask'], '</td><td><a href="', $line['editLink'], '">Edit</a> <a href="', $line['delLink'], '">Del</a></td></tr>';
		}
		echo '
			<tr><td colspan="2"><a href="', $content['newIrcAutoLoginLink'], '">neue Maske</a></td></tr> 
		</table>';
		}
		
		TemplateFooter();
	}
	
	function TemplateHelp() {
		global $content;
		
		TemplateHtmlHeader();
		
		echo '<body><div style="position:absolute;left:5px;right:5px;top:5px;bottom:5px;">', $content['text'], '</div></body>';
		
		TemplateHtmlFooter();
	}	

	function TemplateIrcMaskEdit() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '<h2>IRC-Autologin-Maske ', $content['edit'] ? 'bearbeiten' : 'erstellen', '</h2>
	<form action="', $content['submitUrl'], '" method="post">
	<input type="hidden" name="id" value="', $content['id'], '" />
	<table>
		<tr><th>Maske</th></tr>
		', isset($content['msg']) ? '<tr><td class="imp">'.$content['msg'].'</td></tr>' : '', '
		', isset($content['smsg']) ? '<tr><td class="simp">'.$content['smsg'].'</td></tr>' : '', '
		<tr><td><input type="text" size="64" maxlength="64" name="mask" value="', $content['mask'], '" /></td></tr>
		<tr><td><input type="submit" name="submit" value="Absenden" /></td></tr>
	</table>
	</form>';
		TemplateFooter();
	}
	function TemplateBugs() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo $content['text'];
		TemplateFooter();
	}
	
	function TemplateHighscore() {
		global $content, $scripturl;
		TemplateHeader();
		TemplateMenu();

		echo '<script type="text/javascript"><!-- // --><![CDATA[
	$(function() {
		$( ".column" ).sortable({
			connectWith: ".column"
		});
	
		$( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
			.find( ".portlet-header" )
				.addClass( "ui-widget-header ui-corner-all" )
				.prepend( "<span class=\"ui-icon ui-icon-minusthick\"></span>")
				.end()
			.find( ".portlet-content" );
	
		$( ".portlet-header" ).click(function() {
			$( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
			$( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
		});
	
		$( ".column" ).disableSelection();
	});
		// ]]></script><div><div><form method="get" action="', $scripturl, '">Top <input type="hidden" name="action" value="hs" /><input type="text" name="cnt" value="', $content['cnt'], '" size="5" /><input type="submit" value="Anzeigen" /></form></div>';
		
		$j = 0;
		for($i = 0; $i < 3; ++$i) {
			echo '<div class="column">';
			$max = count($content['hs'])*($i+1)/3;
			for(; $j < $max; ++$j) {
				$hs = $content['hs'][$j];
				echo '<div class="portlet">
					<div class="portlet-header">', $hs['title'], '</div>
					<div class="portlet-content">
						<table style="width:100%;">';
				foreach($hs['data'] as $line) {
					echo '<tr><td>', $line['name'], '</td><td>', $line['value'], '</td></tr>';
				}
				echo '	</table>
					</div>';
				
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
		TemplateFooter();
	}
	function TemplateKbFormat() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '<h2>KBs f&uuml;rs Forum formatieren</h2>
	<form method="POST" action="', $content['submitUrl'], '">
		', ReqID() ,'
		<textarea name="scans" cols="80" rows="10">', $content['scans'], '</textarea><br />
		<select name="target">';
foreach($content['target'] as $v => $t) {
	echo '<option ', $t['selected'] ? 'selected="selected"':'','  value="',$v,'">', $t['desc'], '</option>';
}
echo '</select>
		<input type="submit" name="abs" value="KBs formatieren!"/>
	</form>
<textarea readonly="readonly" cols="80" rows="10" onClick="this.select();">',$content['result'],'</textarea>';
		if(!empty($content['msg']))
			echo '<div class="imp">', $content['msg'], '</div>';
		if(!empty($content['submsg']))
			echo '<div class="simp">', $content['submsg'], '</div>';
		TemplateFooter();
	}

	function TemplateInactives() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '<h2>(Gebbau-)Inaktive</h2>
		<script type="text/javascript">
			$(document).ready(function() {
				$("#inactives_table").tablesorter();
			});
		</script>
		<table border="0" class="tablesorter" id="inactives_table">
		<thead><tr><th>Name</th><th>&nbsp;</th><th>Gebpts</th></tr></thead><tbody>';
		foreach($content['inactives'] as $line) {
			echo '<tr class="',$line['age'],'"><td><a href="', $line['link'], '">', $line['name'], '</a></td><td>', $line['span'], '</td><td>', $line['pts'], '</td></tr>';
		}
		echo '</tbody></table>';
		TemplateFooter();
	}
?>
