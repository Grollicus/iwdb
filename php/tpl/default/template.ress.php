<?php
	function TemplateRessUserList() {
		global $content, $themeurl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
				<h2>Ressourcenproduktion nach Accounts</h2>
				Squad: <select onchange="filterTable(\'resstbl\', 0, this.value);" onkeyup="filterTable(\'resstbl\', 0, this.value);">';
		foreach($content['squads'] as $squad) {
			echo '<option value="', $squad, '">', $squad, '</option>';
		}
		echo '</select>
				<table id="resstbl"  cellpadding="0" cellspacing="0" border="0">
					<tr>
						<th>
							<a href="', $content['sortLinks'][0]['link'], '">Account', $content['sortLinks'][0]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
						</th>
						<th>
							<a href="', $content['sortLinks'][1]['link'], '">Eisen', $content['sortLinks'][1]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][2]['link'], '">Gelagert', $content['sortLinks'][2]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][3]['link'], '">Stahl', $content['sortLinks'][3]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][4]['link'], '">Gelagert', $content['sortLinks'][4]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png"  alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][5]['link'], '">VV4A', $content['sortLinks'][5]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][6]['link'], '">Gelagert', $content['sortLinks'][6]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][7]['link'], '">Chemie', $content['sortLinks'][7]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][8]['link'], '">Gelagert', $content['sortLinks'][8]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][9]['link'], '">Eis', $content['sortLinks'][9]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][10]['link'], '">Gelagert', $content['sortLinks'][10]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][11]['link'], '">Wasser', $content['sortLinks'][11]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][12]['link'], '">Gelagert', $content['sortLinks'][12]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][13]['link'], '">Energie', $content['sortLinks'][13]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][14]['link'], '">Gelagert', $content['sortLinks'][14]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
						<th>
							<a href="', $content['sortLinks'][15]['link'], '">FP', $content['sortLinks'][15]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a>
						</th>
						<th>
							<a href="', $content['sortLinks'][16]['link'], '">Credits', $content['sortLinks'][16]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a><br />
							<span class="sub"><a href="', $content['sortLinks'][17]['link'], '">Gelagert', $content['sortLinks'][17]['active'] ? '<img src="'.$themeurl.'/img/'.$content['order'].'.png" alt="sort" />' : '','</a></span>
						</th>
					</tr>';
		foreach($content['users'] as $user) {
			echo '
				<tr class="', $user['age'], '">
					<td>', $user['name'], '<br /><i style="font-size:smaller;">',$user['accountTyp'] , '@', $user['squad'], '</i></td>
					<td align="right">', $user['vFe'], '<br /><span class="sub">', $user['tFe'], ' ', $user['fe'], '</span></td>
					<td align="right">', $user['vSt'], '<br /><span class="sub">', $user['tSt'], ' ', $user['st'], '</span></td>
					<td align="right">', $user['vVv'], '<br /><span class="sub">', $user['tVv'], ' ', $user['vv'], '</span></td>
					<td align="right">', $user['vCh'], '<br /><span class="sub">', $user['tCh'], ' ', $user['ch'], '</span></td>
					<td align="right">', $user['vEi'], '<br /><span class="sub">', $user['tEi'], ' ', $user['ei'], '</span></td>
					<td align="right">', $user['vWa'], '<br /><span class="sub">', $user['tWa'], ' ', $user['wa'], '</span></td>
					<td align="right">', $user['vEn'], '<br /><span class="sub">', $user['tEn'], ' ', $user['en'], '</span></td>
					<td align="right">', $user['fp'], '</td>
					<td align="right">', $user['vCr'], '<br /><span class="sub">', $user['cr'], '</span></td>
				</tr>';
		}
		echo '<tr style="font-weight:bold;">
					<td>Gesamt</td>
					<td align="right">', $content['ges']['vFe'], '<br /><span class="sub">', $content['ges']['fe'], '</span></td>
					<td align="right">', $content['ges']['vSt'], '<br /><span class="sub">', $content['ges']['st'], '</span></td>
					<td align="right">', $content['ges']['vVv'], '<br /><span class="sub">', $content['ges']['vv'], '</span></td>
					<td align="right">', $content['ges']['vCh'], '<br /><span class="sub">', $content['ges']['ch'], '</span></td>
					<td align="right">', $content['ges']['vEi'], '<br /><span class="sub">', $content['ges']['ei'], '</span></td>
					<td align="right">', $content['ges']['vWa'], '<br /><span class="sub">', $content['ges']['wa'], '</span></td>
					<td align="right">', $content['ges']['vEn'], '<br /><span class="sub">', $content['ges']['en'], '</span></td>
					<td align="right">', $content['ges']['fp'], '<br /></td>
					<td align="right">', $content['ges']['vCr'], '<br /><span class="sub">', $content['ges']['cr'], '</span></td>
				</tr>
				<tr><th colspan="10">Farben geben das Alter der Daten an: ';
		foreach($content['color_stages'] as $stage => $time) {
			echo '&nbsp;<span class="act_', $stage, '">', $time, '</span>';
		}
		echo '<span class="act_5">+</span></th></tr>
				</table>
			</div>';
		Templatefooter();
	}
	
	function TemplateTransporteList() {
		global $content, $themeurl;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content"><h2>Ressourcenbilanz nach Accounts (eingetragene Transportberichte)</h2>';
		
		foreach($content['users'] as $squadname => $squadmembers) {
			echo '<table  cellpadding="0" cellspacing="0" border="0">
				<tr><th colspan="9">', $squadname == 'none' ? '???' : $squadname, '</th></tr>
				<tr>';
			foreach($content['headers'] as $header) {
				echo '<th>',
						$header['sort'] ? '<img src="'.$themeurl.'/img/'.$header['order'].'.png" />' : '',
						'<a href="', $header['link'], '">', $header['title'],'</a>',
						$header['sort'] ? '<img src="'.$themeurl.'/img/'.$header['order'].'.png" />' : '',
					'</th>';
			}
			echo '</tr>';
			foreach($squadmembers as $user) {
				echo '<tr>
					<td>', $user['name'], '</td>
					<td>', $user['fe'], '</td>
					<td>', $user['st'], '</td>
					<td>', $user['vv'], '</td>
					<td>', $user['ch'], '</td>
					<td>', $user['ei'], '</td>
					<td>', $user['wa'], '</td>
					<td>', $user['en'], '</td>
					<td>', $user['bev'], '</td>
				</tr>';
			}
			echo '</table><br />';
		}
		echo '</div>';
		TemplateFooter();
	}
?>
