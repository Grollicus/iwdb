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
		echo '
		<div id="menu">
		<ul>
			<li><a href="', $scripturl, '/?action=index">Index</a></li>
			<li><a href="', $scripturl, '/?action=newscanex">Neuer Bericht</a></li>
			', !$user['isRestricted'] ? '<li><a class="'.($content['action'] == 'hs' ? 'active' : 'item').'" href="'.$scripturl.'/?action=hs">Top 5</a></li>' : '','
			
			<li>Universum
				<ul>
					<li><a href="', $scripturl, '/?action=uni_map">Karte</a></li>
					<li><a href="', $scripturl, '/?action=uni_view">Suche</a>
				<ul>';
		foreach($uni_presets as $presetname => $preseturl) {
			echo '<li><a style="font-size:smaller;" class="item" href="', $scripturl, '/?action=uni_view&amp;',$preseturl,'">', $presetname, '</a></li>';
		}
		echo '</ul></li>
				<li><a href="', $scripturl, '/?action=uni_allyoverview">Ally-Galas</a></li>
			</ul></li>';
		if(!$user['isRestricted'])
		echo '
			<li>Ress
				<ul>
					<li><a href="', $scripturl, '/?action=trade_list">Handel</a></li>
					<li><a href="', $scripturl, '/?action=raids">Raids</a></li>
					<li><a href="', $scripturl, '/?action=ressuserlist">Produktion</a></li>
					<li><a href="', $scripturl, '/?action=transporte">Bilanz</a></li>
			</ul></li>';
		echo '
			<li>Sitter
				<ul>';
		if(!$user['isRestricted'])
		echo '
				<li><a href="', $scripturl, '/?action=sitter_view">Aufträge <span id="sitter_job_cnt">', $content['sitter_job_cnt'] > 0 ? ' ('.$content['sitter_job_cnt'].')' : '', '</span></a></li>
				<li><a href="', $scripturl, '/?action=sitter_list">Logins</a></li>
				<li><a href="', $scripturl, '/?action=sitter_own">Meine Auftr&auml;ge</a></li>
				<li><a href="', $scripturl, '/?action=sitter_history">History</a></li>';
		echo '
				<li><a href="', $scripturl, '/?action=sitter_flotten">Flotten</a></li>
			</ul></li>
			<li>Krieg
				<ul>
					<li><a href="', $scripturl, '/?action=war_kbs">Kampfberichte</a></li>
					<li><a href="', $scripturl, '/?action=war_scans">Scans</a></li>';
		if(!$user['isRestricted'])
		echo '
					<li><a href="', $scripturl, '/?action=war_schedule">Zeitplan</a></li>';
		echo '
				</ul></li>
			<li>Sonstiges
				<ul>
					<li><a href="', $scripturl, '/?action=techtree">Techtree</a></li>
					<li><a href="', $scripturl, '/?action=highscore_inactives">Inaktivensuche</a></li>
					<li><a href="', $scripturl, '/?action=settingsex">Einstellungen</a></li>
				</ul></li>';
		if($user['isAdmin'])
			echo '
				<li>Admin
					<ul>
						<li><a href="', $scripturl, '/?action=mysql">Mysql-Client</a></li>
						<li><a href="', $scripturl, '/?action=speedlog">Speedlog</a></li>
						<li><a href="', $scripturl, '/?action=errors">Fehlerlog</a></li>				
						<li><a href="', $scripturl, '/?action=useradmin">Useradmin</a></li>
						<li><a href="', $scripturl, '/?action=texts">Texte bearbeiten</a></li>
						<li><a href="', $scripturl, '/?action=util">Utils</a></li>
					</ul></li>';
echo '	</ul>
		</div>
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
	<link rel="stylesheet" type="text/css" href="'.$themeurl.'/style.css" />
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
		', $user['isGuest'] || $user['isRestricted'] ?  '' : '
		function timerCallback() {
			AjaxRequest("sitter_cnt");
			window.setTimeout(timerCallback, 120000);
		}
		window.setTimeout(timerCallback, 120000);
		', '
	// ]]></script>
	<script type="text/javascript" src="', $themeurl, '/jquery-1.8.0.min.js"></script>
	<script type="text/javascript" src="', $themeurl, '/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="', $themeurl, '/dhtml.js"></script>', $html_header_add, '
</head>
';
	}
}


if (!function_exists('TemplateHeader'))
{
	function TemplateHeader($html_header_add = '')
	{
		global $themeurl, $user, $scripturl, $content, $spiel;
		TemplateHtmlHeader($html_header_add);
		echo '
<body class="body"><div class="page">
	<div id="header">
		<h1>StonedSheep - ', strtoupper($spiel), 'DB</h1>
		<span id="dhtml_loading_header" style="text-align:right; right:0px; padding:10px; font-weight:bold;"></span>
	</div>
	<div id="nav">
		<div id="navl">
			<a href="', $scripturl, '/index.php?action=iwlogin&amp;from=', $content['action'], '">[', strtoupper($spiel), ']</a>&nbsp;&nbsp;
			<a href="/blub/">[Forum]</a>&nbsp;&nbsp;
			<a href="', $scripturl, '/index.php?action=sitter_login&amp;from=', $content['action'], '&amp;id=next" title="Sitterlogin zum nächsten Account">[Sitten]</a>&nbsp;&nbsp;
		</div>
		<div id="navr">
			<a href="', $scripturl, '/index.php?action=kbformat">[KBFormat]</a>&nbsp;&nbsp;
			<a href="', $scripturl, '/index.php?action=help_page">[FAQ]</a>&nbsp;&nbsp;
			<a href="'.$scripturl.'/?action=logout">[Logout]</a>
		</div>
	</div>
	<div id="usersonline">', implode(', ', $content['users_online']), '</div>
	<div id="main">'; 
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
	</div>
	<div id="footer">';
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
				echo str_replace(array("<pre class='xdebug-var-dump' dir='ltr'>", "</pre>"), array('<span style="font-family: courier, monospace;">', "</span>"), $ob);
				echo '</span>';

				echo '<br /><a href="javascript:toggleVisibility(getElById(\'footer_content\'));">Content</a><hr /><span style="display:none;" id="footer_content">';
				ob_start();
				var_dump($content);
				$ob = ob_get_clean();
				echo str_replace(array("<pre class='xdebug-var-dump' dir='ltr'>", "</pre>"), array('<span style="font-family: courier, monospace;">', "</span>"), $ob);
				echo '</span>';				
			}
		}
		echo '
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
