<?php
//3.0.7
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'wampserver.lib.php';
require 'config.inc.php';

$newMariaDBVersion = $_SERVER['argv'][1];

//on charge le fichier de conf de la nouvelle version
require $c_mariadbVersionDir.'/mariadb'.$newMariaDBVersion.'/'.$wampBinConfFiles;

$mariadbConf['mariadbVersion'] = $newMariaDBVersion;

wampIniSet($configurationFile, $mariadbConf);
?>