<?php
//Script to rebuild symbolic links
//
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$newPhpVersion = $_SERVER['argv'][1];
$verify = (!empty($_SERVER['argv'][2])) ? true : false;
$doReport = (!empty($_SERVER['argv'][3]) && $_SERVER['argv'][3] == 'doreport') ? true : false;

if($wampConf['CreateSymlink'] == 'copy') {
	echo "Stop Apache service\n";
	$command = "net stop ".$c_apacheService;
	`$command`;
}

linkPhpDllToApacheBin($newPhpVersion);

$checkSymlinkResult = CheckSymlink($newPhpVersion);

if($wampConf['CreateSymlink'] == 'copy') {
	echo "Start Apache service\n";
	$command = "net start ".$c_apacheService;
	`$command`;
}

if(!$doReport) {
	if($checkSymlinkResult !== true) {
		echo "***** WARNING *****\n\n";
		echo $checkSymlinkResult;
		echo "\n--- Do you want to copy the results into Clipboard?
	--- Type 'y' to confirm - Press ENTER to continue... ";
	  $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if ($confirm == 'y') {
			write_file("temp.txt",$checkSymlinkResult, true);
			exit(0);
		}
	}
	elseif($verify) {
		$symTxt = ($wampConf['CreateSymlink'] == 'copy') ? 'copied files' : 'symbolic links';
		echo "All ".$symTxt." are OK\n\n";
		echo "Press ENTER to continue... ";
		fgetc(STDIN);
		exit(0);
	}
}
else {
	$message = "--------------------------------------------------\n";
	$message .=  "***** Check symbolic links *****\n\n";
	if($checkSymlinkResult !== true)
		$message .= $checkSymlinkResult."\n";
	else
		$message .= "All symbolic links are OK\n";
	write_file($c_installDir."/wampConfReportTemp.txt",$message,false,false,'ab');
	exit;
}

?>
