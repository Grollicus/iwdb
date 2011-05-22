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
	
	function TemplateSettings() {
		global $scripturl, $user, $content;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
			<h2>Einstellungen</h2>
			<form action="', $scripturl, '/index.php?action=settings" method="post">
			<input type="hidden" name="ID" value="', $content['id'], '" />
			<input type="hidden" name="part" value="tool" />
			<table>
				<tr><th colspan="2">Tool-Account</th></tr>';

		if(isset($content['msg']))
			echo '<tr><td colspan="3"><span class="imp">'.$content['msg'].'</span></td></tr>';
		if(isset($content['gmsg']))
			echo '<tr><td colspan="3"><span class="simp">'.$content['gmsg'].'</span></td></tr>';

		if($user['isAdmin'])
			echo '<tr><td>Username</td><td><input type="text" name="userName" value="', EscapeO($content['settings']['userName']), '" /></td></tr>';
		else
			echo '<tr><td>Username</td><td>', EscapeO($content['settings']['userName']), '</td></tr>';
			
		echo '<tr><td>Neues PW</td><td><input type="password" name="newpw" value="" /></td></tr>';
		echo '<tr><td>Neues PW wdh.</td><td><input type="password" name="newpw2" value="" /></td></tr>';
		echo '<tr><td>angezeigter Name</td><td><input type="text" name="visibleName" value="', EscapeO($content['settings']['visibleName']), '" /></td></tr>';
		echo '<tr><td>E-Mail-Adresse<br /><i>Diese wird (bisher?) nicht genutzt</i></td><td><input type="text" name="email" value="', EscapeO($content['settings']['email']), '" /></td></tr>';
		echo '<tr><td>IP-Sicherheit<br /><i>Wenn aktiv, wird man ausgeloggt wenn die Session zu einer anderen IP-Adresse geh�rte</i></td><td><input type="checkbox" name="ipsecurity" ', $content['settings']['ipsecurity'] == 1 ? 'checked="checked"' : '', ' value="1" /></td></tr>';
		
		if($user['isAdmin'])
			echo '<tr><td>Admin<br /><i>Ist der Benutzer ein Allytool-Admin?</i></td><td><input type="checkbox" name="isAdmin" ', $content['settings']['isAdmin'] == 1 ? 'checked="checked"' : '', ' value="1" /></td></tr>';
		
		echo '<tr><td>IP-Sicherheit<br /><i>beim IceWars-Sitterlogin</i></td><td><input type="checkbox" name="sitteripchange" ', $content['settings']['sitteripchange'] == 1 ? ' checked="checked"' : '', ' value="1" /></td></tr>';
		echo '<tr><td>Sitterskin', HelpLink('settings_sitterskin'), '<br /><i>Der Skin, der beim Sitten anderer Accounts verwendet wird</i></td><td><select name="sitterskin">';
		foreach($content['sitterskins'] as $val => $skin) {
			echo '<option value="',$val,'"', $val==$content['settings']['sitterskin'] ? 'selected="selected"' : '', '>', $skin, '</option>';
		}
		echo '</select></td></tr>';
		if($content['pwcheck'])
			echo '<tr><td>Aktuelles PW:<br /><i>Zur Sicherheit muss bei &Auml;nderungen immer das aktuelle PW mit angegeben werden</i></td><td><input type="password" name="pwcheck" value="" /></td></tr>';
		echo '<tr><td colspan="2"><input type="submit" name="submit" value="Absenden" /></td></tr>
		</table></form><br /><br />
		<form action="', $scripturl, '/index.php?action=settings" method="post">
		<input type="hidden" name="ID" value="', $content['id'], '" />
		<input type="hidden" name="part" value="igm" />		
		<table><tr><th colspan="2">Ingame-Account</th></tr>';
		if(isset($content['igmgmsg']))
			echo '<tr><td colspan="3"><span class="simp">'.$content['igmgmsg'].'</span></td></tr>';
		if(isset($content['igmmsg']))
			echo '<tr><td colspan="3"><span class="imp">'.$content['igmmsg'].'</span></td></tr>';
		echo '
		<tr><td>Ingamename<br /><i>F&uuml;r den Sitterlogin</i></td><td><input type="text" name="igmname" value="', EscapeO($content['igm']['igmname']), '" /></td></tr>
		<tr><td>Sitterpw</td><td><input type="password" name="sitterpw" value="', $content['igm']['sitterpw'], '" /></td></tr>
		<tr><td>Realpw</td><td><input type="password" name="realpw" value="', $content['igm']['realpw'], '" /></td></tr>
		<tr><td>Accounttyp</td><td><select name="accounttyp">';
		foreach ($content['igm']['accounttyp'] as $k=>$v) {
			echo '<option value="', $k, '"', $v['selected'] ? ' selected="selected"' : '', '>',$v['desc'],'</option>';
		}
		echo '</select></td></tr>
		<tr><td>Squad</td><td><input type="text" name="squad" value="', EscapeO($content['igm']['squad']), '" /></td></tr>
		<tr><td>Lehrling von Ikea</td><td><input type="checkbox" name="ikea" value="1" ', $content['igm']['hasIkea'] ? 'checked="checked" ' : '', '/></td></tr>
		<tr><td>Meister der Peitschen</td><td><input type="checkbox" name="mdp" value="1" ', $content['igm']['hasMdP'] ? 'checked="checked" ' : '', '/></td></tr>
		<tr><td>Tausendertrennzeichen<br /><i>das Leerzeichen in 1 000 000</i><br /></td><td><input type="text" size="1" maxlength="1" name="tsdtrennz" value="', $content['igm']['tsdtrennz'], '" /></td></tr>
		<tr><td>Dezimaltrennzeichen<br /><i>Das Komma in 0,25</i><br /></td><td><input type="text" size="1" maxlength="1" name="komma" value="', $content['igm']['komma'], '" /></td></tr>		
		<tr><td colspan="2"><input type="submit" name="submit" value="Absenden" /></td></tr>
		
		</table></form><br /><br />
		<table><tr><th colspan="2">IRC-Autologinmasken</th></tr>';
		foreach($content['ircAutoLogin'] as $line) {
			echo '<tr><td>', $line['mask'], '</td><td><a href="', $line['editLink'], '">Edit</a> <a href="', $line['delLink'], '">Del</a></td></tr>';
		}
		echo '
			<tr><td colspan="2"><a href="', $content['newIrcAutoLoginLink'], '">neue Maske</a></td></tr> 
		</table></div>';
		
		echo '';
		
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
?>