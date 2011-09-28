<?php
	if (!defined("dddfd"))
		die("Hacking attempt");
	
	function TemplateWarKbs() {
		global $content;
		TemplateHeader('<style type="text/css"><!--
.kb_standard, .kb_standard td {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #FFFFFF;
}
.kb_fett, .kb_fett td {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: bold;
	color: #FFFFFF;
}
.kb_tab_trennung_unten, .kb_tab_trennung_unten td {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 9px;
	font-style: normal;
	font-weight: 900;
	border-bottom-style: solid !important;
	border-bottom-width: 1px !important;
	border-top-style: none !important;
	border-right-style: none !important;
	border-left-style: none !important;
	border-bottom-color: #666666 !important;
}
.kb_tab_rand, .kb_tab_rand td {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 9px;
	font-style: normal;
	font-weight: 900;
	margin:0px;
	color: #FFFFFF;
}
.kb_right, .kb_right td
{
	text-align:right;
}
.kb_schiffe_start
{

}
.kb_schiffe_zerstoert, .kb_schiffe_zerstoert td
{
	color:#FF0000;
}
.kb_schiffe_ueberlebt, .kb_schiffe_ueberlebt td
{
	color:#00FF00;
}
.kbtd table {
	border-style: none;
	font-size:smaller;
	background-color: transparent;
}
.kbtd td, .kbtd tr {
	border-style: none;
}
.fake td {
	font-size:xx-small;
	color:#555555;
}
-->
</style>');
		TemplateMenu();
		echo '<div class="content"><h2>Kriegsübersicht</h2>
	<script type="text/javascript"><!-- // --><![CDATA[
		var already_loaded = new Object();
		function loadKB(id, hash) {
			if(already_loaded[id]) {
				toggleTableRow(getElById(\'kbr_\'+id));
				return false;
			}
			already_loaded[id] = true;
			var req = getXMLRequester();
			var url = scriptinterface+"?a=raidkbpassthrough&sid="+sid+"&id="+id+"&hash="+hash;
			req.open(\'GET\', url, true);
			req.onreadystatechange = function(){loadKbcallback(req, id);};
			req.send(null);
			return false;
		}
		function loadKbcallback(req, id) {
			if(req.readyState == 4) {
				if(req.status == 200) {
					var el = getElById(\'kb_\'+id);
					el.innerHTML = req.responseText;
					toggleTableRow(getElById(\'kbr_\'+id));
				} else {
					alert("Request-Fehler: "+req.status);
				}
			}
		}
	// ]]></script>';
		
		foreach($content['wars'] as $war) {
			echo '<table><tr><th colspan="14" style="font-size:larger;">', $war['name'], '</th></tr>
				<tr><th>Zeit</th><th colspan="2">Angreifer</th><th>Start</th><th colspan="2">Verteidiger</th><th>Ziel</th><th>Angriff</th><th>Verlust</th><th>Verteidigung</th><th>Verlust</th><th>Raid</th><th>gebombt</th></tr>';
			foreach($war['kbs'] as $kb) {
				echo '<tr',$kb['isFake'] ? ' class="fake"' : '','><td><a href="', $kb['url'], '" onclick="return loadKB(\'', $kb['id'],'\', \'', $kb['hash'], '\');"> ', $kb['date'], '</a></td><td>',
					$kb['angreiferName'], '</td><td>', $kb['angreiferAlly'], '</td><td>', $kb['startKoords'], '</td><td>', $kb['verteidigerName'], '</td><td>', $kb['verteidigerAlly'], '</td><td>',
					$kb['zielKoords'], '</td><td>', $kb['angreiferWert'], '</td><td>', $kb['angreiferVerlust'], '</td><td>', $kb['verteidigerWert'], '</td><td>', $kb['verteidigerVerlust'], '</td><td>',
					$kb['raidWert'], '</td><td>', $kb['bombWert'], '</td></tr>
					<tr style="display:none;" id="kbr_', $kb['id'], '"><td id="kb_', $kb['id'], '" colspan="18" class="kbtd"></td></tr>';
			}
			echo '</table><br /><br />';
		}
		
		echo '</div>';
		TemplateFooter();
	}
?>