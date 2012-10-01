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
		echo '<div class="content">
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
			viewLoadingState(true);
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
				viewLoadingState(false);
			}
		}
		
		var already_loaded_sb = new Object();
		function loadSB(id) {
			if(already_loaded_sb[id]) {
				toggleTableRow(getElById(\'sbr_\'+id));
				return false;
			}
			already_loaded_sb[id] = true;
			var req = getXMLRequester();
			var url = scriptinterface+"?a=scanprint&sid="+sid+"&id="+id;
			req.open(\'GET\', url, true);
			req.onreadystatechange = function(){loadSbcallback(req, id);};
			req.send(null);
			viewLoadingState(true);
			return false;
		}
		function loadSbcallback(req, id) {
			if(req.readyState == 4) {
				if(req.status == 200) {
					var el = getElById(\'sbr_\'+id);
					el.innerHTML = req.responseText;
					toggleTableRow(getElById(\'sbr_\'+id));
				} else {
					alert("Request-Fehler: "+req.status);
				}
				viewLoadingState(false);
			}
		}
	// ]]></script>';
		
		if(!$content['hasWars']) {
			echo 'Irgendwas läuft da schief, ich hab gar keinen Krieg!<br />';
		}
		
		foreach($content['wars'] as $war) {
			echo '<h2>', $war['name'], '</h2><form method="post" action="',$content['submitUrl'],'"><table cellpadding="0" cellspacing="0" border="0"><tr><td colspan="13" style="border:none;"><table style="border:none;" width="100%"><tr><td style="border:none;width:110px;">', $content['hasPrev'] ? '<a href="'.$content['prevLink'].'">Vorherige Seite</a>' : 'Vorherige Seite', '</td><th style="font-size:larger;text-align:center;">Kampfberichte</th><td style="border:none;width:110px;text-align:right;"><a href="',$content['nextLink'],'">Nächste Seite</a></td></tr></table></td></tr>
				<tr><th>Zeit</th><th colspan="2">Angreifer</th><th>Start</th><th colspan="2">Verteidiger</th><th>Ziel</th><th>Angriff</th><th>Verlust</th><th>Verteidigung</th><th>Verlust</th><th>Raid</th><th>gebombt</th></tr>
				<tr>
					<th>Filter</th>
					<td><input type="text" name="kb_att" value="',$content['filter']['kb_att'],'" /></td>
					<td><input type="text" name="kb_att_ally" value="',$content['filter']['kb_att_ally'],'" size="5" /></td>
					<td><input type="text" name="kb_start" value="',$content['filter']['kb_start'],'" size="9" /></td>
					<td><input type="text" name="kb_def" value="',$content['filter']['kb_def'],'" /></td>
					<td><input type="text" name="kb_def_ally" value="',$content['filter']['kb_def_ally'],'" size="5" /></td>
					<td><input type="text" name="kb_dst" value="',$content['filter']['kb_dst'],'" size="9" /></td>
					<td><input type="text" name="att_value" value="',$content['filter']['att_value'],'" size="9" /></td>
					<td>&nbsp;</td>
					<td><input type="text" name="def_value" value="',$content['filter']['def_value'],'" size="9" /></td>
					<td>&nbsp;</td>
					<td colspan="2"><input type="submit" value="Filtern!" /></td>
				</tr>';
			foreach($war['kbs'] as $kb) {
				echo '<tr',$kb['isFake'] ? ' class="fake"' : '','><td><a href="', $kb['url'], '" onclick="return loadKB(\'', $kb['id'],'\', \'', $kb['hash'], '\');"> ', $kb['date'], '</a></td><td ', $kb['attWin']?'style="font-weight:bold;"':'' ,'>',
					$kb['angreiferName'], '</td><td>', $kb['angreiferAlly'], '</td><td>', $kb['startKoords'], '</td><td ', !$kb['attWin']?'style="font-weight:bold;"':'' ,'>', $kb['verteidigerName'], '</td><td>', $kb['verteidigerAlly'], '</td><td>',
					$kb['zielKoords'], '</td><td>', $kb['angreiferWert'], '</td><td>', $kb['angreiferVerlust'], '</td><td>', $kb['verteidigerWert'], '</td><td>', $kb['verteidigerVerlust'], '</td><td>',
					$kb['raidWert'], '</td><td>', $kb['bombWert'], '</td></tr>
					<tr style="display:none;" id="kbr_', $kb['id'], '"><td id="kb_', $kb['id'], '" colspan="13" class="kbtd"></td></tr>';
			}
			echo '</table></form><br /><br />';
		}
		
		echo '<a href="',$content['showAllLink'],'">Alle (also auch alte) Kriege anzeigen</a></div>';
		TemplateFooter();
	}
	
		function TemplateWarScans() {
		global $content;
		TemplateHeader('<style type="text/css"><!--
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
		echo '<div class="content">
	<script type="text/javascript"><!-- // --><![CDATA[
		var already_loaded_sb = new Object();
		function loadSB(id) {
			if(already_loaded_sb[id]) {
				toggleTableRow(getElById(\'sbr_\'+id));
				return false;
			}
			already_loaded_sb[id] = true;
			var req = getXMLRequester();
			var url = scriptinterface+"?a=scanprint&sid="+sid+"&id="+id;
			req.open(\'GET\', url, true);
			req.onreadystatechange = function(){loadSbcallback(req, id);};
			req.send(null);
			viewLoadingState(true);
			return false;
		}
		function loadSbcallback(req, id) {
			if(req.readyState == 4) {
				if(req.status == 200) {
					var el = getElById(\'sbr_\'+id);
					el.innerHTML = req.responseText;
					toggleTableRow(getElById(\'sbr_\'+id));
				} else {
					alert("Request-Fehler: "+req.status);
				}
				viewLoadingState(false);
			}
		}
	// ]]></script>';
		
		if(!$content['hasWars']) {
			echo 'Irgendwas läuft da schief, ich hab gar keinen Krieg!<br />';
		}
		
		foreach($content['wars'] as $war) {
			echo '<h2>', $war['name'], '</h2>
			<form method="post" action="',$content['submitUrl'],'"><table cellpadding="0" cellspacing="0" border="0"><tr><td colspan="9" style="border:none;"><table style="border:none;" width="100%"><tr><td style="border:none;width:110px;">', $content['hasPrev'] ? '<a href="'.$content['prevLink'].'">Vorherige Seite</a>' : 'Vorherige Seite', '</td><th style="font-size:larger;text-align:center;">Scans</th><td style="border:none;width:110px;text-align:right;"><a href="',$content['nextLink'],'">Nächste Seite</a></td></tr></table></td></tr>
				<tr><th>Zeit</th><th>Typ</th><th>Koords</th><th colspan="2">Besitzer</th><th>Objekttyp</th><th>Planityp</th><th align="center" colspan="8">Ress</th><th>Score</th></tr>
				<tr><th colspan="7">&nbsp;</th><th>RessScore</th><th>Eisen</th><th>Stahl</th><th>Chemie</th><th>VV4A</th><th>Eis</th><th>Wasser</th><th>Energie</th><th>&nbsp;</th></tr>
				<tr>
				<th>Filter</th>
					<td><select name="scan_type">';
			foreach($content['filter']['scan_type'] as $val => $o) {
				echo '<option value="', $val, '" ',$o['selected'] ? 'selected="selected"' : '','>', $o['name'], '</option>';
			}
				echo '</select></td>
					<td><input type="text" size="9" name="scan_coords" value="',$content['filter']['scan_coords'],'" /></td>
					<td><input type="text" name="scan_owner" value="',$content['filter']['scan_owner'],'" /></td>
					<td><input type="text" size="4" name="scan_ally" value="',$content['filter']['scan_ally'],'" /></td>
					<td colspan="2">&nbsp;</td>
					<td><input type="text" size="6" name="scan_ress_score" value="',$content['filter']['scan_ress_score'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_fe" value="',$content['filter']['scan_ress_fe'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_st" value="',$content['filter']['scan_ress_st'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_ch" value="',$content['filter']['scan_ress_ch'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_vv" value="',$content['filter']['scan_ress_vv'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_ei" value="',$content['filter']['scan_ress_ei'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_wa" value="',$content['filter']['scan_ress_wa'],'" /></td>
					<td><input type="text" size="6" name="scan_ress_en" value="',$content['filter']['scan_ress_en'],'" /></td>
					<td colspan="3"><input type="submit" value="Filtern" /></td>
				</tr>';
			foreach($war['scans'] as $scan) {
				echo '<tr><td><a href="', $scan['url'], '" onclick="return loadSB(\'', $scan['id'],'\');"> ', $scan['date'], '</a></td><td>',
					$scan['typ'] ,'</td><td>', $scan['coords'], '</td><td>', $scan['ownerName'], '</td><td>', $scan['ownerAlly'], '</td><td>', $scan['objekttyp'], '</td>
					<td>', $scan['planityp'], '</td><td>', $scan['ressScore'], '</td><td>', $scan['ress']['fe'], '</td><td>', $scan['ress']['st'], '</td>
					<td>', $scan['ress']['ch'], '</td><td>', $scan['ress']['vv'], '</td><td>', $scan['ress']['ei'], '</td>
					<td>', $scan['ress']['wa'], '</td><td>', $scan['ress']['en'], '</td><td>', $scan['score'], '</td>
					</tr><tr style="display:none;" id="sbr_', $scan['id'], '"><td id="sb_', $scan['id'], '" colspan="9"></td></tr>';
			}
			echo '
			</table></form><br /><br />';
		}
		
		echo '<a href="',$content['showAllLink'],'">Alle (also auch alte) Kriege anzeigen</a></div>';
		TemplateFooter();
	}
	
	function TemplateScanPrint() {
		global $content;
		
		echo '<td colspan="5">';
		if($content['hasShips']) {
			echo '<table class="subtable" style="border:none;">';
			foreach($content['scan']['flotten'] as $fl) {
				echo '<tr><td colspan="2"><b>', $fl['typ'] == 'planetar' ? 'Planetare Flotte' : ('Stationierte Flotte von '.$fl['owner']), '</b></td></tr>';
				foreach($fl['ships'] as $s)
					echo '<tr><td>', $s['name'], '</td><td>', $s['cnt'], '</td></tr>';
			}
			echo '</table>';
		}
		if($content['hasGebs']) {
			echo '<table class="subtable" style="border:none;" width="100%">';
			foreach($content['scan']['gebs'] as $geb) {
				echo '<tr><td>', $geb['name'], '</td><td>', $geb['cnt'], '</td></tr>';
			}
			echo '</table>';
		}
		echo '</td><td colspan="11">&nbsp;</td>';
		
	}
	
	function TemplateWarSchedule() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		
		if($content['disabled']) {
			echo '<div class="content">Is kein Krieg!</div>';
			TemplateFooter();
			return;
		}
		
		echo '<div class="content"><script type="text/javascript"><!-- // --><![CDATA[
	function WarScheduleReg(time) {
		AjaxRequest("war_schedule_cb", "m=reg&t="+time+"&el="+time);
		return false;
	}
	function WarScheduleUnReg(id) {
		AjaxRequest("war_schedule_cb", "m=unreg&id="+id+"&el="+id);
		return false;
	}
	
		// ]]></script>
		<h2>Sittereinteilung</h2>';

		foreach($content['schedule'] as $day) {
			echo '<table  cellpadding="0" cellspacing="0" border="0" ', $day['last'] ? '' : 'style="float:left;margin-right:5px;"', '><tr><th colspan="2">', $day['date'], '</th></tr>';
			foreach($day['times'] as $time) {
				echo '<tr><td style="height:24px;">', $time['time'], '</td><td>';
				foreach($time['usedSlots'] as $slot) {
					if($slot['isMe'] && $time['active']) {
						echo '<span id="sched_',$slot['id'],'"><button onclick="WarScheduleUnReg(\'',$slot['id'],'\');"><b>Abmelden</b></button></span>';
					} else {
						echo $slot['name'], '&nbsp;';
					}
				}
				if($time['showReg'] && $time['active'])
					echo '<span id="sched_',$time['regId'],'"><button onclick="WarScheduleReg(\'',$time['regId'],'\');">Mach ich!</button></span>';
				echo '</td></tr>';
			}
			echo '</table>';
		}
		echo '</div>';
		
		TemplateFooter();
	}
	
	function TemplateWarTiming() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
		<script type="text/javascript"><!-- // --><![CDATA[
			function calcTimes(scans, schiff) {
				var hins = $("#hins").val()
				var ruecks = $("#ruecks").val()
				for(var i=0; i < scans.length; i++) {
					var scan = scans[i];
					var fz = flugzeit(scan.dst_g, scan.dst_s, scan.dst_p, scan.src_g, scan.src_s, scan.src_p, schiff.sol*hins, schiff.gal*hins);
					scan.start = fz ? formatdate(scan.time - fz) : "-";
					fz = flugzeit(scan.dst_g, scan.dst_s, scan.dst_p, scan.src_g, scan.src_s, scan.src_p, schiff.sol*ruecks, schiff.gal*ruecks);
					scan.ret = fz ? formatdate(scan.time + fz) : "-";
					scans[i] = scan;
				}
				scans.sort(function(a,b) {return a.start=="-" ? (b.start == "-" ? a.time-b.time : 1) : b.start == "-" ? -1 : a.start-b.start;});
				
				var tbl = $("#timingtab");
				tbl.empty();
				tbl.append("<tr><th>Ziel<\/th><th>Start<\/th><th><\/th><th>Typi<\/th><th>Start<\/th><th>Ankunft<\/th><th>Rück<\/th><\/tr>");
				for(var i=0; i < scans.length; i++) {
					var scan = scans[i];
					var arrivaltime = formatdate(scan.time);
					tbl.append("<tr" + (scan.origin=="hs" ? " style=\"font-style:italic;\"" : "") + "><td>"+scan.dst+"<\/td><td>"+scan.src+"<\/td><td>"+scan.type+"<\/td><td>"+scan.ally+" "+scan.sender+"<\/td><td>"+scan.start+"<\/td><td>"+arrivaltime+"<\/td><td>"+scan.ret+"<\/td><\/tr>");
				}
			}
			var scans = ',$content['scans'],';
			var kbs = ',$content['kbs'],';
			var schiffe = ', $content['schiffe'], ';
			$(function() {
				$("#ship").empty();
				for(var i=0; i < schiffe.length; ++i) {
					var sch=schiffe[i];
					$("#ship").append("<option value=\""+i+"\">"+sch.name+"<\/option>");
				}
				$("#hins").empty();
				$("#ruecks").empty();
				for(var i=5; i <= 130; i += 5) {
					$("#hins").append("<option value=\""+(i*0.01)+"\""+(i==100?" selected=\"selected\"" : "")+">"+i+" %<\/option>");
					$("#ruecks").append("<option value=\""+(i*0.01)+"\""+(i==100?" selected=\"selected\"" : "")+">"+i+" %<\/option>");
				}
				var h = function() {
					var sch = schiffe[$("#ship").val()];
					calcTimes(sch.type=="sonde" ? scans : kbs, sch);
				};
				$("#ship").change(h).keyup(h);
				$("#hins").change(h).keyup(h);
				$("#ruecks").change(h).keyup(h);
				calcTimes(scans, schiffe[0]);
			});
		// ]]></script>
		<select id="ship"><option>JS fail! :(</option></select>Hin: <select id="hins"><option>JS fail! :(</option></select>Rück: <select id="ruecks"><option>JS fail! :(</option></select>
		<table id="timingtab"><tr><td>JS fail! :(</td></tr></table>
		</div>';
		TemplateFooter();
	}
?>
