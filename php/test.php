<?php
$str = 'Das Universum - unendliche Weiten
Galaxy : 7, Sonnensystem 115

 
 
<-- Galaxy  Sonnensystem  -->

Planet		Planetentyp	Objekttyp	Name	Allianztag	Planetenname	Punkte	Aktionen
0		
1		Steinklumpen	Kolonie	Blacky	[UCA]	Provinzial	98071	Flotte versenden
2		Steinklumpen	Kolonie	Iron-Fist	[UCA]	Children of Dune	64462	Flotte versenden
3		Steinklumpen	Kolonie	Torineichenschild	[=MAST=] (HC)	Alderan	119790	Flotte versenden
Geoscan
4		Gasgigant	---			-	0	Flotte versenden
Geoscan
5		Steinklumpen
2 Flotte(n) 	Kolonie	Schaf im Wolfspelz	[YETI]	Dingends	114378	Flotte versenden
6		Steinklumpen
1 Flotte(n) 	Kolonie	Schaf im Wolfspelz	[YETI]	Weide	15157	Flotte versenden
7		Gasgigant	Sammelbasis	Azira	[-AE-] (HC)	Oelquelle	0	Flotte versenden
8		Steinklumpen	Kolonie	spartan_117	[3D]	Reach	52510	Flotte versenden
9		Nichts	Kampfbasis	Brokkus	[-AE-]	A100	0	Flotte versenden
10		Steinklumpen	Kolonie	MoW	[-BL-] (HC)	bin	54656	Flotte versenden
Geoscan
11		Steinklumpen	Kolonie	Babi	[UCA]	BABI Third	187978	Flotte versenden
12		Gasgigant	Kampfbasis	Ogi der Frosch	[-AE-]	Basis von Ogi	0	Flotte versenden
13		Steinklumpen	Kolonie	eisi_eis	[UCA]	Höllywood	69447	Flotte versenden
14		Steinklumpen	Kolonie	Babi	[UCA]	Babi Seven	19803	Flotte versenden
15		Steinklumpen	Sammelbasis	Falke	[=UAC=]	RessBasis von Falke	0	Flotte versenden
Geoscan
16		Asteroiden	---			-	0	Flotte versenden
Geoscan
17		Steinklumpen	Kolonie	MoW	[-BL-] (HC)	doof	10101	Flotte versenden
Geoscan';

$arr = explode("\n", str_replace("\t", " ", str_replace(" \t", " ", str_replace("\r", "\n", str_replace("\r\n", "\n", $str)))));
$res = array();

foreach($arr as $value) {
	echo '<pre><i>';
	echo $value;
	echo '</i>'."\n";
	//if(preg_match('~(\d+)\s\s(\w+)\s([\w\-]+)\s(.*)\s(\[.+\]|)\s(.+)\s(\d+)\sFlotte\sversenden~', $value, $res))
	if(preg_match('~Galaxy\s\:\s(\d+)\,\sSonnensystem\s(\d+)~', $value, $res))
		print_r($res);	
	elseif(preg_match('~(\d+)\s\s(Steinklumpen|Gasgigant|Asteroiden|Eisplanet|Nichts)\s(Kolonie|Kampfbasis|Sammelbasis|\-\-\-)\s(.*)\s(\[.+\]|)\s(.+)\s(\d+)\sFlotte\sversenden~', $value, $res))
		print_r($res);
	elseif(preg_match('~^(\d+\sFlotte\(n\)|\d+\sFlotte\(n\)\s|)\s(Kolonie|Kampfbasis|Sammelbasis|\-\-\-)\s(.*)\s(\[.+\]|)\s(.+)\s(\d+)\sFlotte\sversenden~', $value, $res))
		print_r($res);
	elseif(preg_match('~(\d+)\s\s(Steinklumpen|Gasgigant|Asteroiden|Eisplanet|Nichts)~', $value, $res))
		print_r($res);
	echo '</pre><hr />';
}

?>
