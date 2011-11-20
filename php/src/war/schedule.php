<?php

if(!defined('dddfd'))
	die('Hacking Attempt');

function WarSchedule() {
	global $pre, $content, $ID_MEMBER, $scripturl, $warmode, $user;
	
	if($user['isRestricted'])
		die("hacking attempt");
	
	if(!$warmode) {
		$content['schedule'] = array();
		$content['disabled'] = true;
		TemplateInit('wars');
		TemplateWarSchedule();
		return;
	}
	$content['disabled'] = false;
	$offset = date('Z');
	$now = time() + $offset;
	$start = $now - ($now % 86400) - 86400;
	
	$days = array();
	$s = $start;
	for($i = 0; $i < 4; ++$i) {
		$days[$s] = array();
		for($j = 0; $j < 48; ++$j) {
			$days[$s][$s+$j*1800] = array();
		}
		$s += 86400;
	}

	$q = DBQuery("SELECT war_schedule.id, war_schedule.time, war_schedule.userid, users.visibleName FROM {$pre}war_schedule AS war_schedule LEFT JOIN {$pre}users AS users ON war_schedule.userid=users.id WHERE time>={$start} ORDER BY time DESC, war_schedule.ID ASC", __FILE__, __LINE__);
	while($row = mysql_fetch_row($q)) {
		$t = $row[1] + $offset;
		$days[$t - ($t % 86400)][$t][] = array(
			'id' => $row[0],
			'name' => EscapeOU($row[3]),
			'isMe' => $row[2] == $ID_MEMBER,
		);
	}
	
	$content['schedule'] = array();
	$i = 0;
	foreach($days as $date => $day) {
		$d = array(
			'date' => date('D, d.m', $date-$offset),
			'times' => array(),
			'last' => $i++ == 3,
		);
		foreach($day as $time => $dta) {
			$showReg = 2-count($dta) > 0;
			foreach($dta as $entry) {
				if($entry['isMe'])
					$showReg = false;
			}
			 $d['times'][] = array(
				'time' => date('H:i', $time-$offset),
				'usedSlots' => $dta,
			 	'showReg' => $showReg,
			 	'regId' => $time-$offset,
			 	'active' => $time > $now,
			);
		}
		$content['schedule'][] = $d;
	}
	
	TemplateInit('wars');
	TemplateWarSchedule();
}

function WarScheduleAjax() {
	global $pre, $ID_MEMBER, $user;
	
	if($user['isRestricted'])
		die("hacking attempt");
	
	$oldId = str_replace('"', '', $_REQUEST['el']);
	echo '<data><setValue elementId="sched_', $oldId, '"><![CDATA[';
	switch($_REQUEST['m']) {
		case 'reg':
			DBQuery("INSERT INTO {$pre}war_schedule (time, userid) VALUES (".intval($_REQUEST['t']).", {$ID_MEMBER})", __FILE__, __LINE__);
			$id = mysql_insert_id();
			echo '<button onclick="WarScheduleUnReg(\'',$id,'\');"><b>Abmelden</b></button>';
			break;
		case 'unreg':
			$time = DBQueryOne("SELECT time FROM {$pre}war_schedule WHERE id=".intval($_REQUEST['id']), __FILE__, __LINE__);
			DBQuery("DELETE FROM {$pre}war_schedule WHERE id=".intval($_REQUEST['id']) . ($user['isAdmin'] ? '' : " AND userid={$ID_MEMBER}"), __FILE__, __LINE__);
			echo '<button onclick="WarScheduleReg(\'',$time,'\');" id="sched_',$time,'">Mach ich!</button>';
			$id = $time;
			
			break;
	}
	echo ']]></setValue><setID elementId="sched_',$oldId,'" newId="sched_',$id,'" /></data>';
	
}

?>