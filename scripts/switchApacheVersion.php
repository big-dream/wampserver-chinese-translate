<?php
//3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$newApacheVersion = $_SERVER['argv'][1];

// loading the configuration file of the current php
require $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampBinConfFiles;

// it is verified that the new version of Apache is compatible with the current php
$newApacheVersionTemp = $newApacheVersion;
while (!isset($phpConf['apache'][$newApacheVersionTemp]) && $newApacheVersionTemp != '')
{
    $pos = strrpos($newApacheVersionTemp,'.');
    $newApacheVersionTemp = substr($newApacheVersionTemp,0,$pos);
}
if ($newApacheVersionTemp == '')
{
    exit();
}

//In case of *copy* PHP dll files and phpForApache.ini instead of create symbolic link
//save php.ini of Apache bin folder into phpForApache.ini of active PHP version
if($wampConf['CreateSymlink'] == 'copy') {
	$target = $c_phpVersionDir."/php".$c_phpVersion."/".$phpConfFileForApache;
	$link = $c_apacheVersionDir."/apache".$wampConf['apacheVersion']."/".$wampConf['apacheExeDir']."/php.ini";
	//error_log("copy ".$link." to ".$target);
	if(copy($link, $target) === false) {
		error_log("Error while copy '".$link."' to '".$target."' using php copy() function");
	}
}

// loading Wampserver configuration file of the new version of Apache
require $c_apacheVersionDir.'/apache'.$newApacheVersion.'/'.$wampBinConfFiles;

// copy of VirtualHost between Apache version of the 2.4 branch
if(substr($wampConf['apacheVersion'],0,3) == '2.4' && substr($newApacheVersion,0,3) == '2.4' && ($newApacheVersion != $c_apacheVersion)) {
	$oldVhost = $c_apacheVhostConfFile;
	$newVhost = $c_apacheVersionDir.'/apache'.$newApacheVersion.'/'.$wampConf['apacheConfDir'].'/extra/httpd-vhosts.conf';
	//if identical files, copy no asked
	$content1 = file($oldVhost, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$content2 = file($newVhost, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$diff = array_diff($content1, $content2);
	if(count($diff) > 0) {
		$virtualHost = check_virtualhost();
		$copyFile = false;
		if($virtualHost['include_vhosts'] && $virtualHost['vhosts_exist'] && $virtualHost['nb_Server'] > 0) {
			echo "\n\n**********************************************************\n";
			echo "** Want to copy the VirtualHost already configured for Apache ".$c_apacheVersion."\n";
			echo "** to Apache ".$newApacheVersion."?\n\n";
			echo "Hit y then Enter for 'YES' - Enter for 'NO'\n\n";
			$touche = strtoupper(trim(fgets(STDIN)));
			if($touche === "Y") {
				if(copy($oldVhost,$newVhost) === false) {
					echo "\n\n**** Copy error ****\n\nPress ENTER to continue...\n";
					trim(fgets(STDIN));
				}
				else
					$copyFile = true;
			}
		}
		//Check Include conf/extra/httpd-vhosts.conf uncommented in new Apache version
		if($copyFile) {
			$c_apacheNewConfFile = $c_apacheVersionDir.'/apache'.$newApacheVersion.'/'.$wampConf['apacheConfDir'].'/'.$wampConf['apacheConfFile'];
			$httpConfFileContents = file_get_contents($c_apacheNewConfFile);
			$httpConfFileContents = preg_replace("~^[ \t]*#[ \t]*(Include[ \t]*conf/extra/httpd-vhosts.conf.*)$~m","$1",$httpConfFileContents,1,$count);
			if($count == 1) {
				write_file($c_apacheNewConfFile,$httpConfFileContents);
			}
		}
	}
	//Check added Apache listen ports
	$c_listenPort = listen_ports();
	$portList = '';
	foreach($c_listenPort as $value) {
		if($value != '80')
			$portList .= " '".$value."'";
	}
	if(!empty($portList)) {

		echo "\n\n**********************************************************\n";
		echo "** Listen port(s) ".$portList." are added to Apache ".$c_apacheVersion."\n";
		echo "** \n** You may need to add them to the new version ".$newApacheVersion." of Apache\n";
		echo "**\n**\n** Press ENTER to continue...\n";
		trim(fgets(STDIN));
	}
}

$apacheConf['apacheVersion'] = $newApacheVersion;
wampIniSet($configurationFile, $apacheConf);


?>