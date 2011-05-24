<?php
	if(!defined('dddfd'))
		exit();

	function UserSettings() {
		global $ID_MEMBER, $pre, $user, $content;
		
		if(isset($_REQUEST['ID']) && $user['isAdmin'])
			$id = intval($_REQUEST['ID']);
		else
			$id = $ID_MEMBER;
		if(isset($_REQUEST['submit'])) {
			if($_REQUEST['part'] == 'tool') {
				if($ID_MEMBER == $id) { //pwcheck erforderlich?
					$count = DBQueryOne("SELECT count(*) FROM {$pre}users WHERE ID={$ID_MEMBER} AND pwmd5='".md5($_REQUEST['pwcheck'])."'", __FILE__, __LINE__);
					if($count == 0) {
						$content['msg'] = 'Gib bitte dein aktuelles Passwort ein, um Änderungen an deinem Account zu machen!';
						ShowUserSettingsForm();
						StopExecution();
					}
				}
				$cols = '';
				if($user['isAdmin']) {
					$cols .= ", userName='".EscapeDB(Param('userName'))."'";
					$cols .= ", isAdmin=".(isset($_REQUEST['isAdmin']) ? '1' : '0');
				}
				if(!empty($_REQUEST['newpw'])) {
					if($_REQUEST['newpw'] != $_REQUEST['newpw2'])
						$content['msg'] = 'Die eingegebenen neuen Passw&ouml;rter stimmen nicht &uuml;berein!';
					else
						$cols .= ", pwmd5='".md5($_REQUEST['newpw'])."'";
				}
	
				DBQuery("UPDATE {$pre}users SET
						visibleName='".EscapeDB(Param('visibleName'))."',
						email='".EscapeDB(Param('email'))."',
						ipsecurity='".(isset($_REQUEST['ipsecurity']) ? 1 : 0)."',
						sitteripchange='".(isset($_REQUEST['sitteripchange']) ? 1 : 0)."',
						sitterskin=".intval($_REQUEST['sitterskin'])."
						{$cols}
					WHERE ID={$id}", __FILE__, __LINE__);
				$content['gmsg'] = 'Daten erfolgreich ge&auml;ndert!';
			} else {
				$igmid = DBQueryOne("SELECT igmuser FROM {$pre}users WHERE ID=$id", __FILE__, __LINE__);
				$regx = '#[()\\\\/\[\]0-9]#';
				if(Param('komma') == Param('tsdtrennz') || preg_match($regx, Param('komma')) > 0 || preg_match($regx, Param('tsdtrennz')) > 0) {
					$content['igmmsg'] = 'Dezimal- oder Tausendertrennzeichen fehlerhaft gewählt! Darf nicht: 0-9()\\/[]';
					ShowUserSettingsForm();
					StopExecution();
				}
				//TODO: (,),\,/,[,],1,2,3,4,5,6,7,8,9 nicht als Tausendertrennzeichen/Komma zulassen + Komma != TSDtz testen
				DBQuery("UPDATE {$pre}igm_data SET 
					igmname='".EscapeDB(Param('igmname'))."',
					sitterpw='".EscapeDB(Param('sitterpw'))."',
					realpw='".EscapeDB(Param('realpw'))."',
					accounttyp='".EscapeDB(Param('accounttyp'))."',
					squad='".EscapeDB(Param('squad'))."',
					ikea=". (isset($_REQUEST['ikea']) ? '1' : '0') .",
					mdp=". (isset($_REQUEST['mdp']) ? '1' : '0') .",
					Komma='".EscapeDB(Param('komma'))."',
					tsdTrennZeichen='".EscapeDB(Param('tsdtrennz'))."'
				WHERE id={$igmid}", __FILE__, __LINE__);
				$content['igmgmsg'] = 'Daten erfolgreich ge&auml;ndert!';
			}
		}
		ShowUserSettingsForm();
	}
	
	//TODO: Themeauswahl
	function ShowUserSettingsForm() {
		global $user, $pre, $ID_MEMBER, $content, $scripturl;
		
		if(isset($_REQUEST['ID']) && $user['isAdmin'])
			$id = intval($_REQUEST['ID']);
		else
			$id = $ID_MEMBER;
		
		$content['id'] = $id;
		$content['pwcheck'] = ($id == $ID_MEMBER);
		$content['settings'] = DBQueryOne("SELECT userName, visibleName, email, ipsecurity, sitteripchange, isAdmin, sitterskin, igmuser
FROM {$pre}users AS users
WHERE users.ID={$id}", __FILE__, __LINE__, true);
		$content['sitterskins'] = array (
			0 => 'Account-Standard',
			3 => 'Text-Skin',
			6 => 'IW-Standard',
		);
		$igmid = $content['settings']['igmuser'];
		$igm = DBQueryOne("SELECT igmname, sitterpw, realpw, ikea, mdp, Komma, tsdTrennZeichen, accounttyp, squad FROM {$pre}igm_data where id={$igmid}", __FILE__, __LINE__);
		$content['igm']['igmname'] = EscapeOU($igm[0]);
		$content['igm']['sitterpw'] = EscapeOU($igm[1]);
		$content['igm']['realpw'] = EscapeOU($igm[2]);
		$content['igm']['hasIkea'] = ($igm[3] == 1);
		$content['igm']['hasMdP'] = ($igm[4] == 1);
		$content['igm']['komma'] = EscapeOU($igm[5]);
		$content['igm']['tsdtrennz'] = EscapeOU($igm[6]);
		$content['igm']['accounttyp'] = array(
				'fle' => array('desc' => 'Fleeter', 'selected' => $igm[7]=='fle'),
				'bud' => array('desc' => 'Buddler', 'selected' => $igm[7]=='bud'),
				'mon' => array('desc' => 'Monarch', 'selected' => $igm[7]=='mon'),
				'all' => array('desc' => 'Allrounder', 'selected' => $igm[7]=='all'),
		);
		$content['igm']['squad'] = EscapeOU($igm[8]);
		
		$q = DBQuery("SELECT id, mask FROM {$pre}irc_autologin WHERE uid={$id}", __FILE__, __LINE__);
		$content['ircAutoLogin'] = array();
		while($row = mysql_fetch_row($q)) {
			$content['ircAutoLogin'][] = array(
				'ID' => $row[0],
				'editLink' => $scripturl.'/index.php?action=ircmask&amp;sub=edit&amp;id='.$row[0].'&amp;uid='.$id,
				'delLink' => $scripturl.'/index.php?action=ircmask&amp;sub=del&amp;id='.$row[0].'&amp;uid='.$id,
				'mask' => str_replace('%', '*', EscapeOU($row[1])),
			);
		}
		$content['newIrcAutoLoginLink'] = $scripturl.'/index.php?action=ircmask&amp;sub=new&amp;uid='.$id;
		
		if(isset($_REQUEST['submit'])) {
			if($_REQUEST['part'] == 'tool') {
				if($user['isAdmin']) {
					$content['settings']['userName'] = 		EscapeO(Param('userName'));
					$content['settings']['isAdmin'] = 		isset($_REQUEST['isAdmin']) ? 1 : 0;
				}
				$content['settings']['visibleName'] = 	EscapeO(Param('visibleName'));
				$content['settings']['email'] = 		EscapeO(Param('email'));
				$content['settings']['ipsecurity'] = 	isset($_REQUEST['ipsecurity']) ? 1 : 0;
				$content['settings']['sitteripchange'] =isset($_REQUEST['sitteripchange']) ? 1 : 0;
				$content['settings']['sitterskin'] = 	intval($_REQUEST['sitterskin']);
			} else {
				$content['igm']['igmname'] = 		EscapeO(Param('igmname'));
				$content['igm']['sitterpw'] = 		EscapeO(Param('sitterpw'));
				$content['igm']['realpw'] = 		EscapeO(Param('realpw'));
				$content['img']['hasIkea'] = 		isset($_REQUEST['ikea']);
				$content['img']['hasMdP'] = 		isset($_REQUEST['mdp']);
				$content['igm']['komma'] = 			EscapeO(Param('komma'));
				$content['igm']['tsdtrennz'] = 		EscapeO(Param('tsdtrennz'));
				
				foreach($content['igm']['accounttyp'] as $k => $v) 
					$content['igm']['accounttyp'][$k]['selected'] = false;
				$content['igm']['accounttyp'][EscapeO(Param('accounttyp'))]['selected'] = true;
				$content['igm']['squad'] = EscapeO(Param('squad'));
				
			}
		}
		TemplateInit('main');
		TemplateSettings();
	}
?>
