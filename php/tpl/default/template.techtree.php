<?php

if(!defined('dddfd'))
	die();

function TemplateTechtreeView() {
	global $content, $scripturl, $themeurl;
	
	TemplateHeader();
	TemplateMenu();
	
		echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		/* techtree_data[0=details, 1=Weg][itemid] */
		var techtree_data_loaded = new Array();
		techtree_data_loaded[0] = new Array();
		techtree_data_loaded[1] = new Array();
		var techtree_runningtype = -1;
		var techtree_runningid = 0;

		function techtree_requesthandler() {
			if(reqrunning.readyState == 4) {
				if(reqrunning.status == 200) {
					getElById("tt_"+techtree_runningtype+"_"+techtree_runningid).innerHTML = reqrunning.responseText;
						techtree_data_loaded[techtree_runningtype][techtree_runningid] = true;
				} else {
					getElById("tt_"+techtree_runningtype+"_"+techtree_runningid).innerHTML = "Fehler: "+reqrunning.status;
				}
				techtree_runningtype = -1;
				techtree_runningid = 0;
				reqrunning = false;
				viewLoadingState(false);
			}
		}
		
		function techtree_trigger(id, sid, type) {
			if(!techtree_data_loaded[type][id]) {
				getElById("tt_"+type+"_"+id).innerHTML = "Loading...";
				viewLoadingState(true);
				techtree_runningtype = type;
				techtree_runningid = id;
				requestData(scriptinterface+"?a=techtree&sid="+sid+"&id="+id+"&t="+type, null, techtree_requesthandler);
			}
			toggleTableRow(getElById("row_"+type+"_"+id));
		}
	// ]]></script>
	<div class="content">
		<table width="99%" cellpadding="0" cellspacing="0" border="0">
		<tr><th colspan="2">Techtree&uuml;bersicht</th></tr>
		<tr><td colspan="2"><form action="', $scripturl, '/index.php?action=techtree" method="post">
			<b>Filtern: </b><input type="text" name="filter" value="', $content['filterstr'], '" />&nbsp;<input type="submit" value="Ok" /></form></td>
		</tr>
		<tr><td>&nbsp;</td><td align="center" style="width:125px;"><b>Details</b></td></tr>';
	foreach($content['items'] as $item) {
		echo '<tr><td><span class="techtree_', $item['typ'], $item['done'] ? '_done' : '', '">', $item['name'], '</span><br /></td>';
		echo '
			<td align="center">
				<a href="javascript:techtree_trigger(', $item['id'], ',sid,0)" style="padding:2px;">Details</a><br />
				<a href="javascript:techtree_trigger(', $item['id'], ',sid,1)" style="padding:2px;">Weg</a><br />
			</td>';
		echo '</tr>
		<tr id="row_0_', $item['id'], '" style="display:none;"><td id="tt_0_', $item['id'], '" colspan="3" style="padding:2px;"></td></tr>
		<tr id="row_1_', $item['id'], '" style="display:none;"><td id="tt_1_', $item['id'], '" colspan="3" style="padding:2px;"></td></tr>';
	}
	echo '<tr><td class="windowbg1" colspan="3">
			<span class="techtree_for_done">erledigte</span>/<span class="techtree_for">fehlende Forschung</span> - 
			<span class="techtree_geb_done">gebautes</span>/<span class="techtree_geb">fehlendes Geb&auml;ude</span>
			<br />Geb&auml;ude- und Forschungs&uuml;bersicht als neuen Bericht importieren
			<br /><a href="', $scripturl, '/index.php?action=techtree_missing">Noch fehlende Einträge</a>
		 </td></tr>
		</table>
	</div>';
	TemplateFooter();
}

function sTemplateTechtreeDetails() {
	global $content;
	
	if(!empty($content['beschreibung'])) {
		echo $content['beschreibung'], '<br /><br />';
	}
	echo '
		<table width="100%">
			<tr><th width="50%">Ermöglicht</th><th width="50%">Benötigt</th></tr>
			<tr><td>';
	foreach($content['erm'] as $item) {
		PrintTechtreeItem($item);
	}
	echo '
			</td><td>';
	foreach($content['ben'] as $item) {
		PrintTechtreeItem($item);
	}
	echo '
			</td></tr>
		</table>
		<table width="100%">
			<tr><th colspan="2">Stufen</th></tr>';
	foreach($content['stufen'] as $stufe) {
		echo '<td><b>Stufe ', $stufe['stufe'], '</b></td><td>';
		if(!empty($stufe['dauer'])) {
			echo 'Dauer: ', $stufe['dauer'];
		}
		if(!empty($stufe['kosten']) > 0) {
			echo '<br />Kosten: ';
			foreach($stufe['kosten'] as $k) {
				echo $k['name'], ': ', $k['anz'].' ';
			}
		}
		if(!empty($stufe['bringt']) > 0) {
			echo '<br />Bringt: ';
			foreach($stufe['bringt'] as $b) {
				echo $b['name'], ': ', $b['anz'].' ';
			}
		}
		echo '</td></tr>';
	}
	echo '
		</table>';
}

function TemplateTechtreeListMissing() {
	global $content;
	TemplateHeader();
	TemplateMenu();
	
	echo '<div class="content"><h2>Noch fehlende Techtree-Einträge</h2>';
	foreach($content['missing'] as $missing) {
		echo '<div class="techtree_', $missing['typ'], '">', $missing['name'], '</div>';
	}
	echo '</div>';
	
	TemplateFooter();
}

function PrintTechtreeItem($item) {
	$t = $item['typ'];
	echo '<div class="techtree_',$t, $item['done'] ? '_done' : '', '">', EscapeOU($item['name']), '</div>';
}

?>
