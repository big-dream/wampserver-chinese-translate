<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$newMysqlVersion = $_SERVER['argv'][1];

//on charge le fichier de conf de la nouvelle version
require $c_mysqlVersionDir.'/mysql'.$newMysqlVersion.'/'.$wampBinConfFiles;
if(!array_key_exists('mysqlServiceCmd',$mysqlConf)) {
	$mysqlConf['mysqlServiceCmd'] = $mysqlConf['mysqlExeFile'];
}
$mysqlConf['mysqlVersion'] = $newMysqlVersion;
wampIniSet($configurationFile, $mysqlConf);

?>