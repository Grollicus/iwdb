<?php
	function TemplateKbWarsView() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
				<h2>Kriege verwalten</h2>
				<table width="99%" cellpadding="0" cellspacing="0" border="0">
					<tr><th>Name</th><th>Start</th><th>Ende</th><th>Angreifer</th><th>Verteidiger</th><th>Aktiv</th><th>Del</th></tr>
					';
		foreach($content['wars'] as $war) {
			echo '	<tr>
						<td>', $war['name'], '</td>
						<td>', $war['active'], '</td>
						<td>', $war['start'], '</td>
						<td>', $war['end'], '</td>
						<td>', $war['attmember'], '</td>
						<td>', $war['defmember'], '</td>
					</tr>';
		}
		echo'
					<tr><td colspan="6"><a href="', $scripturl, '/?index.php?action=kb_wars&amp;sub=new">Neuen Krieg hinzuf&uuml;gen</a></td></tr>
				</table>
			 </div>';
		TemplateFooter();
	}
?>