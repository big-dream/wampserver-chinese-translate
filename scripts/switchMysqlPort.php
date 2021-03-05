<?php
// 3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
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
if($portToUse < $minPort || $portToUse > $maxPort || $portToUse == $wampConf['mariaPortUsed'])
	$goodPort = false;

$myIniReplace = false;

if($goodPort) {
	//Change port into my.ini
	$mySqlIniFileContents = @file_get_contents($c_mysqlConfFile) or die ("my.ini file not found");
	$nb_myIni = 0; //must be three replacements: [client], [wampmysqld] and [mysqld] groups
	//Find already used ports
	$portCount = preg_match_all('/^port[ \t]*=[ \t]*('.$portToUse.').*$/m',$mySqlIniFileContents,$matches);
	//If the port number already exists three times, there is nothing to change.
	if($portCount !== 3) {
		$findTxtRegex = array(
		'/^(port)[ \t]*=.*$/m',
		);
		$mySqlIniFileContents = preg_replace($findTxtRegex,"$1 = ".$portToUse, $mySqlIniFileContents, -1, $nb_myIni);
		if($nb_myIni == 3)
			$myIniReplace = true;

		if($myIniReplace) {
			write_file($c_mysqlConfFile,$mySqlIniFileContents);
			$myIniConf['mysqlPortUsed'] = $portToUse;
			if($portToUse == $c_DefaultMysqlPort)
				$myIniConf['mysqlUseOtherPort'] = "off";
			else
				$myIniConf['mysqlUseOtherPort'] = "on";
			wampIniSet($configurationFile, $myIniConf);
		}
	}
}

else {
	echo "您输入的端口: ".$portToUse."\n\n";
	echo "无效\n";
	echo "允许范围 ".$minPort." 至 ".$maxPort."\n但 ".$wampConf['mariaPortUsed']." 不是为 MariaDB 保留的\n";
	echo "\n按回车键（ENTER）继续...";
  trim(fgets(STDIN));
}

?>
