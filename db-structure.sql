-- phpMyAdmin SQL Dump
-- version 3.2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 11, 2014 at 07:17 PM
-- Server version: 5.5.35
-- PHP Version: 5.3.10-1ubuntu3.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `ancient-empires_3`
--

-- --------------------------------------------------------

--
-- Table structure for table `db_besonderheiten`
--

CREATE TABLE IF NOT EXISTS `db_besonderheiten` (
  `ID` int(10) unsigned DEFAULT NULL,
  `Name` char(32) DEFAULT NULL,
  `imp_kurzel` char(1) NOT NULL DEFAULT '',
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_bilanz`
--

CREATE TABLE IF NOT EXISTS `db_bilanz` (
  `planid` int(10) unsigned NOT NULL,
  `empfaenger` char(64) NOT NULL,
  `absender` char(64) NOT NULL,
  `zeit` int(10) unsigned NOT NULL,
  `eisen` int(10) unsigned NOT NULL,
  `stahl` int(10) unsigned NOT NULL,
  `chemie` int(10) unsigned NOT NULL,
  `vv4a` int(10) unsigned NOT NULL,
  `eis` int(10) unsigned NOT NULL,
  `wasser` int(10) unsigned NOT NULL,
  `energie` int(10) unsigned NOT NULL,
  `bev` int(10) unsigned NOT NULL,
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `planid_2` (`planid`,`absender`,`zeit`),
  KEY `empfaenger` (`empfaenger`),
  KEY `absender` (`absender`),
  KEY `zeit` (`zeit`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_building`
--

CREATE TABLE IF NOT EXISTS `db_building` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `plani` int(10) unsigned NOT NULL DEFAULT '0',
  `end` int(11) NOT NULL,
  KEY `uid` (`uid`,`plani`,`end`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_errors`
--

CREATE TABLE IF NOT EXISTS `db_errors` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `file` char(64) DEFAULT NULL,
  `line` int(11) DEFAULT NULL,
  `msg` text,
  `stacktrace` text,
  `request` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_events`
--

CREATE TABLE IF NOT EXISTS `db_events` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `event` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `db_feind_scans`
--

CREATE TABLE IF NOT EXISTS `db_feind_scans` (
  `dst` varchar(9) COLLATE utf8_bin NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `type` enum('sch','geb','geo') COLLATE utf8_bin NOT NULL,
  `start` varchar(9) COLLATE utf8_bin NOT NULL,
  `sender` varchar(255) COLLATE utf8_bin NOT NULL,
  `ally` varchar(16) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `dst` (`dst`,`time`,`type`,`start`,`sender`,`ally`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `db_flotten`
--

CREATE TABLE IF NOT EXISTS `db_flotten` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `startid` int(10) unsigned NOT NULL DEFAULT '0',
  `zielid` int(10) unsigned NOT NULL DEFAULT '0',
  `ankunft` int(10) unsigned NOT NULL DEFAULT '0',
  `nummer` int(10) unsigned NOT NULL,
  `firstseen` int(10) unsigned NOT NULL,
  `notyetseen` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('Angriff','Sondierung (Gebäude/Ress)','Sondierung (Geologie)','Sondierung (Schiffe/Def/Ress)','Transport','Übergabe','Ressourcenhandel (ok)','Ressourcenhandel','Basisaufbau (Kampf)') NOT NULL,
  `erinnerungsstatus` int(10) unsigned NOT NULL DEFAULT '0',
  `safe` tinyint(1) NOT NULL DEFAULT '0',
  `dont_save` int(11) NOT NULL DEFAULT '0',
  `stargate` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `zielid` (`zielid`),
  KEY `zielid_2` (`zielid`,`ankunft`,`action`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_geoscans`
--

CREATE TABLE IF NOT EXISTS `db_geoscans` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `eisen` int(11) NOT NULL,
  `chemie` int(11) NOT NULL,
  `eis` int(11) NOT NULL,
  `gravi` smallint(5) unsigned NOT NULL,
  `lbed` int(11) NOT NULL,
  `nebel` smallint(5) unsigned NOT NULL DEFAULT '0',
  `besonderheiten` int(11) NOT NULL,
  `fmod` smallint(5) unsigned DEFAULT NULL,
  `gebmod` smallint(5) unsigned DEFAULT NULL,
  `gebtimemod` smallint(5) unsigned DEFAULT NULL,
  `shipmod` smallint(5) unsigned DEFAULT NULL,
  `shiptimemod` smallint(5) unsigned DEFAULT NULL,
  `tt_eisen` int(11) NOT NULL,
  `tt_chemie` int(11) NOT NULL,
  `tt_eis` int(11) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `reset` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reset` (`reset`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_highscore`
--

CREATE TABLE IF NOT EXISTS `db_highscore` (
  `time` int(10) unsigned NOT NULL,
  `pos` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `ally` varchar(16) COLLATE utf8_bin NOT NULL,
  `gebp` int(10) unsigned NOT NULL,
  `forp` int(10) unsigned NOT NULL,
  `gesp` int(10) unsigned NOT NULL,
  `ppd` double NOT NULL,
  `diff` int(11) NOT NULL,
  `dabei` int(10) unsigned NOT NULL,
  PRIMARY KEY (`time`,`pos`),
  UNIQUE KEY `name` (`name`,`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `db_highscore_inactive`
--

CREATE TABLE IF NOT EXISTS `db_highscore_inactive` (
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `since` int(10) unsigned NOT NULL,
  `until` int(10) unsigned NOT NULL,
  `gebp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `db_igm_data`
--

CREATE TABLE IF NOT EXISTS `db_igm_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `igmname` varchar(32) DEFAULT NULL,
  `sitterpw` varchar(32) DEFAULT NULL,
  `realpw` varchar(32) DEFAULT NULL,
  `accounttyp` enum('fle','bud','mon','all') DEFAULT 'all',
  `squad` varchar(32) NOT NULL DEFAULT '',
  `ikea` tinyint(1) NOT NULL DEFAULT '0',
  `mdp` tinyint(1) NOT NULL DEFAULT '0',
  `iwsa` tinyint(1) NOT NULL DEFAULT '0',
  `tsdTrennZeichen` char(1) NOT NULL DEFAULT '',
  `Komma` char(1) NOT NULL DEFAULT ',',
  `bslen` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `lastLogin` int(10) unsigned NOT NULL DEFAULT '0',
  `lastParsed` int(10) unsigned NOT NULL,
  `forschung` int(10) unsigned NOT NULL,
  `forschung_ende` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `igmname` (`igmname`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_ip_bans`
--

CREATE TABLE IF NOT EXISTS `db_ip_bans` (
  `ip` char(15) NOT NULL DEFAULT '',
  `exceeds` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_irc_autologin`
--

CREATE TABLE IF NOT EXISTS `db_irc_autologin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `access` tinyint(4) NOT NULL DEFAULT '0',
  `mask` char(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mask` (`mask`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_iw_cache`
--

CREATE TABLE IF NOT EXISTS `db_iw_cache` (
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_kabafilter`
--

CREATE TABLE IF NOT EXISTS `db_kabafilter` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gala` int(10) unsigned DEFAULT NULL,
  `sys` int(10) unsigned DEFAULT NULL,
  `pla` int(10) unsigned DEFAULT NULL,
  `ownerName` varchar(255) DEFAULT NULL,
  `ownerAlly` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_lastest_scans`
--

CREATE TABLE IF NOT EXISTS `db_lastest_scans` (
  `planid` int(10) unsigned NOT NULL,
  `typ` enum('schiff','geb') NOT NULL,
  `scanid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`planid`,`typ`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_nebel`
--

CREATE TABLE IF NOT EXISTS `db_nebel` (
  `ID` int(10) unsigned DEFAULT NULL,
  `Name` char(32) DEFAULT NULL,
  `imp_kurzel` char(1) NOT NULL DEFAULT '',
  UNIQUE KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_raidberichte`
--

CREATE TABLE IF NOT EXISTS `db_raidberichte` (
  `iwid` int(10) unsigned NOT NULL,
  `hash` char(32) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `angreifer` char(32) DEFAULT NULL,
  `angrAlly` char(10) NOT NULL,
  `verteidiger` char(32) DEFAULT NULL,
  `verteidigerAlly` char(10) NOT NULL,
  `score` int(11) NOT NULL,
  `rFe` int(11) NOT NULL,
  `rSt` int(11) NOT NULL,
  `rCh` int(11) NOT NULL,
  `rVv` int(11) NOT NULL,
  `rEi` int(11) NOT NULL,
  `rWa` int(11) NOT NULL,
  `rEn` int(11) NOT NULL,
  `zFe` int(11) NOT NULL,
  `zSt` int(11) NOT NULL,
  `zCh` int(11) NOT NULL,
  `zVv` int(11) NOT NULL,
  `zEi` int(11) NOT NULL,
  `zWa` int(11) NOT NULL,
  `zEn` int(11) NOT NULL,
  PRIMARY KEY (`iwid`),
  KEY `time` (`time`),
  KEY `angreifer` (`angreifer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_requestids`
--

CREATE TABLE IF NOT EXISTS `db_requestids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `db_ressuebersicht`
--

CREATE TABLE IF NOT EXISTS `db_ressuebersicht` (
  `uid` int(10) unsigned NOT NULL,
  `planid` int(10) unsigned NOT NULL,
  `fe` int(10) unsigned NOT NULL,
  `st` int(10) unsigned NOT NULL,
  `vv` int(10) unsigned NOT NULL,
  `ch` int(10) unsigned NOT NULL,
  `ei` int(10) unsigned NOT NULL,
  `wa` int(10) unsigned NOT NULL,
  `en` int(10) unsigned NOT NULL,
  `fp` int(10) unsigned NOT NULL,
  `cr` int(10) unsigned NOT NULL,
  `bev` int(10) unsigned NOT NULL,
  `zu` int(10) unsigned NOT NULL,
  `vFe` int(11) NOT NULL,
  `vSt` int(11) NOT NULL,
  `vVv` int(11) NOT NULL,
  `vCh` int(11) NOT NULL,
  `vEi` int(11) NOT NULL,
  `vWa` int(11) NOT NULL,
  `vEn` int(11) NOT NULL,
  `vCr` int(11) NOT NULL,
  `vBev` int(11) NOT NULL,
  `vZu` int(11) NOT NULL,
  `lCh` int(10) unsigned NOT NULL,
  `lEi` int(10) unsigned NOT NULL,
  `lWa` int(10) unsigned NOT NULL,
  `lEn` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`planid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_scans`
--

CREATE TABLE IF NOT EXISTS `db_scans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iwid` int(10) unsigned DEFAULT NULL,
  `iwhash` char(32) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `gala` tinyint(3) unsigned NOT NULL,
  `sys` smallint(5) unsigned NOT NULL,
  `pla` tinyint(3) unsigned NOT NULL,
  `typ` enum('schiff','geb') NOT NULL,
  `planityp` enum('Nichts','Steinklumpen','Gasgigant','Asteroid','Eisplanet','Stargate','Elektrosturm','Raumverzerrung','Ionensturm','grav. Anomalie') NOT NULL,
  `objekttyp` enum('---','Kolonie','Sammelbasis','Kampfbasis','Raumstation','Artefaktbasis') NOT NULL,
  `basistyp` enum('-','Alpha','Beta','Gamma') NOT NULL,
  `ownername` varchar(32) NOT NULL,
  `ownerally` varchar(10) NOT NULL,
  `fe` int(10) unsigned NOT NULL,
  `st` int(10) unsigned NOT NULL,
  `vv` int(10) unsigned NOT NULL,
  `ch` int(10) unsigned NOT NULL,
  `ei` int(10) unsigned NOT NULL,
  `wa` int(10) unsigned NOT NULL,
  `en` int(10) unsigned NOT NULL,
  `warid` int(10) unsigned NOT NULL DEFAULT '0',
  `ressScore` int(10) unsigned NOT NULL DEFAULT '0',
  `score` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `time` (`time`,`gala`,`sys`,`pla`),
  UNIQUE KEY `iwid` (`iwid`),
  KEY `gala` (`gala`,`sys`,`pla`),
  KEY `warid` (`warid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_scans_flotten`
--

CREATE TABLE IF NOT EXISTS `db_scans_flotten` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scanid` int(10) unsigned NOT NULL,
  `owner` char(32) NOT NULL,
  `typ` enum('planetar','stationiert') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kbid` (`scanid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_scans_flotten_schiffe`
--

CREATE TABLE IF NOT EXISTS `db_scans_flotten_schiffe` (
  `flid` int(10) unsigned NOT NULL,
  `schid` int(10) unsigned NOT NULL,
  `anz` int(10) unsigned NOT NULL,
  PRIMARY KEY (`flid`,`schid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_scans_gebs`
--

CREATE TABLE IF NOT EXISTS `db_scans_gebs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scanid` int(10) unsigned NOT NULL,
  `gebid` int(10) unsigned NOT NULL,
  `anzahl` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kbid` (`scanid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_sitter`
--

CREATE TABLE IF NOT EXISTS `db_sitter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `igmid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `type` enum('Geb','For','Sch','Sonst') DEFAULT 'Sonst',
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `itemid` int(10) unsigned NOT NULL DEFAULT '0',
  `stufe` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `planID` int(10) unsigned NOT NULL DEFAULT '0',
  `usequeue` tinyint(1) NOT NULL DEFAULT '0',
  `anzahl` int(10) unsigned NOT NULL DEFAULT '0',
  `FollowUpTo` int(11) NOT NULL DEFAULT '0',
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `FollowUpTo` (`FollowUpTo`),
  KEY `uid` (`uid`),
  KEY `igmid` (`igmid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_sitterlog`
--

CREATE TABLE IF NOT EXISTS `db_sitterlog` (
  `userid` int(11) NOT NULL,
  `victimid` int(11) NOT NULL,
  `type` enum('auftrag','login') NOT NULL,
  `time` int(11) NOT NULL,
  `text` varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  KEY `userid` (`userid`),
  KEY `victimid` (`victimid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_sittermember`
--

CREATE TABLE IF NOT EXISTS `db_sittermember` (
  `groupid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `level` tinyint(2) DEFAULT NULL,
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_speedlog`
--

CREATE TABLE IF NOT EXISTS `db_speedlog` (
  `action` varchar(63) NOT NULL DEFAULT '',
  `script` tinyint(1) DEFAULT '0',
  `sub` varchar(63) NOT NULL DEFAULT '',
  `runtime` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_techtree_items`
--

CREATE TABLE IF NOT EXISTS `db_techtree_items` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `Type` enum('geb','for','schiff','def') DEFAULT NULL,
  `depth` int(10) unsigned DEFAULT '0',
  `global` tinyint(4) DEFAULT NULL,
  `Class` tinyint(4) DEFAULT NULL,
  `MaxLevel` tinyint(3) unsigned DEFAULT '0',
  `Gebiet` varchar(64) DEFAULT NULL,
  `BenPlanityp` varchar(16) NOT NULL,
  `BenKolotyp` varchar(16) DEFAULT NULL,
  `Beschreibung` text,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`name`,`Type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_techtree_reqs`
--

CREATE TABLE IF NOT EXISTS `db_techtree_reqs` (
  `ItemID` int(10) unsigned DEFAULT NULL,
  `RequiresID` int(10) unsigned DEFAULT NULL,
  UNIQUE KEY `ItemID` (`ItemID`,`RequiresID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_techtree_stufen`
--

CREATE TABLE IF NOT EXISTS `db_techtree_stufen` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ItemID` int(10) unsigned DEFAULT NULL,
  `Stufe` int(10) unsigned DEFAULT NULL,
  `Dauer` int(10) unsigned DEFAULT NULL,
  `bauE` int(10) unsigned DEFAULT NULL,
  `bauS` int(10) unsigned DEFAULT NULL,
  `bauC` int(10) unsigned DEFAULT NULL,
  `bauV` int(10) unsigned DEFAULT NULL,
  `bauEis` int(10) unsigned DEFAULT NULL,
  `bauW` int(10) unsigned DEFAULT NULL,
  `bauEn` int(10) unsigned DEFAULT NULL,
  `bauCr` int(10) unsigned DEFAULT NULL,
  `bauBev` int(10) unsigned DEFAULT NULL,
  `bauFP` int(10) unsigned DEFAULT NULL,
  `E` int(11) DEFAULT NULL,
  `S` int(11) DEFAULT NULL,
  `C` int(11) DEFAULT NULL,
  `V` int(11) DEFAULT NULL,
  `Eis` int(11) DEFAULT NULL,
  `W` int(11) DEFAULT NULL,
  `En` int(11) DEFAULT NULL,
  `Cr` int(11) DEFAULT NULL,
  `Bev` int(11) DEFAULT NULL,
  `FP` int(11) DEFAULT NULL,
  `Sonstiges` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ItemID` (`ItemID`,`Stufe`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_techtree_useritems`
--

CREATE TABLE IF NOT EXISTS `db_techtree_useritems` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `itemid` int(10) unsigned NOT NULL DEFAULT '0',
  `count` int(10) unsigned DEFAULT NULL,
  `coords` char(9) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`,`itemid`,`coords`),
  KEY `uid` (`uid`),
  KEY `uid_2` (`uid`,`coords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_temp`
--

CREATE TABLE IF NOT EXISTS `db_temp` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `value` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `db_texts`
--

CREATE TABLE IF NOT EXISTS `db_texts` (
  `Name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_trade_history`
--

CREATE TABLE IF NOT EXISTS `db_trade_history` (
  `time` int(10) unsigned NOT NULL,
  `type` enum('new','del','edit') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'edit',
  `sender` int(10) unsigned NOT NULL,
  `receiver` int(10) unsigned NOT NULL,
  `dst` char(9) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ress` enum('eisen','stahl','chem','vv4a','eis','wasser','energie','credits','bev','schiff') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `SchiffID` int(10) unsigned NOT NULL,
  `resscnt` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_trade_ignores`
--

CREATE TABLE IF NOT EXISTS `db_trade_ignores` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `end` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_trade_reqs`
--

CREATE TABLE IF NOT EXISTS `db_trade_reqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `ziel` char(9) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ress` enum('eisen','stahl','chem','vv4a','eis','wasser','energie','credits','bev','schiff') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `soll` int(11) NOT NULL DEFAULT '0',
  `ist` int(11) NOT NULL DEFAULT '0',
  `SchiffID` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_universum`
--

CREATE TABLE IF NOT EXISTS `db_universum` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iwid` int(10) unsigned NOT NULL DEFAULT '0',
  `gala` tinyint(3) unsigned NOT NULL,
  `sys` int(10) unsigned NOT NULL,
  `pla` tinyint(3) unsigned NOT NULL,
  `inserttime` int(10) unsigned NOT NULL DEFAULT '0',
  `aktuell` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `planityp` enum('Nichts','Steinklumpen','Gasgigant','Asteroid','Eisplanet','Stargate','Elektrosturm','Raumverzerrung','Ionensturm','grav. Anomalie') NOT NULL DEFAULT 'Nichts',
  `objekttyp` enum('---','Kolonie','Sammelbasis','Kampfbasis','Raumstation','Artefaktbasis') NOT NULL,
  `ownername` char(64) NOT NULL DEFAULT '',
  `planiname` char(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Koords` (`gala`,`sys`,`pla`),
  KEY `gala` (`gala`),
  KEY `sys` (`sys`),
  KEY `iwid` (`iwid`),
  KEY `ownername` (`ownername`),
  KEY `ownername_2` (`ownername`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_uni_userdata`
--

CREATE TABLE IF NOT EXISTS `db_uni_userdata` (
  `name` char(64) NOT NULL DEFAULT '',
  `allytag` char(10) DEFAULT NULL,
  `updatetime` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `allytag` (`allytag`),
  KEY `name_2` (`name`,`allytag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_users`
--

CREATE TABLE IF NOT EXISTS `db_users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(127) NOT NULL DEFAULT '',
  `pwmd5` varchar(32) NOT NULL DEFAULT '',
  `isAdmin` int(1) unsigned DEFAULT NULL,
  `isRestricted` int(1) unsigned NOT NULL DEFAULT '0',
  `visibleName` varchar(127) NOT NULL DEFAULT '',
  `email` varchar(127) NOT NULL DEFAULT '',
  `language` varchar(15) NOT NULL DEFAULT '',
  `theme` varchar(15) NOT NULL DEFAULT 'default',
  `lastactive` int(10) unsigned NOT NULL DEFAULT '0',
  `active` enum('inactive','active','banned') NOT NULL DEFAULT 'inactive',
  `igmuser` int(10) unsigned NOT NULL DEFAULT '0',
  `ipsecurity` int(1) NOT NULL DEFAULT '0',
  `sitterflags` int(11) DEFAULT '0',
  `sitterskin` tinyint(4) DEFAULT '0',
  `forschungs_plani` char(11) NOT NULL DEFAULT '',
  `sitterpts` int(11) NOT NULL DEFAULT '0',
  `sittertime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `userName_2` (`userName`),
  KEY `userName` (`userName`,`pwmd5`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_wars`
--

CREATE TABLE IF NOT EXISTS `db_wars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_bin NOT NULL,
  `allytag` varchar(64) COLLATE utf8_bin NOT NULL,
  `begin` int(10) unsigned NOT NULL DEFAULT '0',
  `end` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `db_war_kbs`
--

CREATE TABLE IF NOT EXISTS `db_war_kbs` (
  `iwid` int(10) unsigned NOT NULL,
  `hash` char(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `att` varchar(128) NOT NULL,
  `attally` varchar(64) NOT NULL,
  `def` varchar(128) NOT NULL,
  `defally` varchar(64) NOT NULL,
  `attvalue` int(10) unsigned NOT NULL,
  `attloss` int(10) unsigned NOT NULL,
  `defvalue` int(10) unsigned NOT NULL,
  `defloss` int(10) unsigned NOT NULL,
  `raidvalue` int(10) unsigned NOT NULL,
  `bombvalue` int(10) unsigned NOT NULL,
  `attwin` tinyint(1) NOT NULL,
  `start` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `dst` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `warid` int(10) unsigned NOT NULL,
  `fake` tinyint(4) unsigned NOT NULL,
  PRIMARY KEY (`iwid`),
  KEY `timestamp` (`warid`,`timestamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_war_schedule`
--

CREATE TABLE IF NOT EXISTS `db_war_schedule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `db_war_stats`
--

CREATE TABLE IF NOT EXISTS `db_war_stats` (
  `id` int(10) unsigned NOT NULL,
  `stats` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

