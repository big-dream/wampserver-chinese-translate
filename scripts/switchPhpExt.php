<?php
// Update 3.2.0 use write_file instead of fwrite, fclose
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$phpIniFileContents = @file_get_contents($c_phpConfFile) or die ("php.ini file not found");
$dll = $reg_dll = $zend = $quote = '';
if(strpos($_SERVER['argv'][1],'php_') !== false) {
	$dll = '.dll';
	$reg_dll = '\.dll';
}
$mode = $_SERVER['argv'][2];
if(strpos($mode,'zend') !== false) {
	$zend = 'zend_';
	$quote = '"';
	$mode = substr($mode,4);
	$dll = '.dll';
	$reg_dll = '\.dll';
}

// on remplace la ligne
if ($mode == 'on') {
	if(preg_match('~^;'.$zend.'extension\s*=\s*"?'.$_SERVER['argv'][1].$reg_dll.'"?~im',$phpIniFileContents,$matchesOFF) !== false)
		$findTxt = $matchesOFF[0];
	else
		$findTxt  = ';'.$zend.'extension='.$_SERVER['argv'][1].$dll;
	$replaceTxt  = $zend.'extension='.$quote.$_SERVER['argv'][1].$dll.$quote;
}
elseif ($mode == 'off') {
	if(preg_match('~^'.$zend.'extension\s*=\s*"?'.$_SERVER['argv'][1].$reg_dll.'"?~im',$phpIniFileContents,$matchesON) !== false)
		$findTxt = $matchesON[0];
	else
		$findTxt  = $zend.'extension='.$_SERVER['argv'][1].$dll;
	$replaceTxt  = ';'.$zend.'extension='.$quote.$_SERVER['argv'][1].$dll.$quote;
}
else
	exit;
$phpIniFileContents2 = str_replace($findTxt,$replaceTxt,$phpIniFileContents);


// on ajoute la ligne si elle n'existe pas
if ($phpIniFileContents2 == $phpIniFileContents) {
	$findTxt  = <<< EOF
;;;;;;;;;;;;;;;;;;;
; Module Settings ;
EOF;

	$replaceTxt  = <<< EOF
	{$zend}extension={$quote}{$_SERVER['argv'][1]}{$dll}{$quote}
;;;;;;;;;;;;;;;;;;;
; Module Settings ;
EOF;

	$phpIniFileContents2 = str_replace($findTxt,$replaceTxt,$phpIniFileContents);
}

write_file($c_phpConfFile,$phpIniFileContents2);

?>