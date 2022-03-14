<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$PHP_fcgi = false;
$PHP_version = $c_phpVersion;
$PHP_ini_file = $c_phpConfFile;
if(isset($_SERVER['argv'][5])) {
	$PhpVersionType = trim($_SERVER['argv'][5]);
	if(strpos($PhpVersionType,'FCGI') !== false) {
		$PHP_fcgi = true;
		$PHP_version = str_ireplace('FCGI','',$PhpVersionType);
		$PHP_ini_file = $c_phpVersionDir.'/php'.$PHP_version.'/php.ini';
	}
}

$phpIniFileContents = @file_get_contents($PHP_ini_file) or die ("php.ini file not found");

$quoted = false;
if($_SERVER['argv'][1] == 'quotes')
	$quoted = true;

$parameter = $_SERVER['argv'][2];
$newvalue = $_SERVER['argv'][3];
$changeError = '';

if($_SERVER['argv'][4] != 'none') {
	$choose = $_SERVER['argv'][4];
	if($choose == 'Seconds') {
		if(preg_match('/^[1-9][0-9]{1,3}$/m',$newvalue) != 1) {
		$changeError = <<< EOFERROR
The value you entered ({$newvalue}) is out of range.
The number of seconds must be between 10 and 9999.
The value is set to 300 seconds by default.
EOFERROR;
		$newvalue = '300';
		}
	}
	elseif($choose == 'Size') {
		$newvalue = strtoupper($newvalue);
		if(preg_match('/^[1-9][0-9]{1,3}(M|G)$/m',$newvalue) != 1) {
		$changeError = <<< EOF1ERROR
The value you entered ({$newvalue}) is out of range.
The number must be between 10 and 9999.
The number must be followed by M (For Mega) or G (For Giga)
The value is set to 128M by default.
EOF1ERROR;
		$newvalue = '128M';
		}
	}
	elseif(strpos($choose,'Integer') !== false ) {
		$choose = str_replace('Integer','',$choose);
		$newvalue = intval($newvalue);
		list($min, $max, $default) = explode("^",$choose);
		if($newvalue < $min || $newvalue > $max) {
		$changeError = <<< EOF2ERROR
The value you entered ({$newvalue}) is out of range.
The number must be between {$min} and {$max}.
And must be an integer value.
The value is set to {$default} by default.
EOF2ERROR;
		$newvalue = $default;
		}
	}
}
if($quoted)
	$newvalue = '"'.$newvalue.'"';

$phpIniFileContents = preg_replace('|^'.$parameter.'[ \t]*=.*|m',$parameter.' = '.$newvalue,$phpIniFileContents, -1, $count);

if($count > 0) {
	write_file($PHP_ini_file,$phpIniFileContents);
}

if(!empty($changeError)) {
	$message = color('red',"********************* WARNING ********************\n\n");
	$message .= $changeError;
	$message .= "\nPress ENTER to continue...";
	Command_Windows($message,-1,-1,0,'Change PHP parameter');
  trim(fgets(STDIN));
}

?>