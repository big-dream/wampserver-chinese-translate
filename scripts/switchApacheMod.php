<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';


$httpdFileContents = @file_get_contents($c_apacheConfFile ) or die ("httpd.conf file not found");

//Uncomment or comment LoadModule line
if($_SERVER['argv'][2] == 'on')
{
    $findTxt  = 'LoadModule '.$_SERVER['argv'][1];
    $replaceTxt  = '#LoadModule '.$_SERVER['argv'][1];
}
else
{
    $findTxt  = '#LoadModule '.$_SERVER['argv'][1];
    $replaceTxt  = 'LoadModule '.$_SERVER['argv'][1];
}

$httpdFileContents = str_replace($findTxt,$replaceTxt,$httpdFileContents);

//Comment or Uncomment #Include conf/extra/httpd-autoindex.conf line
if($_SERVER['argv'][1] == "autoindex_module") {
	if($_SERVER['argv'][2] == 'on') {
	    $findTxt  = 'Include conf/extra/httpd-autoindex.conf';
	    $replaceTxt  = '#Include conf/extra/httpd-autoindex.conf';
	}
	else	{
	    $findTxt  = '#Include conf/extra/httpd-autoindex.conf';
	    $replaceTxt  = 'Include conf/extra/httpd-autoindex.conf';
	}
	$httpdFileContents = str_replace($findTxt,$replaceTxt,$httpdFileContents);
}

write_file($c_apacheConfFile,$httpdFileContents);

?>