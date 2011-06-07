<?php


function TemplateRaidOverview() {
	global $content;
	TemplateHeader('<style type="text/css"><!--
.kb_standard {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	color: #FFFFFF;
}
.kb_fett {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
	font-weight: bold;
	color: #FFFFFF;
}
.kb_tab_trennung_unten {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 9px;
	font-style: normal;
	font-weight: 900;
	border-bottom-style: solid;
	border-bottom-width: 1px;
	border-top-style: none;
	border-right-style: none;
	border-left-style: none;
	border-bottom-color: #666666;
}
.kb_tab_rand {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 9px;
	font-style: normal;
	font-weight: 900;
	margin:0px;
	color: #FFFFFF;
}
.kb_right
{
	text-align:right;
}
.kb_schiffe_start
{

}
.kb_schiffe_zerstoert
{
	color:#FF0000;
}
.kb_schiffe_ueberlebt
{
	color:#00FF00;
}
-->
</style>');
	TemplateMenu();
	echo '<div class="content"><h2>Neue Raids</h2>
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
	// ]]></script>
	<table>
		<tr align="center"><th>Zeit</th><th>Angreifer</th><th>Verteidiger</th><th colspan="8">Gewinn</th><th colspan="7">Zerstört</th></tr>
		<tr><th colspan="3">&nbsp;</th><th>Pts</th><th>Eisen</th><th>Stahl</th><th>Chemie</th><th>VV4A</th><th>Eis</th><th>Wasser</th><th>Energie</th><th>Eisen</th><th>Stahl</th><th>Chemie</th><th>VV4A</th><th>Eis</th><th>Wasser</th><th>Energie</th>';
		
	foreach($content['raids'] as $raid) {
		echo '<tr><td><a href="', $raid['url'], '" onclick="return loadKB(\'', $raid['id'],'\', \'', $raid['hash'], '\');"> ', $raid['date'], '</a></td><td>', 
			$raid['angreiferName'], !empty($raid['angreiferAlly']) ? ' ['.$raid['angreiferAlly'].']' : '', '</td><td>',
			$raid['verteidigerName'], !empty($raid['verteidigerAlly']) ? ' ['.$raid['verteidigerAlly'].']' : '', '</td><td>',
			$raid['score'], '</td><td>', $raid['rFe'], '</td><td>', $raid['rSt'], '</td><td>',
			$raid['rCh'], '</td><td>', $raid['rVv'], '</td><td>', $raid['rEi'], '</td><td>',
			$raid['rWa'], '</td><td>', $raid['rEn'], '</td><td>', $raid['zFe'], '</td><td>',
			$raid['zSt'], '</td><td>', $raid['zCh'], '</td><td>', $raid['zVv'], '</td><td>',
			$raid['zEi'], '</td><td>', $raid['zWa'], '</td><td>', $raid['zEn'], '</td></tr>
			<tr style="display:none;" id="kbr_', $raid['id'], '"><td id="kb_', $raid['id'], '" colspan="18" style="font-size:smaller;"></td></tr>';
	}
	
	echo '</table></div>';
	TemplateFooter();
}

?>
