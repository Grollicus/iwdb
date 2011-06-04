<?php
	if(!defined('dddfd'))
		exit();
	function IrcMask() {
		global $content, $pre, $scripturl, $ID_MEMBER, $user;
		
		$uid = intval($_REQUEST['uid']);
		if($uid != $ID_MEMBER && !$user['isAdmin']) {
			exit();
		}
		
		switch($_GET['sub']) {
			case "new":
				$content['mask'] = '';
				$content['id'] = 0;
				$content['edit'] = false;
				$content['submitUrl'] = $scripturl.'/index.php?action=ircmask&amp;sub=edit&amp;uid='.$uid;
				TemplateInit('main');
				TemplateIrcMaskEdit();
				break;
			case "edit":
				$id = intval($_REQUEST['id']);
				$content['id'] = $id;
				$content['mask'] = str_replace('%', '*', DBQueryOne("SELECT mask FROM {$pre}irc_autologin WHERE id={$id}", __FILE__, __LINE__));
				$content['edit'] = $id != 0;
				$content['submitUrl'] = $scripturl.'/index.php?action=ircmask&amp;sub=edit&amp;uid='.$uid;
				if(isset($_REQUEST['submit'])) {
					$mask = EscapeDB(Param('mask'));
					$content['mask'] = $mask;
					$mask = str_replace('*', '%', $mask);
					if($mask == '%!%@%') {
						$content['msg'] = 'Die Maske ist zu allgemein!';
						TemplateInit('main');
						TemplateIrcMaskEdit();
						return;
					}
					$oldids = DBQueryOne("SELECT id, uid FROM {$pre}irc_autologin WHERE mask='{$mask}'", __FILE__, __LINE__);
					if($oldids !== false && $oldids[0] != $id) {
						$content['msg'] = 'Die Maske ist bereits vergeben!';
						TemplateInit('main');
						TemplateIrcMaskEdit();
						return;
					}
					if($id == 0) {
						$content['msg'] = 'Erfolgreich eingetragen!';
						DBQuery("INSERT INTO {$pre}irc_autologin (uid, access, mask) VALUES ({$uid}, 1, '{$mask}')", __FILE__, __LINE__);
						$content['id'] = mysql_insert_id();
					} else {
						if($oldids[1] != $uid) {
							$content['msg'] = 'Fehler!';
							TemplateInit('main');
							TemplateIrcMaskEdit();
							return;
						}
						$content['msg'] = 'Erfolgreich aktualisiert!';
						DBQuery("UPDATE {$pre}irc_autologin SET mask='{$mask}' WHERE id={$id}", __FILE__, __LINE__);
					}
				}
				TemplateInit('main');
				TemplateIrcMaskEdit();
				break;
			case "del":
				$id = intval($_REQUEST['id']);
				DBQuery("DELETE FROM {$pre}irc_autologin WHERE id={$id}", __FILE__, __LINE__);
				Redirect($scripturl.'/index.php?action=settingsex');
				break;
		}
		
	}
?>
