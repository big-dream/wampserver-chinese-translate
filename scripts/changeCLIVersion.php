<?php
//3.2.0 Possibility to trace
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';


$newPhpVersion = $_SERVER['argv'][1];

//modifying the conf of WampServer
$wampIniNewContents['phpCliVersion'] = $newPhpVersion;
wampIniSet($configurationFile, $wampIniNewContents);


?>