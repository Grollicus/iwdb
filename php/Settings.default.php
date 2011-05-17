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

?>