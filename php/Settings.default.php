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
$token_seed = 'dftg.36qxy';
$sittercolor_stages = array(
	1 => 1800, //30 mins
	2 => 7200, //2h
	3 => 14400, //4h
	4 => 28800, //8h
	5 => 43200, //12h
);
$unicolor_stages = array(
	1 => 86400,//1d
	2 => 604800,//1w
	3 => 1209600,//2w
	4 => 1814400,//3w
	5 => 2419200,//4w
);
//Die Preset-URLs sind von der Form http://$scripturl/tool/?action=uni_view&HIER-DAS-PREFIX
$uni_presets= array(
	'Geoscan' => 'gala_min=&gala_max=&sys_min=&sys_max=&pla_min=&pla_max=&spieler=&tag=&objekttyp[]=---&planiname=&geo_ch_min=0&geo_ch_max=&geo_fe_min=&geo_fe_max=&geo_ei_min=&geo_ei_max=&geo_gravi_min=&geo_gravi_max=&geo_lb_min=&geo_lb_max=&geo_fmod_min=&geo_fmod_max=&geo_gebd_min=&geo_gebd_max=&geo_gebk_min=&geo_gebk_max=&geo_schd_min=&geo_schd_max=&geo_schk_min=&geo_schk_max=&scan_geb=&spalten[]=coords&spalten[]=types&spalten[]=important_specials&spalten[]=geo_fe&spalten[]=geo_ch&spalten[]=geo_ei&spalten[]=geo_gravilb&spalten[]=geo_ttl&spalten[]=geo_mods&sortby[]=coords&orders[]=0',
	'Spieler' => 'gala_min=&gala_max=&sys_min=&sys_max=&pla_min=&pla_max=&spieler=&tag=&objekttyp[]=Kolonie&objekttyp[]=Sammelbasis&objekttyp[]=Kampfbasis&objekttyp[]=Artefaktbasis&planiname=&geo_ch_min=&geo_ch_max=&geo_fe_min=&geo_fe_max=&geo_ei_min=&geo_ei_max=&geo_gravi_min=&geo_gravi_max=&geo_lb_min=&geo_lb_max=&geo_fmod_min=&geo_fmod_max=&geo_gebd_min=&geo_gebd_max=&geo_gebk_min=&geo_gebk_max=&geo_schd_min=&geo_schd_max=&geo_schk_min=&geo_schk_max=&scan_geb=&spalten[]=coords&spalten[]=owner&spalten[]=types&spalten[]=planiname&spalten[]=important_specials&sortby[]=coords&orders[]=0',
);

// Bezeichne Angriffe als Fake wenn angreifer-Wert < $fake_att und verteidiger-Wert < $fake_def 
$fake_att = 200000;
$fake_def = 2000000;

$warmode = false;

?>
