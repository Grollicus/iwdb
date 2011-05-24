<?php

if(!defined('dddfd'))
		exit();

	global $filter_modules;
	$settings_modules = array(
		'username' => array (
			'name' => 'Username',
			'desc' => 'Dein Tool-Login',
			'table' => 'users',
			'col' => 'userName',
			'isValid' => 'CbValidateUsername',
			'prepare' => 'CbPrepareUsername',
			'evaluate' => 'CbValidateUsername',
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
			'evaluate' => 'CbEvaluatePW2',
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
		'sitterskin' => array(
			'name' => 'Sitterskin',
			'desc' => 'Der Skin, der beim Sitten anderer Accounts verwendet wird',
			'table' => 'users',
			'col' => 'sitterskin',
			'isValid' => 'CbValidateSitterSkin',
			'prepare' => 'CbPrepareSitterSkin',
			'evaluate' => 'CbEvaluateSitterSkin',
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
			'isValid' => 'CbValidateIgmName',
			'prepare' => 'CbPrepareIgmName',
			'evaluate' => 'CbEvaluateIgmName',
		),
		'sitterpw' => array (
			'name' => 'SitterPW',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'sitterpw',
			'isValid' => 'CbValidateSitterPW',
			'prepare' => 'CbPrepareSitterPW',
			'evaluate' => 'CbEvaluateSitterPW',
		),
		'realpw' => array (
			'name' => 'RealPW',
			'desc' => 'Dieses wird ausschliesslich für den Link links oben in der Ecke verwendet, der dich in IW einloggt.',
			'table' => 'igm_data',
			'col' => 'realpw',
			'isValid' => 'CbValidateRealPW',
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
			'isValid' => 'CbValidateSquad',
			'prepare' => 'CbPrepareSquad',
			'evaluate' => 'CbEvaluateSquad',
		),
		'ikea' => array (
			'name' => 'Lehrling von Ikea',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'ikea',
			'isValid' => 'CbValidateIkea',
			'prepare' => 'CbPrepareIkea',
			'evaluate' => 'CbEvaluateIkea',
		),
		'mdp' => array (
			'name' => 'Meister der Peitschen',
			'desc' => '',
			'table' => 'igm_data',
			'col' => 'mdp',
			'isValid' => 'CbValidateMdp',
			'prepare' => 'CbPrepareMdp',
			'evaluate' => 'CbEvaluateMdp',
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
		global $settings_modules, $pre, $content, $ID_MEMBER, $user;
		
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
				//Updates machen :)
			}
		}
		
		$cols = array();
		foreach($settings_modules as $mod) {
			$cols[$mod['table']][] = $mod['col'];
		}
		array_walk($cols, 'array_ unique');
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
		
		foreach($settings_modules as $mod) {
			$mod['prepare']($data[$mod['table']]);
		}
		
		TemplateInit('main');
		TemplateUserSettingsEx();
	}

	function CbValidateNoSettings() { return true; }
	function CbPrepareNoSettings($dta) {}
	function CbEvaluateNoSettings() {}
	
	function CbValidateUsername() {
		global $pre, $content, $user, $ID_MEMBER;
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		if(!empty($_REQUEST['username'])) {
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
		global $content;
		
		$content['username'] = isset($_REQUEST['username']) ? EscapeO(Param('username')) : $dta['userName'];
		
	}
	function CbEvaluateUsername() {
		//TODO
	}
	function CbValidatePW() {
		global $content;
		$pw = Param('pw');
		if(strlen($pw) < 6) {
			$content['errors'][] = 'Dein neues Passwort ist zu kurz! Bitte wähl was vernünftiges!';
			return false;
		}
		return true;
	}
	function CbEvaluatePW() {//TODO
	}
	function CbValidatePW2() {
		if(Param('pw') != Param('pw2')) {
			$content['errors'][] = 'Die eingegebenen neuen Passwörter stimmen nicht überein!';
			return false;
		}
		return true;
	}
	function CbEvaluatePW2() {//TODO
	}
	function CbValidateVisibleName() {
		global $pre, $content, $user, $ID_MEMBER;
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		if(!empty($_REQUEST['visibleName'])) {
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
		global $content;
		$content['visibleName'] = isset($_REQUEST['visibleName']) ? EscapeO(Param('visibleName')) : $dta['visibleName'];
	}
	function CbPrepareEmail($dta) {
		global $content;
		$content['email'] = isset($_REQUEST['email']) ? EscapeO(Param('email')) : $dta['email'];
	}
	function CbEvaluateEmail() {
		//TODO
	}
	function CbPrepareIpSec($dta) {
		global $content;
		$content['ipsec'] = isset($_REQUEST['submit']) ? isset($_REQUEST['ipsec']) : $dta['ipsecurity'];
	}
	function CbEvaluateIpsec() {
		//TODO
	}
	function CbPrepareAdmin() {
		global $content, $user;
		$content['isAdmin'] = $user['isAdmin'] && isset($_REQUEST['submit']) ? isset($_REQUEST['isAdmin']) :  $dta['isAdmin'];
	}
	function CbEvaluateAdmin() {
		//TODO
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
		global $content;
		$content['sitterskin'] = isset($_REQUEST['sitterskin']) ? intval($_REQUEST['sitterskin']) : $dta['sitterskin'];
	}
	function CbEvaluateSitterSkin() {
		//TODO
	}
	function CbValidateCurrentPW() {
		global $pre, $content, $user, $ID_MEMBER;
		
		$id = (isset($_REQUEST['ID']) && $user['isAdmin']) ? intval($_REQUEST['ID']) : $ID_MEMBER;
		if($user['isAdmin'] && $id != $ID_MEMBER)
			return true;
		if(0 == DBQueryOne("SELECT count(*) FROM {$pre}users WHERE ID={$id} AND pwmd5='".md5($_POST('currentPW'))."'", __FILE__, __LINE__)) {
			$content['errors'][] = 'Gib bitte dein aktuelles Passwort ein, um Änderungen an deinem Account zu machen!';
			return false;
		}
		return true;
	}
	
	//TODO: die ganzen Callbacks von der igm_users-Tabelle!
?>