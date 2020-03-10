<?php
// Update 3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$phpIniFileContents = @file_get_contents($c_phpConfFile) or die ("php.ini file not found");

if ($_SERVER['argv'][2] == 'off') {
	if(preg_match('/^'.$_SERVER['argv'][1].'\s*=\s*On/im',$phpIniFileContents,$matchesON) !== false)
		$findTxt = $matchesON[0];
	else
		$findTxt  = $_SERVER['argv'][1].' = On';
	$replaceTxt  = $_SERVER['argv'][1].' = Off';
}
else {
	if(preg_match('/^'.$_SERVER['argv'][1].'\s*=\s*Off/im',$phpIniFileContents,$matchesOFF) !== false)
		$findTxt = $matchesOFF[0];
	else
		$findTxt  = $_SERVER['argv'][1].' = Off';
	$replaceTxt  = $_SERVER['argv'][1].' = On';
}

$phpIniFileContents = str_ireplace($findTxt,$replaceTxt,$phpIniFileContents);

write_file($c_phpConfFile,$phpIniFileContents);

?>