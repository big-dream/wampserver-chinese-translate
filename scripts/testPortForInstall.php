<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';
$message = '';
$port_to_check = $wampConf['apachePortUsed'];
$reinstall = isset($_SERVER['argv'][1]) ? true : false;
$fp = @fsockopen("127.0.0.1", $port_to_check, $errno, $errstr, 1);
	$out = "GET / HTTP/1.1\r\n";
  $out .= "Host: 127.0.0.1\r\n";
  $out .= "Connection: Close\r\n\r\n";
if($fp) {
	$message .= "Your port '.$port_to_check.' is actually used by :\n";
	fwrite($fp, $out);
	while (!feof($fp)){
  	$line = fgets($fp, 128);
    if(preg_match('/Server: /',$line)){
    	$message .= $line;
      $gotInfo = 1;
    }
  }
  fclose($fp);
  if($gotInfo != 1)
  	$message .= "Information not available (might be Skype).\n";
  $message .= "Cannot install the Apache service, please stop this application and try again.\nPress Enter to exit...";
  Command_Windows($message,-1,-1,0,'Verify port for installation');
trim(fgets(STDIN));
}
else{
	if(!$reinstall) {
    $message .= "Your port '.$port_to_check.' is available, Install will proceed.\nPress Enter to continue...";
    Command_Windows($message,-1,-1,0,'Verify port for installation');
    trim(fgets(STDIN));
  }
}

?>