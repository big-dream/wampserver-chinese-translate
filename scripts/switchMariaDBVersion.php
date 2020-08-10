<?php
//3.2.3 - check for sc cmd in place of mysqld.exe --install
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
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
if(!array_key_exists('mariadbServiceCmd',$mariadbConf)) {
	$mariadbConf['mariadbServiceCmd'] = $mariadbConf['mariadbExeFile'];
}
$mariadbConf['mariadbVersion'] = $newMariaDBVersion;

wampIniSet($configurationFile, $mariadbConf);
?>