<?php


function TemplateRaidOverview() {
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
-->
</style>');
	TemplateMenu();
	echo '<div class="content" style="border:none;padding:none;"><h2>Neue Raids</h2>
	<script type="text/javascript"><!-- // --><![CDATA[
		var already_loaded = new Object();
		function loadKB(id, hash) {
			if(already_loaded[id]) {
				toggleTableRow(getElById(\'kbr_\'+id));
				return false;
			}
			already_loaded[id] = true;
			var req = getXMLRequester();
			var url = scriptinterface+"?a=kbpassthrough&sid="+sid+"&id="+id+"&hash="+hash;
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
	<table cellpadding="0" cellspacing="0" border="0">
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
			<tr style="display:none;" id="kbr_', $raid['id'], '"><td id="kb_', $raid['id'], '" colspan="18" class="kbtd"></td></tr>';
	}
	
	echo '<tr><td>', $content['hasPrev'] ? '<a href="'.$content['prevLink'].'">Prev</a>' : 'Prev', '</td><td colspan="16">&nbsp;</td><td><a href="', $content['nextLink'], '">Next</a></td></tr></table></div>';
	TemplateFooter();
}

?>
