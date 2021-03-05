<?php
// 3.1.4 Only error message if reinstall all services.
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';

$port_to_check = $wampConf['apachePortUsed'];

$reinstall = isset($_SERVER['argv'][1]) ? true : false;

$fp = @fsockopen("127.0.0.1", $port_to_check, $errno, $errstr, 1);
   $out = "GET / HTTP/1.1\r\n";
   $out .= "Host: 127.0.0.1\r\n";
   $out .= "Connection: Close\r\n\r\n";
if ($fp)
{
           echo  $port_to_check.' 已被它使用 :

';
   fwrite($fp, $out);
   while (!feof($fp))
   {
        $line = fgets($fp, 128);
        if (preg_match('/Server: /',$line))
        {
            echo $line;
            $gotInfo = 1;
        }

    }
    fclose($fp);
    if ($gotInfo != 1)
        echo '未找到占用该端口的程序信息 (可能是Skype).';
    echo '
无法安装Apache服务，请先停止该程序后再试.

按回车键（ENTER）退出...';
trim(fgets(STDIN));
}
else
{ if(!$reinstall) {
    echo $port_to_check.' 端口可用，安装可继续进行.';
    echo '

按回车键（ENTER）继续...';
    trim(fgets(STDIN));
  }
}


?>