<?php
// 3.2.0 - Use write_file function instead of fopen, fwrite, fclose
//         Improvement of process and PID research
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
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
$message .=  "***** ���� ".$port." �˿� *****\n\n";
if($doReport) echo $message;
$message .=  "===== ".$port." �˿ڲ��Խ������ netstat ����ص�����  =====\n\n";
//Port tested by netstat for TCP and TCPv6
$tcp = array('TCP', 'TCPv6');
foreach($tcp as $value) {
$command = 'netstat -anop '.$value.' | FINDSTR /C:":'.$port.'"';
$output = `$command`;
//error_log("output=".$output);
if(!empty($output)) {
	$message .=  "\n���� ".$value."\n";
	if(preg_match("~^[ \t]*TCP.*:".$port." .*LISTENING[ \t]*([0-9]{1,5}).*$~m", $output, $pid) > 0) {
		$message .=  $port." �˿��ѱ� PID = ".$pid[1]." �Ľ���ʹ��\n";
		$command = 'tasklist /FI "PID eq '.$pid[1].'" /FO TABLE /NH';
		$output = `$command`;
		if(!empty($output)) {
			if(preg_match("~^(.+[^ \t])[ \t]+".$pid[1]." ([a-zA-Z]+[^ \t]*).+$~m", $output, $matches) > 0) {
				$message .=  "����PID ".$pid[1]." ���� '".$matches[1]."' �Ự ".$matches[2]."\n";
				$command = 'tasklist /SVC | FINDSTR /C:"'.$pid[1].'"';
				$output = `$command`;
				if(!empty($output)) {
					if(preg_match("~^(.+[^ \t])[ \t]+".$pid[1]." ([a-zA-Z]+[^ \t]*).+$~m", $output, $matches) > 0) {
						$message .=  "����PID ".$pid[1]." ���� '".$matches[1]."' ������ '".$matches[2]."'\n";
						if($matches[2] == $_SERVER['argv'][2])
							$message .=  "����Wampserver�ķ��� - �������\n";
						else {
							if($mysqlTest) {
								if($matches[2] == $c_mysqlService || $matches[2] == $c_mariadbService) {
									$forwhat = "MySQL �� MariaDB";
								  $message .= "�÷���(".$matches[2].")���� Wampserver �� ".$forwhat."\n";
								}
								else {
									if($matches[2] != 'N/A') {
										$message .=  "*** ���� *** Wampserver �ƺ�û�и÷���\n��ȷֵ: '".$c_mysqlService."' �� '".$c_mariadbService."'\n";
										}
									else {
										$message .= $matches[2]." û���� PID ".$pid[1]." ��صķ���\n";
										if($wampConf['SupportMySQL'] == 'on' && version_compare($c_mysqlVersion,'8.0.0', '>=')) {
											$command = 'tasklist /SVC /FI "IMAGENAME eq mysqld.exe" | FINDSTR /C:"'.$c_mysqlService.'"';
											$output = `$command`;
											if(!empty($output)) {
												if(preg_match("~^(mysqld\.exe)[ \t]+([0-9]+)[ \t]+(".$c_mysqlService.").*$~m",$output, $matches) > 0) {
													$message .= "'".$matches[1]."' ������ '".$matches[3]."' �������� ���� PID ".$matches[2]."\n";
												}
											}
										}
									}
								}
							}
							else
								$message .=  "*** ���� *** �ⲻ�� Wampserver �ķ��� - ��ȷֵ: '".$_SERVER['argv'][2]."'\n";
						}
					}
				}
			}
			else
				$message .=  "�޷��������б����ҵ� PID ".$pid[1]." �Ľ���\n";
		}
	}
	else
	 	$message .=  "�Ҳ��� ".$port." �˿� TCP Э����ص���Ϣ\n";
}
else
	$message .=  "�Ҳ��� ".$port." �˿� TCP Э����ص���Ϣ\n";
}

if(!$only_process) {
	$message .=  "\n===== ʹ�� socket ���� ".$port." �˿� =====\n\n";
	//Port tested by open socket
	$fp = @fsockopen("127.0.0.1", $port, $errno, $errstr, 2);
	$out = "GET / HTTP/1.1\r\n";
	$out .= "Host: 127.0.0.1\r\n";
	$out .= "Connection: Close\r\n\r\n";
	if(!$fp) {
		$message .= $port." �ƺ�δ��ʹ��.\n�޷����� socket ͨ��.\n";
		if($errno == 0) {
			$message .= "�޷����� connect().\n�޷����� socket ����.\n";
		}
		else {
			$message .= "������: ".$errno." - ������Ϣ: ".$errstr."\n";
		}

	}
	else {
		$message .=   $port." �˿��ѱ���ʹ�� :\n\n";
		$gotInfo = false;
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
		$message .= "δ�ҵ�ռ�øö˿ڵĳ�����Ϣ (������ Skype �� IIS).\n";
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
		echo "\n--- �Ƿ�Ҫ��������Ƶ�������?\n
--- ���� 'y' ȷ�ϸ��� - ���س�����ENTER������...";
    $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if ($confirm == 'y') {
			write_file("temp.txt",$message, true);
		}
		exit();
 	}

echo '

���س�����ENTER���˳�...';
trim(fgets(STDIN));

?>
