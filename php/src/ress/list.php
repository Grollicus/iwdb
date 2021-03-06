<?php
	if(!defined('dddfd'))
		exit();
	function FormattedTimeLeft($ress, $v, $age) {
		if($v >= 0)
			return '';
		$t = (-100*$ress / $v)+($age-time())/3600;
		if($t < 1)
			return '<b>&lt;1 h</b>';
		if($t > 72)
			return '&gt;75 h';
		return number_format($t, 1, ',', '.').'h';
	}
	function RessUserList() {
		global $content, $pre, $scripturl, $unicolor_stages, $user;
		
		if($user['isRestricted'])
			die("Hacking Attempt");
		
		$content['squads'] = array();
		$q = DBQuery("SELECT igm_data.squad FROM {$pre}igm_data AS igm_data GROUP BY igm_data.squad", __FILE__, __LINE__);
		while($row = mysql_fetch_row($q)) {
			$content['squads'][] = $row[0];
		}

		$r = array('fe', 'st', 'vv', 'ch', 'ei', 'wa', 'en');
		$cols = '';
		foreach($r as $col) {
			$cols .= ", SUM($col), SUM(v$col)";
		}
		
		$orders = array(
			0  => 'igm_data.igmname',
			1  => 'SUM(vFe)',
			2  => 'SUM(fe)',
			3  => 'SUM(vSt)',
			4  => 'SUM(st)',
			5  => 'SUM(vVv)',
			6  => 'SUM(vv)', 
			7  => 'SUM(vCh)',
			8  => 'SUM(ch)',
			9  => 'SUM(vEi)',
			10 => 'SUM(ei)',
			11 => 'SUM(vWa)',
			12 => 'SUM(wa)',
			13 => 'SUM(vEn)',
			14 => 'SUM(en)',
			15 => 'SUM(fp)',
			16 => 'SUM(vCr)',
			17 => 'SUM(cr)',
		);
		if(isset($_REQUEST['order'])) {
			$order = $orders[$_REQUEST['order']];
			$activeOrder = intval($_REQUEST['order']);
		} else {
			$order = $orders[0];
			$activeOrder = 0;
		}
		if(isset($_REQUEST['asc']) && $_REQUEST['asc'] == '1') {
			$asc = true;
		} else {
			$asc = false;
			$order .= ' DESC';
		}
		
		$q = DBQuery("SELECT igm_data.id, igm_data.igmname, igm_data.accounttyp, igm_data.squad $cols, SUM(fp), cr, SUM(vCr), MIN(ressuebersicht.time)
FROM {$pre}ressuebersicht AS ressuebersicht INNER JOIN {$pre}igm_data AS igm_data ON ressuebersicht.uid=igm_data.id 
GROUP BY uid ORDER BY $order", __FILE__, __LINE__);
		
		$content['users'] = array();
		$sums = array();
		$accountTypen = array(
			'fle' => '<b>Fleeter</b>',
			'bud' => 'Buddler',
			'mon' => 'Monarch',
			'all' => 'Allrounder',
		);
		for($i=4; $i<=20;++$i) {
			$sums[$i]=0;
		}
		while($row = mysql_fetch_row($q)) {
			$content['users'][] = array(
				'loginLink' => $scripturl.'/index.php?action=sitter_login&amp;from=ressuserlist&amp;id='.$row[0],
				'name' => EscapeOU($row[1]),
				'accountTyp' => $accountTypen[$row[2]],
				'squad' => EscapeOU($row[3]),
				'fe' => number_format($row[4], 0, ',', '.'),
				'vFe' => number_format($row[5]/100, 2, ',', '.'),
				'tFe' => FormattedTimeLeft($row[4],$row[5], $row[21]),
				'st' => number_format($row[6], 0, ',', '.'),
				'vSt' => number_format($row[7]/100, 2, ',', '.'),
				'tSt' => FormattedTimeLeft($row[6],$row[7], $row[21]),
				'vv' => number_format($row[8], 0, ',', '.'),
				'vVv' => number_format($row[9]/100, 2, ',', '.'),
				'tVv' => FormattedTimeLeft($row[10],$row[9], $row[21]),
				'ch' => number_format($row[10], 0, ',', '.'),
				'vCh' => number_format($row[11]/100, 2, ',', '.'),
				'tCh' => FormattedTimeLeft($row[10],$row[11], $row[21]),
				'ei' => number_format($row[12], 0, ',', '.'),
				'vEi' => number_format($row[13]/100, 2, ',', '.'),
				'tEi' => FormattedTimeLeft($row[12],$row[13], $row[21]),
				'wa' => number_format($row[14], 0, ',', '.'),
				'vWa' => number_format($row[15]/100, 2, ',', '.'),
				'tWa' => FormattedTimeLeft($row[14],$row[15], $row[21]),
				'en' => number_format($row[16], 0, ',', '.'),
				'vEn' => number_format($row[17]/100, 2, ',', '.'),
				'tEn' => FormattedTimeLeft($row[16],$row[17], $row[21]),
				'fp' => number_format($row[18]/100, 2, ',', '.'),
				'cr' => number_format($row[19], 0, ',', '.'),
				'vCr' => number_format($row[20]/100, 2, ',', '.'),
				'tCr' => FormattedTimeLeft($row[19],$row[20], $row[21]),
				'age' => ActualityColor($row[21]),
			);
			for($i=4; $i<=20;++$i) {
				$sums[$i]+=$row[$i];
			}
		}
		$content['ges'] = array(
			'name' => EscapeOU($row[0]),
			'fe' => number_format($sums[4], 0, ',', '.'),
			'vFe' => number_format($sums[5]/100, 2, ',', '.'),
			'st' => number_format($sums[6], 0, ',', '.'),
			'vSt' => number_format($sums[7]/100, 2, ',', '.'),
			'vv' => number_format($sums[8], 0, ',', '.'),
			'vVv' => number_format($sums[9]/100, 2, ',', '.'),
			'ch' => number_format($sums[10], 0, ',', '.'),
			'vCh' => number_format($sums[11]/100, 2, ',', '.'),
			'ei' => number_format($sums[12], 0, ',', '.'),
			'vEi' => number_format($sums[13]/100, 2, ',', '.'),
			'wa' => number_format($sums[14], 0, ',', '.'),
			'vWa' => number_format($sums[15]/100, 2, ',', '.'),
			'en' => number_format($sums[16], 0, ',', '.'),
			'vEn' => number_format($sums[17]/100, 2, ',', '.'),
			'fp' => number_format($sums[18]/100, 2, ',', '.'),
			'cr' => number_format($sums[19], 0, ',', '.'),
			'vCr' => number_format($sums[20]/100, 2, ',', '.'),
		);
		
		$content['sortLinks'] = array();
		foreach($orders as $id => $o) {
			$content['sortLinks'][] = array(
				'link' => $scripturl.'/index.php?action=ressuserlist&amp;order='.$id.'&amp;asc='.($activeOrder == $id && !$asc ? '1' : '0'),
				'active' => $activeOrder == $id,
			);
		}
		$content['order'] = $asc ? 'up' :  'down';
		
		$content['color_stages'] = array();
		foreach($unicolor_stages as $stage => $time) {
			$content['color_stages'][$stage] = FormatDays($time);
		}
		
		TemplateInit('ress');
		TemplateRessUserList();
	}
?>
