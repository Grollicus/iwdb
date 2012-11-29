<?php
if (!defined("dddfd"))
	die("Hacking attempt");

global $schiffe;
$schiffe = array(
	array('type' => 'sonde', 'name' => 'Terminus Sonde', 'gal' => 75000, 'sol' => 11000, 'war' => false),
	array('type' => 'sonde', 'name' => 'Sonde X11', 'gal' => 60000, 'sol' => 10000, 'war' => false),
	array('type' => 'sonde', 'name' => 'Sonde X13', 'gal' => 85000, 'sol' => 13000, 'war' => false),
	array('type' => 'schiff','name' => 'Systrans', 'gal' => 3900, 'sol' => 370, 'war' => false),
	array('type' => 'schiff','name' => 'Kamel Z-98', 'gal' => 4500, 'sol' => 500, 'war' => true),
	array('type' => 'schiff','name' => 'Waschbär', 'gal' => 4300, 'sol' => 500, 'war' => true),
	array('type' => 'schiff','name' => 'Kronk', 'gal' => 5700, 'sol' => 450, 'war' => false),
	array('type' => 'schiff','name' => 'Zeus', 'gal' => 5600, 'sol' => 200, 'war' => false),
	array('type' => 'schiff','name' => 'Eraser 90%', 'gal' => 4900*0.9, 'sol' => 630*0.9, 'war' => true),
	array('type' => 'schiff','name' => 'Eraser 95%', 'gal' => 4900*0.95, 'sol' => 630*0.95, 'war' => true),
	array('type' => 'schiff','name' => 'Eraser', 'gal' => 4900, 'sol' => 630, 'war' => true),
	array('type' => 'schiff','name' => 'X12', 'gal' => 4900, 'sol' => 600, 'war' => true),
);
	
	
function WarTiming() { //TODO: das gleiche für alte Kampfberichte einbauen
	global $pre, $content, $schiffe;

	$scans = array();
	$types = array(
		'Sondierung (Schiffe/Def/Ress)' => 'Schiffe/Def/Ress',
		'Sondierung (Gebäude/Ress)' => 'Gebäude/Ress',
		'Sondierung (Geologie)' => 'Geologie',
	);
	$q = DBQuery("SELECT dst.gala, dst.sys, dst.pla, flotten.ankunft, flotten.action, src.gala, src.sys, src.pla, src.ownername, uni_userdata.allytag FROM (({$pre}flotten AS flotten INNER JOIN {$pre}universum AS src ON flotten.startid=src.id) INNER JOIN {$pre}universum AS dst ON flotten.zielid=dst.id) LEFT JOIN {$pre}uni_userdata AS uni_userdata ON dst.ownername=uni_userdata.name WHERE flotten.action IN ('Sondierung (Gebäude/Ress)','Sondierung (Geologie)','Sondierung (Schiffe/Def/Ress)')", __FILE__, __LINE__);
	while($row = mysql_fetch_row($q)) {
		$scans[] = array(
			'kind' => 'scan',
			'origin' => 'hs',
			'dst' => EscapeOU($row[0].':'.$row[1].':'.$row[2]),
			'dst_g' => intval($row[0]),
			'dst_s' => intval($row[1]),
			'dst_p' => intval($row[2]),
			'time' => intval($row[3]),
			'type' => $types[$row[4]],
			'src' => EscapeOU($row[5].':'.$row[6].':'.$row[7]),
			'src_g' => intval($row[5]),
			'src_s' => intval($row[6]),
			'src_p' => intval($row[7]),
			'sender' => EscapeOU($row[8]),
			'ally' => empty($row[9]) ? "" : EscapeOU('['.$row[9].']'),
		);
	}
	
	$types = array(
		'sch' => 'Schiffe/Def/Ress',
		'geb' => 'Gebäude/Ress',
		'geo' => 'Geologie',
	);
	$q = DBQuery("SELECT dst, time, type, start, sender, ally FROM {$pre}feind_scans ORDER BY time DESC limit 0,100", __FILE__, __LINE__);
	while($row = mysql_fetch_row($q)) {
		$dst = explode(':', $row[0]);
		$src = explode(':', $row[3]);
		$scans[] = array(
			'kind' => 'scan',
			'origin' => 'report',
			'dst' => EscapeOU($row[0]),
			'dst_g' => intval($dst[0]),
			'dst_s' => intval($dst[1]),
			'dst_p' => intval($dst[2]),
			'time' => intval($row[1]),
			'type' => $types[$row[2]],
			'src' => EscapeOU($row[3]),
			'src_g' => intval($src[0]),
			'src_s' => intval($src[1]),
			'src_p' => intval($src[2]),
			'sender' => EscapeOU($row[4]),
			'ally' => EscapeOU($row[5]),
		);
	}
	
	$kbs = array();
	$q = DBQuery("SELECT dst.gala, dst.sys, dst.pla, flotten.ankunft, flotten.action, src.gala, src.sys, src.pla, src.ownername, uni_userdata.allytag FROM (({$pre}flotten AS flotten INNER JOIN {$pre}universum AS src ON flotten.startid=src.id) INNER JOIN {$pre}universum AS dst ON flotten.zielid=dst.id) LEFT JOIN {$pre}uni_userdata AS uni_userdata ON dst.ownername=uni_userdata.name WHERE flotten.action = 'Angriff'", __FILE__, __LINE__);
	while($row = mysql_fetch_row($q)) {
		$kbs[] = array(
			'kind' => 'kb',
			'origin' => 'hs',
			'dst' => EscapeOU($row[0].':'.$row[1].':'.$row[2]),
			'dst_g' => intval($row[0]),
			'dst_s' => intval($row[1]),
			'dst_p' => intval($row[2]),
			'time' => intval($row[3]),
			'type' => 'Att',
			'src' => EscapeOU($row[5].':'.$row[6].':'.$row[7]),
			'src_g' => intval($row[5]),
			'src_s' => intval($row[6]),
			'src_p' => intval($row[7]),
			'sender' => EscapeOU($row[8]),
			'ally' => empty($row[9]) ? "" : EscapeOU('['.$row[9].']'),
		);
	}
	$content['scans'] = EscapeJSU($scans);
	$content['schiffe'] = EscapeJSU($schiffe);
	$content['kbs'] = EscapeJSU($kbs);
	
	TemplateInit('wars');
	TemplateWarTiming();
	
}

