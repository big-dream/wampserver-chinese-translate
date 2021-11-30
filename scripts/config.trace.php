<?php

define('WAMPTRACE_PROCESS',	false);

if(WAMPTRACE_PROCESS) {
	if(!defined('WAMPTRACE_FILE')) {
		$wampConf = @parse_ini_file('../wampmanager.conf');
		define('WAMPTRACE_FILE', $wampConf['installDir']."/logs/wamptrace.log");
		//Create file with datetime in first line
		$fp = fopen(WAMPTRACE_FILE, "ab");
		fwrite($fp,"- Wampserver trace report - ".date(DATE_RSS)."\n");
		fclose($fp);
		unset($wampConf,$fp);
	}
	error_log("script ".__FILE__." WAMPTRACE_FILE=".WAMPTRACE_FILE."\n",3,WAMPTRACE_FILE);
}

?>
