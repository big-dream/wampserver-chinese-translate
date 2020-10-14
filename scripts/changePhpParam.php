<?php
//3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$phpIniFileContents = @file_get_contents($c_phpConfFile) or die ("php.ini file not found");

$quoted = false;
if($_SERVER['argv'][1] == 'quotes')
	$quoted = true;
$parameter = $_SERVER['argv'][2];
$newvalue = $_SERVER['argv'][3];
$changeError = '';

if(!empty($_SERVER['argv'][4])) {
	$choose = $_SERVER['argv'][4];
	if($choose == 'Seconds') {
		if(preg_match('/^[1-9][0-9]{1,3}$/m',$newvalue) != 1) {
		$changeError = <<< EOFERROR
您输入的值({$newvalue})超出范围.
输入的值必须是整数，范围在10至9999之间。
已将值设为默认的 60 秒.
EOFERROR;
		$newvalue = '300';
		}
	}
	elseif($choose == 'Size') {
		$newvalue = strtoupper($newvalue);
		if(preg_match('/^[1-9][0-9]{1,3}(M|G)$/m',$newvalue) != 1) {
		$changeError = <<< EOF1ERROR
您输入的值({$newvalue})超出范围.
输入的值必须是整数，范围在10至9999之间.
数字后面必须跟着单位，M或G.
已将值设为默认的 128M.
EOF1ERROR;
		$newvalue = '128M';
		}
	}
	elseif($choose == 'Integer') {
		$newvalue = intval($newvalue);
		list($min, $max, $default) = explode("^",$_SERVER['argv'][5]);
		if($newvalue < $min || $newvalue > $max) {
		$changeError = <<< EOF2ERROR
您输入的值({$newvalue})超出范围.
输入的值必须是整数，范围在 {$min} 至 {$max} 之间.
已将值设为默认的 {$default}.
EOF2ERROR;
		$newvalue = $default;
		}
	}
}
if($quoted)
	$newvalue = '"'.$newvalue.'"';

$phpIniFileContents = preg_replace('|^'.$parameter.'[ \t]*=.*|m',$parameter.' = '.$newvalue,$phpIniFileContents, -1, $count);

if($count > 0) {
	write_file($c_phpConfFile,$phpIniFileContents);
}

// Check if we need to modify also CLI php.ini and $c_phpConfFileIni
if(in_array($parameter,$phpCLIparams)) {
	//error_log("aussi dans CLI=".$c_phpCliConfFile);
	$phpIniCLIFileContents = @file_get_contents($c_phpCliConfFile) or die ("php.ini 文件未找到");
	$phpIniCLIFileContents = preg_replace('|^'.$parameter.'[ \t]*=.*|m',$parameter.' = '.$newvalue,$phpIniCLIFileContents, -1, $count);

	if($count > 0) {
		write_file($c_phpCliConfFile,$phpIniCLIFileContents);
	}
	if($c_phpConfFileIni <> $c_phpCliConfFile) {
		//error_log("aussi dans CLI=".$c_phpConfFileIni);
		$phpIniFileContentsIni = @file_get_contents($c_phpConfFileIni) or die ("php.ini 文件未找到");
		$phpIniFileContentsIni = preg_replace('|^'.$parameter.'[ \t]*=.*|m',$parameter.' = '.$newvalue,$phpIniFileContentsIni, -1, $count);

		if($count > 0) {
			write_file($c_phpConfFileIni,$phpIniFileContentsIni);
		}
	}
}

if(!empty($changeError)) {
	echo "********************* WARNING ********************\n\n";
	echo $changeError;
	echo "\n按回车键(ENTER)继续...";
  trim(fgets(STDIN));
}

?>