<?php
/***************************************************************************
*                   This File belongs to the dddfd-Game                    *
* Filename: template.index.php                                             *
* Created: 06.10.2005 10:31:59                                             *
* It is part of the Template "default"                                     *
***************************************************************************/

/*
 * What does this File actually do?
 * It registers some default-versions of functions, that CAN be registered in another template
 * but if they aren`t, they'll be defined here
 * 
 * void TemplateMain()
 * 	*A fallback for the Main part of the template, sometimes printing the content
 * void TemplateMenu()
 * 	*Print a Javascript-Menu
 * void TemplateHeader()
 * 	*Prints the Pageheader (<head>)
 * void TemplateFooter()
 * 	*Prints the Pagefooter showing the runtime for this page
 * void TemplateRess()
 * 	*Prints a Ressheader showing the current state of ressources at the selected town
 */

if (!defined("dddfd"))
	die("Hacking attempt");


if(!function_exists('TemplateMenu')) {
	function TemplateMenu() {
		global $scripturl, $content, $user, $spiel, $uni_presets;
		echo '<div class="menu">
		<table>
			<tr><th><a class="',$content['action'] == 'index' ? 'active' : 'item','" href="', $scripturl, '/?action=index">Index</a></th></tr>
			<tr><td><a class="',$content['action'] == 'newscanex' ? 'active' : 'item','" href="', $scripturl, '/?action=newscanex">Neuer Bericht</a></td></tr>
			', !$user['isRestricted'] ? '<tr><td><a class="'.($content['action'] == 'hs' ? 'active' : 'item').'" href="'.$scripturl.'/?action=hs">Top 5</a></td></tr>' : '','
			<tr><th>Universum</th></tr>
			<tr><td><a class="',$content['action'] == 'uni_map' ? 'active' : 'item','" href="', $scripturl, '/?action=uni_map">Karte</a></td></tr>
			<tr><td><a class="',$content['action'] == 'uni_view' ? 'active' : 'item','" href="', $scripturl, '/?action=uni_view">Suche</a>';
		foreach($uni_presets as $presetname => $preseturl) {
			echo '<br /><a style="font-size:smaller; padding-left:8px;" class="item" href="', $scripturl, '/?action=uni_view&amp;',$preseturl,'">', $presetname, '</a>';
		}
		echo '
			</td></tr>
			<!--tr><td><a class="',$content['action'] == 'uni_whosat' ? 'active' : 'item','" href="', $scripturl, '/?action=uni_whosat">Wer ist in...</a></td></tr>
			<tr><td><a class="',$content['action'] == 'uni_allyat' ? 'active' : 'item','" href="', $scripturl, '/?action=uni_allyat">Wo ist Ally xyz..?</a></td></tr-->
			<tr><td><a class="',$content['action'] == 'uni_allyoverview' ? 'active' : 'item','" href="', $scripturl, '/?action=uni_allyoverview">Ally-Gala-&Uuml;bersicht</a></td></tr>';
		if(!$user['isRestricted'])
		echo '
			<tr><th>Ress</th></tr>
			<tr><td><a class="',$content['action'] == 'trade_list' ? 'active' : 'item','" href="', $scripturl, '/?action=trade_list">Handel</a></td></tr>
			<tr><td><a class="',$content['action'] == 'raids' ? 'active' : 'item','" href="', $scripturl, '/?action=raids">Raids</a></td></tr>
			<tr><td><a class="',$content['action'] == 'ressuserlist' ? 'active' : 'item','" href="', $scripturl, '/?action=ressuserlist">Produktion</a></td></tr>
			<tr><td><a class="',$content['action'] == 'transporte' ? 'active' : 'item','" href="', $scripturl, '/?action=transporte">Bilanz</a></td></tr>';
		echo '
			<tr><th>Sitterzeugs</th></tr>';
		if(!$user['isRestricted'])
		echo '
			<tr><td><a class="',$content['action'] == 'sitter_view' ? 'active' : 'item','" href="', $scripturl, '/?action=sitter_view">Sitterauftr&auml;ge <span id="sitter_job_cnt">', $content['sitter_job_cnt'] > 0 ? ' ('.$content['sitter_job_cnt'].')' : '', '</span></a></td></tr>
			<tr><td><a class="',$content['action'] == 'sitter_list' ? 'active' : 'item','" href="', $scripturl, '/?action=sitter_list">Sitterlogins</a></td></tr>
			<tr><td><a class="',$content['action'] == 'sitter_history' ? 'active' : 'item','" href="', $scripturl, '/?action=sitter_history">History</a></td></tr>';
		echo '
			<tr><td><a class="',$content['action'] == 'sitter_flotten' ? 'active' : 'item','" href="', $scripturl, '/?action=sitter_flotten">Flottenübersicht</a></td></tr>
			
			<tr><th>Krieg</th></tr>
			<tr><td><a class="',$content['action'] == 'war_kbs' ? 'active' : 'item','" href="', $scripturl, '/?action=war_kbs">Kampfberichte</a></td></tr>
			<tr><td><a class="',$content['action'] == 'war_scans' ? 'active' : 'item','" href="', $scripturl, '/?action=war_scans">Scans</a></td></tr>';
		if(!$user['isRestricted'])
		echo '
			<tr><td><a class="',$content['action'] == 'war_schedule' ? 'active' : 'item','" href="', $scripturl, '/?action=war_schedule">Zeitplan</a></td></tr>';
		echo '
			<tr><td><a class="',$content['action'] == 'war_timing' ? 'active' : 'item','" href="', $scripturl, '/?action=war_timing">Timing</a></td></tr>
			<tr><th>Sonstiges</th></tr>
			<tr><td><a class="',$content['action'] == 'techtree' ? 'active' : 'item','" href="', $scripturl, '/?action=techtree">Techtree</a></td></tr>
			<tr><td><a class="',$content['action'] == 'highscore_inactives' ? 'active' : 'item','" href="', $scripturl, '/?action=highscore_inactives">Inaktivensuche</a></td></tr>
			<tr><td><a class="',$content['action'] == 'settingsex' ? 'active' : 'item','" href="', $scripturl, '/?action=settingsex">Einstellungen</a></td></tr>';
		if($user['isAdmin'])
		echo '
				<tr><th>Admin</th></tr>
				<tr><td><a class="',$content['action'] == 'mysql' ? 'active' : 'item','" href="', $scripturl, '/?action=mysql">Mysql-Client</a></td></tr>
				<tr><td><a class="',$content['action'] == 'speedlog' ? 'active' : 'item','" href="', $scripturl, '/?action=speedlog">Speedlog</a></td></tr>
				<tr><td><a class="',$content['action'] == 'errors' ? 'active' : 'item','" href="', $scripturl, '/?action=errors">Fehlerlog</a></td></tr>				
				<tr><td><a class="',$content['action'] == 'useradmin' ? 'active' : 'item','" href="', $scripturl, '/?action=useradmin">Useradmin</a></td></tr>
				<tr><td><a class="',$content['action'] == 'texts' ? 'active' : 'item','" href="', $scripturl, '/?action=texts">Texte bearbeiten</a></td></tr>
				<tr><td><a class="',$content['action'] == 'util' ? 'active' : 'item','" href="', $scripturl, '/?action=util">Utils</a></td></tr>';
echo '		<tr><th>&nbsp;</th></tr>
			<tr><td><a class="item" href="'.$scripturl.'/?action=logout">Logout</a></td></tr>
		</table></div>
		<script type="text/javascript"><!-- // --><![CDATA[
			', $user['isGuest'] || $user['isRestricted'] ?  '' : '
			function timerCallback() {
				AjaxRequest("sitter_cnt");
				window.setTimeout(timerCallback, 120000);
			}
			$(function() {window.setTimeout(timerCallback, 120000)});
			', '
		// ]]></script>
		<div id="content">
';
	}
}


