<?php
if(!defined('dddfd') || !$user['isAdmin'])
	exit();

function MySQLClient() {
	global $content, $pre, $db_host, $db_user, $db_pass, $db_name;

if(get_magic_quotes_gpc() == 1) {
	$content['server'] = isset($_POST['address']) ? stripslashes($_POST['address']) : $db_host;
	$content['user'] = isset($_POST['username']) ? stripslashes($_POST['username']) : $db_user;
	$content['pw'] = isset($_POST['password']) ? stripslashes($_POST['password']) : $db_pass;
	$content['db'] = isset($_POST['DB']) ? stripslashes($_POST['DB']) : $db_name;
	$content['cmd'] = isset($_POST['befehl']) ? stripslashes($_POST['befehl']) : '';
} else {
	$content['server'] = isset($_POST['address']) ? $_POST['address'] : $db_host;
	$content['user'] = isset($_POST['username']) ? $_POST['username'] : $db_user;
	$content['pw'] = isset($_POST['password']) ? $_POST['password'] : $db_pass;
	$content['db'] = isset($_POST['DB']) ? $_POST['DB'] : $db_name;
	$content['cmd'] = isset($_POST['befehl']) ? $_POST['befehl'] : '';
}

	if(isset($_POST['submit']) && $_POST['submit'] == 'Query') {
		
		$err = 0;
		$content['cols'] = 1;
		if(!empty($_POST['username']))
			$con = mysql_connect($_POST['address'], $_POST['username'], $_POST['password'], true);
		else
			$con = @mysql_connect($_POST['address'], NULL, NULL, true);
		if(!$con) {
			$err = 1;
			$content['msg'] = '<b>mysql_connect:</b> '.mysql_error();
		}
		if(!empty($_POST['DB']) && ($err == 0) && !mysql_select_db($_POST['DB'])) {
			$err = 2;
			$content['msg'] = '<b>mysql_select_db:</b> '.mysql_error();
		}
		$cmd = str_replace(array('{$pre}','$pre'), $pre, $content['cmd']);
		if(($err == 0) && !($q = mysql_query($cmd, $con))) {
			$err = 3;
			$content['msg'] = '<b>mysql_query:</b>: '.mysql_error();
		}
		if($err == 0) {
			$content['affected'] = @mysql_affected_rows();
			$content['numrows'] = @mysql_num_rows($q);
			$content['result'] = array();
			$content['colnames'] = array();
			if($content['numrows'] > 0) {
				while($row = mysql_fetch_row($q))
					$content['result'][] = $row;
				mysql_data_seek($q, 0);
				$row = mysql_fetch_assoc($q);
				$content['cols'] = count($row);
				foreach($row as $key => $value) {
					$content['colnames'][] = $key;
				}
			}
		}
	/*	if($err != 1)
			mysql_close($con);*/
		TemplateInit('mysql');
		TemplateMySQLQueryResult();
	} else {
		TemplateInit('mysql');
		TemplateMySQLQueryForm();
	}
	echo '';
	?>
	<?php /* } else {
		if (empty($_POST["username"])) {
		echo "Connecting to '$_POST[adress]'...&nbsp;&nbsp;";
			$con = mysql_connect($_POST["adress"]);
		} else {
			echo "Connecting to '$_POST[username]@$_POST[adress]'...&nbsp;&nbsp;";
			$con = mysql_connect($_POST["adress"],$_POST["username"],$_POST["PW"]);
		}
		if (!$con) {
			echo "<font color=red>failed<br>\n" . mysql_error() . "</font><br>\n";
			die();
		} else {
			echo "<font color=green>done</font><br>\n";
		}
		if (!empty($_POST["DB"])) {
			echo "Selecting database '$_POST[DB]'...&nbsp;&nbsp;";
			mysql_select_db($_POST["DB"],$con) or die("<font color=red>failed<br>\n" . mysql_error() . "</font><br>\n");
			echo "<font color=green>done</font><br>\n";
		}
		echo "Executing '$_POST[befehl]'...&nbsp;&nbsp;";
		$res = mysql_query(stripslashes($_POST["befehl"]),$con);
		if (!$res) {
			echo "<font color=red>failed<br>\n" . mysql_error() . "</font><br>\n";
			die();
		}
		echo "<font color=green>done</font><br>\n<p>\n";
		echo "" . mysql_affected_rows($con) . " rows were affected<br>\n<p>";
		$row = mysql_fetch_array($res,MYSQL_ASSOC);
		if (empty($row)) {
			echo "<font color=red>No result</font>";
		} else {
			echo "<table border=\"1\">\n<tr>";
			foreach ($row as $key=>$value) {
				echo "<th>$key</th>";
			}
			echo "</tr>\n";
			while (!empty($row)) {
				echo "<tr>";
				foreach ($row as $value) {
					echo "<td>".nl2br(htmlentities($value))."</td>";
				}
				echo "</tr>\n";
				$row = mysql_fetch_array($res,MYSQL_NUM);
			}
			echo "</table>\n";
		}
	echo "<form method=post>\n";
	foreach ($_POST as $name=>$value) {
		if ($name=='befehl')
			echo '<div style="visibility: hidden"><textarea name="befehl">'.stripslashes($value).'</textarea></div>';
		else
			echo "<input type=hidden name='$name' value='".$value."'>\n";
	}
	?>
	<input type=hidden name="new" value="1">
	<input type=submit value="Neuer Befehl">
	</form>
	<? } ?>
	</body>
</html> 
<? */ } ?>
