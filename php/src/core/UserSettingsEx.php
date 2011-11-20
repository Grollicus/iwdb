<?php

if(!defined('dddfd'))
		exit();

	global $settings_modules;
	$settings_modules = array(
		'id' => array(
			'name' => '',
			'desc' => '',
			'table' => 'users',
			'col' => 'ID',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareID',
			'evaluate' => 'CbEvaluateNoSettings',
		),
		'username' => array (
			'name' => 'Username',
			'desc' => 'Dein Tool-Login',
			'table' => 'users',
			'col' => 'userName',
			'isValid' => 'CbValidateUsername',
			'prepare' => 'CbPrepareUsername',
			'evaluate' => 'CbEvaluateUsername',
		),
		'pw' => array(
			'name' => 'Neues PW',
			'desc' => '',
			'table' => 'users',
			'col' => 'pwmd5',
			'isValid' => 'CbValidatePW',
			'prepare' => 'CbPrepareNoSettings',
			'evaluate' => 'CbEvaluatePW',
		),
		'pw2' => array(
			'name' => 'Neues PW wdh.',
			'desc' => '',
			'table' => 'users',
			'col' => 'pwmd5',
			'isValid' => 'CbValidatePW2',
			'prepare' => 'CbPrepareNoSettings',
			'evaluate' => 'CbEvaluateNoSettings',
		),
		'visibleName' => array(
			'name' => 'Angezeigter Name',
			'desc' => 'Unter diesem Namen werden andere Leute dich im Tool sehen',
			'table' => 'users',
			'col' => 'visibleName',
			'isValid' => 'CbValidateVisibleName',
			'prepare' => 'CbPrepareVisibleName',
			'evaluate' => 'CbEvaluateVisibleName',
		),
		'email' => array(
			'name' => 'E-Mail-Adresse',
			'desc' => 'Diese wird (bisher?) noch nicht genutzt',
			'table' => 'users',
			'col' => 'email',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareEmail',
			'evaluate' => 'CbEvaluateEmail',
		),
		'ipsec' => array(
			'name' => 'IP-Sicherheit',
			'desc' => 'Wenn aktiv, wirst du ausgeloggt wenn die Session zu einer anderen IP-Adresse gehörte',
			'table' => 'users',
			'col' => 'ipsecurity',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareIpSec',
			'evaluate' => 'CbEvaluateIpSec',
		),
		'isAdmin' => array(
			'name' => 'Admin',
			'desc' => 'Ist der Benutzer ein Allytool-Admin?',
			'table' => 'users',
			'col' => 'isAdmin',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareAdmin',
			'evaluate' => 'CbEvaluateAdmin',
		),
		'isRestricted' => array(
			'name' => 'Eingeschr&auml;nkt',
			'desc' => 'Ist der Benutzer ein Allytool-Gast?',
			'table' => 'users',
			'col' => 'isRestricted',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareRestricted',
			'evaluate' => 'CbEvaluateRestricted',
		),
		'sitterskin' => array(
			'name' => 'Sitterskin',
			'desc' => 'Der Skin, der beim Sitten anderer Accounts verwendet wird',
			'table' => 'users',
			'col' => 'sitterskin',
			'isValid' => 'CbValidateSitterSkin',
			'prepare' => 'CbPrepareSitterSkin',
			'evaluate' => 'CbEvaluateSitterSkin',
		),
		'token' => array(
			'name' => 'Token',
			'desc' => 'Damit kannst du den Reminder mit dem Tool verbinden',
			'table' => 'users',
			'col' => 'id',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareToken',
			'evaluate' => 'CbEvaluateNoSettings',
		),
		'currentPW' => array(
			'name' => 'Aktuelles PW',
			'desc' => 'Zur Sicherheit muss bei Änderungen immer das aktuelle PW mit angegeben werden',
			'table' => 'users',
			'col' => 'pwmd5',
			'isValid' => 'CbValidateCurrentPW',
			'prepare' => 'CbPrepareNoSettings',
			'evaluate' => 'CbEvaluateNoSettings',
		),
		
		'igmname' => array (
			'name' => 'Ingame-Account',
			'desc' => 'Für den Sitterlogin',
			'table' => 'igm_data',
			'col' => 'igmname',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareIgmName',
			'evaluate' => 'CbEvaluateIgmName',
		),
		'sitterpw' => array (
			'name' => 'SitterPW',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'sitterpw',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareSitterPW',
			'evaluate' => 'CbEvaluateSitterPW',
		),
		'realpw' => array (
			'name' => 'RealPW',
			'desc' => 'Dieses wird ausschliesslich für den Link links oben in der Ecke verwendet, der dich in IW einloggt.',
			'table' => 'igm_data',
			'col' => 'realpw',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareRealPW',
			'evaluate' => 'CbEvaluateRealPW',
		),
		'accounttyp' => array (
			'name' => 'Accounttyp',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'accounttyp',
			'isValid' => 'CbValidateAccounttyp',
			'prepare' => 'CbPrepareAccounttyp',
			'evaluate' => 'CbEvaluateAccounttyp',
		),
		'squad' => array (
			'name' => 'Squad',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'squad',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareSquad',
			'evaluate' => 'CbEvaluateSquad',
		),
		'ikea' => array (
			'name' => 'Lehrling von Ikea',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'ikea',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareIkea',
			'evaluate' => 'CbEvaluateIkea',
		),
		'mdp' => array (
			'name' => 'Meister der Peitschen',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'mdp',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareMdp',
			'evaluate' => 'CbEvaluateMdp',
		),
		'iwsa' => array(
			'name' => 'IWSA',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'iwsa',
			'isValid' => 'CbValidateNoSettings',
			'prepare' => 'CbPrepareIwsa',
			'evaluate' => 'CbEvaluateIwsa',
		),
		'tsdTrennZeichen' => array (
			'name' => 'Tausendertrennzeichen',
			'desc' => 'das Leerzeichen in 1 000 000',
			'table' => 'igm_data',
			'col' => 'tsdTrennZeichen',
			'isValid' => 'CbValidateTsdTrennz',
			'prepare' => 'CbPrepareTsdTrennz',
			'evaluate' => 'CbEvaluateTsdTrennz',
		),
		'Komma' => array (
			'name' => 'Dezimaltrennzeichen',
			'desc' => 'Das Komma in 0,25',
			'table' => 'igm_data',
			'col' => 'Komma',
			'isValid' => 'CbValidateKomma',
			'prepare' => 'CbPrepareKomma',
			'evaluate' => 'CbEvaluateKomma',
		),
	);


	function UserSettingsEx() {
		global $settings_modules, $pre, $content, $ID_MEMBER, $user, $scripturl;
		
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		$igmid = DBQueryOne("SELECT igmuser FROM {$pre}users WHERE ID=".$id, __FILE__, __LINE__);
		$users_where = 'WHERE ID='.$id;
		$igm_data_where = 'WHERE ID='.$igmid;
		
		if(isset($_REQUEST['submit'])) {
			$valid = true;
			foreach($settings_modules as $mod) {
				$valid &= (call_user_func($mod['isValid']));
			}
			if($valid) {
				
				$set = array('users' => '', 'igm_data' => '');
				foreach($settings_modules as $mod) {
					$set[$mod['table']] .= call_user_func($mod['evaluate']);
				}
				$users_set = substr($set['users'], 0, -2);
				$igm_data_set = substr($set['igm_data'], 0, -2);
				
				
				DBQuery("UPDATE {$pre}users SET {$users_set} {$users_where}", __FILE__, __LINE__);
				DBQuery("UPDATE {$pre}igm_data SET {$igm_data_set} {$igm_data_where}", __FILE__, __LINE__);
				//Updates machen :)
			}
		}
		
		$cols = array();
		foreach($settings_modules as $mod) {
			$cols[$mod['table']][] = $mod['col'];
		}
		foreach($cols as $k => $c)
			$cols[$k] = array_unique($c);
		foreach($cols as $tbl => $columns) {
			switch($tbl) {
				case 'users':
					$where = $users_where;
					break;
				case 'igm_data':
					$where = $igm_data_where;
					break;
				default:
					$where = 'WHERE 0';
					break;
			}
			$col_str = '';
			foreach($columns as $col) {
				$col_str .= $col.', ';
			}
			$col_str = substr($col_str, 0, -2);
			$data[$tbl] = DBQueryOne("SELECT {$col_str} FROM {$pre}{$tbl} {$where}", __FILE__, __LINE__, true);
		}
		
		foreach($settings_modules as $key => $mod) {
			$content['settings'][$key] = array(
				'name' => $mod['name'],
				'desc' => $mod['desc'],
				'data' => $mod['prepare']($data[$mod['table']]),
			);
			
		}
		
		
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
		$content['submiturl'] = $scripturl.'/index.php?action=settingsex';
		
		TemplateInit('main');
		TemplateUserSettingsEx();
	}

	function CbValidateNoSettings() { return true; }
	function CbPrepareNoSettings($dta) {}
	function CbEvaluateNoSettings() {return '';}
	
	
	function CbPrepareID() {
		global $user, $ID_MEMBER;
		return (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
	}
	function CbValidateUsername() {
		global $pre, $content, $user, $ID_MEMBER;
		if(!$user['isAdmin'])
			return true;
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		if(!empty($_REQUEST['userName'])) {
			$content['errors'][] = 'Der Username darf nicht leer sein!';
			return false;
		}
		if(0 < DBQueryOne("SELECT count(*) FROM {$pre}users WHERE ID<>{$id} AND userName LIKE '".EscapeDB(Param('username'))."'", __FILE__, __LINE__)) {
			$content['errors'][] = 'Der Username ist schon vergeben!';
			return false;
		}
		return true;
	}
	function CbPrepareUsername($dta) {
		global $user;
		return array(
			'editable' => $user['isAdmin'],
			'value' => ($user['isAdmin'] && isset($_REQUEST['username'])) ? EscapeO(Param('username')) : EscapeOU($dta['userName'])
		);
		
	}
	function CbEvaluateUsername() {
		global $user;
		if(!$user['isAdmin'] || !isset($_REQUEST['username']))
			return false;
		return "userName='".EscapeDB(Param('username'))."', ";
	}
	function CbValidatePW() {
		global $content;
		$pw = Param('pw');
		if($pw != '' && strlen($pw) < 6) {
			$content['errors'][] = 'Dein neues Passwort ist zu kurz! Bitte wähl was vernünftiges!';
			return false;
		}
		return true;
	}
	function CbEvaluatePW() {
		if(empty($_REQUEST['pw'])) {
			return '';
		}
		return "pwmd5='".md5($_REQUEST['pw'])."', ";
	}
	function CbValidatePW2() {
		if($_REQUEST['pw'] != $_REQUEST['pw2']) {
			$content['errors'][] = 'Die eingegebenen neuen Passwörter stimmen nicht überein!';
			return false;
		}
		return true;
	}
	function CbValidateVisibleName() {
		global $pre, $content, $user, $ID_MEMBER;
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		if(empty($_REQUEST['visibleName'])) {
			$content['errors'][] = 'Der Angezeigte Name darf nicht leer sein!';
			return false;
		}
		if(0 < DBQueryOne("SELECT count(*) FROM {$pre}users WHERE ID<>{$id} AND visibleName LIKE '".EscapeDB(Param('visibleName'))."'", __FILE__, __LINE__)) {
			$content['errors'][] = 'Der Angezeigte Name ist schon vergeben!';
			return false;
		}
		return true;
	}
	function CbPrepareVisibleName($dta) {
		return isset($_REQUEST['visibleName']) ? EscapeO(Param('visibleName')) : EscapeOU($dta['visibleName']);
	}
	function CbEvaluateVisibleName() {
		return "visibleName='".EscapeDB(Param('visibleName'))."', ";
	}
	function CbPrepareEmail($dta) {
		return isset($_REQUEST['email']) ? EscapeO(Param('email')) : EscapeOU($dta['email']);
	}
	function CbEvaluateEmail() {
		return "email='".EscapeDB(Param('email'))."', ";
	}
	function CbPrepareIpSec($dta) {
		return isset($_REQUEST['submit']) ? isset($_REQUEST['ipsec']) : $dta['ipsecurity'];
	}
	function CbEvaluateIpsec() {
		if(isset($_REQUEST['ipsec']))
			return "ipsecurity='1', ";
		return "ipsecurity='0', ";
	}
	function CbPrepareAdmin($dta) {
		global $user;
		return array(
			'editable' => $user['isAdmin'],
			'value' => ($user['isAdmin'] && isset($_REQUEST['submit'])) ? isset($_REQUEST['isAdmin']) : $dta['isAdmin'],
		);
	}
	function CbEvaluateAdmin() {
		global $user;
		if(!$user['isAdmin'])
			return '';
		return "isAdmin=".(isset($_REQUEST['isAdmin']) ? "'1', " : "'0', ");
	}
	function CbPrepareRestricted($dta) {
		global $user;
		return array(
			'editable' => $user['isAdmin'],
			'value' => ($user['isAdmin'] && isset($_REQUEST['submit'])) ? isset($_REQUEST['isRestricted']) : $dta['isRestricted'],
		);
	}
	function CbEvaluateRestricted() {
		global $user;
		if(!$user['isAdmin'])
			return '';
		return "isRestricted=".(isset($_REQUEST['isRestricted']) ? "'1', " : "'0', ");
	}
	function CbValidateSitterSkin() {
		global $content;
		if($_REQUEST['sitterskin'] != 0 && $_REQUEST['sitterskin'] != 3 && $_REQUEST['sitterskin'] != 6) {
			$content['errors'][] = 'WTF-Sitterskin!';
			return false;
		}
		return true;
	}
	function CbPrepareSitterSkin($dta) {
		$selected = isset($_REQUEST['sitterskin']) ? intval($_REQUEST['sitterskin']) : $dta['sitterskin'];
		return array(
			'0' => array('selected' => $selected == 0, 'text' => 'Account-Standard'),
			'3' => array('selected' => $selected == 3, 'text' => 'Textskin'),
			'6' => array('selected' => $selected == 6, 'text' => 'IW-Standard'),
		);
	}
	function CbEvaluateSitterSkin() {
		return "sitterskin='".intval($_REQUEST['sitterskin'])."', ";
	}
	function CbPrepareToken($dta) {
		global $token_seed, $ID_MEMBER, $user;
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		return $id.':'.sha1($token_seed.$id);
	}
	function CbValidateCurrentPW() {
		global $pre, $content, $user, $ID_MEMBER;
		
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		if($user['isAdmin'] && $id != $ID_MEMBER)
			return true;
		if($_REQUEST['pw'] == '')
			return true;
		if(0 == DBQueryOne("SELECT count(*) FROM {$pre}users WHERE ID={$id} AND pwmd5='".md5($_POST['currentPW'])."'", __FILE__, __LINE__)) {
			$content['errors'][] = 'Aus Sicherheitsgründen muss zum Ändern des Passworts auch das aktuelle Passwort angegeben werden!';
			return false;
		}
		return true;
	}
	
	function CbPrepareIgmName($dta) {
		return isset($_REQUEST['igmname']) ? EscapeO(Param('igmname')) : EscapeOU($dta['igmname']);
	}
	function CbEvaluateIgmName() {
		return "igmname='".EscapeDB(Param('igmname'))."', ";
	}
	function CbPrepareSitterPW($dta) {
		return isset($_REQUEST['sitterpw']) ? EscapeO(Param('sitterpw')) : '';
	}
	function CbEvaluateSitterPW() {
		if(empty($_REQUEST['sitterpw']))
			return '';
		return "sitterpw='".EscapeDB(Param('sitterpw'))."', ";
	}
	function CbPrepareRealPW($dta) {
		return isset($_REQUEST['realpw']) ? EscapeO(Param('realpw')) : '';
	}
	function CbEvaluateRealPW() {
		if(empty($_REQUEST['realpw']))
			return '';
		return "realpw='".EscapeDB(Param('realpw'))."', ";
	}
	function CbValidateAccounttyp() {
		global $content;
		$t = Param('accounttyp');
		if($t != 'fle' && $t != 'bud' && $t != 'mon' && $t != 'all') {
			$content['errors'][] = 'WTF-Accounttyp!';
			return false;
		}
		return true;
	}
	function CbPrepareAccounttyp($dta) {
		$selected = isset($_REQUEST['accounttyp']) ? EscapeO(Param('accounttyp')) : EscapeOU($dta['accounttyp']);
		return array(
			'fle' => array('selected' => $selected == 'fle', 'text' => 'Fleeter'),
			'bud' => array('selected' => $selected == 'bud', 'text' => 'Buddler'),
			'mon' => array('selected' => $selected == 'mon', 'text' => 'Monarch'),
			'all' => array('selected' => $selected == 'all', 'text' => 'Allrounder'),
		);
	}
	function CbEvaluateAccounttyp() {
		return "accounttyp='".EscapeDB(Param('accounttyp'))."', ";
	}
	function CbPrepareSquad($dta) {
		return isset($_REQUEST['squad']) ? EscapeO(Param('squad')) : EscapeOU($dta['squad']);
	}
	function CbEvaluateSquad() {
		return "squad='".EscapeDB(Param('squad'))."', ";
	}
	function CbPrepareIkea($dta) {
		return isset($_REQUEST['submit']) ? isset($_REQUEST['ikea']) : $dta['ikea'];
	}
	function CbEvaluateIkea() {
		return "ikea=".(isset($_REQUEST['ikea']) ? "'1', " : "'0', ");
	}
	function CbPrepareMdp($dta) {
		return isset($_REQUEST['submit']) ? isset($_REQUEST['mdp']) : $dta['mdp'];
	}
	function CbEvaluateMdp() {
		return "mdp=".(isset($_REQUEST['mdp']) ? "'1', " : "'0', ");
	}
	function CbPrepareIwsa($dta) {
		return isset($_REQUEST['submit']) ? isset($_REQUEST['iwsa']) : $dta['iwsa'];
	}
	function CbEvaluateIwsa() {
		return "iwsa=".(isset($_REQUEST['iwsa']) ? "'1', " : "'0', ");
	}
	function CbValidateTsdTrennz() {
		global $content;
		$tz = Param('tsdTrennZeichen');
		if(strlen($tz) > 1) {
			$content['errors'][] = 'Dein Tausendertrennzeichen ist zu lang! (evtl Leerzeichen dahinter?)';
			return false;
		}
		return true;
	}
	function CbPrepareTsdTrennz($dta) {
		return isset($_REQUEST['tsdTrennZeichen']) ? EscapeO(Param('tsdTrennZeichen')) : EscapeOU($dta['tsdTrennZeichen']);
	}
	function CbEvaluateTsdTrennz() {
		return "tsdTrennZeichen='".EscapeDB(Param('tsdTrennZeichen'))."', ";
	}
	function CbValidateKomma() {
		global $content;
		$tz = Param('Komma');
		if(strlen($tz) != 1) {
			$content['errors'][] = 'Dein Dezimaltrennzeichen ist nicht genau ein Zeichen! (evtl Leerzeichen dahinter?)';
			return false;
		}
		if($tz == Param('tsdTrennZeichen')) {
			$content['errors'][] = 'Dein Tausender- und Dezimaltrennzeichen sind gleich! Wie soll das Tool das auseinanderhalten?';
			return false;
		}
		return true;
	}
	function CbPrepareKomma($dta) {
		return isset($_REQUEST['Komma']) ? EscapeO(Param('Komma')) : EscapeOU($dta['Komma']);
	}
	function CbEvaluateKomma() {
		return "Komma='".EscapeDB(Param('Komma'))."', ";
	}
?>