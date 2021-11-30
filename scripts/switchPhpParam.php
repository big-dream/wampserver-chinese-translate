<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$phpIniFileContents = @file_get_contents($c_phpConfFile) or die ("php.ini file not found");

if($_SERVER['argv'][2] == 'off') {
	$findTxt  = $_SERVER['argv'][1].' = On';
	$replaceTxt  = $_SERVER['argv'][1].' = Off';
	$regex = 'on';
}
elseif($_SERVER['argv'][2] == 'on') {
	$findTxt  = $_SERVER['argv'][1].' = Off';
	$replaceTxt  = $_SERVER['argv'][1].' = On';
	$regex = 'off';
}
elseif($_SERVER['argv'][2] == '0') {
	$findTxt  = $_SERVER['argv'][1].' = 1';
	$replaceTxt  = $_SERVER['argv'][1].' = 0';
	$regex = '1';
}
elseif($_SERVER['argv'][2] == '1') {
	$findTxt  = $_SERVER['argv'][1].' = 0';
	$replaceTxt  = $_SERVER['argv'][1].' = 1';
	$regex = '0';
}

if(preg_match('/^'.$_SERVER['argv'][1].'\s*=\s*'.$regex.'/im',$phpIniFileContents,$matchesON) === 1)
	$findTxt = $matchesON[0];

$phpIniFileContents = str_ireplace($findTxt,$replaceTxt,$phpIniFileContents);

write_file($c_phpConfFile,$phpIniFileContents);

?>