<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$myIniFileContents = @file_get_contents($c_mysqlConfFile) or die ("my.ini file not found");

if($_SERVER['argv'][2] == 'off')
{
    $findTxt  = $_SERVER['argv'][1].' = On';
    $replaceTxt  = $_SERVER['argv'][1].' = Off';
}
else
{
    $findTxt  = $_SERVER['argv'][1].' = Off';
    $replaceTxt  = $_SERVER['argv'][1].' = On';
}


$myIniFileContents = str_ireplace($findTxt,$replaceTxt,$myIniFileContents);

write_file($c_mysqlConfFile,$myIniFileContents);

?>