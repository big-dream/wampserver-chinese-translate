<?php

//Script to rebuild symbolic links
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$newPhpVersion = trim($_SERVER['argv'][1]);
$verify = (!empty($_SERVER['argv'][2])) ? true : false;
$doReport = (!empty($_SERVER['argv'][3]) && $_SERVER['argv'][3] == 'doreport') ? true : false;
$noCreate = (!empty($_SERVER['argv'][4]) && $_SERVER['argv'][4] == 'nocreate') ? true : false;
$message = '';

// Re-create symbolic links
if(!$noCreate) linkPhpDllToApacheBin($newPhpVersion);

$checkSymlinkResult = CheckSymlink($newPhpVersion);

if(!$doReport) {
	if($checkSymlinkResult !== true) {
		$message .= color('red')."\n***** WARNING *****\n\n";
		$message .=  $checkSymlinkResult.color('reset');
		$message .= "\n--- Do you want to copy the results into Clipboard?\n--- Type 'y' to confirm - Press ENTER to continue...\n";
		Command_Windows($message,-1,-1,0,'Verify Symbolik links');
	  $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if($confirm == 'y') {
			write_file("temp.txt",$checkSymlinkResult, true);
			exit(0);
		}
	}
	elseif($verify) {
		$message .= "All symbolic links are OK\n\n";
		$message .= "Press ENTER to continue... ";
		Command_Windows($message,50,-1,0,'Verify Symbolik links');
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
