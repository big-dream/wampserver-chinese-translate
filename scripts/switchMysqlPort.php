<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';
$message = '';
$minPort = "3301";
$maxPort = "3309";

//Replace UsedMysqlPort by NewMysqlport ($_SERVER['argv'][1])
$portToUse = intval(trim($_SERVER['argv'][1]));
//Check validity
$goodPort = true;
if($portToUse < $minPort || $portToUse > $maxPort || $portToUse == $wampConf['mariaPortUsed'])
	$goodPort = false;
//If nocheck second parameter
if(!empty($_SERVER['argv'][2]) && $_SERVER['argv'][2] == 'nocheck')
	$goodPort = true;

$myIniReplace = false;

if($goodPort) {
	//Change port into my.ini
	$mySqlIniFileContents = @file_get_contents($c_mysqlConfFile) or die ("my.ini file not found");
	$nb_myIni = 0; //must be three replacements: [client], [wampmariadb] and [mysqld] groups
	//Find already used ports
	$portCount = preg_match_all('/^port[ \t]*=[ \t]*('.$portToUse.').*$/m',$mySqlIniFileContents,$matches);
	//If the port number already exists three times, there is nothing to change.
	if($portCount !== 3) {
		$findTxtRegex = '/^((port[ \t]*=[ \t]*)[0-9]*)/m';
		preg_match_all($findTxtRegex,$mySqlIniFileContents,$matches);
		reset($matches[2]);
		foreach($matches[1] as $value) {
			$value2 = current($matches[2]);
			$mySqlIniFileContents = str_replace($value,$value2.$portToUse,$mySqlIniFileContents,$count);
			if($count > 0) $myIniReplace = true;
			next($matches[2]);
		}

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
	$message .= color('red')."The port number you give: ".$portToUse."\n";
	$message .= "is not valid".color('black')."\n";
	$message .= "Must be between ".$minPort." and ".$maxPort."\nbut not ".$wampConf['mariaPortUsed']." that is already used by MariaDB\n";
	$message .= "\nPress ENTER to continue...";
	Command_Windows($message,-1,-1,0,'Check port for MySQL');
  trim(fgets(STDIN));
}

?>
