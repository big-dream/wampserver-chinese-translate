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

$wampConfFile = $c_installDir."/wampmanager.conf";
$wampConfFileContents = @file_get_contents($wampConfFile) or die ("wampmanager.conf file not found");

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
�������ֵ({$newvalue})������Χ.
�����ֵ��������������Χ��10��9999֮�䡣
�ѽ�ֵ��ΪĬ�ϵ� 300 ��.
EOFERROR;
		$newvalue = '300';
		}
	}
	elseif($choose == 'Size') {
		$newvalue = strtoupper($newvalue);
		if(preg_match('/^[1-9][0-9]{1,3}(M|G)$/m',$newvalue) != 1) {
		$changeError = <<< EOF1ERROR
�������ֵ({$newvalue})������Χ.
�����ֵ��������������Χ��10��9999֮��.
���ֺ��������ŵ�λ��M��G.
�ѽ�ֵ��ΪĬ�ϵ� 128M.
EOF1ERROR;
		$newvalue = '128M';
		}
	}
	elseif($choose == 'Integer') {
		$newvalue = intval($newvalue);
		list($min, $max, $default) = explode("^",$_SERVER['argv'][5]);
		if($newvalue < $min || $newvalue > $max) {
		$changeError = <<< EOF2ERROR
�������ֵ({$newvalue})������Χ.
�����ֵ��������������Χ�� {$min} �� {$max} ֮��.
�ѽ�ֵ��ΪĬ�ϵ� {$default}.
EOF2ERROR;
		$newvalue = $default;
		}
	}
}
if($quoted)
	$newvalue = '"'.$newvalue.'"';

$wampConfFileContents = preg_replace('|^'.$parameter.'[ \t]*=.*|m',$parameter.' = '.$newvalue,$wampConfFileContents, -1, $count);

if($count > 0) {
	write_file($wampConfFile,$wampConfFileContents);
}

if(!empty($changeError)) {
	echo "********************* WARNING ********************\n\n";
	echo $changeError;
	echo "\n���س���(ENTER)����...";
  trim(fgets(STDIN));
}

?>