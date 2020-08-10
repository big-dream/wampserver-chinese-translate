<?php
//Script to rebuild symbolic links
//
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
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
	echo "停止 Apache 服务\n";
	$command = "net stop ".$c_apacheService;
	`$command`;
}

linkPhpDllToApacheBin($newPhpVersion);

$checkSymlinkResult = CheckSymlink($newPhpVersion);

if($wampConf['CreateSymlink'] == 'copy') {
	echo "启动 Apache 服务\n";
	$command = "net start ".$c_apacheService;
	`$command`;
}

if(!$doReport) {
	if($checkSymlinkResult !== true) {
		echo "***** 警告 *****\n\n";
		echo $checkSymlinkResult;
		echo "\n--- 是否要将结果复制到剪贴板?
	--- 输入 'y' 确认复制 - 按回车键(Enter)继续... ";
	  $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if ($confirm == 'y') {
			write_file("temp.txt",$checkSymlinkResult, true);
			exit(0);
		}
	}
	elseif($verify) {
		$symTxt = ($wampConf['CreateSymlink'] == 'copy') ? '复制的文件' : '建立的符号链接';
		echo "所有 ".$symTxt." 已完成\n\n";
		echo "按回车键(Enter)继续... ";
		fgetc(STDIN);
		exit(0);
	}
}
else {
	$message = "--------------------------------------------------\n";
	$message .=  "***** 检查符号链接 *****\n\n";
	if($checkSymlinkResult !== true)
		$message .= $checkSymlinkResult."\n";
	else
		$message .= "所有符号链接正常\n";
	write_file($c_installDir."/wampConfReportTemp.txt",$message,false,false,'ab');
	exit;
}

?>
