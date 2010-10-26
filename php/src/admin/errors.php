<?php
if(!defined('dddfd'))
	exit();

function ListErrors() {
	global $pre, $user, $content, $sourcedir;
	
	if(!$user['isAdmin'])
		die('hacking attempt');
	if(isset($_REQUEST['sub'])) {
		switch($_REQUEST['sub']) {
			case 'del':
				DBQuery("DELETE FROM {$pre}errors WHERE ID=".intval($_REQUEST['id']), __FILE__, __LINE__);
				if(mysql_affected_rows() > 0)
					$content['msg'] = "Eintrag erfolgreich gel&ouml;scht!";
				break;
			case 'delall':
				DBQuery("TRUNCATE TABLE {$pre}errors", __FILE__, __LINE__);
				$content['msg'] = "Tabelle erfolgreich geleert, ".mysql_affected_rows(). " Eintr&auml;ge gel&ouml;scht!";
				break;
			case 'fdelall':
				fclose(fopen(dirname($sourcedir)."/errors.txt", 'w'));
				break;
		}
	}
	$content['dberrcount'] = DBQueryOne("SELECT count(*) FROM {$pre}errors", __FILE__, __LINE__);
	$q = DBQuery("SELECT errors.ID, errors.time, errors.user, errors.file, errors.line, errors.msg, errors.stacktrace, errors.request, users.visibleName FROM {$pre}errors as errors left join {$pre}users as users on errors.user=users.id", __FILE__, __LINE__);
	$content['dberrorlines'] = array();
	while($row = mysql_fetch_row($q)) {
		$content['dberrorlines'][$row[0]] = array(
			'ID' => intval($row[0]),
			'time' => intval($row[1]),
			'user' => EscapeO($row[2]),
			'file' => EscapeO($row[3]),
			'line' => intval($row[4]),
			'msg' => nl2br(EscapeO($row[5])),
			'stacktrace' => nl2br(EscapeO($row[6])),
			'request' => nl2br(EscapeO($row[7])),
			'username' => EscapeO($row[8]),
		);
	}
	$content['fileerrors'] = array();
	$i = 0;
	if(file_exists(dirname($sourcedir)."/errors.txt")) {
		$str = file_get_contents(dirname($sourcedir)."/errors.txt");
		$errs = explode('|^|', $str);
		foreach($errs as $err) {
			$err = trim($err);
			if(empty($err))
				continue;
			$parts = explode(',', $err, 6);
			$content['fileerrors'][] = array(
				'time' => FormatDate($parts[0]),
				'userid' => $parts[1],
				'file' => EscapeO($parts[2]),
				'line' => $parts[3],
				'stacktrace' => EscapeO($parts[4]),
				'msg' => EscapeO($parts[5]),
			); 
			++$i;
		}
	}
	$content['fileerrcount'] = $i;
	TemplateInit('admin');
	TemplateListErrors();
}

?>
