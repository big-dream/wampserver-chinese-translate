<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require_once 'config.inc.php';
require_once 'wampserver.lib.php';

$message = '';

$c_listenPort = listen_ports($c_apacheConfFile);

$action = trim($_SERVER['argv'][1]);
$portToTreat = intval(trim($_SERVER['argv'][2]));

$goodPort = true;

if($action == 'add') {
	//Check validity
	if($portToTreat <= 80 || $portToTreat == 8080 || ($portToTreat > 81 && $portToTreat < 1025) || $portToTreat > 65535 || in_array($portToTreat,$c_listenPort))
		$goodPort = false;

	if($goodPort) {//---
		$httpdFileContents = file_get_contents($c_apacheConfFile);
		$count = 0;
		$search = array(
			"~^(Define[ \t]+APACHE_DIR.*VERSION_APACHE})~m",
			"~^(Listen[ \t]+\[::0\]:".$c_UsedPort.")~m",
		);
		$replace = array (
			'${1}'."\r\n".'Define MYPORT'.$portToTreat.' '.$portToTreat,
			'${1}'."\r\n".'Listen 0.0.0.0:${MYPORT'.$portToTreat.'}'."\r\n".'Listen [::0]:${MYPORT'.$portToTreat.'}',
		);
		$httpdFileContents = preg_replace($search,$replace,$httpdFileContents, -1, $count);
		if($count == 2) {
			write_file($c_apacheConfFile,$httpdFileContents);
		}
	}//--
}
elseif($action == 'delete') {
	$goodPort = true;
	//httpd.conf file
	$httpdFileContents = file_get_contents($c_apacheConfFile);
	//Check if variable to delete is used in httpd-vhosts.conf
	$httpdVhostFileContents = file_get_contents($c_apacheVhostConfFile);
	if(strpos($httpdVhostFileContents,'MYPORT'.$portToTreat) !== false) {
		$message .= "The port number you give: ".$portToTreat."\n\n";
		$message .= "is used in httpd-vhosts.conf file as port number\n";
		$message .= "with Apache variable \${MYPORT".$portToTreat."}\n\n";
		$message .= "If you delete the Listen Port ".$portToTreat."\n";
		$message .= "it will be replaced by port ".$c_UsedPort."\n";
		$message .= "\nPress the Y key then ENTER for Y - Press ENTER only to exit";
		Command_Windows($message,-1,-1,0,'Listen port Apache');
  	$rep = strtoupper(trim(fgets(STDIN)));
  	if($rep <> 'Y')	exit;
	}
	$count = 0;
	$search = array(
		"~^(Define[ \t]+MYPORT".$portToTreat."[ \t]+".$portToTreat."\r?\n?)~m",
		"~^(Listen[ \t]+.*MYPORT".$portToTreat."\}\r?\n?)~m",
		"~^(Listen[ \t]+0.0.0.0:".$portToTreat."\r?\n?)~m",
		"~^(Listen[ \t]+[::0]:".$portToTreat."\r?\n?)~m",
	);
	$replace = '';
	$httpdFileContents = preg_replace($search,$replace,$httpdFileContents, -1, $count);
	if($count > 0) {
		$httpdFileContents = clean_file_contents($httpdFileContents,array(3,2));
		write_file($c_apacheConfFile,$httpdFileContents);
	}
	//httpd-vhosts.conf file
	$count = 0;
	$search = '~\$\{MYPORT'.$portToTreat.'\}~mi';
	$replace = $c_UsedPort;
	$httpdVhostFileContents = preg_replace($search,$replace,$httpdVhostFileContents, -1, $count);
	if($count > 0) {
		$c_apacheVhostConfFile = clean_file_contents($c_apacheVhostConfFile,array(3,2));
		write_file($c_apacheVhostConfFile,$httpdVhostFileContents);
	}
}

if(!$goodPort) {
	$message .= "The port number you give: ".$portToTreat."\n\n";
	$message .= "is not valid or already used or is default port\n";
	$message .= "\nPress ENTER to continue...";
	Command_Windows($message,-1,-1,0,'Listen port Apache');
  trim(fgets(STDIN));
}

?>
