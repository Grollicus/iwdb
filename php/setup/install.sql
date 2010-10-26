CREATE TABLE `pre_besonderheiten` (
  `ID` int(10) unsigned default NULL,
  `Name` char(32) default NULL,
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('1', 'alte Ruinen');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('2', 'Asteroideng�rtel');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('4', 'instabiler Kern');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('8', 'Gold');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('16', 'nat�rliche Quelle');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('32', 'planetarer Ring');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('64', 'radioaktiv');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('128', 'toxisch');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('256', 'Ureinwohner');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('512', 'wenig Rohstoffe');
INSERT INTO pre_besonderheiten (ID, Name) VALUES ('1024', 'Mond');


CREATE TABLE `pre_building` (
  `uid` int(10) unsigned default NULL,
  `coords` char(32) default NULL,
  `end` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `pre_errors` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `time` int(10) unsigned default NULL,
  `user` int(10) unsigned default NULL,
  `file` char(64) default NULL,
  `line` int(11) default NULL,
  `msg` text,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;


CREATE TABLE `pre_geoscans` (
  `id` int(10) unsigned default NULL,
  `eisen` int(11) default NULL,
  `chemie` int(11) default NULL,
  `eis` int(11) default NULL,
  `gravi` tinyint(3) unsigned default NULL,
  `lbed` int(11) default NULL,
  `nebel` tinyint(4) default NULL,
  `besonderheiten` int(11) default NULL,
  `fmod` tinyint(4) default NULL,
  `gebmod` tinyint(4) default NULL,
  `gebtimemod` tinyint(4) default NULL,
  `shipmod` tinyint(4) default NULL,
  `shiptimemod` tinyint(4) default NULL,
  `tt_eisen` float default NULL,
  `tt_chemie` float default NULL,
  `tt_eis` float default NULL,
  `timestamp` int(10) unsigned default NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pre_nebel` (
  `ID` int(10) unsigned default NULL,
  `Name` char(32) default NULL,
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO pre_nebel (ID, Name) VALUES ('1', 'blauer Nebel');
INSERT INTO pre_nebel (ID, Name) VALUES ('2', 'gelber Nebel');
INSERT INTO pre_nebel (ID, Name) VALUES ('3', 'gr�ner Nebel');
INSERT INTO pre_nebel (ID, Name) VALUES ('4', 'roter Nebel');
INSERT INTO pre_nebel (ID, Name) VALUES ('5', 'violetter Nebel');


CREATE TABLE `pre_ships` (
  `ID` int(11) NOT NULL auto_increment,
  `iwid` int(11) default NULL,
  `name` char(64) default NULL,
  `kosten_fe` int(11) default NULL,
  `kosten_st` int(11) default NULL,
  `kosten_vv` int(11) default NULL,
  `kosten_ch` int(11) default NULL,
  `kosten_ei` int(11) default NULL,
  `kosten_wa` int(11) default NULL,
  `kosten_en` int(11) default NULL,
  `kosten_be` int(11) default NULL,
  `kosten_cr` int(11) default NULL,
  `dauer` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `name` (`name`),
  KEY `iwid` (`iwid`)
) ENGINE=MyISAM AUTO_INCREMENT=123 DEFAULT CHARSET=utf8;

CREATE TABLE `pre_sitter` (
  `ID` int(11) NOT NULL auto_increment,
  `userid` int(11) default NULL,
  `groupsallowed` int(11) default NULL,
  `time` int(11) default NULL,
  `done` tinyint(1) NOT NULL default '0',
  `itemid` int(10) unsigned default '0',
  `Text` varchar(255) default NULL,
  `type` enum('Geb','For','Sch','Def') default 'Def',
  `planID` int(10) unsigned default NULL,
  `Anzahl` int(10) unsigned default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;


CREATE TABLE `pre_sitterlog` (
  `userid` int(11) default NULL,
  `victimid` int(11) default NULL,
  `type` enum('auftrag','login') default NULL,
  `time` int(11) default NULL,
  `auftragsid` int(11) default NULL,
  KEY `userid` (`userid`),
  KEY `victimid` (`victimid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `pre_speedlog` (
  `action` varchar(63) NOT NULL default '',
  `script` tinyint(1) default '0',
  `sub` varchar(63) NOT NULL default '',
  `runtime` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `pre_techtree_items` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `Name` char(127) not null default '',
  `Type` enum('geb','for','schiff','def') default NULL,
  `global` tinyint(4) default NULL,
  `Class` tinyint(4) default NULL,
  `Gebiet` varchar(64) default NULL,
  `BenKolotyp` varchar(16) default NULL,
  `Beschreibung` text,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Name` (`Name`,`Type`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;


CREATE TABLE `pre_techtree_reqs` (
  `ItemID` int(10) unsigned default NULL,
  `RequiresID` int(10) unsigned default NULL,
  UNIQUE KEY `ItemID` (`ItemID`,`RequiresID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `pre_techtree_stufen` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `ItemID` int(10) unsigned default NULL,
  `Stufe` int(10) unsigned default NULL,
  `Dauer` int(10) unsigned default NULL,
  `bauE` int(10) unsigned default NULL,
  `bauS` int(10) unsigned default NULL,
  `bauC` int(10) unsigned default NULL,
  `bauV` int(10) unsigned default NULL,
  `bauEis` int(10) unsigned default NULL,
  `bauW` int(10) unsigned default NULL,
  `bauEn` int(10) unsigned default NULL,
  `bauCr` int(10) unsigned default NULL,
  `bauBev` int(10) unsigned default NULL,
  `bauFP` int(10) unsigned default NULL,
  `E` int(11) default NULL,
  `S` int(11) default NULL,
  `C` int(11) default NULL,
  `V` int(11) default NULL,
  `Eis` int(11) default NULL,
  `W` int(11) default NULL,
  `En` int(11) default NULL,
  `Cr` int(11) default NULL,
  `Bev` int(11) default NULL,
  `FP` int(11) default NULL,
  `Sonstiges` varchar(255) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `ItemID` (`ItemID`,`Stufe`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


CREATE TABLE `pre_uni_userdata` (
  `name` char(64) default NULL,
  `allytag` char(10) default NULL,
  `updatetime` int(10) unsigned default NULL,
  UNIQUE KEY `name` (`name`),
  KEY `allytag` (`allytag`),
  KEY `name_2` (`name`,`allytag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `pre_universum` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `iwid` int(10) unsigned default '0',
  `gala` tinyint(3) unsigned NOT NULL,
  `sys` int(10) unsigned NOT NULL,
  `pla` tinyint(3) unsigned NOT NULL,
  `inserttime` int(10) unsigned default '0',
  `aktuell` tinyint(1) default '0',
  `planityp` enum('Nichts','Steinklumpen','Gasgigant','Asteroiden','Eisplanet') default NULL,
  `objekttyp` enum('---','Kolonie','Sammelbasis','Kampfbasis') default NULL,
  `ownername` char(64) default '',
  `planiname` char(64) default '',
  `punkte` int(10) unsigned default '0',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Koords` (`gala`,`sys`,`pla`),
  KEY `gala` (`gala`),
  KEY `sys` (`sys`),
  KEY `iwid` (`iwid`),
  KEY `ownername` (`ownername`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;


CREATE TABLE `pre_users` (
  `ID` int(10) unsigned NOT NULL auto_increment,
  `userName` varchar(127) NOT NULL default '',
  `pwmd5` varchar(32) NOT NULL default '',
  `isAdmin` int(1) unsigned default NULL,
  `visibleName` varchar(127) NOT NULL default '',
  `email` varchar(127) NOT NULL default '',
  `language` varchar(15) NOT NULL default '',
  `theme` varchar(15) NOT NULL default 'default',
  `lastactive` int(10) unsigned NOT NULL default '0',
  `active` enum('inactive','active','banned') NOT NULL default 'inactive',
  `ipsecurity` int(1) NOT NULL default '0',
  `sitterlogin` char(64) default '',
  `sitterpw` char(32) default '',
  `sitterflags` int(11) default '0',
  `sitterskin` tinyint(4) default '0',
  `sitteripchange` tinyint(1) default '0',
  PRIMARY KEY  (`ID`),
  KEY `userName` (`userName`,`pwmd5`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
INSERT INTO pre_users (ID, userName, pwmd5, isAdmin, visibleName, email, language, theme, lastactive, active, ipsecurity, sitterlogin, sitterpw, sitterflags, sitterskin, sitteripchange) VALUES ('1', 'grollicus', 'e638f7d51818758264fa897a551e5511', '1', 'grollicus', 'change@this.1337', 'english', 'default', '1154192663', 'active', '0', 'Xardas', 'sdklghslejk5rke', '1', '3', '1');


