<?php

	function TemplateIndex() {
		global $content, $user, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
//		echo '<div class="content"><table border="0"><tr><th>Ding</th><th>Status</th><th>fertig zu</th></tr>';
//		foreach($content['projektuebersicht'] as $line) {
//			echo '<tr><td>', $line[0], '</td><td>', $line[1], '</td><td>',  $line[2], '%</td>';
//		}
//		echo '</table></div>';
		echo '<div class="content">';
		foreach($content['problems'] as $problem) {
			if(!empty($problem['link']))
				echo '<br /><a href="', $problem['link'], '"><span style="font-size: large;" class="', $problem['class'], '">', $problem['text'], '</span></a>';
			else
				echo '<br /><span class="', $problem['class'], '">', $problem['text'], '</span>';
		}
		echo GetText2('welcomepage');
		echo '</div>';
		TemplateFooter();
	}
	
	function TemplateNewscanEx() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
			<h2>Neuen Bericht einlesen', HelpLink('newscan'), '</h2>
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
		</form></div>';
		TemplateFooter();
		
	}
	
	function TemplateUnknownAction() {
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content"><h2>Unbekannte Action!</h2></div>';
		TemplateFooter();
	}

	function TemplateUserSettingsEx() {
		global $content;
		
		TemplateHeader();
		TemplateMenu();
		
		echo '<div class="content">
		<form method="POST" action="', $content['submiturl'], '">
		<input type="hidden" name="ID" value="',$content['settings']['id']['data'],'" />
		<table>
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
					echo '<td><input type="checkbox" name="', $name, '" value="1" ', $mod['data'] ? 'checked="checked"' : '' ,' /></td>';
					break;
				case 'isAdmin':
					echo '<td><input type="checkbox" name="', $name, '" value="1" ', $mod['data']['value'] ? 'checked="checked"' : '' , $mod['data']['editable'] ? '' : 'disabled="disabled"', ' /></td>';
					break;
				case 'sitterskin':
				case 'accounttyp':
					echo '<td><select name="', $name, '">';
					foreach($mod['data'] as $val => $desc) {
						echo '<option value="', $val, '" , ',$desc['selected'] ? 'selected="selected" ' : '','>', $desc['text'], '</option>';
					}
					echo '</select></td>';
					break;
				case 'tsdTrennZeichen':
				case 'Komma':
					echo '<td><input type="text" size="3" name="', $name, '" value="', $mod['data'], '" /></td>';
					break;
				default:
					echo $name, ' => ', var_dump($mod), '<br />';
			}
			echo '</tr>';
			if($name == 'currentPW')
				echo '<tr><td colspan="2"><input name="submit" value="Absenden" type="submit" /></td></tr></table><br /><table><tr><th colspan="2">Ingame-Einstellungen</th></tr>';
		}
			
		echo'<tr><td colspan="2"><input name="submit" value="Absenden" type="submit" /></td></tr></table></form>
		<br /><table><tr><th colspan="2">IRC-Autologinmasken</th></tr>';
		foreach($content['ircAutoLogin'] as $line) {
			echo '<tr><td>', $line['mask'], '</td><td><a href="', $line['editLink'], '">Edit</a> <a href="', $line['delLink'], '">Del</a></td></tr>';
		}
		echo '
			<tr><td colspan="2"><a href="', $content['newIrcAutoLoginLink'], '">neue Maske</a></td></tr> 
		</table></div>';
		
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
		echo '<div class="content"><h2>IRC-Autologin-Maske ', $content['edit'] ? 'bearbeiten' : 'erstellen', '</h2>
	<form action="', $content['submitUrl'], '" method="post">
	<input type="hidden" name="id" value="', $content['id'], '" />
	<table>
		<tr><th>Maske</th></tr>
		', isset($content['msg']) ? '<tr><td class="imp">'.$content['msg'].'</td></tr>' : '', '
		<tr><td><input type="text" size="64" maxlength="64" name="mask" value="', $content['mask'], '" /></td></tr>
		<tr><td><input type="submit" name="submit" value="Absenden" /></td></tr>
	</table>
	</form>
</div>';
		TemplateFooter();
	}
	function TemplateBugs() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">', $content['text'], '</div>';
		TemplateFooter();
	}
	
	function TemplateHighscore() {
		global $content, $scripturl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">';
		
		$i = 0;
		foreach($content['hs'] as $hs) {
			switch ($i++) {
				case 0:
				case 3:
				case 6:
				case 9:
				case 12:
					echo '<table style="float:left; min-width:150px; margin-right:5px;">';
					break;
				case 1:
				case 4:
				case 7:
				case 10:
				case 13:
					//if($i == 11 || $i == 5)
					//	echo '<table style="margin-right: 5px; min-width:150px;">';
					//else
						echo '<table style="float:left; margin-right: 5px; min-width:150px;">';
					break;
				case 2:
				case 5:
				case 8:
				case 11:
					echo '<table style="min-width:150px;">';
					break;
			}
			echo '<tr><th colspan="2">', $hs['title'], '</th></tr>';
			foreach($hs['data'] as $line) {
				echo '<tr><td>', $line['name'], '</td><td>', $line['value'], '</td></tr>';
			}
			echo '</table>';
			if($i == 3 || $i == 6 || $i == 9) {
				echo '<br />';
			}
		}
		
		echo '<br /><form method="get" action="', $scripturl, '"><div>Top <input type="hidden" name="action" value="hs" /><input type="text" name="cnt" value="', $content['cnt'], '" size="5" /><input type="submit" value="Anzeigen" /></div></form></div>';
		TemplateFooter();
	}
?>