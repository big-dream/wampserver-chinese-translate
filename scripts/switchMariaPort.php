<?php
// script to change MariaDB port used
// 3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$minPort = "3301";
$maxPort = "3309";

//Replace UsedMysqlPort by NewMysqlport ($_SERVER['argv'][1])
$portToUse = intval(trim($_SERVER['argv'][1]));
//Check validity
$goodPort = true;
if($portToUse < $minPort || $portToUse > $maxPort || $portToUse == $wampConf['mysqlPortUsed'])
	$goodPort = false;

$myIniReplace = false;

if($goodPort) {
	//Change port into my.ini
	$mariaIniFileContents = @file_get_contents($c_mariadbConfFile) or die ("my.ini file not found");
	$nb_myIni = 0; //must be three replacements: [client], [wampmariadb] and [mysqld] groups
	$findTxtRegex = array(
	'/^(port)[ \t]*=.*$/m',
	);
	$mariaIniFileContents = preg_replace($findTxtRegex,"$1 = ".$portToUse, $mariaIniFileContents, -1, $nb_myIni);
	if($nb_myIni == 3)
		$myIniReplace = true;

	if($myIniReplace) {
		write_file($c_mariadbConfFile,$mariaIniFileContents);
		$myIniConf['mariaPortUsed'] = $portToUse;
		if($portToUse == $c_DefaultMysqlPort)
			$myIniConf['mariaUseOtherPort'] = "off";
		else
			$myIniConf['mariaUseOtherPort'] = "on";
		wampIniSet($configurationFile, $myIniConf);
	}
}

else {
	echo "������Ķ˿�: ".$portToUse."\n\n";
	echo "��Ч\n";
	echo "����Χ ".$minPort." �� ".$maxPort."\n�� ".$wampConf['mysqlPortUsed']." ����Ϊ MySQL ������\n";
	echo "\n���س���(Enter)����...";
  trim(fgets(STDIN));
}

?>
