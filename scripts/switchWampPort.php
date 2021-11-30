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

//Replace Used Port by New port ($_SERVER['argv'][1])
$portToUse = intval(trim($_SERVER['argv'][1]));

$ChangeVhosts = (empty($_SERVER['argv'][2])) ? true : false;

//Check validity
$goodPort = true;
if($portToUse < 80 || ($portToUse > 81 && $portToUse < 1025) || $portToUse > 65535)
	$goodPort = false;

if($goodPort) {
	//Change port into httpd.conf
	$httpdFileContents = @file_get_contents($c_apacheConfFile ) or die ("httpd.conf file not found");
	$findTxtRegex = array(
	'/^(Listen 0.0.0.0:)[0-9]{2,5}/m',
	'/^(Listen \[::0\]:)[0-9]{2,5}/m',
	'/^(ServerName localhost:)[0-9]{2,5}/m',
	);
	$search = $replace = array();
	foreach($findTxtRegex as $value) {
		if(preg_match_all($value,$httpdFileContents,$matches,PREG_SET_ORDER) > 0) {
			foreach($matches as $key => $value) {
				if($value[0] <> $value[1].$portToUse) {
					$search[] = $value[0];
					$replace[] = $value[1].$portToUse;
				}
			}
		}
	}
	if(count($search) > 0) {
		$httpdFileContents = str_replace($search,$replace,$httpdFileContents,$count);
		if($count > 0) write_file($c_apacheConfFile,$httpdFileContents);
	}

	$virtualHost = check_virtualhost(true);

	//Change port into httpd-vhosts.conf
	if($virtualHost['include_vhosts'] && $virtualHost['vhosts_exist'] && $ChangeVhosts) {
		$c_vhostConfFile = $virtualHost['vhosts_file'];
		$myVhostsContents = file_get_contents($c_vhostConfFile) or die ("httpd-vhosts.conf file not found");
		$findTxtRegex = '/^([ \t]*<VirtualHost[ \t]+.+:)([0-9]{2,5})>/m';
		$replaceTxtRegex = '${1}'.$portToUse.'>';

		preg_match_all($findTxtRegex,$myVhostsContents,$matches);
		$count = 0;
		foreach($matches[2] as $key => $value) {
			if($value <> $portToUse) {
				$myVhostsContents = str_replace($matches[0][$key],$matches[1][$key].$portToUse.'>',$myVhostsContents,$nb);
				$count += $nb;
			}
		}

		//$myVhostsContents = preg_replace($findTxtRegex,$replaceTxtRegex, $myVhostsContents, -1, $count);
		if($count > 0) write_file($c_vhostConfFile,$myVhostsContents);
	}

	$apacheConf['apachePortUsed'] = $portToUse;
	if($portToUse == $c_DefaultPort) {
		$apacheConf['apacheUseOtherPort'] = "off";
	}
	else {
		$apacheConf['apacheUseOtherPort'] = "on";
	}

	wampIniSet($configurationFile, $apacheConf);
}
else {
	$message .= color('red')."The port number you give: ".$portToUse."\n\n";
	$message .= "is not valid or is not allowed.".color('black')."\n";
	$message .= "\nPress ENTER to continue... ";
  Command_Windows($message,-1,-1,0,'Switch Wampmanager port');
  trim(fgets(STDIN));
}

?>
