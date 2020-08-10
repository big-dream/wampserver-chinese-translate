<?php
////3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$myIniFileContents = @file_get_contents($c_mariadbConfFile) or die ("my.ini file not found");

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
		$newvalue = '60';
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
}
if($quoted)
	$newvalue = '"'.$newvalue.'"';

//if sql-mode
$count = 0;
if($parameter == 'sql-mode') {
	if($newvalue == 'none') {
		$myIniFileContents = preg_replace('/^sql-mode.*$/m',';${0}',$myIniFileContents,-1, $count);
		if(strpos($myIniFileContents,";sql-mode=\"\"") !== false) {
			$myIniFileContents = str_replace(";sql-mode=\"\"","sql-mode=\"\"",$myIniFileContents,$count);
		}
		else {
			//add sql-mode="" under section [wampmariadb]
			$section = '['.$c_mariadbService.']';
			$addTxt = 'sql-mode=""';
			$myIniFileContents = str_replace($section,$section."\r\n".$addTxt,$myIniFileContents,$count);
		}
	}
	elseif($newvalue == 'default') {
		$myIniFileContents = preg_replace('/^sql-mode.*$/m',';${0}',$myIniFileContents,-1, $count);
	}
	elseif($newvalue == 'user') {
		$myIniFileContents = preg_replace('/^sql-mode.*$/m',';${0}',$myIniFileContents);
		$myIniFileContents = preg_replace('/^;(sql-mode[ \t]*=[ \t]*"[^"].*)$/m','${1}',$myIniFileContents,-1, $count);
	}
}
else {
	//Number of replacements limited to 1 to replace only in the first section [wampmysqld]
	$myIniFileContents = preg_replace('|^'.$parameter.'[ \t]*=.*|m',$parameter.' = '.$newvalue,$myIniFileContents, 1, $count);
}

if($count > 0) {
	write_file($c_mariadbConfFile,$myIniFileContents);
}
if(!empty($changeError)) {
	echo "********************* WARNING ********************\n\n";
	echo $changeError;
	echo "\n按回车键(ENTER)继续...";
  trim(fgets(STDIN));
}

?>