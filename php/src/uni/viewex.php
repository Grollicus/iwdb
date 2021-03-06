<?php
//TODO: Schiffe vernünftig ausgeben
	if(!defined('dddfd'))
		exit();
	
	global $filter_modules;
	$filter_modules = array(
		'gala' => array(
			'title' => 'Gala',
			'desc' => '',
			'prepare' => 'GalaPrepare',
			'genFilter' => 'GalaGenFilter',
			'mods_req' => array('coords'),
		),
		'sys' => array(
			'title' => 'System',
			'desc' => '',
			'prepare' => 'SysPrepare',
			'genFilter' => 'SysGenFilter',
			'mods_req' => array('coords'),
		),
		'pla' => array(
			'title' => 'Planet',
			'desc' => '',
			'prepare' => 'PlaPrepare',
			'genFilter' => 'PlaGenFilter',
			'mods_req' => array('coords'),
		),
		'spieler' => array(
			'title' => 'Spielername',
			'desc' => '_ steht für ein unbekanntes Zeichen, % für mehrere',
			'prepare' => 'SpielerPrepare',
			'genFilter' => 'SpielerGenFilter',
			'mods_req' => array('coords', 'owner'),
		),
		'tag' => array(
			'title' => 'Allytag',
			'desc' => 'Ohne [], _ steht für ein unbekanntes Zeichen, % für mehrere',
			'prepare' => 'TagPrepare',
			'genFilter' => 'TagGenFilter',
			'mods_req' => array('coords', 'owner'),
		),
		'planityp' => array(
			'title' => 'Planityp',
			'desc' => 'Mehrfachauswahl mit [Strg]',
			'prepare' => 'PlanitypPrepare',
			'genFilter' => 'PlanitypGenFilter',
			'mods_req' => array('coords', 'types'),
		),
		'objekttyp' => array(
			'title' => 'Objekttyp',
			'desc' => 'Mehrfachauswahl mit [Strg]',
			'prepare' => 'ObjekttypPrepare',
			'genFilter' => 'ObjekttypGenFilter',
			'mods_req' => array('coords', 'types'),
		),
		'planiname' => array(
			'title' => 'Planiname',
			'desc' => '_ steht für ein unbekanntes Zeichen, % für mehrere',
			'prepare' => 'PlaninamePrepare',
			'genFilter' => 'PlaninameGenFilter',
			'mods_req' => array('coords', 'planiname'),
		),
		'geo_ch' => array(
			'title' => 'Chemie',
			'desc' => '',
			'prepare' => 'GeoChPrepare',
			'genFilter' => 'GeoChGenFilter',
			'mods_req' => array('coords', 'geo_ch'),
		),
		'geo_fe' => array(
			'title' => 'Eisen',
			'desc' => '',
			'prepare' => 'GeoFePrepare',
			'genFilter' => 'GeoFeGenFilter',
			'mods_req' => array('coords', 'geo_fe'),
		),
		'geo_ei' => array(
			'title' => 'Eis',
			'desc' => '',
			'prepare' => 'GeoEiPrepare',
			'genFilter' => 'GeoEiGenFilter',
			'mods_req' => array('coords', 'geo_ei'),
		),
		'geo_gravi' => array(
			'title' => 'Gravitation',
			'desc' => '',
			'prepare' => 'GeoGraviPrepare',
			'genFilter' => 'GeoGraviGenFilter',
			'mods_req' => array('coords', 'geo_gravilb'),
		),
		'geo_lb' => array(
			'title' => 'Lebensbedingungen',
			'desc' => '',
			'prepare' => 'GeoLbPrepare',
			'genFilter' => 'GeoLbGenFilter',
			'mods_req' => array('coords', 'geo_gravilb'),
		),
		'geo_bes' => array(
			'title' => 'Besonderheiten',
			'desc' => 'Mehrfachauswahl mit [Strg]',
			'prepare' => 'GeoBesPrepare',
			'genFilter' => 'GeoBesGenFilter',
			'mods_req' => array('coords', 'important_specials'),
		),
		'geo_fmod' => array(
			'title' => 'Forschungskosten',
			'desc' => '',
			'prepare' => 'GeoFModPrepare',
			'genFilter' => 'GeoFModGenFilter',
			'mods_req' => array('coords', 'geo_mods'),
		),
		'geo_gebd' => array(
			'title' => 'Gebäudedauer',
			'desc' => '',
			'prepare' => 'GeoGebDPrepare',
			'genFilter' => 'GeoGebDGenFilter',
			'mods_req' => array('coords', 'geo_mods'),
		),
		'geo_gebk' => array(
			'title' => 'Gebäudekosten',
			'desc' => '',
			'prepare' => 'GeoGebKPrepare',
			'genFilter' => 'GeoGebKGenFilter',
			'mods_req' => array('coords', 'geo_mods'),
		),
		'geo_schd' => array(
			'title' => 'Schiffsdauer',
			'desc' => '',
			'prepare' => 'GeoSchDPrepare',
			'genFilter' => 'GeoSchDGenFilter',
			'mods_req' => array('coords', 'geo_mods'),
		),
		'geo_schk' => array(
			'title' => 'Schiffskosten',
			'desc' => '',
			'prepare' => 'GeoSchKPrepare',
			'genFilter' => 'GeoSchKGenFilter',
			'mods_req' => array('coords', 'geo_mods'),
		),
		'scan_geb' => array(
			'title' => 'Gebäude vorhanden',
			'desc' => '_ steht für ein unbekanntes Zeichen, % für mehrere',
			'prepare' => 'ScansGebPrepare',
			'genFilter' => 'ScansGebGenFilter',
			'mods_req' => array('coords',),
		),
		'geo_gesprengt' => array(
			'title' => 'Gesprengt',
			'desc' => 'Nur gesprengte Planis anzeigen',
			'prepare' => 'GeoGesprengtPrepare',
			'genFilter' => 'GeoGesprengtGenFilter',
			'mods_req' => array('coords', 'geo_ttl'),
		),
		'raw_sql' => array(
			'title' => 'raw SQL',
			'desc' => 'für Besonderen Kram',
			'prepare' => 'RawSqlPrepare',
			'genFilter' => 'RawSqlGenFilter',
			'mods_req' => array('coords'),
		),
	);
	
	function ViewFilteredUniEx() {
		ViewFilteredUniverseEx(array_diff_key($_REQUEST, $_COOKIE), true);
	}
	function ViewFilteredUniverseEx($request, $do_show) {
		global $content, $scripturl, $filter_modules;
		
		$data = array();
		foreach($filter_modules as $name => $mod) {
			$data[] = array(
				'name' => $name, 
				'title' => $mod['title'], 
				'desc' => $mod['desc'], 
				'data' => $mod['prepare']($request)
			);
		}
		$content['filter'] = $data;
		
		$cond = '';
		$mods_req = array();
		foreach($filter_modules as $mod) {
			$filter = $mod['genFilter']($request);
			if(!empty($filter)) {
				$cond .= $filter.' AND ';
				foreach($mod['mods_req'] as $req) {
					$mods_req[$req] = true;
				}
			}
		}
		if(!empty($cond))
			$cond = substr($cond, 0, -5);
		
		$modules = array(
			'coords' => array('desc' => 'Koordinaten', 'selected' => false),
			'owner' => array('desc' => 'Besitzer (Name und Ally)', 'selected' => false),
			'types' => array('desc' => 'Plani- und Objekttyp', 'selected' => false),
			'planiname' => array('desc' => 'Planiname', 'selected' => false),
			'important_specials' => array('desc' => 'Wichtige Besonderheiten', 'selected' => false),
			'geo_fe' => array('desc' => 'Eisendichte', 'selected' => false),
			'geo_ch' => array('desc' => 'Chemiedichte', 'selected' => false),
			'geo_ei' => array('desc' => 'Eisdichte', 'selected' => false),
			'geo_tt' => array('desc' => 'Techteam-Werte', 'selected' => false),
			'geo_gravilb' => array('desc' => 'Gravitation/Lebensbed.', 'selected' => false),
			'geo_ttl' => array('desc' => 'Zeit bis zur Sprengung', 'selected' => false),
			'geo_mods' => array('desc' => 'Dauer-, Kosten- und Forschungsmod.', 'selected' => false),
			'scan_gebs' => array('desc' => 'Gebäudeliste', 'selected' => false),
			'scan_schiffe' => array('desc' => 'Schiffsliste', 'selected' => false),
		);
		
		foreach($mods_req as $col) {
			if(isset($modules[$col]))
				$modules[$col]['selected'] = true;
		}
		$spalten = isset($request['spalten']) ? $request['spalten'] : array('coords', 'important_specials', 'owner', 'planiname', 'types','geo_fe','geo_ch','geo_ei','geo_gravilb','geo_ttl','geo_mods');
		foreach($spalten as $col) {
			if(isset($modules[$col])) {
				$modules[$col]['selected'] = true;
				$mods_req[$col] = true;
			}
		}
		$active_mods = array_keys($mods_req);
		$content['modules'] = $modules;
		
		$sortings = array(
			'ally' => array('desc' => 'Allytag', 'selected' => false),
			'coords' => array('desc' => 'Koordinaten', 'selected' => false),
			'geo_ch' => array('desc' => 'Chemie', 'selected' => false),
			'geo_ei' => array('desc' => 'Eisdichte', 'selected' => false),
			'geo_fe' => array('desc' => 'Eisen', 'selected' => false),
			'geo_gravi' => array('desc' => 'Gravitation', 'selected' => false),
			'geo_lb' => array('desc' => 'Lebensbedingungen', 'selected' => false),
			'geo_ttl' => array('desc' => 'Geoscan-TTL', 'selected' => false),
			'objecttype' => array('desc' => 'Objekttyp', 'selected' => false),
			'owner' => array('desc' => 'Spielername', 'selected' => false),
			'planityp' => array('desc' => 'Planityp', 'selected' => false),
		);
		
		$sortby = array();
		$sort = array();
		if(!empty($request['sortby'])) {
			foreach($request['sortby'] as $num => $s) {
				$name = EscapeOU($s);
				$num = intval($num);
				$ord = $request['orders'][$num];
				$sortby[$name] = $ord;
				
				$sort[$num] = array('items' => $sortings, 'asc' => $ord == 0);
				$sort[$num]['items'][$name]['selected'] = true;
			}
		} else {
			$sort[0] = array('items' => $sortings, 'asc' => true);
			$sort[0]['items']['coords']['selected'] = true;
		}
		$content['sort'] = $sort;
		
		$limit = isset($request['limit']) ? intval($request['limit']) : 0;
		$req = array();
		foreach($request as $k => $v) {
			if(is_array($v))
				$k .= '[]';
			$k = EscapeOU($k);
			if(!isset($req[$k]))
				$req[$k] = array();
			if(is_array($v)) {
				foreach($v as $d) {
					$req[$k][] = EscapeOU($d);
				}
			} else {
				$req[$k][] = EscapeOU($v);
			}
		}
		$req['limit'] = array($limit >= 50 ? $limit - 50 : 0);
		$prev_link = $scripturl.'/index.php?'.ImplodeReq($req);
		$req['limit'] = array($limit + 50);
		$next_link = $scripturl.'/index.php?'.ImplodeReq($req);
		$req['limit'] = array($limit);
		unset($req['sortby[]']);
		unset($req['orders[]']);
		$current_link = $scripturl.'/index.php?'.ImplodeReq($req);
		$content['hasResults'] = $do_show;

		if($do_show) {
			ViewEx($active_mods, $cond, $sortby, $limit, $current_link, $next_link, $prev_link);
		}
		$content['submitUrl'] = $scripturl. '/?action=uni_view';
		TemplateInit('uniex');
		TemplateViewFilteredUniverseEx();
	}
	
	function ImplodeReq($req) {
		$str = '';
		foreach($req as $k => $v) {
			foreach($v as $d) {
				$str .= $k.'='.$d.'&amp;';
			}
		}
		return substr($str, 0, -5);
	}
	
	function GalaPrepare($request) {
		if(isset($request['gala'])) {
			return array('min' => EscapeOU($request['gala']), 'max' => EscapeOU($request['gala']));
		} else {
			return array(
				'min' => isset($request['gala_min']) ? EscapeOU($request['gala_min']) : '',
				'max' => isset($request['gala_max']) ? EscapeOU($request['gala_max']) : '',
			);
		}
	}
	
	function SysPrepare($request) {
	if(isset($request['sys'])) {
			return array('min' => EscapeOU($request['sys']), 'max' => EscapeOU($request['sys']));
		} else {
			return array(
				'min' => isset($request['sys_min']) ? EscapeOU($request['sys_min']) : '',
				'max' => isset($request['sys_max']) ? EscapeOU($request['sys_max']) : '',
			);
		}
	}
	function PlaPrepare($request) {
		if(isset($request['pla'])) {
			return array('min' => EscapeOU($request['pla']), 'max' => EscapeOU($request['pla']));
		} else {
			return array(
				'min' => isset($request['pla_min']) ? EscapeOU($request['pla_min']) : '',
				'max' => isset($request['pla_max']) ? EscapeOU($request['pla_max']) : '',
			);
		}
	}
	function SpielerPrepare($request) {
		return isset($request['spieler']) ? EscapeO(Param('spieler', $request)) : '';
	}
	function TagPrepare($request) {
		return isset($request['tag']) ? EscapeO(Param('tag', $request)) : '';
	}
	function PlanitypPrepare($request) {
		$ret = array(
			'Steinklumpen' => false,
			'Gasgigant' => false,
			'Asteroid' => false,
			'Eisplanet' => false,
		);
		if(isset($request['planityp'])) {
			foreach($request['planityp'] as $v) {
				if(isset($ret[$v]))
					$ret[$v] = true;
			}
		}
		return $ret;
	}
	function ObjekttypPrepare($request) {
		$ret = array(
			'---' => false,
			'Kolonie' => false,
			'Sammelbasis' => false,
			'Kampfbasis' => false,
			'Artefaktbasis' => false,
		);
		if(isset($request['objekttyp'])) {
			foreach($request['objekttyp'] as $v) {
				if(isset($ret[$v]))
					$ret[$v] = true;
			}
		}
		return $ret;
	}
	function PlaninamePrepare($request) {
		return isset($request['planiname']) ? EscapeO(Param('planiname', $request)) : '';
	}
	function GeoChPrepare($request) {
		return array(
			'min' => isset($request['geo_ch_min']) && $request['geo_ch_min'] != '' ? floatval($request['geo_ch_min']) : '',
			'max' => isset($request['geo_ch_max']) && $request['geo_ch_max'] != '' ? floatval($request['geo_ch_max']) : '',
		);
	}
	function GeoBesPrepare($request) {
		global $pre;
		$ret = array('0' => array('value' => '0', 'name' => 'Keine Besonderheiten', 'selected' => false));
		
		$q = DBQuery("SELECT ID, name FROM {$pre}besonderheiten", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q))
			 $ret[$row[0]] = array('value' => $row[0], 'name' => EscapeOU($row[1]), 'selected' => false);
		$ret['n_0'] = array('value' => 'n_0', 'name' => 'Kein Nebel', 'selected' => false);
		$q = DBQuery("SELECT ID, name FROM {$pre}nebel", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q))
			$ret['n_'.$row[0]] = array('value' => 'n_'.$row[0], 'name' => EscapeOU($row[1]), 'selected' => false);
		if(isset($request['geo_bes'])) {
			foreach($request['geo_bes'] as $v) {
				if(isset($ret[$v]))
					$ret[$v]['selected'] = true;
			}
		}
		return $ret;
	}
	function GeoFePrepare($request) {
		return array(
			'min' => isset($request['geo_fe_min']) && $request['geo_fe_min'] != '' ? floatval($request['geo_fe_min']) : '',
			'max' => isset($request['geo_fe_max']) && $request['geo_fe_max'] != '' ? floatval($request['geo_fe_max']) : '',
		);
	}
	function GeoEiPrepare($request) {
		return array(
			'min' => isset($request['geo_ei_min']) && $request['geo_ei_min'] != '' ? floatval($request['geo_ei_min']) : '',
			'max' => isset($request['geo_ei_max']) && $request['geo_ei_max'] != '' ? floatval($request['geo_ei_max']) : '',
		);
	}
		function GeoGraviPrepare($request) {
		return array(
			'min' => isset($request['geo_gravi_min']) && $request['geo_gravi_min'] != '' ? floatval($request['geo_gravi_min']) : '',
			'max' => isset($request['geo_gravi_max']) && $request['geo_gravi_max'] != '' ? floatval($request['geo_gravi_max']) : '',
		);
	}
	function GeoLbPrepare($request) {
		return array(
			'min' => isset($request['geo_lb_min']) && $request['geo_lb_min'] != '' ? floatval($request['geo_lb_min']) : '',
			'max' => isset($request['geo_lb_max']) && $request['geo_lb_max'] != '' ? floatval($request['geo_lb_max']) : '',
		);
	}
	function GeoFModPrepare($request) {
		return array(
			'min' => isset($request['geo_fmod_min']) && $request['geo_fmod_min'] != '' ? floatval($request['geo_fmod_min']) : '',
			'max' => isset($request['geo_fmod_max']) && $request['geo_fmod_max'] != '' ? floatval($request['geo_fmod_max']) : '',
		);
	}
	function GeoGebDPrepare($request) {
		return array(
			'min' => isset($request['geo_gebd_min']) && $request['geo_gebd_min'] != '' ? floatval($request['geo_gebd_min']) : '',
			'max' => isset($request['geo_gebd_max']) && $request['geo_gebd_max'] != '' ? floatval($request['geo_gebd_max']) : '',
		);
	}
	function GeoGebKPrepare($request) {
		return array(
			'min' => isset($request['geo_gebk_min']) && $request['geo_gebk_min'] != '' ? floatval($request['geo_gebk_min']) : '',
			'max' => isset($request['geo_gebk_max']) && $request['geo_gebk_max'] != '' ? floatval($request['geo_gebk_max']) : '',
		);
	}
	function GeoSchDPrepare($request) {
		return array(
			'min' => isset($request['geo_schd_min']) && $request['geo_schd_min'] != '' ? floatval($request['geo_schd_min']) : '',
			'max' => isset($request['geo_schd_max']) && $request['geo_schd_max'] != '' ? floatval($request['geo_schd_max']) : '',
		);
	}
	function GeoSchKPrepare($request) {
		return array(
			'min' => isset($request['geo_schk_min']) && $request['geo_schk_min'] != '' ? floatval($request['geo_schk_min']) : '',
			'max' => isset($request['geo_schk_max']) && $request['geo_schk_max'] != '' ? floatval($request['geo_schk_max']) : '',
		);
	}
	function ScansGebPrepare($request) {
		return array (
			'name' => isset($request['scan_geb']) ? EscapeO(Param('scan_geb', $request)) : '',
			'cnt' => isset($request['scan_geb_cnt']) ? EscapeO(Param('scan_geb_cnt', $request)) : '',
			'cmp' => array(
				array('value' => 0, 'desc' => '>=', 'selected' => isset($request['scan_geb_cmp']) && $request['scan_geb_cmp'] == '0'),
				array('value' => 1, 'desc' => '<=', 'selected' => isset($request['scan_geb_cmp']) && $request['scan_geb_cmp'] == '1'),
			)
		);
	}
	function GeoGesprengtPrepare($request) {
		return isset($request['geo_gesprengt']);
	}
	function RawSqlPrepare($request) {
		return array(
			'sql' => isset($request['raw_sql']) ? EscapeO(Param('raw_sql', $request)) : '',
			'hash' => isset($request['raw_sql_hash']) ? EscapeO(Param('raw_sql_hash', $request)) : '',
		);
	}
	
	function IntValueFilter($col, $min, $max, $req) {
		if(!empty($req[$min])) {
			if(!empty($req[$max])) {
				return "$col BETWEEN ".intval($req[$min]).' AND '.intval($req[$max]);
			} else {
				return "$col >= ".intval($req[$min]);
			}
		} else {
			if(!empty($request[$max])) {
				return "$col <= ".intval($req[$max]);
			}
		}
		return '';
	}
	function FloatValueFilter($col, $fact, $min, $max, $req) {
	if(isset($req[$min]) && $req[$min] != '') {
			if(isset($req[$max]) && $req[$max] != '') {
				return "$col BETWEEN ".(floatval($req[$min])*$fact).' AND '.(floatval($req[$max])*$fact);
			} else {
				return "$col >= ".(floatval($req[$min])*$fact);
			}
		} else {
			if(isset($req[$max]) && $req[$max] != '') {
				return "$col <= ".(floatval($req[$max])*$fact);
			}
		}
		return '';
	}
	function StringLikeFilter($col, $name, $req) {
		if(!empty($req[$name])) {
			return "$col LIKE '".EscapeDB(Param($name, $req))."'";
		}
		return '';
	}
	
	function GalaGenFilter($request) {
		return IntValueFilter('uni.gala', 'gala_min', 'gala_max', $request);
	}
	function SysGenFilter($req) {
		return IntValueFilter('uni.sys', 'sys_min', 'sys_max', $req);
	}
	function PlaGenFilter($req) {
		return IntValueFilter('uni.pla', 'pla_min', 'pla_max', $req);
	}
	function SpielerGenFilter($req) {
		return StringLikeFilter('uni.ownername', 'spieler', $req);
	}
	function TagGenFilter($req) {
		return StringLikeFilter('userdata.allytag', 'tag', $req);
	}
	function PlanitypGenFilter($req) {
		if(empty($req['planityp']))
			return '';
		$ret = 'uni.planityp IN (';
		foreach($req['planityp'] as $v) {
			$ret .= "'".EscapeDB($v)."',";
		}
		return substr($ret, 0, -1).')';
	}
	function ObjekttypGenFilter($req) {
		if(empty($req['objekttyp']))
			return '';
		$ret = 'uni.objekttyp IN (';
		foreach($req['objekttyp'] as $v) {
			$ret .= "'".EscapeDB($v)."',";
		}
		return substr($ret, 0, -1).')';
	}
	function PlaninameGenFilter($req) {
		return StringLikeFilter('uni.planiname', 'planiname', $req);
	}
	function GeoBesGenFilter($req) {
		if(!isset($req['geo_bes']))
			return '';
		$bes = 0;
		$n = array();
		$no_bes = false;
		foreach($req['geo_bes'] as $v) {
			if(is_numeric($v)) {
				if($v == 0)
					$no_bes = true;
				else
					$bes |= intval($v);
			} else {
				$v = substr($v, 2);
				$n[] = intval($v);
			}
		}
		$ret = '';
		if($bes != 0||$no_bes) {
			$ret = ' AND (';
			if($bes != 0)
				$ret .= 'geoscans.besonderheiten & '.$bes.' <> 0 OR ';
			if($no_bes)
				$ret .= 'geoscans.besonderheiten = 0 OR ';
			$ret = substr($ret, 0, -4). ')';
		}
		if(!empty($n))
			$ret .= ' AND geoscans.nebel IN ('.implode(',', $n).')';
		return substr($ret, 5);
	}
	function GeoChGenFilter($req) {
		return FloatValueFilter('geoscans.chemie', 10, 'geo_ch_min', 'geo_ch_max', $req);
	}
	function GeoFeGenFilter($req) {
		return FloatValueFilter('geoscans.eisen', 10, 'geo_fe_min', 'geo_fe_max', $req);
	}
	function GeoEiGenFilter($req) {
		return FloatValueFilter('geoscans.eis', 10, 'geo_ei_min', 'geo_ei_max', $req);
	}
	function GeoGraviGenFilter($req) {
		return FloatValueFilter('geoscans.gravi', 100, 'geo_gravi_min', 'geo_gravi_max', $req);
	}
	function GeoLbGenFilter($req) {
		return FloatValueFilter('geoscans.lbed', 10, 'geo_lb_min', 'geo_lb_max', $req);
	}
	function GeoFModGenFilter($req) {
		return FloatValueFilter('geoscans.fmod', 100, 'geo_fmod_min', 'geo_fmod_max', $req);
	}
	function GeoGebDGenFilter($req) {
		return FloatValueFilter('geoscans.gebtimemod', 100, 'geo_gebd_min', 'geo_gebd_max', $req);
	}
	function GeoGebKGenFilter($req) {
		return FloatValueFilter('geoscans.gebmod', 100, 'geo_gebk_min', 'geo_gebk_max', $req);
	}
	function GeoSchDGenFilter($req) {
		return FloatValueFilter('geoscans.shiptimemod', 100, 'geo_schd_min', 'geo_schd_max', $req);
	}
	function GeoSchKGenFilter($req) {
		return FloatValueFilter('geoscans.shipmod', 100, 'geo_schk_min', 'geo_schk_max', $req);
	}
	function ScansGebGenFilter($req) {
		global $pre;
		//return StringLikeFilter('gebs.name', 'scan_geb', $req);
		if(empty($req['scan_geb']))
			return '';
		$cnt = 0;
		if(!empty($req['scan_geb_cnt']))
			$cnt = intval(Param('scan_geb_cnt', $req));
		$gebIDs = DBQueryOne("SELECT GROUP_CONCAT(ID SEPARATOR',') FROM {$pre}techtree_items WHERE type='geb' AND name LIKE '%".EscapeDB(Param('scan_geb'))."%'", __FILE__, __LINE__);
		if(!isset($req['scan_geb_cmp']) || $req['scan_geb_cmp'] == '0') { //>=
			if(empty($gebIDs))
				return '1=0';
			return "EXISTS (SELECT * FROM ({$pre}lastest_scans AS geb_filter_ls LEFT JOIN {$pre}scans_gebs AS geb_filter_gebs ON geb_filter_ls.scanid=geb_filter_gebs.scanid) WHERE geb_filter_ls.planid = uni.ID AND geb_filter_ls.typ='geb' AND geb_filter_gebs.anzahl >= {$cnt} AND geb_filter_gebs.gebid IN ({$gebIDs}))";
		} else {
			if(empty($gebIDs))
				return '';
			return "EXISTS (SELECT * FROM ({$pre}lastest_scans AS geb_filter_ls LEFT JOIN {$pre}scans_gebs AS geb_filter_gebs ON geb_filter_ls.scanid=geb_filter_gebs.scanid AND geb_filter_gebs.gebid IN ({$gebIDs})) WHERE geb_filter_ls.planid = uni.ID AND geb_filter_ls.typ='geb' AND (geb_filter_gebs.anzahl <= {$cnt} OR geb_filter_gebs.anzahl IS NULL))";
		}
	}
	function GeoGesprengtGenFilter($req) {
		return isset($req['geo_gesprengt']) ? "(geoscans.reset<=".(time()-86400).")" : "";
	}
	
	function RawSqlGenFilter($req) {
		global $uni_secret;
		if(empty($req['raw_sql']) || empty($req['raw_sql_hash']) || hash('sha256', Param('raw_sql', $req).$uni_secret) !== Param('raw_sql_hash', $req))
			return '';
		return Param('raw_sql', $req);
	}
	
	//$active_mods = array (keys of $modules)
	//$where_condition = string to filter results (SQL) - without WHERE
	//$order = array('columy by which to sort' => >0 if DESC)
	//$limit_min = int lower bound, upper will be $limit_mit+50
	//$current_link = string complete link to the current page - without order
	//$next_link = string complete link to next page
	//previous_link = string complete link to previous page
	function ViewEx(array $active_mods, $where_condition, array $order, $limit_min, $current_link = '', $next_link = '', $previous_link = '') {
		//Uni anzeigen beruhend auf Datenmodulen, gefiltert durch Filtermodule
		//Datenmodul:	-benötigte Tabellen (in der Reihenfolge so dass von uni ausgehend gejoint werden kann) mit einem Verknüpfungstyp
		//				-benötigte Spalten 
		//				-stellt Anzeigedaten bereit, die dann von einem Anzeigemodul bearbeitet werden
		//	Verknüpfungstyp: LEFT JOIN oder INNER JOIN, wobei Inner Vorrang hat vor Left
		//Anzeigemodul: -im Template definiert
		//				-stellt Anzeigedaten dar
		//Filtermodul:	-stellt eine Bedingung dar, die angezeigte Datensätze erfüllen müssen
		//				-rekursiv (todo: realisierung überlegen^^)
		
		global $db_host, $db_user_uni, $db_pass_uni, $db_name, $pre, $content, $debug, $sql_log, $unicolor_stages;
		
		//$tables = array(name => table, name => table, ..)
		//$table = array('name' => [table name in db], 'cond' => join condition),
		
		$tables = array(
			'uni' => array(
				'name' => "{$pre}universum",
				'link' => array(),
			),
			'userdata' => array(
				'name' => "{$pre}uni_userdata",
				'cond' => 'uni.ownername = userdata.name',
			),
			'geoscans' => array(
				'name' => "{$pre}geoscans",
				'cond' => 'uni.ID = geoscans.ID',
			),
			'lastest_geb_scan' => array(
				'name' => "{$pre}lastest_scans",
				'cond' => "uni.ID = lastest_geb_scan.planid AND lastest_geb_scan.typ='geb'",
			),
			'geb_scan' => array(
				'name' => "{$pre}scans",
				'cond' => 'lastest_geb_scan.scanid=geb_scan.id',
			),
			'scan_gebs' => array(
				'name' => "{$pre}scans_gebs",
				'cond' => 'geb_scan.id=scan_gebs.scanid',
			),
			'lastest_schiff_scan' => array(
				'name' => "{$pre}lastest_scans",
				'cond' => "uni.ID = lastest_schiff_scan.planid AND lastest_schiff_scan.typ='schiff'",
			),
			'schiff_scan' => array(
				'name' => "{$pre}scans",
				'cond' => 'lastest_schiff_scan.scanid=schiff_scan.id',
			),
			'scan_flotten' => array(
				'name' => "{$pre}scans_flotten",
				'cond' => 'schiff_scan.id=scan_flotten.scanid',
			),
		);
		
		// $modules = array('modulename' => module, ..);
		// $module = array('cols' => array(col, col, col, ..), 'tables' => array(table, table, table, ..), 'cb' => 'Callback to format', 'titles' => array(name => title, name => title, name => title, ..);
		// $table = array('name', jointyp)
		// $jointyp = 0, wenn left join, 1, wenn inner
		// $title = array('Title, Column will be hidden if empty', 'Description, shown on mouseover', priority - the more, the more right, 'sort - none if empty'),
		$modules = array(
			'coords' => array(
				'cols' => array('uni.gala', 'uni.sys', 'uni.pla', 'uni.inserttime', 'uni.planityp'),
				'tables' => array(array('uni', 0)),
				'cb' => 'ModCoordsCb',
				'titles' => array('coords' => array('Koords', 'Koordinaten', 0, 'coords')),
			),
			'geo_ch' => array(
				'cols' => array('geoscans.chemie', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset', 'geoscans.gebtimemod'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoChCb',
				'titles' => array('geo_ch' => array('rChem%', 'reale Chemiedichte', 11, 'geo_ch')),
			),
			'geo_ei' => array(
				'cols' => array('geoscans.eis', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset', 'geoscans.gebtimemod'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoEiCb',
				'titles' => array('geo_ei' => array('rEis%', 'reale Eisdichte', 12, 'geo_ei')),
			),
			'geo_fe' => array(
				'cols' => array('geoscans.eisen', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset', 'geoscans.gebtimemod'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoFeCb',
				'titles' => array('geo_fe' => array('rEisen%', 'reale Eisendichte', 10, 'geo_fe')),
			),
			'geo_gravilb' => array(
				'cols' => array('geoscans.gravi', 'geoscans.lbed', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoGraviLbCb',
				'titles' => array('geo_gravi' => array('Gravi', 'Gravitation', 14, 'geo_gravi'), 'geo_lb' => array('Lbed', 'Lebensbedingungen', 13, 'geo_lb')),
			),
			'geo_mods' => array(
				'cols' => array('geoscans.fmod','geoscans.gebmod','geoscans.gebtimemod','geoscans.shipmod','geoscans.shiptimemod', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoModsCb',
				'titles' => array('geo_fmod' => array('Fmod', 'Forschungsmodifikator', 18), 'geo_gmod' => array('GebK', 'GebäudeKostenmodifikator', 19), 'geo_gtmod' => array('GebZ', 'GebäudeBauzeitmodifikator', 20), 'geo_smod' => array('SchK', 'SchiffsKostenmodifikator', 21), 'geo_stmod' => array('SchD', 'SchiffsDauermodifikator', 22)),
			),
			'geo_tt' => array(
				'cols' => array('geoscans.tt_eisen', 'geoscans.tt_chemie', 'geoscans.tt_eis', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset', 'geoscans.gebtimemod'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoTechTeamCb',
				'titles' => array('geo_ttfe' => array('TT Eisen', 'reale Eisendichte mit TechTeam', 15), 'geo_ttch' => array('TT Chem', 'reale Chemiedichte mit TechTeam', 16), 'geo_ttei' => array('TT Eis', 'reale Eisdichte mit TechTeam', 17))
			),
			'geo_ttl' => array(
				'cols' => array('geoscans.timestamp AS geotime', 'geoscans.reset AS georeset'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModGeoTTLCb',
				'titles' => array('geo_ttl' => array('TTL', 'Zeit bis der Plani gesprengt wird in Tagen', 23, 'geo_ttl'))
			),
			'owner' => array(
				'cols' => array('uni.ownername', 'userdata.allytag'),
				'tables' => array(array('uni', 0), array('userdata', 0)),
				'cb' => 'ModOwnerCb',
				'titles' => array('owner' => array('Besitzer', 'Besitzername', 2, 'owner'), 'tag' => array('Ally', 'Allianztag', 3, 'ally')),
			),
			'planiname' => array(
				'cols' => array('uni.planiname'),
				'tables' => array(array('uni', 0)),
				'cb' => 'ModPlaninameCb',
				'titles' => array('name' => array('Name', 'Planetenname', 8)),
			),
			'types' => array(
				'cols' => array('uni.planityp', 'uni.objekttyp'),
				'tables' => array(array('uni', 0)),
				'cb' => 'ModTypesCb',
				'titles' => array('planityp' => array('Planityp', 'Planetentyp', 6, 'planityp'), 'objekttyp' => array('Objekttyp', 'Objekttyp', 7, 'objecttype')),
			),
			'scan_gebs' => array(
				'cols' => array("geb_scan.time AS gebScanTime, geb_scan.fe AS geb_fe, geb_scan.st AS geb_st, geb_scan.vv AS geb_vv, geb_scan.ch AS geb_ch, geb_scan.ei AS geb_ei, geb_scan.wa AS geb_wa, geb_scan.en AS geb_en, (SELECT GROUP_CONCAT(gebs.anzahl, '|', geb_items.name SEPARATOR '/') FROM {$pre}scans_gebs AS gebs LEFT JOIN {$pre}techtree_items AS geb_items ON gebs.gebid=geb_items.ID WHERE gebs.scanid=geb_scan.id) AS scan_gebs"),
				'tables' => array(array('uni', 0), array('lastest_geb_scan', 0), array('geb_scan', 0)),
				'cb' => 'ModScanGebsCb',
				'titles' => array('scan_gebs' => array('', '', 101)),
				'group' => 'uni.id',
			),
			'scan_schiffe' => array(
				'cols' => array("schiff_scan.time AS schiffScanTime, schiff_scan.fe AS schiff_fe, schiff_scan.st AS schiff_st, schiff_scan.vv AS schiff_vv, schiff_scan.ch AS schiff_ch, schiff_scan.ei AS schiff_ei, schiff_scan.wa AS schiff_wa, schiff_scan.en AS schiff_en, GROUP_CONCAT(scan_flotten.owner, '||', scan_flotten.typ, '||', (SELECT GROUP_CONCAT(schiffe.anz, ',', schiffsnamen.name SEPARATOR '|') FROM {$pre}scans_flotten_schiffe AS schiffe INNER JOIN {$pre}techtree_items AS schiffsnamen ON schiffe.schid=schiffsnamen.ID WHERE schiffe.flid=scan_flotten.id GROUP BY scan_flotten.id) SEPARATOR '|||') AS scan_schiffe"),
				'tables' => array(array('uni', 0), array('lastest_schiff_scan', 0), array('schiff_scan', 0), array('scan_flotten', 0)),
				'cb' => 'ModScanSchiffeCb',
				'titles' => array('scan_schiffe' => array('', '', 100)),
				'group' => 'uni.id',
			),
			'important_specials' => array(
				'cols' => array('nebel', 'besonderheiten', 'geoscans.timestamp AS geotime', 'geoscans.reset AS georeset'),
				'tables' => array(array('uni', 0), array('geoscans', 0)),
				'cb' => 'ModImportantSpecialsCb',
				'titles' => array('important_specials' => array('!', 'Wichtige Besonderheiten (A=Asteroidengürtel, G=Gold, I=Instabiler Kern, M=Mond, R=radioaktiv, T=toxisch, P=planetarer Ring)', 1)),
			),
		);
		
		// $orders = array('name' => array( 'tables' => array(required table names), 'orderBy' => 'orderBy-Specification'),..
		$orders = array(
			'ally' => array('tables' => array('uni', 'userdata'), 'orderBy' => 'userdata.allytag'),
			'coords' => array('tables' => array('uni'), 'orderBy' => 'uni.gala, uni.sys, uni.pla'),
			'geo_ch' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.chemie'),
			'geo_ei' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.eis'),
			'geo_fe' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.eisen'),
			'geo_gravi' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.gravi'),
			'geo_lb' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.lbed'),
			'geo_ttl' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'georeset'),
			'objecttype' => array('tables' => array('uni'), 'orderBy' => 'uni.objekttyp'),
			'owner' => array('tables' => array('uni'), 'orderBy' => 'uni.ownername'),
			'planityp' => array('tables' => array('uni'), 'orderBy' => 'uni.planityp'),
			'-ally' => array('tables' => array('uni'), 'orderBy' => 'userdata.allytag DESC'),
			'-coords' => array('tables' => array('uni'), 'orderBy' => 'uni.gala DESC, uni.sys DESC, uni.pla DESC'),
			'-geo_ch' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.chemie DESC'),
			'-geo_ei' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.eis DESC'),
			'-geo_fe' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.eisen DESC'),
			'-geo_gravi' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.gravi DESC'),
			'-geo_lb' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'geoscans.lbed DESC'),
			'-geo_ttl' => array('tables' => array('uni', 'geoscans'), 'orderBy' => 'georeset DESC'),
			'-objecttype' => array('tables' => array('uni'), 'orderBy' => 'uni.objekttyp DESC'),
			'-owner' => array('tables' => array('uni'), 'orderBy' => 'uni.ownername DESC'),
			'-planityp' => array('tables' => array('uni'), 'orderBy' => 'uni.planityp DESC'),
		);
		
		$con = mysql_connect($db_host, $db_user_uni, $db_pass_uni);
		if(!$con || !mysql_select_db($db_name, $con)) {
			LogError(mysql_error(), __FILE__, __LINE__, ERROR_CRITICAL);
		}
		if(mysql_query("SET NAMES utf8", $con) === false) {
			LogError(mysql_error(), __FILE__, __LINE__, ERROR_CRITICAL);
		}
		if(mysql_query("SET group_concat_max_len = 4096", $con) === false) {
			LogError(mysql_error(), __FILE__, __LINE__, ERROR_CRITICAL);
		}
		
		$tables_used = array('uni' => 0);
		$cols = array('uni.ID' => true);
		$titles = array();
		$title_count=0;
		$group = '';
		
		foreach($active_mods as $mod_name) {
			$mod = $modules[$mod_name];
			foreach($mod['tables'] as $mod_tbl) {
				if(isset($tables_used[$mod_tbl[0]]))
					$tables_used[$mod_tbl[0]] += $mod_tbl[1];
				else
					$tables_used[$mod_tbl[0]] = $mod_tbl[1];
			}
			foreach($mod['cols'] as $mod_col) {
				if(!isset($cols[$mod_col])) {
					$cols[$mod_col] = true;	
				}
			}
			foreach($mod['titles'] as $id => $title) {
				$titles[] = array(
					'id' => $id,
					'hidden' => empty($title[0]),
					'title' => $title[0],
					'desc' => $title[1],
					'num' => $title[2],
					'hasLink' => !empty($title[3]),
					'link' => !empty($title[3]) ? $current_link.'&amp;sortby[]='.$title[3].'&amp;orders[]='.((isset($order[$title[3]]) && $order[$title[3]]) <= 0 ? '1' : '0') : '',
					'hasImage' => !empty($title[3]) && isset($order[$title[3]]),
					'image' => (!empty($title[3]) && isset($order[$title[3]]) && $order[$title[3]]) <= 0 ? 'down.png' : 'up.png',
				);
				if(!empty($title[0]))
					$title_count++;
			}
			if(isset($mod['group']))
				$group .= $mod['group'].', ';
		}
		usort($titles, 'titles_cmp_function');
		
		if(!empty($group))
			$group = substr($group, 0, -2);
		
		foreach($order as $o => $d) {
			if($d > 0)
				$o = '-'.$o;
			foreach($orders[$o]['tables'] as $tbl) {
				if(!isset($tables_used[$tbl]))
					$tables_used[$tbl] = 0;
			}
		}
		$qry = "SELECT ";
		foreach(array_keys($cols) as $col) {
			$qry .= $col.', ';
		}
		$qry = substr($qry, 0, -2);
		$qry .= " FROM ";
		$tbls = $tables['uni']['name']." AS uni";
		foreach($tables_used as $tbl => $join_type) {
			if($tbl != 'uni') {
				$tbls = '('.$tbls.($join_type > 0 ? ' INNER' : ' LEFT').' JOIN '.$tables[$tbl]['name']." AS ".$tbl;
				if(!empty($tables[$tbl]['cond']))
					$tbls .= ' ON '.$tables[$tbl]['cond'];
				$tbls .= ')';
			}
		}
		$qry .= $tbls;
		if(!empty($where_condition))
			$qry .= " WHERE ".$where_condition;
		
		if(!empty($group)) {
			$qry .= ' GROUP BY '.$group;
		}
		
		if(empty($order))
			$order = array('coords' => 0);
		$ord = 'ORDER BY ';
		foreach($order as $o => $direction) {
			if($direction > 0) {
				$o = '-'.$o;
			}
			$ord .= $orders[$o]['orderBy'];
			$ord .= ', ';
		}
		$ord = substr($ord, 0, -2);
		
		$qry .= " $ord LIMIT ".$limit_min.",".($limit_min+50);
		
		if($debug >= 1)
			$sql_log[] = EscapeOU($qry);
		$q = mysql_query($qry, $con);
		if($q === false)
			DBError($qry, __FILE__, __LINE__-2, $con);
		$a = array();
		while($row = mysql_fetch_assoc($q)) {
			$data = array('ID' => $row['ID']);
			foreach($active_mods as $mod) {
				$modules[$mod]['cb']($row, $data);
			}
			$a[] = $data;
		}
		
		$color_stages = array();
		foreach($unicolor_stages as $stage => $time) {
			$color_stages[$stage] = FormatDays($time);
		}
		
		$content['uni'] = array(
			'data' => $a,
			'titles' => $titles,
			'columns' => $title_count,
			'hasNextLink' => !empty($next_link),
			'nextLink' => $next_link,
			'hasPrevLink' => !empty($previous_link),
			'prevLink' => $previous_link,
			'color_stages' => $color_stages
		);
		
	}
	function titles_cmp_function($a, $b) {
		return $a['num'] - $b['num'];
	}
	
	function ModCoordsCb($row, &$data) {
		$data['coords'] = $row['gala'].':'.$row['sys'].':'.$row['pla'];
		$data['act'] = ($row['planityp'] == 'Stargate') ? 'systemmap_stargate' : ActualityColor($row['inserttime']);
	}
	function ModGeoChCb($row, &$data) {
		if(is_null($row['chemie'])) {
			$data['geo_ch'] = '???';
		} else {
			$data['geo_ch'] = ($row['gebtimemod'] == 0) ? number_format($row['chemie']*0.1, 1, ',', '.').'/???' : number_format($row['chemie']*10/$row['gebtimemod'], 1, ',', '.');
		}
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModGeoEiCb($row, &$data) {
		if(is_null($row['eis'])) {
			$data['geo_ei'] = '???';
		} else {
			$data['geo_ei'] = ($row['gebtimemod'] == 0) ? number_format($row['eis']*0.1, 1, ',', '.').'/???' : number_format($row['eis']*10/$row['gebtimemod'], 1, ',', '.');
		}
		//$data['geo_ei'] = is_null($row['eis']) ? '???' : number_format($row['eis']*0.1, 1, ',', '.');
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModGeoFeCb($row, &$data) {
		if(is_null($row['eisen'])) {
			$data['geo_fe'] = '???';
		} else {
			$data['geo_fe'] = ($row['gebtimemod'] == 0) ? number_format($row['eisen']*0.1, 1, ',', '.').'/???' : number_format($row['eisen']*10/$row['gebtimemod'], 1, ',', '.');
		}
		//$data['geo_fe'] = is_null($row['eisen']) ? '???' : number_format($row['eisen']*0.1, 1, ',', '.');
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModGeoGraviLbCb($row, &$data) {
		$data['geo_gravi'] = is_null($row['gravi']) ? '???' : number_format($row['gravi']*0.01, 2, ',', '.');
		$data['geo_lb'] = is_null($row['lbed']) ? '???' : number_format($row['lbed']*0.1, 1, ',', '.');
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModGeoModsCb($row, &$data) {
		$data['geo_fmod'] = is_null($row['fmod']) ? '???' : number_format($row['fmod']*0.01, 2, ',', '.');
		$data['geo_gmod'] = is_null($row['gebmod']) ? '???' : number_format($row['gebmod']*0.01, 2, ',', '.');
		$data['geo_gtmod'] = is_null($row['gebtimemod']) ? '???' : number_format($row['gebtimemod']*0.01, 2, ',', '.');
		$data['geo_smod'] = is_null($row['shipmod']) ? '???' : number_format($row['shipmod']*0.01, 2, ',', '.');
		$data['geo_stmod'] = is_null($row['shiptimemod']) ? '???' : number_format($row['shiptimemod']*0.01, 2, ',', '.');
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModGeoTechTeamCb($row, &$data) {
		$data['geo_ttfe'] = is_null($row['tt_eisen']) ? '???' : (($row['gebtimemod'] == 0) ? number_format($row['tt_eisen']*0.1, 1, ',', '.').'/???' : number_format($row['tt_eisen']*10/$row['gebtimemod'], 1, ',', '.'));
		$data['geo_ttch'] = is_null($row['tt_chemie']) ? '???' : (($row['gebtimemod'] == 0) ? number_format($row['tt_chemie']*0.1, 1, ',', '.').'/???' : number_format($row['tt_chemie']*10/$row['gebtimemod'], 1, ',', '.'));
		$data['geo_ttei'] = is_null($row['tt_eis']) ? '???' : (($row['gebtimemod'] == 0) ? number_format($row['tt_eis']*0.1, 1, ',', '.').'/???' : number_format($row['tt_eis']*10/$row['gebtimemod'], 1, ',', '.'));
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModGeoTTLCb($row, &$data) {
		$data['geo_ttl'] = is_null($row['georeset']) ? '???' : round(($row['georeset'] - time())/86400.0);
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
	}
	function ModOwnerCb($row, &$data) {
		$data['owner'] = EscapeOU($row['ownername']);
		$data['tag'] = !empty($row['allytag']) ? '['.EscapeOU($row['allytag']).']' : '';
	}
	function ModPlaninameCb($row, &$data) {
		$data['name'] = EscapeOU($row['planiname']);
	}
	function ModTypesCb($row, &$data) {
		$data['planityp'] = EscapeOU($row['planityp']);
		$data['objekttyp'] = EscapeOU($row['objekttyp']);
	}
	function ModScanGebsCb($row, &$data) {
		if(is_null($row['scan_gebs'])) {
			$data['scan_gebs_exists'] = false;
			$data['scan_gebs'] = array();
			$data['scan_gebs_age'] = 'act_5';
			return;
		}
		$data['scan_gebs_exists'] = true;
		$arr = explode('/', $row['scan_gebs']);
		$gebs = array();
		foreach($arr as $line) {
			$a = explode('|', $line);
			$gebs[] = array('anz' => intval($a[0]), 'name' => EscapeOU($a[1]));
		}
		$data['scan_gebs'] = $gebs;
		$data['scan_gebs_age'] = ActualityColor($row['gebScanTime']);
		$data['scan_gebs_time'] = FormatDate($row['gebScanTime']);
		$data['scan_gebs_fe'] = number_format($row['geb_fe'], 0, ',', '.');
		$data['scan_gebs_st'] = number_format($row['geb_st'], 0, ',', '.');
		$data['scan_gebs_vv'] = number_format($row['geb_vv'], 0, ',', '.');
		$data['scan_gebs_ch'] = number_format($row['geb_ch'], 0, ',', '.');
		$data['scan_gebs_ei'] = number_format($row['geb_ei'], 0, ',', '.');
		$data['scan_gebs_wa'] = number_format($row['geb_wa'], 0, ',', '.');
		$data['scan_gebs_en'] = number_format($row['geb_en'], 0, ',', '.');
	}
	function ModScanSchiffeCb($row, &$data) {
		$data['scan_schiffe_age'] = ActualityColor($row['schiffScanTime']);
		$data['scan_schiffe_time'] = FormatDate($row['schiffScanTime']);
		if(empty($row['scan_schiffe'])) {
			$data['scan_schiffe_exists'] = false;
			$data['scan_schiffe'] = array();
			return;
		}
		$data['scan_schiffe_exists'] = true;
		$flotten = explode('|||', $row['scan_schiffe']);
		$flotten_data = array();
		foreach($flotten as $fl) {
			$fl_data = explode('||', $fl);
			$flotte = array(
				'name' => EscapeOU($fl_data[0]),
				'typ' => ucfirst($fl_data[1]),
			);
			$schiffe = explode('|', $fl_data[2]);
			$sch = array();
			foreach($schiffe as $schiff) {
				$schiff = explode(',', $schiff, 2);
				$sch[] = array(
					'anz' => number_format(intval($schiff[0]), 0, ',', '.'),
					'name' => EscapeOU($schiff[1]),
				);
			}
			$flotte['schiffe'] = $sch;
			$flotten_data[] = $flotte;
		}
		$data['scan_schiffe'] = $flotten_data;
		$data['scan_schiffe_fe'] = number_format($row['schiff_fe'], 0, ',', '.');
		$data['scan_schiffe_st'] = number_format($row['schiff_st'], 0, ',', '.');
		$data['scan_schiffe_vv'] = number_format($row['schiff_vv'], 0, ',', '.');
		$data['scan_schiffe_ch'] = number_format($row['schiff_ch'], 0, ',', '.');
		$data['scan_schiffe_ei'] = number_format($row['schiff_ei'], 0, ',', '.');
		$data['scan_schiffe_wa'] = number_format($row['schiff_wa'], 0, ',', '.');
		$data['scan_schiffe_en'] = number_format($row['schiff_en'], 0, ',', '.');
	}
	
	function ModImportantSpecialsCb($row, &$data) {
		global $pre;
		
		static $bscache = array();
		static $ncache = array();
		
		if(!isset($data['geotime']))
			$data['geotime'] = GeoActualityColor($row['geotime'], $row['georeset']);
		
		if(is_null($row['besonderheiten'])) {
			$data['important_specials'] = array(array('short' => '?', 'name' => 'Keine Ahnung, hab kein Geoscan von dem Plani!'));
			return;
		}
		
		$num = $row['besonderheiten'];
		$ret = array();
		for($i = 1;$num > 0; $i *= 2) {
			if(($num & $i) > 0) {
				$num ^= $i;
				if(!isset($bescache[$i])) {
					$arr = DBQueryOne("SELECT imp_kurzel, Name FROM {$pre}besonderheiten WHERE ID=$i", __FILE__, __LINE__);
					$bescache[$i] = array('short' => EscapeOU($arr[0]), 'name' => EscapeOU($arr[1]));
				}
				if(!empty($bescache[$i]))
					$ret[] = $bescache[$i];
			}
		}
		
		$n = $row['nebel'];
		if($n > 0) {
			if(!isset($ncache[$n])) {
				$arr = DBQueryOne("SELECT imp_kurzel, Name FROM {$pre}nebel WHERE ID=$n", __FILE__, __LINE__);
				$ncache[$n] = array('short' => EscapeOU($arr[0]), 'name' => EscapeOU($arr[1]));
			}
			if(!empty($ncache[$n]))
				$ret[] = $ncache[$n];
		}
		
		$data['important_specials'] = $ret;
	}
	
	function GeoActualityColor($geotime, $reset) {
		//Diese Funktion behandelt einen Sonderfall der Akualitätsdaten: 
		//Da die Angabe der Sprengzeit nicht ganz genau ist, zeigt es potentiell 
		//schon gesprengte Planis lieber als veraltet an. An sonsten halt gesprengte Planis = veraltet
		if(time()-172800 > $reset)//$now-2 Tage, wegen +-1 Tag
			return 'act_5';
		return ActualityColor($geotime);
	}
?>
