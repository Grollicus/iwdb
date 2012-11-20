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
$uni_secret = '6e33d0ff63e0333cc6380037158d9be154b5e648f1dd0a47a5a0e0fa6e8a4688';
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
	'Geoscan' => 'gala_min=&amp;gala_max=&amp;sys_min=&amp;sys_max=&amp;pla_min=&amp;pla_max=&amp;spieler=&amp;tag=&amp;objekttyp[]=---&amp;planiname=&amp;geo_ch_min=0&amp;geo_ch_max=&amp;geo_fe_min=&amp;geo_fe_max=&amp;geo_ei_min=&amp;geo_ei_max=&amp;geo_gravi_min=&amp;geo_gravi_max=&amp;geo_lb_min=&amp;geo_lb_max=&amp;geo_fmod_min=&amp;geo_fmod_max=&amp;geo_gebd_min=&amp;geo_gebd_max=&amp;geo_gebk_min=&amp;geo_gebk_max=&amp;geo_schd_min=&amp;geo_schd_max=&amp;geo_schk_min=&amp;geo_schk_max=&amp;scan_geb=&amp;spalten[]=coords&amp;spalten[]=types&amp;spalten[]=important_specials&amp;spalten[]=geo_fe&amp;spalten[]=geo_ch&amp;spalten[]=geo_ei&amp;spalten[]=geo_gravilb&amp;spalten[]=geo_ttl&amp;spalten[]=geo_mods&amp;sortby[]=coords&amp;orders[]=0',
	'Spieler' => 'gala_min=&amp;gala_max=&amp;sys_min=&amp;sys_max=&amp;pla_min=&amp;pla_max=&amp;spieler=&amp;tag=&amp;objekttyp[]=Kolonie&amp;objekttyp[]=Sammelbasis&amp;objekttyp[]=Kampfbasis&amp;objekttyp[]=Artefaktbasis&amp;planiname=&amp;geo_ch_min=&amp;geo_ch_max=&amp;geo_fe_min=&amp;geo_fe_max=&amp;geo_ei_min=&amp;geo_ei_max=&amp;geo_gravi_min=&amp;geo_gravi_max=&amp;geo_lb_min=&amp;geo_lb_max=&amp;geo_fmod_min=&amp;geo_fmod_max=&amp;geo_gebd_min=&amp;geo_gebd_max=&amp;geo_gebk_min=&amp;geo_gebk_max=&amp;geo_schd_min=&amp;geo_schd_max=&amp;geo_schk_min=&amp;geo_schk_max=&amp;scan_geb=&amp;spalten[]=coords&amp;spalten[]=owner&amp;spalten[]=types&amp;spalten[]=planiname&amp;spalten[]=important_specials&amp;sortby[]=coords&amp;orders[]=0',
	'Blocken' => 'gala_min=19&amp;gala_max=19&amp;sys_min=1&amp;sys_max=25&amp;pla_min=&amp;pla_max=&amp;spieler=&amp;tag=&amp;planiname=&amp;geo_ch_min=&amp;geo_ch_max=&amp;geo_fe_min=&amp;geo_fe_max=&amp;geo_ei_min=&amp;geo_ei_max=&amp;geo_gravi_min=&amp;geo_gravi_max=&amp;geo_lb_min=&amp;geo_lb_max=&amp;geo_fmod_min=&amp;geo_fmod_max=&amp;geo_gebd_min=&amp;geo_gebd_max=&amp;geo_gebk_min=&amp;geo_gebk_max=&amp;geo_schd_min=&amp;geo_schd_max=&amp;geo_schk_min=&amp;geo_schk_max=&amp;scan_geb=&amp;scan_geb_cmp=0&amp;scan_geb_cnt=&amp;raw_sql=(userdata.allytag IS NULL or userdata.allytag!=\'FP\') AND uni.objekttyp != \'Kolonie\'&amp;raw_sql_hash=e21c4d34fdc179fdc230ca38540e1d5913b3af7fa0fbd1e640de0d5e59ce97fc&amp;spalten[]=coords&amp;spalten[]=owner&amp;spalten[]=types&amp;spalten[]=planiname&amp;spalten[]=important_specials&amp;spalten[]=geo_fe&amp;spalten[]=geo_ch&amp;spalten[]=geo_ei&amp;spalten[]=geo_gravilb&amp;spalten[]=geo_ttl&amp;spalten[]=geo_mods&amp;sortby[]=coords&amp;orders[]=0&amp;limit=0',
);

// Bezeichne Angriffe als Fake wenn angreifer-Wert < $fake_att und verteidiger-Wert < $fake_def 
$fake = 200000;

$warmode = false;
$allow_restricted = false;

?>
