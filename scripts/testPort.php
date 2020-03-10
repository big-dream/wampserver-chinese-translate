<?php
// 3.2.0 - Use write_file function instead of fopen, fwrite, fclose
//         Improvement of process and PID research
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$only_process = false;
$mysqlTest = false;
$responselines = '';
if(!empty($_SERVER['argv'][2])) {
	if($_SERVER['argv'][2] == $c_mysqlService || $_SERVER['argv'][2] == $c_mariadbService) {
		$port = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '3306';
		$only_process = true;
		$mysqlTest = true;
	}
	else
		$port = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '80';
}

$doReport = (!empty($_SERVER['argv'][3]) && $_SERVER['argv'][3] == 'doreport') ? true : false;
$message = ($doReport ? "--------------------------------------------------\n" : '');
$message .=  "***** Test which uses port ".$port." *****\n\n";
if($doReport) echo $message;
$message .=  "===== Tested by command netstat filtered on port ".$port." =====\n\n";
//Port tested by netstat for TCP and TCPv6
$tcp = array('TCP', 'TCPv6');
foreach($tcp as $value) {
$command = 'netstat -anop '.$value.' | FINDSTR /C:":'.$port.'"';
$output = `$command`;
//error_log("output=".$output);
if(!empty($output)) {
	$message .=  "\nTest for ".$value."\n";
	if(preg_match("~^[ \t]*TCP.*:".$port." .*LISTENING[ \t]*([0-9]{1,5}).*$~m", $output, $pid) > 0) {
		$message .=  "Your port ".$port." is used by a processus with PID = ".$pid[1]."\n";
		$command = 'tasklist /FI "PID eq '.$pid[1].'" /FO TABLE /NH';
		$output = `$command`;
		if(!empty($output)) {
			if(preg_match("~^(.+[^ \t])[ \t]+".$pid[1]." ([a-zA-Z]+[^ \t]*).+$~m", $output, $matches) > 0) {
				$message .=  "The processus of PID ".$pid[1]." is '".$matches[1]."' Session: ".$matches[2]."\n";
				$command = 'tasklist /SVC | FINDSTR /C:"'.$pid[1].'"';
				$output = `$command`;
				if(!empty($output)) {
					if(preg_match("~^(.+[^ \t])[ \t]+".$pid[1]." ([a-zA-Z]+[^ \t]*).+$~m", $output, $matches) > 0) {
						$message .=  "The service of PID ".$pid[1]." for '".$matches[1]."' is '".$matches[2]."'\n";
						if($matches[2] == $_SERVER['argv'][2])
							$message .=  "This service is from Wampserver - It is correct\n";
						else {
							if($mysqlTest) {
								if($matches[2] == $c_mysqlService || $matches[2] == $c_mariadbService) {
									$forwhat = "MySQL or MariaDB";
								  $message .= "This service ".$matches[2]." is from Wampserver for ".$forwhat."\n";
								}
								else {
									if($matches[2] != 'N/A') {
										$message .=  "*** WARNING *** This service does not seem from Wampserver\nShould be: '".$c_mysqlService."' or '".$c_mariadbService."'\n";
										}
									else {
										$message .= $matches[2]." means that there are no service related to PID ".$pid[1]."\n";
										if($wampConf['SupportMySQL'] == 'on' && version_compare($c_mysqlVersion,'8.0.0', '>=')) {
											$command = 'tasklist /SVC /FI "IMAGENAME eq mysqld.exe" | FINDSTR /C:"'.$c_mysqlService.'"';
											$output = `$command`;
											if(!empty($output)) {
												if(preg_match("~^(mysqld\.exe)[ \t]+([0-9]+)[ \t]+(".$c_mysqlService.").*$~m",$output, $matches) > 0) {
													$message .= "Processus '".$matches[1]."' is launched by service '".$matches[3]."' with PID ".$matches[2]."\n";
												}
											}
										}
									}
								}
							}
							else
								$message .=  "*** ERROR *** This service IS NOT from Wampserver - Should be: '".$_SERVER['argv'][2]."'\n";
						}
					}
				}
			}
			else
				$message .=  "The processus of PID ".$pid[1]." is not found with tasklist\n";
		}
	}
	else
	 	$message .=  "Port ".$port." is not found associated with TCP protocol\n";
}
else
	$message .=  "Port ".$port." is not found associated with TCP protocol\n";
}

if(!$only_process) {
	$message .=  "\n===== Tested by attempting to open a socket on port ".$port." =====\n\n";
	//Port tested by open socket
	$fp = @fsockopen("127.0.0.1", $port, $errno, $errstr, 2);
	$out = "GET / HTTP/1.1\r\n";
	$out .= "Host: 127.0.0.1\r\n";
	$out .= "Connection: Close\r\n\r\n";
	if(!$fp) {
		$message .= "Your port ".$port." seems not actually used.\nUnable to initiate a socket connection\n";
		if($errno == 0) {
			$message .= "Error occurred before the call to connect().\nLets assume that the socket could not be initialized.\n";
		}
		else {
			$message .= "Error number: ".$errno." - Error string: ".$errstr."\n";
		}

	}
	else {
		$message .=   "Your port ".$port." is actually used by :\n\n";
		$gotinfo = false;
		fwrite($fp, $out);
		while (!feof($fp)) {
			$line = fgets($fp, 128);
			$responselines .= $line;
			if (preg_match('#Server:#',$line))	{
				$message .=  $line;
				$gotInfo = true;
			}
		}
		fclose($fp);
		if ($gotInfo != true) {
		$message .= "Server information not available (might be Skype or IIS).\n";
		//if(!empty($responselines) && is_string($responseline))
			//$message .= "Response is: ".$responselines."\n";
		}
	}
}
if($doReport){
	write_file($c_installDir."/wampConfReportTemp.txt",$message,false,false,'ab');
	exit;
}

echo $message;
	if(!empty($message)) {
		echo "\n--- Do you want to copy the results into Clipboard?
--- Type 'y' to confirm - Press ENTER to continue...";
    $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if ($confirm == 'y') {
			write_file("temp.txt",$message, true);
		}
		exit();
 	}

echo '

Press Enter to exit...';
trim(fgets(STDIN));

?>
