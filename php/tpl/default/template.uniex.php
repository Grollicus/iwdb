<?php
	if(!defined('dddfd'))
		die();
	function TemplateUniViewEx() {
		global $content, $themeurl;
		
		echo '<table width="99%" cellpadding="0" cellspacing="0" border="0">
			<tr><td colspan="', $content['uni']['columns'], '">
				<table width="100%" border="0" style="border: none;" class="subtable"><tr><td align="left" style="width: 90px;">', $content['uni']['hasPrevLink'] ? '<a href="'.$content['uni']['prevLink'].'">Vorherige Seite</a>' : 'Vorherige Seite', '</td>
				<th align="center">Universumsansicht</th>
				<td align="right" style="width: 90px;">', $content['uni']['hasNextLink'] ? '<a href="'.$content['uni']['nextLink'].'">Nächste Seite</a>' : 'Nächste Seite', '</td></tr></table>
			</td></tr><tr style="white-space:nowrap;">';
		foreach($content['uni']['titles'] as $title) {
			if(!$title['hidden'])
				echo '<th title="', $title['desc'], '">',
					$title['hasImage'] ? '<img src="'.$themeurl.'/img/'.$title['image'].'" />' : '', 
					$title['hasLink'] ? '<a href="'.$title['link'].'">' : '' , 
					$title['title'], 
					$title['hasLink'] ? '</a>' : '',
					$title['hasImage'] ? '<img src="'.$themeurl.'/img/'.$title['image'].'" />' : '',
					 '</th>';
		}
		echo '</tr>';
		foreach($content['uni']['data'] as $row) {
			echo '<tr>';
			foreach($content['uni']['titles'] as $v) {
				switch($v['id']) {
					case 'scan_gebs':
						echo '</tr><tr><td colspan="', $content['uni']['columns'], '" class="', $row['scan_gebs_time'], '">';
						if(!empty($row['scan_gebs'])) {
							echo '<table border="0" style="border: none;" class="subtable">';
							foreach($row['scan_gebs'] as $geb) {
								echo '<tr><td>', $geb['name'], '</td><td align="right">', $geb['anz'], '</td></tr>';
							}
							echo '</table>';
						}
						echo '</td>';
						break;
					case 'scan_schiffe':
						echo '</tr><tr><td colspan="', $content['uni']['columns'], '" class="', $row['scan_schiffe_age'], '">';
						if(!empty($row['scan_schiffe'])) {
							echo '<table border="0" style="border: none;" class="subtable">';
							foreach($row['scan_schiffe'] as $flotte) {
								echo '<tr><td colspan="2" style="font-weight: bold;">', $flotte['typ'], 'e Flotte von ', $flotte['name'], '</td></tr>';
								foreach($flotte['schiffe'] as $schiff) {
									echo '<tr><td>', $schiff['name'], '</td><td>', $schiff['anz'], '</td></tr>';
								}
							}
							echo '</table>';
						}
						echo '</td></tr>';
						break;
					case 'coords':
						echo '<td class="', $row['act'], '">', $row[$v['id']], '</td>';
						break;
					case 'important_specials':
						echo '<td class="', $row['geotime'], '">';
						foreach($row['important_specials'] as $s) {
							echo '<span class="geo_class_', $s['short'], '" title="', $s['name'], '">', $s['short'], '</span>';
						}
						echo '</td>';
						break;
					case 'geo_ch':
					case 'geo_fe':
					case 'geo_ei':
					case 'geo_gravi':
					case 'geo_lb':
					case 'geo_fmod':
					case 'geo_gmod':
					case 'geo_gtmod':
					case 'geo_smod':
					case 'geo_stmod':
					case 'geo_ttl':
						echo '<td class="', $row['geotime'], '">', $row[$v['id']], '</td>';
					break;
					default:
						echo '<td>', $row[$v['id']], '</td>';
						break;
				}
			}
			echo '</tr>';
		}
		echo '<tr><td colspan="', $content['uni']['columns'], '">
				<table width="100%" border="0" style="border: none; margin:0px; padding:0px;" class="subtable"><tr><td width="50%" align="left" style="border: none; margin:0px; padding:0px;">', $content['uni']['hasPrevLink'] ? '<a href="'.$content['uni']['prevLink'].'">Vorherige Seite</a>' : 'Vorherige Seite', '</td>
				<td align="right" style="border: none; margin:0px; padding:0px;">', $content['uni']['hasNextLink'] ? '<a href="'.$content['uni']['nextLink'].'">Nächste Seite</a>' : 'Nächste Seite', '</td></tr></table>
			</td></tr>
			<tr><th colspan="', $content['uni']['columns'], '">Farben geben das Alter der Daten an: ';
		foreach($content['uni']['color_stages'] as $stage => $time) {
			echo '&nbsp;<span class="act_', $stage, '">', $time, '</span>';
		}
		echo '<span class="act_5">+</span></th></tr>
		</table>'; 
	}
	
	function TemplateViewFilteredUniverseEx() {
		global $content;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content" style="border:none;">';
		
		if($content['hasResults']) {
			TemplateUniViewEx();
		}
		echo '
		<br /><br /><form action="', $content['submitUrl'], '" method="post">
			<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th colspan="2"><input type="submit" value="Filtern" /></th></tr>';
		foreach($content['filter'] as $filter) {
			echo '<tr><td style="width:300px;">', $filter['title'], (!empty($filter['desc']) ? '<br /><i>'.$filter['desc'].'</i>': ''), '</td><td>';
			switch($filter['name']) {
				case 'gala':
				case 'sys':
				case 'pla':
				case 'geo_ch':
				case 'geo_fe':
				case 'geo_ei':
				case 'geo_gravi':
				case 'geo_lb':
				case 'geo_fmod':
				case 'geo_gebd':
				case 'geo_gebk':
				case 'geo_schd':
				case 'geo_schk':
					echo '<input type="text" size="3" name="', $filter['name'], '_min" value="', $filter['data']['min'], '" /> - <input type="text" size="3" name="', $filter['name'], '_max" value="', $filter['data']['max'], '" />';
					break;
				case 'spieler':
				case 'tag':
				case 'planiname':
				case 'scan_geb':
					echo '<input type="text" size="30" name="', $filter['name'], '" value="', $filter['data'], '" />';
					break;
				case 'planityp':
				case 'objekttyp':
					echo '<select name="', $filter['name'], '[]" size="', count($filter['data']), '" multiple="multiple">';
					foreach($filter['data'] as $name => $val) {
						echo '<option value="', $name, '"', $val ? ' selected="selected"' : '', '>', $name, '</option>';
					}
					echo '</select>';
					break;
				case 'geo_bes':
					echo '<select name="', $filter['name'], '[]" size="6" multiple="multiple">';
					foreach($filter['data'] as $item) {
						echo '<option value="', $item['value'], '"', $item['selected'] ? ' selected="selected"' : '', '>', $item['name'], '</option>';
					}
					echo '</select>';
					break;
					break;
				default:
					var_dump($filter['data']);
					break;
			}
			echo '</td></tr>';
		}
		echo '<tr><td><b>Spalten</b><br /><i>Mehrfachauswahl mit [Strg]</i></td><td><select name="spalten[]" multiple="multiple" size="4">';
		foreach($content['modules'] as $name => $mod) {
			echo '<option value="', $name, '" ', $mod['selected'] ? ' selected="selected"': '', '>', $mod['desc'], '</option>';
		}
		echo '
			</select></td></tr>
			<tr><td><b>Sortierung</b></td><td>';
		foreach($content['sort'] as $id => $sort) {
			echo '<select name="sortby[', $id, ']">';
			foreach($sort['items'] as $name => $item) {
				echo '<option value="', $name, '" ', $item['selected'] ? 'selected="selected"' : '', '>', $item['desc'], '</option>';
			}
			echo '</select>
			<select name="orders[', $id, ']">
				<option value="0" ', $sort['asc'] ? 'selected="selected"' : '', '>Aufsteigend</option>
				<option value="1" ', !$sort['asc'] ? 'selected="selected"' : '', '>Absteigend</option>
			</select><br />';
		}
		echo '</td></tr>
			<tr><th colspan="2"><input type="submit" value="Filtern" /></th></tr>
			</table>
		</form>';
		
		echo '</div>';
		TemplateFooter();
	}
?>