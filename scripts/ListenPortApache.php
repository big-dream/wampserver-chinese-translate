<?php
// 3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';


$c_listenPort = listen_ports();

//action ($_SERVER['argv'][1])
$action = trim($_SERVER['argv'][1]);
//port ($_SERVER['argv'][2])
$portToTreat = intval(trim($_SERVER['argv'][2]));

$goodPort = true;

if($action == 'add') {
	//Check validity
	if($portToTreat <= 80 || $portToTreat == 8080 || ($portToTreat > 81 && $portToTreat < 1025) || $portToTreat > 65535 || in_array($portToTreat,$c_listenPort))
		$goodPort = false;

	if($goodPort) {

		$httpdFileContents = file_get_contents($c_apacheConfFile);
		$count = 0;

		$search = array(
			"~^([ \t]*Define[ \t]+APACHE_DIR[ \t]+.*)\s?$~m",
			"~^([ \t]*Listen[ \t]+\[::0\]:".$c_UsedPort.")\s?$~m",
		);
		$replace = array (
			'${1}'."\r\n".'Define MYPORT'.$portToTreat.' '.$portToTreat,
			'${1}'."\r\n".'Listen 0.0.0.0:${MYPORT'.$portToTreat.'}'."\r\n".'Listen [::0]:${MYPORT'.$portToTreat.'}',
		);
		$httpdFileContents = preg_replace($search,$replace,$httpdFileContents, -1, $count);
		if($count == 2) {
			write_file($c_apacheConfFile,$httpdFileContents);
		}
	}
}
elseif($action == 'delete') {
	$goodPort = true;
	//httpd.conf file
	$httpdFileContents = file_get_contents($c_apacheConfFile);
	//Check if variable to delete is used in httpd-vhosts.conf
	$httpdVhostFileContents = file_get_contents($c_apacheVhostConfFile);
	if(strpos($httpdVhostFileContents,'MYPORT'.$portToTreat) !== false) {
		echo "您输入的端口: ".$portToTreat."\n\n";
		echo "禁止在 httpd-vhosts.conf 文件使用Apache变量用作端口号\n";
		echo "变量 \${MYPORT".$portToTreat."}\n";
		echo "\n按回车键(Enter)继续...";
  	trim(fgets(STDIN));
  	exit;
	}
	$count = 0;
	$search = array(
		"~^Define[ \t]+MYPORT".$portToTreat."[ \t]+.*\s?$~m",
		"~^Listen[ \t]+.*MYPORT".$portToTreat.".*\s?$~m",
	);
	$replace = array (
		'',
		'',
	);
	$httpdFileContents = preg_replace($search,$replace,$httpdFileContents, -1, $count);
	if($count == 3) {
		$httpdFileContents = clean_file_contents($httpdFileContents,array(3,2));
		write_file($c_apacheConfFile,$httpdFileContents);
	}
	//httpd-vhosts.conf file
	$httpdVhostFileContents = file_get_contents($c_apacheVhostConfFile);
	$count = 0;
	$search = '~\$\{MYPORT'.$portToTreat.'}~mi';
	$replace = $c_UsedPort;
	if($count > 0) {
		write_file($c_apacheVhostConfFile,$httpdVhostFileContents);
	}
}

if(!$goodPort) {
	echo "您输入的端口: ".$portToTreat."\n\n";
	echo "无效/已使用或是默认端口\n";
	echo "\n按回车键(Enter)继续...";
  trim(fgets(STDIN));
}

?>
