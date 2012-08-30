<?php
	if (!defined("dddfd"))
		die("Hacking attempt");

	function TemplateSitterTaskList() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		echo '
		<div class="content">
			<h2>Sitterauftr&auml;ge</h2>
			<table><tr>';
		foreach($content['pages'] as $page) {
			echo '<th><a href="', $page['link'], '">', $page['desc'], '</a></th>';
		}
		echo '</tr></table><br />
		',isset($content['msg']) ? '<div class="simp">'.$content['msg'].'</div><br />' : '','
			<table>
				<tr><th colspan="5">Sitterauftr&auml;ge offen</th></tr>
				<tr><th style="width:120px;">Zeit</th><th>Bei</th><th>Koordinaten</th><th>Auftrag</th><th></th></tr>';
		foreach ($content['sitternow'] as $line) {
			echo '
				<tr class="sitterjob_', $line['ownershipState'], '">
					<td>', $line['time'], '</td>
					<td><a href="', $line['loginLink'], '">[', $line['igmName'], ']</a><br /><i style="fonz-size:smaller;">(', $line['userName'], ')</i></td>
					<td>', $line['coords'], ' ', $line['planiName'], '</td>
					<td><b>', $line['typeLong'], '</b><br />', $line['text'], '</td>
					<td>',$line['hasEditLinks'] ? '<a href="'.$line['editLink'].'">Edit</a>'.($line['hasAppendLink'] ? '<br /><a href="'.$line['appendLink'].'">Anhängen</a>' : '').'<br /><a href="'.$line['delLink'].'">Del</a>' : '&nbsp;', '</td>
				</tr>';
		}
		echo '<tr><th colspan="5">Farbenlegende: <span class="sitterjob_own">Eigener Auftrag</span> <span class="sitterjob_account">Fremder Auftrag für eigenen Account</span></th></tr></table><br /><br /><br />
			<table>
				<tr><th colspan="5">Kommende Sitterauftr&auml;ge</th></tr>
				<tr><th style="width:120px;">Zeit</th><th>Bei</th><th>Koordinaten</th><th>Auftrag</th><th>&nbsp;</th></tr>';
		foreach ($content['sittersoon'] as $line) {
			echo '
				<tr class="sitterjob_', $line['ownershipState'], '">
					<td>', $line['time'], '</td>
					<td>', $line['igmName'], '<br /><i style="fonz-size:smaller;">(', $line['userName'], ')</i></td>
					<td>', $line['coords'], ' ', $line['planiName'], '</td>
					<td><b>', $line['typeLong'], '</b><br />', $line['text'], '</td>
					<td>',$line['hasEditLinks'] ? '<a href="'.$line['editLink'].'">Edit</a>'.($line['hasAppendLink'] ? '<br /><a href="'.$line['appendLink'].'">Anhängen</a>' : '').'<br /><a href="'.$line['delLink'].'">Del</a>' : '&nbsp;', '</td>
				</tr>';
		}
		echo '<tr><th colspan="5">Farbenlegende: <span class="sitterjob_own">Eigener Auftrag</span> <span class="sitterjob_account">Fremder Auftrag für eigenen Account</span></th></tr>
		</table></div>';
		TemplateFooter();
	}

	function TemplateSitterEdit() {
		global $content;
		
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">
	<h2>', $content['heading'], '</h2>
	<form action="', $content['submitAction'], '" method="post">
	<table>
				<tr>';
		foreach($content['pages'] as $page) {
			echo '<th', $page['active'] ? ' style="font-style: italic;"' : '', '><a href="', $page['link'], '">', $page['desc'], '</a></th>';
		}
		echo '</tr>
	</table><br />';
		if(!empty($content['errors'])) {
			echo '<div class="imp">';
			foreach($content['errors'] as $err) {
				echo $err.'<br />';
			}
			echo '</div>';
		}
		echo '<table>
		<tr><th>', $content['subHeading'], '</th><th>&nbsp;</th></tr>';
		foreach($content['mods'] as $n => $mod) {
			if(!$mod['hidden']) {
				echo '
		<tr><td>', $mod['name'], '<br />', !empty($mod['desc']) ? '<i>'.$mod['desc'].'</i>' : '', '</td><td>';
				switch($n) {
					case 'notes':
					case 'bauschleife':
						echo '<textarea name="', $n, '" rows="4" cols="30"', isset($content['readonly_'.$n]) ? ' readonly="readonly"' : '', '>', $content[$n], '</textarea>';
						break;
					case 'zeit':
					case 'anzahl':
						echo '<input type="text" name="', $n, '" value="', $content[$n], '"', isset($content['readonly_'.$n]) ? ' readonly="readonly"' : '', ' />';
						break;
					case 'account':
					case 'planet':
					case 'forschung':
					case 'schiff':
						echo '<select name="', $n, '" id="', $n, '">';
						foreach($content[$n] as $acc) {
							echo '<option value="', $acc['id'], '"', $acc['selected'] ? ' selected="selected"' : '', '>', $acc['name'], '</option>';
						}
						echo '</select>';
						break;
					case 'use_bauschleife':
						echo '<input type="checkbox" name="use_bauschleife" value="1"', $content['use_bauschleife'] ? ' checked="checked"' : '', ' />';
						break;
					case 'gebaeude':
						echo '<select name="gebaeude" id="gebaeude">';
						foreach($content['gebaeude'] as $geb) {
							echo '<option value="', $geb['id'], '"', $geb['selected'] ? ' selected="selected"' : '', '>', $geb['name'], '</option>';
						}
						echo '</select>&nbsp;<select name="stufe" id="stufen">';
						foreach($content['stufe'] as $s) {
							echo '<option value="', $s['id'], '"', $s['selected'] ? ' selected="selected"' : '', '>', $s['name'], '</option>';
						}
						echo '</select>';
						break;
					case 'angehaengtAn':
						echo '<input type="hidden" name="angehaengtAn" value="', $content['angehaengtAn'], '" />', $content['angehaengtAn'] != 0 ? '<span class="imp">Sitterauftrag mit der Nummer '.$content['angehaengtAn'].'</span>' : '-';
						break;
				}
				echo '</td></tr>';
			}
		}
		echo '
		<tr><td colspan="2"><button onclick="window.location=\'',$content['backLink'],'\';return false;" >Zurück</button><div style="float:right;"><input type="submit" name="submit" value="Absenden" /></div></td></tr>
	</table>';
		foreach($content['mods'] as $n => $mod) {
			if($mod['hidden']) {
				echo '
		<input type="hidden" name="', $mod['name'], '" value="', $content[$mod['name']], '" />';
			}
		}
		echo '
	</form>
</div><script type="text/javascript"><!-- // --><![CDATA[
', isset($content['mods']['planet']) ? '
	function UpdatePlanet() {
		var accSel = getElById("account");
		var igmid = accSel.options[accSel.selectedIndex].value;
		scriptRequest("sitter_planis", "igmid="+igmid, UpdatePlanetCallback);
	}
	function UpdatePlanetCallback(req) {
		FillSelect("planet", req.responseXML);
		'.(isset($content['mods']['gebaeude']) ? 'UpdateGebs();' : '').'
		'.(isset($content['mods']['forschung']) ? 'UpdateForschungen();' : '').'
	}
	OnSelectChanged("account", UpdatePlanet);' : '', '
', isset($content['mods']['gebaeude']) ? '
	function UpdateGebs() {
		var planiSel = getElById("planet");
		var planid = planiSel.options[planiSel.selectedIndex].value;
		var accSel = getElById("account");
		var igmid = accSel.options[accSel.selectedIndex].value;
		scriptRequest("sitter_gebs", "igmid="+igmid+"&planid="+planid, UpdateGebsCallback);
	}
	function UpdateGebsCallback(req) {
		FillSelect("gebaeude", req.responseXML);
		UpdateStufen();
	}
	function UpdateStufen() {
		var planiSel = getElById("planet");
		var planid = planiSel.options[planiSel.selectedIndex].value;
		var gebSel = getElById("gebaeude");
		var gebid = gebSel.options[gebSel.selectedIndex].value;
		scriptRequest("sitter_stufen", "planid="+planid+"&itemid="+gebid, UpdateStufenCallback);
	}
	function UpdateStufenCallback(req) {
		FillSelect("stufen", req.responseXML);
	}
	OnSelectChanged("planet", UpdateGebs);
	OnSelectChanged("gebaeude", UpdateStufen);
' : '', '
', isset($content['mods']['forschung']) ? '
	function UpdateForschungen() {
		var accSel = getElById("account");
		var igmid = accSel.options[accSel.selectedIndex].value;
		scriptRequest("sitter_forschungen", "igmid="+igmid, UpdateForschungenCallback);
	}
	function UpdateForschungenCallback(req) {
		FillSelect("forschung", req.responseXML);
	}' : '', '
// ]]></script>';
		TemplateFooter();
	}
	
	function TemplateSitterHistory() {
		global $content, $scripturl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content">';
		//Sitter für User erledigt
		echo '<table width="99%" cellpadding="0" cellspacing="0" border="0">
				<tr><th colspan="4">Was andere bei dir gemacht haben</th></tr>';
		foreach($content['ownlog'] as $line) {
			echo '
			<tr>
				<td style="font-size:smaller;width:120px;">', $line['time'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['user'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['type'], '</td>
				<td style="font-size:smaller;">', $line['text'], '</td>
			</tr>';
		}
		echo '</table><br />';

		//User für andere erledigt
		echo '<table width="99%" cellpadding="0" cellspacing="0" border="0">';
		echo '<tr><th colspan="4">Was du bei anderen gemacht hast</th></tr>';
		foreach($content['otherlog'] as $line) {
			echo '
			<tr>
				<td style="font-size:smaller;width:120px;">', $line['time'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['igmAccount'], '</td>
				<td style="font-size:smaller;width:120px;">', $line['type'], '</td>
				<td style="font-size:smaller;">', $line['text'], '</td>
			</tr>';
		}
		echo '</table><br />
			<a href="', $scripturl, '/index.php?action=sitter_globalhist">Globale History</a>
		</div>';
		TemplateFooter();
	}
	
	function TemplateSitterGlobalHistory() {
		global $content, $scripturl;
		TemplateHeader();
		TemplateMenu();
		echo '<div class="content"><h2>Globales Sitterlog</h2><table width="99%" cellpadding="0" cellspacing="0" border="0"><th>Toolaccount</th><th>Opfer</th><th>&nbsp;</th><th>Zeit</th><th>&nbsp;</th></tr>';
		foreach($content['log'] as $row) {
			echo '<tr><td>', $row['user'], '</td><td>', $row['victim'], '</td><td>', $row['type'], '</td><td>', $row['time'], '</td><td>', $row['text'], '</td></tr>';
		}
		echo '</table><br /><a href="', $scripturl, '/index.php?action=sitter_history">Lokale History</a></div>';
		TemplateFooter();
	}
	
	function TemplateSitterLogin() {
		global $scripturl, $content, $spiel, $themeurl;
		TemplateHtmlHeader();
		
		echo '<body>
		<script type="text/javascript"><!-- // --><![CDATA[
			var v = {
				"uid": ', $content['id'], ',
				"jid": ', $content['jid'], ',
				"sitter": ', $content['sitter'], '
			};
			function uid_change(uid, name, warning) {
				if(!uid)
					return;
				v.uid = uid;
				v.jid = 0;
				v.sitter=true;
				document.title = "IW - "+name+" - StonedSheep-DB";
				$("iframe", "#iwframe").attr("src", "', $content['loginBase'], '&ID="+uid);
				$(".sitterjob_info:visible").parent().each(function(i, el) {
					$(el).html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />").load($(el).data("url"), {"uid": v.uid, "id": v.jid});
				});
				$(".sitter_log:visible").parent().each(function(i, el) {
					$(el).html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />").load($(el).data("url"), {"uid": v.uid, "id": v.jid});
				});
				$(".uid", ".sitter_ress").each(function(i,el) {$(el).val(v.uid);});
				$(".anz", ".sitter_ress").click();
				$("#loginSelect").val(uid);
				if(warning)
					loginwarning(warning);
			}
			function show_dialog(text, url, ext) {
				var opts = {
					title: text,
					width: 325,
					stack: {group: "*", min: 50},
					dragStart: function(evt, ui) { $("#overlay").css("display", "block"); },
					dragStop: function(evt, ui) { savestate(); $("#overlay").css("display", "none"); },
					resizeStart: function(evt, ui) { $("#overlay").css("display", "block"); },
					resizeStop: function(evt, ui) { savestate(); $("#overlay").css("display", "none"); },
					close: function(evt, ui) { savestate();},
				};
				$.extend(opts, ext);
				$("<div class=\"sitterutil sitterutil_box\"><\/div>")
					.html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />")
					.data("url", url)
					.data("title", text)
					.load(url, {"uid": v.uid, "id": v.jid})
					.dialog(opts);
			}
			function savestate() {
				var state = $.map($(".sitterutil:visible"), function(elem, i) {
					var el = $(elem);
					return {
						pos: el.parent().offset(),
						width: el.parent().width(),
						height: el.parent().height(),
						url: el.data("url"),
						title: el.data("title"),
					};
				});
				$.cookie(v.sitter?"state":"fullstate", JSON.stringify(state), {expires: 7});
			}
			function loadstate() {
				var state = JSON.parse($.cookie(v.sitter?"state":"fullstate"));
				if(!state)
					return;
				var has_sitter=false;
				$.each(state, function() {
					if(this.title=="Sitteraufträge")
						has_sitter=true;
					show_dialog(this.title, this.url, {
						position: [this.pos.left, this.pos.top],
						height: this.height,
						width: this.width,
					});
				});
				if(!has_sitter && v.jid != 0)
					show_dialog("Sitteraufträge", "'.$scripturl.'/index.php?action=sitterutil_jobex", {open: function(evt, ui) { savestate();}});
			}
			function loginwarning(username) {
				$("<div><strong>Achtung:<\/strong> "+($("<div/>").text(username).html())+" hat sich in den letzten 5 Minuten eingeloggt!<\/div>").dialog({modal:true, title:"Loginwarnung", Buttons: { Ok: function() { $(this).dialog("close");}}});
			}
			$(document).ready(function() {
				$("a", "#igmnav").button();
				$("a", "#mnav").click(function(e) {
					e.preventDefault();
					show_dialog(this.text, this.href, {open: function(evt, ui) { savestate();}});
					return false;
				});
				$("#nextLogin").click(function(e) {e.preventDefault();$.get("',$content['jsonLink'],'", {next:1}, function(dta) {uid_change(dta.uid, dta.name, dta.loginwarning);}, "json");});
				$("#idleLogin").click(function(e) {e.preventDefault();$.get("',$content['jsonLink'],'", {idle:1}, function(dta) {uid_change(dta.uid, dta.name, dta.loginwarning);}, "json");});
				$("#loginSelect").change(function() {$.get("',$content['jsonLink'],'", {idinfo:$(this).val()}, function(dta) {uid_change(dta.uid, dta.name, dta.loginwarning);}, "json");});
				loadstate();
				document.title = "IW - ', $content['accName'], ' - StonedSheep-DB";
				', $content['loginWarning'] ? 'loginwarning('.$content['loginLastUser'].')' : '', '
			});
		// ]]></script>
		<div id="overlay"></div>
		<div id="iwframe">
			<iframe src="', $content['loginUrl'], '">Dein Browser unterstützt keine Frames :(</iframe>
		</div>
		<div id="igmnav">
			<div id="lnav">
				<a target="_top" href="'.$content['exitLink'].'">Zur&uuml;ck</a>
			</div>
			<div id="mnav">
				<a href="'.$scripturl.'/index.php?action=sitterutil_jobex">Sitteraufträge</a>
				<a href="'.$scripturl.'/index.php?action=sitterutil_newscan">Scans einlesen</a>
				<a href="'.$scripturl.'/index.php?action=sitterutil_trade">Handel</a>
				<a href="'.$scripturl.'/index.php?action=sitterutil_log">Log</a>
				<a href="'.$scripturl.'/index.php?action=sitterutil_ress">Ress</a>
				<div id="act" title="Wie lange der Account nicht mehr gesittet wurde" class="', $content['actuality_color'], ' .ui-widget-content">
					<span title="'.$content['accountInfo']['typeDesc'].'">'.$content['accountInfo']['type'].'</span>'
					.($content['accountInfo']['iwsa'] ? '&nbsp;<span title="Supporter-Account">IWSA</span>':'')
					.($content['accountInfo']['ikea'] ? '&nbsp;<span title="Ikea-Account">I</span>':'')
					.($content['accountInfo']['mdp'] ? '&nbsp;<span title="Meister der Peitschen-Account">M</span>':''), '
				</div> <select id="loginSelect">';
			foreach($content['userLogins'] as $user) {
				echo '<option value="', $user['value'], '" ', $user['isSelected'] ? 'selected="selected"' : '', '>', $user['name'], '</option>';
			}
			echo '</select>
			</div>
			<div id="rnav">
				<a title="In den nächsten Account mit Leerlauf einloggen" href="#" id="idleLogin">LeerlfAcc</a>
				<a title="In den Account einloggen, der am längsten nicht mehr gesittet wurde" href="#" id="nextLogin">NxtAcc</a>
			</div>
		</div>
	</body>
';
		TemplateHtmlFooter();
	}
	
	function TemplateSitterList() {
		global $content, $scripturl;
		
		TemplateHeader();
		TemplateMenu();
		
		echo '
			<div class="content">
				<table width="99%" cellpadding="0" cellspacing="0" border="0">
					<tr><th>Igmname</th><th title="Zeitpunkt, wann die erste Bauschleife/Forschungsschleife ausläuft">Bau/Forschung bis', HelpLink('sitter_bauschleifen_auslauf'), '</th><th>Forschung</th><th title="Nächste angreifende Flotte / Scan">nächste  feindl. Flotte</th></tr>';
		foreach($content['list'] as $item) {
			echo '<tr class="', $item['actuality'], '"><td><a href="', $item['loginLink'] ,'">[', $item['rawType'] == 'fle' ? '<b>'.$item['igmName'].'</b>' : $item['igmName'], ']</a><br /><i style="font-size:smaller;">',$item['accountTyp'] , '@', $item['squad'], '</i>',
				$item['hasIkea'] ? ', <i style="font-size:smaller;">Ikea</i>' : '',
				$item['hasMdP'] ? ', <i style="font-size:smaller;">MdP</i>' : '',
			 '</td><td>', $item['bauEnde'], '</td><td>', $item['forschung'] ,'</td><td>', $item['angriffAnkunft'], '</td></tr>';
		}
		echo '
				<tr><th colspan="4">Die Farben zeigen, wann zuletzt jemand die Hauptseite eingelesen hat:';
		foreach($content['time_stages'] as $k => $t)
			echo '&nbsp;<span class="act_',$k,'">', $t, '</span>';
			echo '<span class="act_5">+</span></th></tr>
				</table>
			</div>';
		
		TemplateFooter();
	}

	function TemplateSitterUtilJobEx() {
		global $content, $themeurl;
		echo '<script type="text/javascript"><!-- // --><![CDATA[
			$(function() {
				function showmove() {
					$.data(document.body, "job_allow_update", false);
					var jobs = $.data(document.body, "jobs");
					if(jobs.length > 0) {
						var f = $.data(document.body, "job_current");
						$(".sitterjob_info").html(
						"<div class=\"imp\"></div><table>"
							+ "<tr><th>Zeit</th><td><input type=\"text\" name=\"zeit\" value=\""+f.time+"\" /></td></tr>"
							+ "<tr><th>oder Bauschleife</th><td><textarea name=\"bauschleife\" cols=\"24\" rows=\"1\"></textarea></td></tr>"
							+ "<tr><th>Kommentar</th><td><textarea name=\"kommentar\" cols=\"24\" rows=\"1\"></textarea></td></tr>"
							+ "<tr><td colspan=\"2\" align=\"center\"><a href=\"#\" class=\"do_move\">Verschieben!</a><a href=\"#\" class=\"show\">Zurück</a></td></tr>"
						+ "</table>"
						);
						$(".show", ".sitterjob_info").button().click(function(e) {
							e.preventDefault();
							showjob();
							return false;
						});
						$(".do_move", ".sitterjob_info").button().click(function(e) {
							e.preventDefault();
							var util = $(this).parent().parent().parent().parent().parent();
							$(".do_move", ".sitterjob_info").button("disable");
							$(".show", ".sitterjob_info").button("disable");
							$.post(f.url, 
								{jid: f.id, uid: v.uid, move: 1, json: 1, zeit: $("input[name=\"zeit\"]", util).val(), bauschleife: $("textarea[name=\"bauschleife\"]", util).val(), kommentar: $("input[name=\"kommentar\"]", util).val()}, 
								function(resp) {
									if(resp.success) {
										$.data(document.body, "jobs", resp.jobs);
										showjob(resp.msg, false);
									} else {
										$(".imp", util).text(resp.msg);
										$(".do_move", ".sitterjob_info").button("enable");
										$(".show", ".sitterjob_info").button("enable");
									}
								},
								"json"
							);
							return false;
						});
					}
				}
				function showjob(smsg, fmsg) {
					$.data(document.body, "job_allow_update", true);
					var jobs = $.data(document.body, "jobs");
					if(jobs.length > 0) {
						var f = false;
						if($.data(document.body, "job_specific")) {
							f = $.grep(jobs, function(el, i) {return el.id == $.data(document.body, "job_specific");});
							f = !f ? jobs[0] : f[0];
						} else {
							f = jobs[0];
						}
						$.data(document.body, "job_current", f);
						$.data(document.body, "job_specific", false);
						$(".sitterjob_info").html("<div class=\"imp\">"+(fmsg?fmsg:"")+"</div><div class=\"simp\">"+(smsg?smsg:"")+"</div>"
						+ "<table>"
							+ "<tr><th> Zeit:</th><td>"+ f.time +"</td></tr>"
							+ "<tr><th>Planet:</th><td>"+(f.hasPlani ? f.coords : "")+f.planiName+"</td></tr>"
							+ "<tr><th>Auftrag:</th><td>"+f.text+"</td></tr>"
							+ (f.hasFollowUp ? "<tr><th>Bauschleife<br /><i style=\"font-size:smaller\">Strg+a, Strg+c der Bauseite</i></th><td><textarea name=\"bauschleife\"></textarea></td></tr>" : "")
							+ "<tr><td colspan=\"2\" align=\"center\"><a href=\"#\" class=\"done\">Erledigt</a><a href=\"#\" class=\"move\">Verschieben</a></td></tr>"
						+ "</table>");
						$(".done", ".sitterjob_info").button().click(function(e) {
							e.preventDefault();
							var util = $(this).parent().parent().parent().parent().parent();
							$(".done", ".sitterjob_info").button("disable");
							$(".move", ".sitterjob_info").button("disable");
							$.post(f.url,
								{jid: f.id, done: 1, bauschleife: $("input[textarea=\"bauschleife\"]", util).val()},
								function(resp) {
									if(resp.success) {
										$.data(document.body, "jobs", $.grep(v, function(el, i) {return el.id == $.data(document.body, "job_current");}, true));
										showjob(resp.msg, false);
									} else {
										$(".imp", util).text(resp.msg);
										$(".done", ".sitterjob_info").button("enable");
										$(".move", ".sitterjob_info").button("enable");
									}
								},
								"json");
							return false;
						});
						$(".move", ".sitterjob_info").button().click(function(e) {
							e.preventDefault();
							showmove();
							return false;
						});
					} else {
						$(".sitterjob_info").text("Kein Sitterauftrag!");
					}
				}
				if(!$.data(document.body, "job_update")) {
					$.data(document.body, "job_update", true);
					$.data(document.body, "job_allow_update", true);
					function job_update() {
						$.get("', $content['updateUrl'], '", {uid: v.uid}, function(dta) {$.data(document.body, "jobs", dta);}, "json");
						if($.data(document.body, "job_allow_update"))
							showjob();
						window.setTimeout(job_update, 120000);
					}
					window.setTimeout(job_update, 120000);
				}
				$.data(document.body, "jobs", ',EscapeJSU($content['jobs']),');
				$.data(document.body, "job_specific", ', $content['has_specific'] ? $content['specific_job'] : 'false', ');
				showjob();
			});
		// ]]></script>
		<div class="sitterjob_info"><img src="',$themeurl,'/img/load.gif" alt="Loading.." title="Loading.." /></div>
		';
	}
	
	function TemplateSitterUtilNewscan() {
		global $content, $scripturl;
		echo '<script type="text/javascript"><!-- // --><![CDATA[
			$(function() {
				$(".scans", ".sitter_newscan").keyup(function() {
					var txt = $(this).val();
					if(txt.length==0)
						return;
					$(this).val(\'\');
					var util = $(this).parent().parent();
					$.post("', $content['submitUrl'], '",
						{uid: v.uid, scans: txt, abs: 1, next: $(".next:checked", util).val(), idle: $(".idle:checked", util).val()},
						function(resp) {
							if(resp.err)
								$(".imp", ".sitter_newscan").html("<div>"+resp.err+"<div>");
							if(resp.msg)
								$(".simp", ".sitter_newscan").html("<div>"+resp.msg+"<div>");
							if(resp.nextid) {
								var v = resp.nextid;
								uid_change(v.uid, v.name, v.loginwarning);
							}
						},
						"json");
						if($(".next:checked", util).val() || $(".idle:checked", util).val()) {
							$("iframe", "#iwframe").attr("src", "about:blank");
						}
				});
			});
		// ]]></script>
		<div class="sitter_newscan">
			<div class="imp"></div>
			<div><textarea name="scans" class="scans" rows="4" cols="36"></textarea></div>
			<div><label><input type="checkbox" class="next" value="1" title="Den Scan einlesen und danach direkt weiter zu dem am längsten nicht gesitteten Account" />Nächster</label><label><input type="checkbox" class="next" value="1"  title="Den Scan einlesen und danach direkt weiter zum nächsten Account mit Leerlauf!" />Nächster+Leerlauf</label></div>
		</form>
		<div class="simp"></div>
		</div>';
	}
	
	function TemplateSitterUtilTrade() {
		global $content, $themeurl;
		echo '
	<div class="sitter_trade">';
		if($content['hasReq']) {
			echo '
		<script type="text/javascript"><!-- // --><![CDATA[
			$(function() {
				$(".done", ".sitter_trade").button().click(function(e) {
					e.preventDefault();
					$(".sitter_trade")
						.html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />")
						.load("', $content['submitUrl'], '", {fullDone:1, rid: "',$content['req']['id'], '", reqid: "', $content['reqid'], '"});
					return false;
				});
				$(".part_done", ".sitter_trade").button().click(function(e) {
					e.preventDefault();
					var util = $(this).parent().parent().parent().parent().parent().parent();
					$(".sitter_trade")
						.load("', $content['submitUrl'], '", {partDone:1, rid: "',$content['req']['id'], '", reqid: "', $content['reqid'], '", cnt: $(".cnt", util).val()})
						.html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />");
					return false;
				});
				$(".ign", ".sitter_trade").button().click(function(e) {
					e.preventDefault();
					$(".sitter_trade")
						.html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />")
						.load("', $content['submitUrl'], '", {ignore:1, rid: "',$content['req']['id'], '", reqid: "', $content['reqid'], '"});
					return false;
				});
			});
		// ]]></script>
		<form action="', $content['submitUrl'], '" class="frm" method="post">
		<table border="1" width="100%">
<tr><td colspan="2" align="center"><input value="Done" class="done" name="fullDone" type="submit" /> <input type="text" name="cnt" class="cnt" size="6" /> <input type="submit" class="part_done" value="Teilw." name="partDone" /> <input type="submit" name="ignore" class="ign" value="Ignorieren" /></td></tr>
			<tr><th>Zeit</th><td>', $content['req']['time'], '</td></tr>
			<tr><th>Ziel</th><td>', $content['req']['ziel'], ' (bei ', $content['req']['user'], ')</td></tr>
			<tr><th>Priorität</th><td>', $content['req']['priority'], '</td></tr>
			<tr><th>Bedarf</th><td>(', $content['req']['soll'], '-', $content['req']['ist'], '=)', $content['req']['fehl'], 'x ', $content['req']['nameLong'], '</td></tr>
			<tr><th>Kommentar</th><td>', $content['req']['comment'], '</td></tr>
		</table>
		<input type="hidden" name="rid" value="', $content['req']['id'],'" />
		', ReqID(), '
		</form>';
		} else { 
			echo 'Kein weiterer Bedarf vorhanden!';
		}
		echo '
	</div>';
	}

	function TemplateSitterUtilLog() {
		global $content;
		echo '
	<div class="sitter_log">
		<table width="99%" cellpadding="0" cellspacing="0" border="1">';
		foreach($content['log'] as $line) {
			echo '
			<tr>
				<td style="width:120px;">', $line['time'], '</td>
				<td style="width:80px;">', $line['user'], '</td>',
				$line['type'] == 'L' ? '<td style="width:15px;" title="Login">L</td>' : '<td style="width:15px;" title="Auftrag">A</td>',
				'<td align="left">', $line['text'], '</td>
			</tr>';
		}
		echo '</table>
	</div>';
	}	

	function TemplateSitterUtilRess() {
		global $content, $themeurl;
		echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		$(function() {
			$(".anz", ".sitter_ress").button().click(function(e) {
				e.preventDefault();
				var util = $(this).parent().parent().parent();
				$(util)
					.load("', $content['submitUrl'], '", {uid:$(".uid", util).val(), ress:$(".ress", util).val()})
					.html("<img src=\"',$themeurl,'/img/load.gif\" alt=\"Loading..\" title=\"Loading..\" />");
					return false;
			});
		});
	// ]]></script>
	<div class="sitter_ress">
		<form action="', $content['submitUrl'], '" method="post">
			<select class="ress" name="ress">';
		foreach($content['ress'] as $r) {
			echo '
				<option value="', $r['value'], '"', $r['selected'] ? ' selected="selected"' : '', '>', $r['name'], '</option>';
		}
		echo '
			</select>@<select class="uid" name="uid">';
		foreach($content['users'] as $user) {
			echo '
				<option value="', $user['id'], '"', $user['selected'] ? ' selected="selected"' : '', '>', $user['name'], '</option>';
		}
		echo '
			</select>
			<input type="submit" name="submit" class="anz" value="Anz." />
		</form>
		<table width="99%" cellpadding="0" cellspacing="0" border="1"><tr align="center">';
		echo '<th>&nbsp;</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['name'], '<br />(', $line['coords'], ')</td>';
		}
		echo '</tr><tr>';
		echo '<th title="rumfliegende Ress">R</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['ress'], '</td>';
		}
		echo '</tr><tr>';
		echo '<th title="Produktion / h">P</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['prod'], '</td>';
		}
		echo '</tr><tr>';
		echo '<th title="Lagerplatz/-inhalt h&auml;lt noch..">H</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['haelt'], '</td>';
		}
		echo '</tr><tr>';
		echo '<th title="Lagergr&ouml;&szlig;e">L</th>';
		foreach($content['data'] as $line) {
			echo '<td>', $line['lager'], '</td>';
		}
		echo '</tr>
		</table>
	</div>';
	}

	function TemplateFeindlFlottenUebersicht() {
		global $content;
		TemplateHeader();
		TemplateMenu();
		echo '	<script type="text/javascript"><!-- // --><![CDATA[
		var UserRows = new Object();';
		foreach($content['users'] as $user) {
			echo '
			UserRows[', $user['uid'], '] = new Array(';
			foreach($user['planis'] as $p) {
				echo $p['ID'], ', ';
			}
			echo '0);';
		}
		echo '
		function toggleUserRows(uid) {
			var rows = UserRows[uid];
			for(var i = 0; i < rows.length; ++i) {
				var el = rows[i];
				if(el != 0) {
					toggleVisibility(getElById("p_"+el));
				}
			}
		}
	// ]]></script>
	<div class="content"><h2>Übersicht feindliche Flotten</h2>
		<table cellpadding="0" cellspacing="0" border="0"><tr><th colspan="4">Spieler</th><th>Ankunft</th></tr>';
		foreach($content['users'] as $user) {
			echo '
			<tr class="danger_', $user['gefahrenLevel'], '"><td colspan="4"><a href="',$user['loginLink'], '">[', $user['name'], ']</a></td><td>', $user['ersteAnkunft'], '</td></tr>';
			foreach($user['planis'] as $plani) {
				echo '
			<tr id="p_', $plani['ID'], '" class="danger_', $plani['gefahrenLevel'], '">
				<td>&nbsp;</td>
				<td>(', $plani['startkoords'],')', $plani['startname'], '<br /><i>[', $plani['startally'], ']', $plani['startowner'], '</i></td>
				<td>(', $plani['zielkoords'],')', $plani['zielname'], '<br /><i>[', $plani['zielally'], ']', $plani['zielowner'], '</i></td>
				<td>', $plani['bewegung'], '</td>
				<td>', $plani['ankunft'], '</td>
			</tr>';
			}
		}
		echo '<tr><th colspan="5">Farbenlegende: <span class="danger_1">Angriff INC</span></th></tr></table></div>';
		TemplateFooter();
	}
	
	
?>