if(!function_exists('TemplateHtmlHeader')) {
	function TemplateHtmlHeader($html_header_add = '') {
		global $themeurl, $user, $scripturl, $content;
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="description" content="StonedSheep - IWDB" />
	<title>StonedSheep-DB</title>
	<link rel="stylesheet" type="text/css" href="'.$themeurl.'/style.css?v=1" />
	<link rel="stylesheet" type="text/css" href="'.$themeurl.'/jquery-ui-1.8.23.custom.css?v=1" />
	<link rel="icon" href="favicon.png" type="image/png" />
	<script type="text/javascript"><!-- // --><![CDATA[
		var themeurl = "', $themeurl, '";
		var scriptinterface = "', $scripturl, '/scriptinterface.php";
		var sid = "', $content['sid'], '";
		function displayHelp(topic) {
			fenster = window.open("', $scripturl, '/index.php?action=help&name="+topic, "Hilfe", "width=400,height=200,resizable=yes");
			fenster.focus();
			return false;
		}
	// ]]></script>
	<script type="text/javascript" src="', $themeurl, '/jquery-1.8.0.min.js?v=1"></script>
	<script type="text/javascript" src="', $themeurl, '/jquery.tablesorter.min.js?v=1"></script>
	<script type="text/javascript" src="', $themeurl, '/jquery.cookie.js?v=1"></script>
	<script type="text/javascript" src="', $themeurl, '/jquery-ui-1.8.23.custom.min.js?v=1"></script>
	<script type="text/javascript" src="', $themeurl, '/moment.min.js?v=1"></script>
	<script type="text/javascript" src="', $themeurl, '/dhtml.js?v=1"></script>', $html_header_add, '
</head>
';
	}
}


