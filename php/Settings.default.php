<?php

######### Mainteance & Development #########
$mainteance = 0; // 1 enables mainteance-mode and only allows Admins to login, 2 disables everything, default 0
$mainteance_title = "Mainteance-Mode!";
$mainteanceBody = "Nothing 's happening!";
$debug = 0; //>0 = log/output some stuff - the more the more

######### Database ######### 
$db_host = 'localhost';
$db_user = 'dbuser';
$db_pass = 'dbpw';
$db_name = 'dbname';
$db_prefix = 'iwdb_';
$db_persistent = 1;

###### Addidional Database User with Select only privilege on some data Tables only #####
$db_user_uni = 'uniuser';
$db_pass_uni = 'unipw';

######### Paths #########
$sourcedir = "/var/www/iwdb/src";
$themedir = "/var/www/iwdb/tpl";
$scripturl = "http://yourhost/iwdb";
$themeurl = "http://yourhost/iwdb/tpl";

######## Cookies #########
$cookie['name'] = "dddfd-Cookie";
$cookie['path'] = "/";
$cookie['domain'] = NULL; 

######## IWDB-Util ########
$util_host = 'localhost';
$util_port = 5124;

######### Stuff #########
$default_gala = 9;
$spiel = 'iw';
$sittercolor_stages = array(
	1 => 1800, //30 mins
	2 => 3600, //1h
	3 => 7200, //2h
	4 => 10400, //3h
	5 => 14400, //4h
);
$unicolor_stages = array(
	1 => 86400,//1d
	2 => 604800,//1w
	3 => 1209600,//2w
	4 => 1814400,//3w
	5 => 2419200,//4w
);

?>