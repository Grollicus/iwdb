<?php
/***************************************************************************
*                   This File belongs to the dddfd-Game                    *
* Filename: template.login.php                                             *
* Created: 06.10.2005 22:21:38                                             *
* Stuff about Login & Logout                                               *
***************************************************************************/


if (!defined("dddfd"))
	die("Hacking attempt");
	
function Template_Login()
{
	global $content, $scripturl;
	TemplateHtmlHeader();
	//Ok, they want a login-box. So they'll get one.
	
	echo '<body>
	<form action="', $content['submitUrl'] ,'" method="post">
		<table>
			', isset($content['message']) ? ('<tr><td colspan="2"><b>'.$content['message'].'</b></td></tr>') : '' ,'
			<tr><td>Username</td><td><input type="text" name="login_user" value="', $content['user'], '" /></td></tr>
			<tr><td>Passwort</td><td><input type="password" name="login_pass" value="', $content['pw'], '" /></td></tr>
			<tr><td colspan="2" align="center"><input type="submit" name="login" value="Abschicken" /></td></tr> 
		</table>';
	foreach($content['postdata'] as $name => $value) {
		echo '<input type="hidden" name="', $name, '" value="', $value, '" />';
	}
	echo '
	</form></body>';
	TemplateHtmlFooter();
}
?>