if (!function_exists('TemplateHeader'))
{
	function TemplateHeader($html_header_add = '') {
		global $themeurl, $user, $scripturl, $content, $spiel;
		TemplateHtmlHeader($html_header_add);
		echo '
<body class="body"><div class="page">
	<div class="header">
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr class="categoryheader">
				<td style="height: 32px;">
					<span style="font-size: 140%; font-weight: bold;">StonedSheep - ', strtoupper($spiel), 'DB</span>
				</td>
				<td id="dhtml_loading_header" style="text-align:left; padding:10px; font-weight:bold;">
				</td>
			</tr>
			<tr><td class="categoryheader">
				<a href="', $scripturl, '/index.php?action=iwlogin&amp;from=', $content['action'], '">[', strtoupper($spiel), ']</a>&nbsp;&nbsp;
				<a href="/blub/">[Forum]</a>&nbsp;&nbsp;
				<a href="', $scripturl, '/index.php?action=sitter_login&amp;from=', $content['action'], '&amp;id=next" title="Sitterlogin zum nächsten Account">[Sitten]</a>&nbsp;&nbsp;
				<b>User Online:</b> ', implode(', ', $content['users_online']), '
				</td><td class="categoryheader" style="text-align: right;">
				<a href="', $scripturl, '/index.php?action=war_stats">[Kriegsstats]</a>
				<a href="', $scripturl, '/index.php?action=kbformat">[KBFormat]</a>
				<a href="', $scripturl, '/index.php?action=help_page">[FAQ]</a>
			</td></tr>
		</table>
	</div>'; 
	}
}

if(!function_exists('TemplateHtmlFooter')) {
	function TemplateHtmlFooter() {
		echo '
</html>';
	}
}

	
if (!function_exists('TemplateFooter'))
{
	function TemplateFooter()
	{
		global $starttime, $sql_log, $content;
		echo '
	<div class="footer">';
		if($content['debug_mode'] >= 1) {
			echo '
		<hr style="margin-top:20px;" />
		It is now ', FormatDate(time()), ' (', time(), ')<br />
		Needed '.number_format(microtime(true)-$starttime,3).' secs for this useless stuff<br />
		Browser: ', $_SERVER['HTTP_USER_AGENT'];
			if($content['debug_mode'] >= 2) {
				echo '<br /><a href="javascript:toggleVisibility(getElById(\'footer_sql\'));">SQL</a>';
			}
			echo '<div style="display:none;width:95%;border:solid 2px blue;" id="footer_sql">';
			foreach($sql_log as $q) {
				echo "\n<hr />\n".$q;
			}
			echo '</div>';
			if($content['debug_mode'] >= 2) {
				echo '<br /><a href="javascript:toggleVisibility(getElById(\'footer_req\'));">Req</a><hr /><span style="display:none;" id="footer_req">';
				ob_start();
				var_dump($_REQUEST);
				$ob = ob_get_clean();
				//echo str_replace(array("<pre class='xdebug-var-dump' dir='ltr'>", "</pre>"), array('<span style="font-family: courier, monospace;">', "</span>"), $ob);
				echo EscapeOU($ob);
				echo '</span>';

				echo '<br /><a href="javascript:toggleVisibility(getElById(\'footer_content\'));">Content</a><hr /><span style="display:none;" id="footer_content">';
				ob_start();
				var_dump($content);
				$ob = ob_get_clean();
				//echo str_replace(array("<pre class='xdebug-var-dump' dir='ltr'>", "</pre>"), array('<span style="font-family: courier, monospace;">', "</span>"), $ob);
				echo EscapeOU($ob);
				echo '</span>';				
			}
		}
		echo '
	</div>
</div>
</div>
</body>
';
	TemplateHtmlFooter();
	}
}

if(!function_exists('HelpLink'))
{
	function HelpLink($topic) {
		global $scripturl;
		return ' <sup><a href="'.$scripturl.'/index.php?action=help&amp;name='.$topic.'" target="_blank" onclick="return displayHelp(\''. $topic. '\');">(?)</a></sup>';
	}
}

if(!function_exists('ReqIDPost'))
{
	function ReqID() {
		global $content;
		return ' <input type="hidden" name="reqid" value="'. $content['reqid']. '" />';
	}
}
?>
