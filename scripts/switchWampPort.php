<?php
// 3.2.0 Use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

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
	'/^(Listen 0.0.0.0:).*$/m',
	'/^(Listen \[::0\]:).*$/m',
	'/^(ServerName localhost:).*$/m',
	);
	$httpdFileContents = preg_replace($findTxtRegex,'${1}'.$portToUse, $httpdFileContents,1);

	write_file($c_apacheConfFile,$httpdFileContents);

	$virtualHost = check_virtualhost(true);

	//Change port into httpd-vhosts.conf
	if($virtualHost['include_vhosts'] && $virtualHost['vhosts_exist'] && $ChangeVhosts) {
		$c_vhostConfFile = $virtualHost['vhosts_file'];
		$myVhostsContents = file_get_contents($c_vhostConfFile) or die ("httpd-vhosts.conf file not found");
		$findTxtRegex = $replaceTxtRegex = array();
		$findTxtRegex[] = '/^([ \t]*<VirtualHost[ \t]+.+:)[^\$].*$/m';
		$replaceTxtRegex[] = '${1}'.$portToUse.'>';
		if(version_compare($wampConf['apacheVersion'], '2.4.0', '<')) {
			//Second element only for Apache 2.2
			$findTxtRegex[] = '/^([ \t]*NameVirtualHost).*$/m';
			$replaceTxtRegex[] = '${1} *:'.$portToUse;
		}

		$myVhostsContents = preg_replace($findTxtRegex,$replaceTxtRegex, $myVhostsContents);

		write_file($c_vhostConfFile,$myVhostsContents);
	}

	$apacheConf['apachePortUsed'] = $portToUse;
	if($portToUse == $c_DefaultPort)
		$apacheConf['apacheUseOtherPort'] = "off";
	else
		$apacheConf['apacheUseOtherPort'] = "on";
	wampIniSet($configurationFile, $apacheConf);
}
else {
	echo "您输入的端口: ".$portToUse."\n\n";
	echo "无效或不允许.\n";
	echo "\n按回车键(Enter)继续...";
  trim(fgets(STDIN));
}

?>
