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
$message .=  "***** 测试 ".$port." 端口 *****\n\n";
if($doReport) echo $message;
$message .=  "===== ".$port." 端口测试结果来自 netstat 命令返回的内容  =====\n\n";
//Port tested by netstat for TCP and TCPv6
$tcp = array('TCP', 'TCPv6');
foreach($tcp as $value) {
$command = 'netstat -anop '.$value.' | FINDSTR /C:":'.$port.'"';
$output = `$command`;
//error_log("output=".$output);
if(!empty($output)) {
	$message .=  "\n测试 ".$value."\n";
	if(preg_match("~^[ \t]*TCP.*:".$port." .*LISTENING[ \t]*([0-9]{1,5}).*$~m", $output, $pid) > 0) {
		$message .=  $port." 端口已被 PID = ".$pid[1]." 的进程使用\n";
		$command = 'tasklist /FI "PID eq '.$pid[1].'" /FO TABLE /NH';
		$output = `$command`;
		if(!empty($output)) {
			if(preg_match("~^(.+[^ \t])[ \t]+".$pid[1]." ([a-zA-Z]+[^ \t]*).+$~m", $output, $matches) > 0) {
				$message .=  "进程PID ".$pid[1]." 程序 '".$matches[1]."' 会话 ".$matches[2]."\n";
				$command = 'tasklist /SVC | FINDSTR /C:"'.$pid[1].'"';
				$output = `$command`;
				if(!empty($output)) {
					if(preg_match("~^(.+[^ \t])[ \t]+".$pid[1]." ([a-zA-Z]+[^ \t]*).+$~m", $output, $matches) > 0) {
						$message .=  "进程PID ".$pid[1]." 程序 '".$matches[1]."' 服务名 '".$matches[2]."'\n";
						if($matches[2] == $_SERVER['argv'][2])
							$message .=  "这是Wampserver的服务 - 结果正常\n";
						else {
							if($mysqlTest) {
								if($matches[2] == $c_mysqlService || $matches[2] == $c_mariadbService) {
									$forwhat = "MySQL 或 MariaDB";
								  $message .= "该服务(".$matches[2].")来自 Wampserver 的 ".$forwhat."\n";
								}
								else {
									if($matches[2] != 'N/A') {
										$message .=  "*** 警告 *** Wampserver 似乎没有该服务\n正确值: '".$c_mysqlService."' 或 '".$c_mariadbService."'\n";
										}
									else {
										$message .= $matches[2]." 没有与 PID ".$pid[1]." 相关的服务\n";
										if($wampConf['SupportMySQL'] == 'on' && version_compare($c_mysqlVersion,'8.0.0', '>=')) {
											$command = 'tasklist /SVC /FI "IMAGENAME eq mysqld.exe" | FINDSTR /C:"'.$c_mysqlService.'"';
											$output = `$command`;
											if(!empty($output)) {
												if(preg_match("~^(mysqld\.exe)[ \t]+([0-9]+)[ \t]+(".$c_mysqlService.").*$~m",$output, $matches) > 0) {
													$message .= "'".$matches[1]."' 进程由 '".$matches[3]."' 服务启动 关联 PID ".$matches[2]."\n";
												}
											}
										}
									}
								}
							}
							else
								$message .=  "*** 错误 *** 这不是 Wampserver 的服务 - 正确值: '".$_SERVER['argv'][2]."'\n";
						}
					}
				}
			}
			else
				$message .=  "无法在任务列表里找到 PID ".$pid[1]." 的进程\n";
		}
	}
	else
	 	$message .=  "找不到 ".$port." 端口 TCP 协议相关的信息\n";
}
else
	$message .=  "找不到 ".$port." 端口 TCP 协议相关的信息\n";
}

if(!$only_process) {
	$message .=  "\n===== 使用 socket 测试 ".$port." 端口 =====\n\n";
	//Port tested by open socket
	$fp = @fsockopen("127.0.0.1", $port, $errno, $errstr, 2);
	$out = "GET / HTTP/1.1\r\n";
	$out .= "Host: 127.0.0.1\r\n";
	$out .= "Connection: Close\r\n\r\n";
	if(!$fp) {
		$message .= $port." 似乎未被使用.\n无法建立 socket 通信.\n";
		if($errno == 0) {
			$message .= "无法调用 connect().\n无法进行 socket 测试.\n";
		}
		else {
			$message .= "错误码: ".$errno." - 错误信息: ".$errstr."\n";
		}

	}
	else {
		$message .=   $port." 端口已被它使用 :\n\n";
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
		$message .= "未找到占用该端口的程序信息 (可能是 Skype 或 IIS).\n";
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
		echo "\n--- 是否要将结果复制到剪贴板?\n
--- 输入 'y' 确认复制 - 按回车键（ENTER）继续...";
    $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if ($confirm == 'y') {
			write_file("temp.txt",$message, true);
		}
		exit();
 	}

echo '

按回车键（ENTER）退出...';
trim(fgets(STDIN));

?>
