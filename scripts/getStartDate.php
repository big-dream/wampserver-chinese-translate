<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

// --- DateTime of launch into string like "2021-08-24 10:17"
$WampStartOnOri = IntlDateFormatter::formatObject(new DateTime('now'),'Y-MM-d HH:mm:ss');

//modifying wampmanager.conf
$wampIniNewContents['wampStartDate'] = $WampStartOnOri;
wampIniSet($configurationFile, $wampIniNewContents);

?>