function SitterUtilFlug() {
	global $schiffe, $content;
	$content['schiffe'] = EscapeJSU($schiffe);
	$content['time'] = FormatPreciseDate(time());
	TemplateInit('sitter');
	TemplateSitterUtilFlug();
}

function SitterFeindlFlottenUebersicht() {
	global $content, $pre, $scripturl, $schiffe;
	$q = DBQuery("SELECT startuni.gala, startuni.sys, startuni.pla, startuni.planiname, startuni.ownername, zieluni.gala, zieluni.sys, zieluni.pla, zieluni.planiname, zieluni.ownername, flotten.action, flotten.ankunft, flotten.firstseen, flotten.notyetSeen, flotten.safe, flotten.dont_save, igm_data.id
FROM (((({$pre}flotten AS flotten INNER JOIN {$pre}universum AS startuni ON flotten.startid = startuni.ID) 
	INNER JOIN {$pre}universum AS zieluni ON flotten.zielid=zieluni.ID)
	LEFT JOIN {$pre}uni_userdata AS start_userdata ON startuni.ownername = start_userdata.name)
	LEFT JOIN {$pre}uni_userdata AS ziel_userdata ON zieluni.ownername = ziel_userdata.name)
	INNER JOIN {$pre}igm_data AS igm_data ON zieluni.ownername=igm_data.igmname
WHERE flotten.action IN ('Angriff', 'Sondierung (Gebäude/Ress)','Sondierung (Schiffe/Def/Ress)')
ORDER BY flotten.ankunft", __FILE__, __LINE__);
	$fl = array();
	while($row = mysql_fetch_row($q)) {
		$fl[] = array(
			'startkoords' => $row[0].':'.$row[1].':'.$row[2],
			's_g' => intval($row[0]),
			's_s' => intval($row[1]),
			's_p' => intval($row[2]),
			'startname' => EscapeOU($row[3]),
			'startowner' => EscapeOU($row[4]),
			'zielkoords' => $row[5].':'.$row[6].':'.$row[7],
			'd_g' => intval($row[5]),
			'd_s' => intval($row[6]),
			'd_p' => intval($row[7]),
			'zielname' => EscapeOU($row[8]),
			'zielowner' => EscapeOU($row[9]),
			'bewegung' => EscapeOU($row[10]),
			'gefaehrlich' => $row[10] == 'Angriff',
			'ankunft' => intval($row[11]),
			'firstSeen' => intval($row[12]),
			'notyetSeen' => intval($row[13]),
			'safe' => $row[14] == 1,
			'dont_save' => $row[15] != 0,
			'loginLink' => $scripturl.'/index.php?action=sitter_login&amp;show_save=1&amp;from=sitter_flotten&amp;id='.$row[16],
		);
	}
	$content['flotten'] = EscapeJSU($fl);
	$content['schiffe'] = EscapeJSU(array_values(array_filter($schiffe, function($el) {return $el['war'];})));
	TemplateInit('sitter');
	TemplateFeindlFlottenUebersicht();
}

?>