<?php
	function TemplateAccountOverview() {
		global $content, $themeurl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content"><h2>Übersicht über den Account "', $content['accountName'], '"</h2>';
		foreach($content['planis'] as $scan) {
			echo '
	<table width="99%">
		<tr><th colspan="2">[', $scan['coords'], ']', $scan['name'], '</th><th>', $scan['besitzer'], '</th></tr>
		<tr class="', $scan['aktualitaet'], '"><td>', $scan['planityp'], '</td><td>', $scan['objekttyp'], '</td><td>', $scan['zeit'], '</td></tr>
		<tr><td colspan="3">
			<img src="', $themeurl, '/img/eisen.png" alt="Eisen"/>: ', $scan['fe'], '
			<img src="', $themeurl, '/img/stahl.png" alt="Eisen"/>: ', $scan['st'], '
			<img src="', $themeurl, '/img/vv4a.png" alt="Eisen"/>: ', $scan['vv'], '
			<img src="', $themeurl, '/img/chem.png" alt="Eisen"/>: ', $scan['ch'], '
			<img src="', $themeurl, '/img/eis.png" alt="Eisen"/>: ', $scan['ei'], '
			<img src="', $themeurl, '/img/wasser.png" alt="Eisen"/>: ', $scan['wa'], '
			<img src="', $themeurl, '/img/energie.png" alt="Eisen"/>: ', $scan['en'], '
		</td></tr>
		<tr><td colspan="3">';
			foreach($scan['gebs'] as $geb) {
				echo $geb['anz'],'x ', $geb['name'], '<br />';
			}
			echo '
		</td></tr>
	</table>';
		}
		echo '</div>';
		TemplateFooter();
	}
	
	

?>