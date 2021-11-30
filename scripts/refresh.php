<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

$doReport = (isset($_SERVER['argv'][1]) && trim($_SERVER['argv'][1]) == 'doreport') ? true : false;
//$doReport = true;

if($doReport) {
	$wampReportTxt = '';
	$wampReport = array_fill_keys(array('gen1','gen3','mysql','mariadb','gen2','phpConf'), '');
	$ReportStartOn = IntlDateFormatter::formatObject(new DateTime('now'),"eeee d MMMM Y '-' HH:mm");
	$wampReport['gen1'] .= time()."\n------ Wampserver configuration report\n".$ReportStartOn."\n";
}

require 'config.inc.php';
require 'wampserver.lib.php';
// Get Aestan Tray Menu version
$contents = file_get_contents($wampserverIniFile);
preg_match('~^AeTrayVersion=([0-9\.]+)\r?$~mi',$contents,$matches);
$wamp_versions_here += array('wamp_aestan' => $matches[1]);
unset($contents);

//Verify some files
require 'refreshVerifyFiles.php';

// *******************
// language management
// Get current language
$lang = $wampConf['language'];

// Load language file if exists
require $langDir.$wampConf['defaultLanguage'].'.lang';
if(is_file($langDir.$lang.'.lang')){
	require $langDir.$lang.'.lang';
}
/*if(is_file($langDir.$lang.'_utf8.lang')){
	require $langDir.$lang.'_utf8.lang';
}*/
// Load modules default language files
if($handle = opendir($langDir.$modulesDir)) {
	while (false !== ($file = readdir($handle)))	{
		if($file != "." && $file != ".." && preg_match('|_'.$wampConf['defaultLanguage'].'|',$file)) {
			include $langDir.$modulesDir.$file;
			//Save array $w_settings default language
			$w_settings_save = $w_settings;
		}
	}
	closedir($handle);
}

// Load modules current language files if exists
if($handle = opendir($langDir.$modulesDir)) {
	while (false !== ($file = readdir($handle)))	{
		if($file != "." && $file != ".." && preg_match('|_'.$lang.'|',$file)) {
			include $langDir.$modulesDir.$file;
			//Merge save array with current language
			$w_settings = array_replace($w_settings_save,$w_settings);
		}
	}
	closedir($handle);
}

//Update string to use alternate port.
$w_AlternatePort = sprintf($w_UseAlternatePort, $c_UsedPort);
if($c_UsedPort == $c_DefaultPort) {
	$UrlPort = '';
	$w_newPort = "8080";
}
else {
	$UrlPort = ':'.$c_UsedPort;
	$w_newPort = "80";
}
//Update string if there are more than one Apache Listen ports
$c_listenPort = listen_ports($c_apacheConfFile);
$TplListenPorts = ';';
$ListenPorts = '';
$ListenPortsExists = false;
if(count($c_listenPort) > 1) {
	$ListenPorts = implode(" ",$c_listenPort);
	$TplListenPorts = '';
	$ListenPortsExists = true;
}
//Update string for add Listen Port
$w_addPort = 8081;
while(in_array($w_addPort, $c_listenPort)) {
	$w_addPort++;
}
$w_addPort = (string)$w_addPort;

//Update string to use alternate MySQL port.
$w_AlternateMysqlPort = sprintf($w_UseAlternatePort, $c_UsedMysqlPort);
if($c_UsedMysqlPort == $c_DefaultMysqlPort) {
	$w_newMysqlPort = "3308";
}
else {
	$w_newMysqlPort = "3306";
}
//Update string to use alternate MariaDB port.
$w_AlternateMariaPort = sprintf($w_UseAlternatePort, $c_UsedMariaPort);
if($c_UsedMariaPort == $c_DefaultMysqlPort) {
	$w_newMariaPort = "3309";
}
else {
	$w_newMariaPort = "3306";
}

// ************************************************
//Before to require wampmanager.tpl ($templateFile)
// we need to change some options, otherwise the variables are replaced by their content.
// Retrieve last start date of Wampserver
$WampStartOnOri = $wampConf['wampStartDate'];
// Wampserver last launched date and hour (formated)
$WampStartOn = IntlDateFormatter::formatObject(new DateTime($WampStartOnOri),$w_FormatDate);
// Option to launch Homepage at startup
$RunAtStart = ($wampConf['HomepageAtStartup'] == 'on' ? '' : ';');
// Option to see www dir in menu
$ShowWWWdir = ($wampConf['ShowWWWdirMenu'] == 'on' ? '' : ';');
// Item submenu Apache Check port used (if not 80)
$ApaTestPortUsed = ($wampConf['apacheUseOtherPort'] == 'on' ? '' : ';');
// Item Tools submenu Check MySQL port used (if not 3306)
$MysqlTestPortUsed = (($wampConf['SupportMySQL'] == 'on' && ($wampConf['mysqlUseOtherPort'] == 'on'  && $wampConf['mysqlPortOptionsMenu'] == 'on')) ? '' : ';');
// Item Tools submenu Check MariaDB port used (if not 3306)
$MariaTestPortUsed = (($wampConf['SupportMariaDB'] == 'on' && ($wampConf['mariaUseOtherPort'] == 'on' && $wampConf['mariadbPortOptionsMenu'] == 'on')) ? '' : ';');
$SupportMysqlAndMariaDB = (($wampConf['SupportMariaDB'] == 'on' && $wampConf['SupportMySQL'] == 'on') ? '' : ';');
$MariadbDefault = (($wampConf['SupportMySQL'] == 'on' && $wampConf['SupportMariaDB'] == 'on' && $wampConf['mariaPortUsed'] == $wampConf['mysqlDefaultPort']) ? '' : ';');
$MysqlDefault = (($wampConf['SupportMySQL'] == 'on' && $wampConf['SupportMariaDB'] == 'on' && $wampConf['mysqlPortUsed'] == $wampConf['mysqlDefaultPort']) ? '' : ';');
if(!empty($MariadbDefault) && !empty($MysqlDefault))
	$DefaultDBMS = 'none';
else
	$DefaultDBMS = (empty($MariadbDefault) ? 'MariaDB '.$c_mariadbVersion : 'MySQL '.$c_mysqlVersion);
//Show MySQL and MariaDB change prompt
if($wampConf['MysqlMariaChangePrompt'] == 'on') {
	$MysqlMariaPrompt = '';
	$MysqlMariaPromptBool = true;
}
else {
	$MysqlMariaPrompt = ';';
	$MysqlMariaPromptBool = false;
}
// Instructions for use file
$c_useFileExists = ';';
if(file_exists($c_installDir.'/instructions_utilisation.pdf')) {
	$c_useFile = 'instructions_utilisation.pdf';
	$c_useFileExists = '';
}
elseif(file_exists($c_installDir.'/instructions_for_use.pdf')) {
	$c_useFile = 'instructions_for_use.pdf';
	$c_useFileExists = '';
}
//Check if Apache Graceful Restart is supported
$Apache_Graceful_Restart = <<< EOF
Action: run; Filename: "${c_apacheExe}"; Parameters: "-n ${c_apacheService} -k restart"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
EOF;
$Apache_Service_Restart = <<< EOF
Action: Service; Service: ${c_apacheService}; ServiceAction: restart; Flags: ignoreerrors waituntilterminated
EOF;
$Apache_Restart = $Apache_Service_Restart;
$Apache_Graceful = ';';
if($wampConf['apacheGracefulRestart'] == 'on') {
	$Apache_Restart = $Apache_Graceful_Restart;
	$Apache_Graceful = '';
}

//Check some values about Apache VirtualHost
$virtualHost = check_virtualhost(true);
//Option to show Edit httpd-vhosts.conf
$EditVhostConf  = (($virtualHost['include_vhosts'] === false || $virtualHost['vhosts_exist'] === false) ? ';' : '');
//Translated by in About
$w_translated_by = (isset($w_translated_by )) ? $w_translated_by : '';

//Retrieve Windows charset
$Windows_Charset = '';
$command = 'CMD /D /C powershell [System.Text.Encoding]::Default | FINDSTR /I /C:"WebName"';
$output = `$command`;
if(preg_match('/^WebName[\t ]+:[\t ]([a-zA-Z0-9\-]+)\r?$/i',$output,$matches) > 0) {
	$Windows_Charset = $matches[1];
}

//Add value to Wampserver Report
if($doReport) {
$WinVer = php_uname('s').' '.php_uname('r').' '.php_uname('v');
$wampReport['gen1'] .= <<< EOF
- ${WinVer}
- Windows Charset: ${Windows_Charset}
- Wampserver version ${c_wampVersion} - ${c_wampMode}
- Wampserver install version ${c_wampVersionInstall}
- Install directory: ${c_installDir}
- Default browser: ${c_navigator} ${c_edge}
- Default text editor: ${c_editor}
- Default log viewer: ${c_logviewer}
- Apache ${c_apacheVersion} - Port ${c_UsedPort}
- Additional Apache listening ports: ${ListenPorts}
- PHP ${c_phpVersion}

EOF;
}

//Update MySQL and/or MariaDB my.ini file
//Replace # comment by ; to be compatible with parse_ini_file
//PHP 5.3.0 Hash marks (#) should no longer be used as comments and will throw a deprecation warning if used.
//PHP 7.0.0 Hash marks (#) are no longer recognized as comments.
// Option to support MySQL
$mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql');
array_walk($mysqlVersionList,function(&$value, $key){$value = str_replace('mysql','',$value);});
// Sort in versions number order
natcasesort($mysqlVersionList);
if($wampConf['SupportMySQL'] == 'on' && count($mysqlVersionList) > 0) {
	create_wamp_versions($mysqlVersionList,'mysql');
	if($doReport)	$wampReport['gen3'] .= "MySQL versions seen by refresh listDir:\n".implode(' - ',$mysqlVersionList)."\n";
	$SupportMySQL = '';
	$EmptyMysqlLog = ' '.$c_installDir.'/'.$logDir.'mysql.log';
	//Check Console prompt
	if($wampConf['mysqlUseConsolePrompt'] == 'on') {
		$mysqlConsolePromptUsed = $wampConf['mysqlConsolePrompt'];
		$mysqlConsolePromptChange = 'off';
	}
	else {
		$mysqlConsolePromptUsed = 'default';
		$mysqlConsolePromptChange = 'on';
	}
	$myIniContents = file_get_contents_dos($c_mysqlConfFile);
	$myIniContents = preg_replace('/^#(.*)$/m',';${1}',$myIniContents,-1,$count);
	if($count > 0) {
		write_file($c_mysqlConfFile,$myIniContents);
	}
	unset ($myIniContents);
	if($doReport)	$wampReport['mysql'] .= "\n- MySQL ".$c_mysqlVersion." Port ".$c_UsedMysqlPort;
}
else {
	$SupportMySQL = ';';
	$EmptyMysqlLog = '';
}

// Option to support MariaDB
$mariadbVersionList = listDir($c_mariadbVersionDir,'checkMariaDBConf','mariadb');
array_walk($mariadbVersionList,function(&$value, $key){$value = str_replace('mariadb','',$value);});
// Sort in versions number order
natcasesort($mariadbVersionList);
if($wampConf['SupportMariaDB'] == 'on' && count($mariadbVersionList) > 0) {
	create_wamp_versions($mariadbVersionList,'mariadb');
	if($doReport)	$wampReport['gen3'] .= "MariaDB versions seen by refresh listDir:\n".implode(' - ',$mariadbVersionList)."\n";
	$SupportMariaDB = '';
	$EmptyMariaLog = ' '.$c_installDir.'/'.$logDir.'mariadb.log';
	//Check Console prompt
	if($wampConf['mariadbUseConsolePrompt'] == 'on') {
		$mariadbConsolePromptUsed = $wampConf['mariadbConsolePrompt'];
		$mariadbConsolePromptChange = 'off';
	}
	else {
		$mariadbConsolePromptUsed = 'default';
		$mariadbConsolePromptChange = 'on';
	}

	$myIniContents = file_get_contents_dos($c_mariadbConfFile);
	$myIniContents = preg_replace('/^#(.*)$/m',';${1}',$myIniContents,-1,$count);
	if($count > 0) {
		write_file($c_mariadbConfFile,$myIniContents);
	}
	unset ($myIniContents);
	if($doReport)	$wampReport['mariadb'] .= "\n- MariaDB ".$c_mariadbVersion." Port ".$c_UsedMariaPort;
}
else {
	$SupportMariaDB = ';';
	$EmptyMariaLog = '';
}

// Support mysql Service with mysqld.exe or windows command sc
$mysqlMysqlService = '';
$mysqlCmdScService = ';';
if(isset($wampConf['mysqlServiceCmd']) && $wampConf['mysqlServiceCmd'] == 'windows') {
	$mysqlMysqlService = ';';
	$mysqlCmdScService = '';
}

// Support mariadb Service with mysqld.exe or windows command sc
$mariaMysqlService = '';
$mariaCmdScService = ';';
if(isset($wampConf['mariadbServiceCmd']) && $wampConf['mariadbServiceCmd'] == 'windows') {
	$mariaMysqlService = ';';
	$mariaCmdScService = '';
}

// Option if neither MySQL nor MariaDB
if($SupportMySQL == ';' && $SupportMariaDB == ';') {
	$noDBMS = true;
	$SupportDBMS = ';';
	if($doReport)	$wampReport['gen1'] .="\n--- No DBMS (nor MySQL, nor MariaDB)";
}
else {
	$noDBMS = false;
	$SupportDBMS = '';
}
if($doReport) {
	$wampReport['gen2'] .= <<< EOF

- PHP ${c_phpCliVersion} for CLI (Internal Wampserver PHP scripts)
EOF;
	$wampConfSections = @parse_ini_file($configurationFile,true,INI_SCANNER_RAW);
	$wampReport['gen2'] .= "\n------ Wampserver configuration ------\n";
	$sections = array('options','apacheoptions','mysqloptions','mariadboptions');
	foreach($sections as $section) {
		$wampReport['gen2'] .= "---------- Section [".$section."]\n";
		$nbbyline = 0;
		foreach($wampConfSections[$section] as $key => $value) {
			$wampReport['gen2'] .= str_pad($key." = ".$value,35);
			if(++$nbbyline >= 2) {
				$wampReport['gen2'] .= "\n";
				$nbbyline = 0;
			}
		}
		$wampReport['gen2'] .= "\n";
	}
	$wampReport['gen2'] .= "---------------------------------------------\n";
	unset($wampConfSections,$sections,$section);
}

//Warnings at the end if needed
$WarningsAtEnd 	= false;
$WarningMenu = ';WAMPMENULEFTEND
';
$WarningText = '';

// Get PhpMyAdmin version's
GetPhpMyAdminVersions();
if($phmyadOK) {
	$temp = '0.0.0';
	foreach($phpMyAdminAlias as $value) {
		if(version_compare($value['version'], $temp, '>'))
			$temp = $value['version'];
	}
	$wamp_versions_here += array('wamp_phpmyadmin' => $temp);
}

// Get adminer version
$adminerVersion = '';
$adminerOK = false;

if(file_exists($aliasDir.'adminer.conf')) {
	$adminerOK = true;
	$myalias = @file_get_contents($aliasDir.'adminer.conf');
	//Alias /adminer "J:/wamp/apps/adminer4.3.1/"
	if(preg_match('~^Alias\s*/adminer\s*".*apps/adminer([0-9\.]*)/"\s?$~m',$myalias,$matches) > 0 )
	$adminerVersion = $matches[1];
	$wamp_versions_here += array('wamp_adminer' => $matches[1]);
}
// Show Adminer in Wampmanager menu
$adminerMenu = (($adminerOK && $wampConf['ShowadminerMenu'] == 'on') ? '' : ';');

// Get phpsysinfo version
if(file_exists($aliasDir.'phpsysinfo.conf')) {
	$myalias = @file_get_contents($aliasDir.'phpsysinfo.conf');
	if(preg_match('~^Alias\s*/phpsysinfo\s*".*apps/phpsysinfo([0-9\.]*)/"\s?$~m',$myalias,$matches) > 0 )
	$phpsysinfoVersion = $matches[1];
	$wamp_versions_here += array('wamp_phpsysinfo' => $matches[1]);
}

//-------------------------------------------------
//Warning if hosts file is not writable
if(!$c_hostsFile_writable) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nThere is problem with the file C:\\Windows\\System32\\drivers\\etc\\hosts\r\nIn order to create or modify VirtualHost,\r\nit is imperative to be able to write to the hosts file.\r\nCheck that your anti-virus allows to write the hosts file.\r\n");
	$message .= "\r\n----------------------------------------\r\n".$WarningMsg;
	$WarningText .= 'Type: item; Caption: "hosts file not writable"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	if($doReport)	$wampReport['gen2'] .= "\nFile C:\\Windows\\System32\\drivers\\etc\\hosts is not writable.";
}
else {
	// Verify hosts file contents
	if($wampConf['NotVerifyHosts'] == 'off') {
		//Cleaning of hosts file
		$rewriteHost = $validServer = $localIPv4 = $localIPv6 = false;
		$myHostsContents = file_get_contents($c_hostsFile);
		$myHostsContents = clean_file_contents($myHostsContents,array(2,1),true);
		$rewriteHost = $clean_count;
		//Verify if there is at least one valid ServerName
		if(preg_match('~^(127\.|10\.|172\.16\.|192\.168\.)[ \t]*.*\s?$~m',$myHostsContents) > 0 )
			$validServer = true;
		//Verify at least 127.0.0.1 localhost and ::1 localhost
		if(preg_match('~^127\.0\.0\.1[ \t]*localhost\s?$~m',$myHostsContents) > 0 )
			$localIPv4 = true;
		if(preg_match('~^::1[ \t]*localhost\s?$~m',$myHostsContents) > 0 )
			$localIPv6 = true;
		//Rewrite host file if necessary
		if(!$validServer) {
			$myHostsContents = "#\r\n";
			$rewriteHost = true;
		}
		if(!$localIPv4) {
			$myHostsContents .= "127.0.0.1 localhost\r\n";
			$rewriteHost = true;
		}
		if(!$localIPv6) {
			$myHostsContents .= "::1 localhost\r\n";
			$rewriteHost = true;
		}
		if($rewriteHost) {
			error_log("rewrite hosts");
			//Try to do a backup of hosts file
			if($wampConf['BackupHosts'] == 'on') {
				@copy($c_hostsFile,$c_hostsFile."_wampsave.".$next_hosts_save);
				$next_hosts_save++;
			}
			$fp = fopen($c_hostsFile, 'r+b');
			if(flock($fp, LOCK_EX)) { // acquire an exclusive lock
				ftruncate($fp, 0); // truncate file
				fwrite($fp, $myHostsContents);
				fflush($fp); // flush output before releasing the lock
				flock($fp, LOCK_UN); // release the lock
			}
			else {
				$errorTxt = 'Unable to write to '.$c_hostsFile.' file';
			  error_log($errorTxt);
  			if(WAMPTRACE_PROCESS) error_log("script ".__FILE__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
			  if($doReport) {
			  	if(!$localIPv4)
			  		$wampReport['gen2'] .= "\n-- No 127.0.0.1 localhost in hosts file";
			  	if(!$localIPv6)
			  		$wampReport['gen2'] .= "\n-- No ::1 localhost in hosts file";
			  	$wampReport['gen2'] .="\n- Unable to rewrite ".$c_hostsFile." file";
			  }
			}
		fclose($fp);
		}
		//Warning if hosts file is too big
		//Count number of lines in hosts file
		if(($c_hostsFile_toobig = count(file($c_hostsFile, FILE_IGNORE_NEW_LINES))) > $wampConf['HostsLinesLimit']) {
			if($doReport)	$wampReport['gen2'] .= "\nToo more lines in ".$c_hostsFile." file";
			$WarningsAtEnd = true;
			$message = color('red',"\r\nThe C:\\Windows\\System32\\drivers\\etc\\hosts file\r\nhas ".$c_hostsFile_toobig." lines\r\nthat's far too much for proper operation.\r\nThe role of the hosts file is to serve as local DNS,\r\ni. e. to give the connections between local IPs and ServerNames.\r\nIts purpose is absolutely not to be used for filtering unwanted urls.\r\n");
			$WarningText .= 'Type: item; Caption: "Too more lines in hosts"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
	}
}

if($doReport) {
	// hosts file
	$wampReport['gen2'] .="\n------ ".$c_hostsFile." file contents ------\n------ Limited to the first 30 lines ------\n";
	$wampReport['gen2'] .= implode(PHP_EOL, array_slice(file($c_hostsFile), 0, 30));
	$wampReport['gen2'] .="\n----------------------------------------------";
	// httpd-vhosts.conf file
	$wampReport['gen2'] .="\n-- ".$c_apacheVhostConfFile." file contents --\n------ Limited to the first 40 lines ------\n";
	$wampReport['gen2'] .= implode(PHP_EOL, array_slice(file($c_apacheVhostConfFile), 0, 40));
	$wampReport['gen2'] .="\n----------------------------------------------";
}

//Warning if tmp/ folder does not exist or is not writable
$checktmp = checkDir($c_installDir.'/tmp');
if($checktmp !== 'OK') {
	if($doReport)	$wampReport['gen2'] .= "\n-- ".$c_installDir."/tmp/ directory doesn't exists or is not writable";
	$WarningsAtEnd = true;
	$message = color('red',"\r\nFolder ".$c_installDir."/tmp\r\n".$checktmp."\r\n");
	$WarningText .= 'Type: item; Caption: "Error '.$c_installDir.'/tmp"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
//Warning if syntax error in Apache config files
$command = $c_apacheExe.'  -t';
$output = proc_open_output($command);
if(!empty($output)) {
	if(stripos($output,'Syntax error') !== false) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nThere is a syntax error in Apache conf files.\r\n".$output."\r\n");
	$WarningText .= 'Type: item; Caption: "Syntax error Apache conf files"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	if($doReport)	$wampReport['gen2'] .= "\nWARNING:\n".$message;
	}
}

//Warning if not Apache variables
if($ApacheDefineError) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nUnable to find the Apache variables.\r\nThere may be a syntax error in Apache conf files.\r\nTo be checked by the tool integrated in Wampserver:\r\nRight-click -> Tools -> Check httpd.conf syntax.\r\n");
	$WarningText .= 'Type: item; Caption: "Error Apache Variables"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	if($doReport)	$wampReport['gen2'] .= "\nWARNING: Unable to find Apache variables\nThere may be a syntax error in Apache conf files.\n";
}
//Warning if Edge defined as navigator and not Windows 10
if($c_edgeDefinedError) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nEdge is defined as default browser\r\nEdge should be defined as default navigator only with Windows 10\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Edge as browser error"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
// Verify that default browser exists
if($c_navigator != "Edge" && $c_navigator != "cmd.exe" && $c_navigator != "iexplore.exe") {
	if(!file_exists($c_navigator)) {
		$WarningsAtEnd = true;
		$message = color('red',"\r\n".$c_navigator." is defined as default browser\r\nThis browser exe file does not exist\r\n");
		if($doReport)	$wampReport['gen2'] .= $message;
		$WarningText .= 'Type: item; Caption: "Default browser does not exist"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}
// Verify that default editor exists
if(!file_exists($c_editor)) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\n".$c_editor." is defined as default text editor\r\nThis editor exe file does not exist\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Default text editor does not exist"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
// Verify that default logviewer exists
if(!file_exists($c_logviewer)) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\n".$c_logviewer." is defined as default log viewer\r\nThis log viewer exe file does not exist\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Default log viewer does not exist"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
//Verify PHPIniDir "${APACHE_DIR}/bin into httpd.conf
$httpdFileContents = file_get_contents_dos($c_apacheConfFile);
if(strpos($httpdFileContents,'PHPIniDir "${APACHE_DIR}/bin"') === false) {
	$insert = 'PHPIniDir "${APACHE_DIR}/bin"
';
	$replace = 'LoadModule php';
	$httpdFileContents = str_replace($replace,$insert.$replace,$httpdFileContents,$count);
	if($count > 0) {
		if(WAMPTRACE_PROCESS) error_log("write ".$c_apacheConfFile." in ".__FILE__." line ". __LINE__."\n",3,WAMPTRACE_FILE);
  	write_file($c_apacheConfFile,$httpdFileContents);
	}
}
unset($httpdFileContents);

//Warning if install dir or php into PATH environment variable.
if($wampConf['NotVerifyPATH'] == 'off') {
	$message = '';
	$pathWampFound = array();
	$pathLines = explode(';',getenv('PATH'));
	//Check if there is Wamp install dir in PATH
	$wampinstallfound = false;
	$phpinstallfound = false;
	foreach($pathLines as $key => $value) {
		if(stripos(str_replace('\\','/',$value), $c_installDir) !== false && !$wampinstallfound) {
			$message .= "\nWarning: There is Wampserver path (".$c_installDir.")\ninto Windows PATH environnement variable: (".$value.")\n";
			$pathWampFound[] = $key;
			$wampinstallfound = true;
		}
		if(stripos(str_replace('\\','/',$value), 'wamp') !== false && !$wampinstallfound) {
			$message .= "\nWarning: It seems that there is Wampserver path \ninto Windows PATH environnement variable: (".$value.")\n";
			$pathWampFound[] = $key;
			$wampinstallfound = true;
		}
		if((stripos(str_replace('\\','/',$value), 'php/') !== false || stripos(str_replace('\\','/',$value), '/php')) && !$phpinstallfound) {
			$message .= "\nWarning: It seems that a PHP installation is declared in the environment variable PATH\n";
			$message .= $value."\n";
			$pathWampFound[] = $key;
			$phpinstallfound = true;
		}
	}
	if($wampinstallfound || $phpinstallfound) {
		$WarningsAtEnd = true;
		$message .= color('red',"\nWampserver does not use, modify or require the PATH environment variable.\n");
		$message .= "Using a PATH on Wampserver or PHP version\nmay be detrimental to the proper functioning of Wampserver.\n";
		if($doReport)	$wampReport['gen2'] .= $message;
		$WarningText .= 'Type: item; Caption: "Warning '.$c_installDir.' or PHP in PATH"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}

// Forum for help - linklang
$forum = ($lang == 'french') ? '1' : '2';
$LinkLang = ($lang == 'french') ? 'french' : 'english';

//***************************
// Clean logs files if needed
// Get filesize of log files
$logFilesSize = $logFilesSizeClean = array();
foreach($logFilesList as $value) {
	$size = filesize($value);
	$logFilesSizeClean[$value] = $size;
}

if($wampConf['AutoCleanLogs'] == 'on') {
	if($wampConf['AutoCleanLogsMin'] < 1) $wampConf['AutoCleanLogsMin'] = 1;
	foreach($logFilesSizeClean as $key => $value) {
		// Before counting the number of lines in the file, which consumes resources,
		// we look at the size and count the lines only if the size is > 100000 bytes
		if($value > 100000) {
			$fileArray = file($key);
			$fileCount = count($fileArray);
			if($fileCount > $wampConf['AutoCleanLogsMax']) {
				$newLogFile = clean_file_contents(implode(PHP_EOL, array_slice($fileArray, -($wampConf['AutoCleanLogsMin']))),array(1,0),true);
				write_file($key,$newLogFile);
				unset($newLogFile);
			}
			unset($fileArray);
		}
	}
}
foreach($logFilesList as $value) {
	$size = filesize($value);
	$logFilesSize[str_replace($c_installDir.'/'.$logDir,'',$value)] = FileSizeConvert($size);
}
unset($logFilesSizeClean);
// END of clean log files
//***********************

//************************************
// Load Template file as file contents
// Find all variables assigned to the PromptText fields
// of the Aestan Tray menu's Prompt variables type and
// replace the end of lines with #13 and commas with &#44;
$tpl = file_get_contents($templateFile);
if(preg_match_all('~^.*PromptText:[\t ]*"(\$.+)"[\t ]*;.*$~mi',$tpl,$matches) > 0) {
	foreach($matches[1] as $value) {
		$value = str_replace(array('$','{','}'),'',$value);
		if(isset($$value)) {
			$$value = ReplaceAestan($$value);
		}
	}
}
unset($tpl,$matches);
// END of PromptText replacements
//*******************************

//**************************************************
// Create definitions of TextMenu (TextKeyx) for Text items
// From $AesTextMenus in config.inc.php
$TextSubmenuName = $TextSubmenuCaption = $Glyph = array();
$TextMenus = '';
foreach($AesTextMenus as $key => $value) {
	$TextSubmenuName[] = (strpos($value[0],'$') === 0) ? ${$temp = substr($value[0],1)} : $value[0];
	if(strpos($value[1],'$') === 0){
		$temp = substr($value[1],1);
		$CaptionTemp = $$temp;
		// Add space at the end of the variable to avoid duplicate Captions
		$$temp .= ' ';
	}
	else
		$CaptionTemp = $value[1];
	$TextSubmenuCaption[] = $CaptionTemp;
	$TextMenus .= 'TextKey'.$key.'="'.$CaptionTemp.'",'.$value[2].','.$value[3].','.$value[4].','.$value[5].',';
	$tempText = (strpos($value[6],'$') === 0) ? ${$temp = substr($value[6],1)} : $value[6];
	$tempText = ReplaceAestan($tempText, 'ToSpace');
	$TextMenus .= $tempText.',';
	if(is_array($value[7])) {
		$tempText = '';
		foreach($value[7] as $valueTxt) {
			$tempText .= ${$temp = substr($valueTxt,1)};
		}
	}
	else {
		$tempText = (strpos($value[7],'$') === 0) ? ${$temp = substr($value[7],1)} : $value[7];
	}
	if($value[8] > 0) $tempText = wordwrap($tempText,$value[8],"\r\n");
	$tempText = ReplaceAestan($tempText);
	$TextMenus .= $tempText."\r\n";
	$Glyph[] = ($value[9] > -1) ? '; Glyph: '.$value[9] : '';
}
// Create submenus definitions - Example below
//[AddingVersions]
//Type: item; Caption: "Add Apache, PHP, MySQL, MariaDB, etc. versions."; Action: Multi; Actions: none
$i = 0;
$TextSubmenus = '';
reset($Glyph);
foreach($TextSubmenuName as $value) {
	$GlyphM = current($Glyph);
	$TextSubmenus .= <<< EOF
[${value}]
Type: item; Caption: "${TextSubmenuCaption[$i]}"; Action: Multi; Actions: none${GlyphM}

EOF;
$i++;
next($Glyph);
}
// END of TextMenu ************************
//*****************************************

//************************************
// Create definitions of Custom Prompt
// From $AesPromptCustom in config.inc.php
$PromptCustom = '';
foreach($AesPromptCustom as $key => $value) {
	$PromptCustom .= 'PromptKey'.$key.'=';
	$PromptTemp = '';
	foreach($value as $indice) $PromptTemp .= $indice.',';
	$PromptCustom .= substr($PromptTemp,0,-1)."\r\n";
}
$PromptCustom .="\r\n";
// END of Custom Prompt
//*********************

// ***************************************************************
// *** Load Template file as require - $tpl is the template string
require $templateFile;

// Do TextMenus replacements
$search = ';WAMPTEXTMENUSTART
';
$tpl = str_replace($search,$search.$TextMenus,$tpl);
$search = ';WAMPITEMSTEXTSTART
';
$tpl = str_replace($search,$search.$TextSubmenus,$tpl);
unset($TextMenus,$TextSubmenus,$TextSubmenuName,$TextSubmenuCaption,$tempText);

// Do CustomPrompt replacement
$search = ';WAMPPROMPTCUSTOMSTART
';
$tpl = str_replace($search,$search.$PromptCustom,$tpl);
unset($PromptCustom,$PromptTemp);

//******************************
// Create PhpMyAdmin menu item's
// Show PhpMyAdmin in Wampmanager menu ?
if($phmyadOK && $wampConf['ShowphmyadMenu'] == 'on') {
	$ItemMenuPMA = $SubPhpMyAdmin = '';
	foreach($phpMyAdminAlias as $value) {
		$glyph = (($value['compat']) ? 39 : 23);
		$ItemMenuPMA .= <<< EOF
${SupportDBMS}Type: item; Caption: "${w_phpmyadmin}	${value['version']}"; Action: run; FileName: "${c_navigator}"; Parameters: "${c_edge}http://localhost${UrlPort}/${value['alias']}/"; Glyph: ${glyph}

EOF;
	}
	if($phpmyadminCount > 1 ) {
		$subPhpMyAdmin = <<< EOF
${SupportDBMS}Type: submenu; Caption: "PhpMyAdmin"; Submenu: MultiplephpMyAdmin; Glyph: 39

EOF;
	// Do PhpMyAdmin replacements
		$search = ';WAMPPHPMYADMIN
';
		$tpl = str_replace($search,$search.$subPhpMyAdmin,$tpl);
		$search = ';WAMPMULTIPLEPHPMYADMIN
';
		$tpl = str_replace($search,$search.$ItemMenuPMA,$tpl);
		// Add warnings PhpMyAdmin if needed
		if($WarningsPMA) {
			$WarningTextAll = '
Type: Separator;
';
			$tpl = str_replace('WAMPMULTIPLEPHPMYADMINEND',$WarningTextAll.$WarningMenuPMA.$WarningTextPMA,$tpl);
		}
	}
	else {
		$search = ';WAMPPHPMYADMIN
';
		$tpl = str_replace($search,$search.$ItemMenuPMA,$tpl);
	}

	unset($ItemMenuPMA,$SubPhpMyAdmin);
}
// END of PhpMyAdmin menu
//***********************

// ****************************************
// Create menu with the available languages
if($handle = opendir($langDir))
{
	while (false !== ($file = readdir($handle)))
	{
		if($file != "." && $file != ".." && preg_match('|\.lang|',$file))
		{
			if($file == $lang.'.lang')
				$langList[$file] = 1;
			else
				$langList[$file] = 0;
		}
	}
	closedir($handle);
}

$langText = ";WAMPLANGUAGESTART
Type: separator; Caption: \"".$w_language."\";
";
ksort($langList);
foreach ($langList as $langname=>$langstatus)
{
  $cleanLangName = str_replace('.lang','',$langname);
  if($langList[$langname] == 1)
    $langText .= 'Type: item; Caption: "'.$cleanLangName.'"; Glyph: 13; Action: multi; Actions: lang_'.$cleanLangName.'
';
  else
    $langText .= 'Type: item; Caption: "'.$cleanLangName.'"; Action: multi; Actions: lang_'.$cleanLangName.'
';

}

foreach ($langList as $langname=>$langstatus)
{
  $cleanLangName = str_replace('.lang','',$langname);
  $langText .= <<< EOF
[lang_${cleanLangName}]
Action: run; FileName: "${c_phpCli}";Parameters: "changeLanguage.php ${cleanLangName}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
}

$tpl = str_replace(';WAMPLANGUAGESTART',$langText,$tpl);
unset($langText);
// END of menu with the available languages
// ****************************************

//***************************
// Creating PHP versions menu
$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php');
array_walk($phpVersionList,function(&$value, $key){$value = str_replace('php','',$value);});
// Sort in versions number order
natcasesort($phpVersionList);
create_wamp_versions($phpVersionList,'php');
if($doReport)	$wampReport['gen3'] .= "PHP versions seen by refresh listDir:\n".implode(' - ',$phpVersionList)."\n";
$myPattern = ';WAMPPHPVERSIONSTART';
$myreplace = $myPattern."
";
$myreplacemenu = '';
foreach ($phpVersionList as $onePhpVersion)
{
  $phpGlyph = '';
  //it checks if the PHP is compatible with the current version of apache
  unset($phpConf);
  include $c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampBinConfFiles;

  $apacheVersionTemp = $wampConf['apacheVersion'];
  while (!isset($phpConf['apache'][$apacheVersionTemp]) && $apacheVersionTemp != '')
  {
    $pos = strrpos($apacheVersionTemp,'.');
    $apacheVersionTemp = substr($apacheVersionTemp,0,$pos);
  }

  // Is PHP incompatible with the current version of apache
  $incompatiblePhp = 0;
  if(empty($apacheVersionTemp))
  {
    $incompatiblePhp = -1;
    $phpGlyph = '; Glyph: 19';
		$phpErrorMsg = "apacheVersion = empty in wampmanager.conf file";
  }
  elseif(empty($phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']))
  {
    $incompatiblePhp = -2;
    $phpGlyph = '; Glyph: 19';
		$phpErrorMsg = "\$phpConf['apache']['".$apacheVersionTemp."']['LoadModuleFile'] does not exists or is empty in ".$c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampBinConfFiles;
  }
  elseif(!file_exists($c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']))
  {
    $incompatiblePhp = -3;
    $phpGlyph = '; Glyph: 19';
		$phpErrorMsg = $c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']." does not exists.";
  }

  if($onePhpVersion === $wampConf['phpVersion'])
    $phpGlyph = '; Glyph: 13';

    $myreplace .= 'Type: item; Caption: "'.$onePhpVersion.'"; Action: multi; Actions:switchPhp'.$onePhpVersion.$phpGlyph.'
';
  if($incompatiblePhp == 0)
  {
  $myreplacemenu .= <<< EOF
[switchPhp${onePhpVersion}]
Action: run; FileName: "${c_phpCli}";Parameters: "switchPhpVersion.php ${onePhpVersion}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: service; Service: ${c_apacheService}; ServiceAction: restart; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
  }
  else
  {
  $myreplacemenu .= '[switchPhp'.$onePhpVersion.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 1 '.base64_encode($onePhpVersion).' '.base64_encode($phpErrorMsg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
  }
}
$myreplace .= 'Type: submenu; Caption: " "; Submenu: AddingVersions; Glyph: 1

';

$tpl = str_replace($myPattern,$myreplace.$myreplacemenu,$tpl);
unset($myreplace,$myreplacemenu,$myPattern);
// END of PHP versions menu
//*************************

// ********************************
// Creating the PHP extensions menu
$myphpini = file_get_contents_dos($c_phpConfFile);
$myphpini = clean_file_contents($myphpini,array(2,1),false,true,$c_phpConfFile);
$NBextPHPlines = 0;
//recovering the extensions loading configuration
preg_match_all('/^extension\s*=\s*"?([a-z0-9_]+)"?.*\r?$/im',$myphpini,$matchesON);
preg_match_all('/^;extension\s*=\s*"?([a-z0-9_]+)"?.*\r$/im',$myphpini,$matchesOFF);

$ext = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');

//recovering the zend_extensions loading configuration
preg_match_all('~^zend_extension\s*=\s*"([a-z0-9_:/\-\.]+)\.dll"?~im',$myphpini,$matchesON);
preg_match_all('~^;zend_extension\s*=\s*"([a-z0-9_:/\-\.]+)\.dll"?~im',$myphpini,$matchesOFF);
if(count($matchesON[0]) > 0 ) {
	$i = 0 ;
	foreach($matchesON[0] as $value) {
		foreach($zend_extensions as $key => $zend_value) {
			if(stripos($value,$key) !== false) {
				$zend_extensions[$key]['loaded'] = '1';
				$zend_extensions[$key]['content'] = $matchesON[1][$i];
				$i++;
			}
		}
	}
}

if(count($matchesOFF[0]) > 0 ) {
	$i = 0 ;
	foreach($matchesOFF[0] as $value) {
		foreach($zend_extensions as $key => $zend_value) {
			if(stripos($value,$key) !== false) {
				$zend_extensions[$key]['loaded'] = '0';
				$zend_extensions[$key]['content'] = $matchesOFF[1][$i];
				$i++;
			}
		}
	}
}

if(preg_match('/^.*php_xdebug\-([0-9\.]+[alpha|beta|rc1-9]*)\-.*\s?$/im',$zend_extensions['php_xdebug']['content'],$matches) > 0 )
	$zend_extensions['php_xdebug']['version'] = $matches[1];
foreach($zend_extensions as $key => $value) {
	$ext[$key] = $zend_extensions[$key]['loaded'];
}
ksort($ext);
$Extensions_in_php_ini = array_combine(array_keys($ext),array_keys($ext));
// recovering the extensions list (.dll files) present in the directory ext
$extDirContents = glob($c_phpExtDir.'/*.dll');
array_walk($extDirContents,function(&$item){
	$item = str_replace('.dll','',basename($item));
	});
$dll_in_php_ext_dir = array_combine($extDirContents,$extDirContents);
//For PHP 7.2.0+ we have to add php_ at the beginning if not
function add_php(&$item, $key) {
	if(strpos($item,'php_') === false)
		$item = 'php_'.$item;
}
array_walk($Extensions_in_php_ini,'add_php');
$Extensions_in_php_ini = array_combine($Extensions_in_php_ini,$Extensions_in_php_ini);

// both tables are "crossed"
//DLL extension file exists but no extension= line in phpForApache.ini
$noExtLine = array_diff_key($dll_in_php_ext_dir,$Extensions_in_php_ini);

//extension= line exists in phpForApache.ini but no dll file
$noDllFile = array_diff_key($Extensions_in_php_ini,$dll_in_php_ext_dir);

foreach($noExtLine as $value) {
	if(array_key_exists($value,$zend_extensions))
		$ext[$value] = -4; //dll must be loaded by zend_extension
	elseif(in_array($value,$phpNotLoadExt))
		$ext[$value] = -3; //dll not to be loaded by extension = in php.ini
	else
		$ext[$value] = -1; //dll file exists but not extension line in php.ini
}
foreach($noDllFile as $value) {
	if(array_key_exists($value,$zend_extensions))
		$ext[$value] = -4; //dll must be loaded by zend_extension
	else
		$ext[$value] = -2; //extension line in php.ini but not dll file
}

// Check if it is a zend_extension
foreach($ext as $key => $value) {
	if(array_key_exists($key,$zend_extensions)) {
		$ext[$key] = -4; //dll must be loaded by zend_extension
		// Check if there is content
		if(empty($zend_extensions[$key]['content']))
			$ext[$key] = -5; //Does not exists
		// Check if dll file exists
		if(!file_exists($zend_extensions[$key]['content'].".dll"))
			$ext[$key] = -6; //Dll not exists
	}
}

//we construct the corresponding menu
$extText = ';WAMPPHP_EXTSTART
';
$extTextInfo = "";
$notloadExt = false;
$notloadExtZend = false;
$extTextNoDll = "";
$notDll = false;
$extTextNoline = "";
$notLine = false;

foreach ($ext as $extname=>$extstatus)
{
  if($ext[$extname] == 1) {
  	$NBextPHPlines++;
    $extText .= 'Type: item; Caption: "'.$extname.'"; Glyph: 13; Action: multi; Actions: php_ext_'.$extname.'
';
	}
  elseif($ext[$extname] == -1)
  {
		if(!$notLine) {
			$extTextNoline .= 'Type: separator; Caption: "'.$w_ext_noline.'"
';
			$notLine = true;
		}
   	//Warning icon to indicate problem with this extension: No extension line in php.ini
    $extTextNoline .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$extname.' ; Glyph: 19;
';
	}
  elseif($ext[$extname] == -2)
  {
		if(!$notDll) {
			$extTextNoDll .= 'Type: separator; Caption: "'.$w_ext_nodll.'"
';
			$notDll = true;
		}
   	//Square red icon to indicate problem with this extension: no dll file in ext directory
    $extTextNoDll .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$extname.' ; Glyph: 11;
';
	}
  elseif($ext[$extname] == -3)
  {
		if(!$notloadExt) {
			$extTextInfo .= 'Type: separator; Caption: "'.$w_ext_spec.'"
';
			$notloadExt = true;
		}
   	//blue || icon to indicate that the dll must not be loaded by extension = in php.ini
    $extTextInfo .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$extname.' ; Glyph: 22;
';
	}
  elseif($ext[$extname] == -4) //Must be loaded by zend_extension
  {
		if(!$notloadExtZend) {
			$extTextInfo .= 'Type: separator; Caption: "'.$w_ext_zend.'"
';
			$notloadExtZend = true;
		}
		$GlyphZend = '';
		if($zend_extensions[$extname]['loaded'] == '1')
			$GlyphZend = "Glyph: 13;";
			$extname_nophp = str_replace('php_','',$extname);
   	$extTextInfo .= 'Type: item; Caption: "'.$extname_nophp.' '.$zend_extensions[$extname]['version'].'"; '.$GlyphZend.'Action: multi; Actions: php_ext_'.$extname.'
';
	}
  elseif($ext[$extname] == -5)
  {
  	 //Zend extension does not exixts - do nothing
  }
  elseif($ext[$extname] == -6)  //Zend extension dll file does not exixts - do nothing
  {
		if(!$notDll) {
			$extTextNoDll .= 'Type: separator; Caption: "'.$w_ext_nodll.'"
';
			$notDll = true;
		}
   	//Square red icon to indicate problem with this extension: no dll file in ext directory
    $extTextNoDll .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$extname.' ; Glyph: 11;
';
  }
  else
  {
  	$NBextPHPlines++;
    $extText .= 'Type: item; Caption: "'.$extname.'"; Action: multi; Actions: php_ext_'.$extname.'
';
	}
}
$extText .= $extTextNoline.$extTextNoDll.$extTextInfo;

foreach ($ext as $extname=>$extstatus)
{
	if($ext[$extname] == 1 || $ext[$extname] == 0) {
		$SwitchAction = ($ext[$extname] == 1 ? 'off' : 'on');
	$extText .= <<< EOF
[php_ext_${extname}]
Action: run; FileName: "${c_phpCli}";Parameters: "switchPhpExt.php ${extname} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
	}
	elseif($ext[$extname] == -4) {
		$SwitchAction = ($zend_extensions[$extname]['loaded'] == 1 ? 'zendoff' : 'zendon');
		$extcontent = $zend_extensions[$extname]['content'];
	$extText .= <<< EOF
[php_ext_${extname}]
Action: run; FileName: "${c_phpCli}";Parameters: "switchPhpExt.php ${extcontent} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
	}
	elseif($ext[$extname] == -1 || $ext[$extname] == -2 || $ext[$extname] == -3 || $ext[$extname] == -6) {
		$extname_msg = $extname;
		if($ext[$extname] == -1) $msgNum = 3;
		elseif($ext[$extname] == -2) $msgNum = 4;
		elseif($ext[$extname] == -3) $msgNum = 5;
		elseif($ext[$extname] == -6) {
			$msgNum = 16;
			$extname_msg = $zend_extensions[$extname]['content'];
		}
    $extText .= '[php_ext_'.$extname.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php '.$msgNum.' '.base64_encode($extname_msg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}
//error_log("NBext=".$NBextPHPlines);
$NBextPHPlines = ceil(($NBextPHPlines)/2);

$tpl = str_replace(';WAMPPHP_EXTSTART',$extText,$tpl);
unset($extText,$extTextNoline,$extTextNoDll,$extTextInfo);
// *** END of PHP extensions menu
// ******************************

// **********************************************
// Creating the PHP parameters configuration menu
$myphpini = parse_ini_file($c_phpConfFile,false,INI_SCANNER_RAW);
$myphpinitxt = file_get_contents($c_phpConfFile);
$phpReportConf = array();
// values are recovered from the file phpForApache.ini
$phpParams = array_combine($phpParams,$phpParams);
foreach($phpParams as $next_param_name => $next_param_text) {
	if(array_key_exists($next_param_name,$myphpini)) $phpReportConf[$next_param_name] = $myphpini[$next_param_name];
  if(isset($myphpini[$next_param_text])) {
  	if(empty($myphpini[$next_param_text]))
  		$params_for_wampini[$next_param_name] = '0';
  	if((stripos($next_param_name, 'xdebug') !== false) && $zend_extensions['php_xdebug']['loaded'] == '0') {
			$params_for_wampini[$next_param_name] = -4; //Extension not loaded - Parameter not to display
		}
  	elseif(array_key_exists($next_param_name, $phpParamsNotOnOff)) {
  		if($phpParamsNotOnOff[$next_param_name]['change'] !== true) {
  	  	$params_for_wampini[$next_param_name] = -2;
  	  	$phpErrorMsg = "\nIf you want to change this value, you can do it directly in the file:\n".$c_phpConfFile."\nNot to change the wrong file, the best way to access this file is:\nWampmanager icon->PHP->php.ini\n";
  		}
  		else {
  	  	$params_for_wampini[$next_param_name] = -3;
  	  	if($next_param_name == 'xdebug.mode' && $myphpini[$next_param_name] == false)
  	  		$myphpini[$next_param_name] = 'off';
  		}
  	}
  	elseif(strtolower($myphpini[$next_param_text]) == "off")
  		$params_for_wampini[$next_param_name] = 'off';
  	elseif(strtolower($myphpini[$next_param_text]) == "on")
  		$params_for_wampini[$next_param_name] = 'on';
  	elseif($myphpini[$next_param_text] == 0)
  		$params_for_wampini[$next_param_name] = '0';
  	elseif($myphpini[$next_param_text] == 1)
  		$params_for_wampini[$next_param_name] = '1';
  	else
  	  $params_for_wampini[$next_param_name] = -2;
  }
  else {//Parameter in $phpParams (config.inc.php) does not exist in php.ini or is commented
  	if(strpos($myphpinitxt,';'.$next_param_text) === false)
  		$params_for_wampini[$next_param_name] = -1;
  	else {// Directive is commented (; before)
    	$params_for_wampini[$next_param_name] = -5;
 		}
  }
}
ksort($phpReportConf);
if($doReport) {
	//PHP configuration values
	$wampReport['phpConf'] .= "\n-- PHP Configuration values\n\n";
	$nbbyline = 0;
	foreach($phpReportConf as $key => $value) {
		$wampReport['phpConf'] .= str_pad(' '.$key." = ".$value,40);
		if(++$nbbyline >= 2) {
			$wampReport['phpConf'] .= "\n";
			$nbbyline = 0;
		}
	}
	$wampReport['phpConf'] .= "\n";
	$wampReport['phpConf'] .= "\n--------------------------------------------------\n";
}
unset($phpReportConf);

$phpConfText = ";WAMPPHP_PARAMSSTART
";
$phpConfTextInfo = "";
$phpConfTextComment = "";
$action_sup = $seeInfoGlyphException = array();
$information_only = false;
$xDebugSep = false;
$NBparamPHP = $NBparamPHPinfo = $NBparamPHPcomment = $NBparamPHPxdebug = 0;
foreach ($params_for_wampini as $paramname => $paramstatus) {
	$seeInfoGlyphException[$paramname] = false;
	$xdebugParam = (strpos($paramname, 'xdebug') !== false) ? true : false;
	if($xdebugParam && $zend_extensions['php_xdebug']['loaded'] == '1') {
		$NBparamPHPxdebug++;
		if(!$xDebugSep) {
			$xDebugSep = true;
			$phpConfText .= 'Type: Separator; Caption: "Extension Zend xdebug '.$zend_extensions['php_xdebug']['version'].'"
';
		}
	}
  if($params_for_wampini[$paramname] == '1' || $params_for_wampini[$paramname] == 'on') {
		if(!$xdebugParam) $NBparamPHP++;
	  $phpConfText .= 'Type: item; Caption: "'.$paramname.'"; Glyph: 13; Action: multi; Actions: '.$phpParams[$paramname].'
';
	}
  elseif($params_for_wampini[$paramname] == '0' || $params_for_wampini[$paramname] == 'off') { //It does not display non-existent settings in php.ini
		if(!$xdebugParam) $NBparamPHP++;
    $phpConfText .= 'Type: item; Caption: "'.$paramname.'"; Action: multi; Actions: '.$phpParams[$paramname].'
';
	}
	elseif($params_for_wampini[$paramname] == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
		if(!$xdebugParam) $NBparamPHP++;
		$action_sup[] = $paramname;
		$phpConfText .= 'Type: submenu; Caption: "'.$paramname.' = '.$myphpini[$paramname].'"; Submenu: '.$paramname.'; Glyph: 9
';
	}
	elseif($params_for_wampini[$paramname] == -2) { // Information to indicate different from 0 or 1 or On or Off
		$NBparamPHPinfo++;
		if(!$information_only) {
			$phpConfTextInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
		// Tests for 'error_reporting'
		if(($paramname == 'error_reporting') && (version_compare($c_phpVersion, '5.4.0') >= 0)) {
			$seeInfoGlyphException[$paramname] = true;
			$report_err = errorLevel($myphpini[$paramname]);
			$phpConfTextInfo .= 'Type: separator;
';
			$firstReportErr = true;
			foreach($report_err as $key => $value) {
				if($firstReportErr) {
    			$phpConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$report_err[$key]['str'].'"; Glyph: 22; Action: multi; Actions: '.$phpParams[$paramname].'
';
					$firstReportErr = false;
				}
				else {
    			$phpConfTextInfo .= 'Type: item; Caption: "'.$report_err[$key]['str'].'"; Action: multi; Actions: none
';
				}
				if(strpos($report_err[$key]['comment'],'^') !== false) {
					list($err_title, $err_info) = explode('^',$report_err[$key]['comment']);
   				$phpConfTextInfo .= 'Type: item; Caption: "       '.$err_title.'"; Action: multi; Actions: none
';
					$phpConfTextInfo .= menu_multi_lines($err_info);
				}
				else {
   				$phpConfTextInfo .= menu_multi_lines($report_err[$key]['comment']);
				}
			}
			$phpConfTextInfo .= 'Type: separator;
';
		} // End tests for 'error_reporting'
		else {
			if($seeInfoMessage) {
    		$phpConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$myphpini[$paramname].'"; Action: multi; Actions: '.$phpParams[$paramname].' ;
';
			}
			else {
    		$phpConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$myphpini[$paramname].'"; Action: multi; Actions: none
';
			}
		}
	} //End for -2
	elseif($params_for_wampini[$paramname] == -4) {
		// Do nothing
	}
	elseif($params_for_wampini[$paramname] == -5) {
		$NBparamPHPcomment++;
    $phpConfTextComment .= 'Type: item; Caption: ";'.$paramname.'"; Action: multi; Actions: none
';
	}
} // end foreach $params_for_wampini
// $NBparamPHPlines used for BigMenus (Aestan Tray Menu columns menus)
//error_log("NBparamPHP=".$NBparamPHP." - NBparamPHPinfo=".$NBparamPHPinfo." - NBparaPHPcomment=".$NBparamPHPcomment." - NBparamPHPxdebug=".$NBparamPHPxdebug);
$NBparamPHPlines = $NBparamPHP + 1;
unset($NBparamPHP,$NBparamPHPinfo,$NBparamPHPcomment,$NBparamPHPxdebug);

//Check for supplemtary actions
$MenuSup = $SubMenuSup = array();
if(count($action_sup) > 0) {
	$i = 0;
	foreach($action_sup as $action) {
		$MenuSup[$i] = $SubMenuSup[$i] = '';
		if($action == 'date.timezone') {
			$RegionCitySelected = $myphpini[$action];
			$RegionSelected = $RegionCitySelected;
			$CitySelected = '';
			if(strpos($RegionCitySelected,"/") !== false) {
				list($RegionSelected,$CitySelected) = explode("/",$RegionCitySelected);
			}
			if($phpParamsNotOnOff[$action]['quoted'])
				$quoted = 'quotes';
			else
				$quoted = 'noquotes';
			$MenuSup[$i] .= '['.$action.']
Type: separator; Caption: "'.$phpParamsNotOnOff[$action]['title'].'"
';
			$tzs = timezone_identifiers_list();
			sort($tzs);
			$regions = $phpParamsNotOnOff[$action]['values'];
			$group = '';
			foreach ($regions as $value) {
					$Glyph = ($value == $RegionSelected ? '; Glyph: 9' : '');
			    $MenuSup[$i] .= 'Type: submenu; Caption: "'.$value.'"; Submenu: tz'.$value.$Glyph.'
';
			}
			foreach ($tzs as $tz) {
			  $z = explode('/', $tz, 2);
			  if(count($z) != 2 || !in_array($z[0], $regions)) continue;
			  if($group != $z[0]) {
			    $group = $z[0];
			    $MenuSup[$i] .= <<< EOF
[tz${z[0]}]
Type: Separator; Caption: "${z[0]}"

EOF;
			  }
			  $Glyph = ($tz == $RegionCitySelected ? '; Glyph: 9' : '');
				$MenuSup[$i] .= 'Type: item; Caption: "'.$tz.'"; Action: multi; Actions: time_'.$tz.$Glyph.'
';
				$SubMenuSup[$i] .= <<< EOF
[time_${tz}]
Action: run; FileName: "${c_phpCli}";Parameters: "changePhpParam.php ${quoted} ${action} ${tz}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
			}
		} // End of date.timezone
		else {
			//If change parameter doesn't support 'Apache Graceful Restart' but 'Apache service restart'
			$Apache_Save_Restart = $Apache_Restart_Php_Conf = $Apache_Restart;
			foreach($phpParamsNotGraceful as $value) {
				if(strpos($action, $value) !== false) {
					$Apache_Restart_Php_Conf = $Apache_Service_Restart;
					break;
				}
			}
			$MenuSup[$i] .= '['.$action.']
Type: separator; Caption: "'.$phpParamsNotOnOff[$action]['title'].'"
';
			$c_values = $c_infos = $phpParamsNotOnOff[$action]['values'];
			if(!empty($phpParamsNotOnOff[$action]['infos'])) $c_infos = $phpParamsNotOnOff[$action]['infos'];
			if($phpParamsNotOnOff[$action]['quoted'])
				$quoted = 'quotes';
			else
				$quoted = 'noquotes';
			foreach($c_values as $key => $value) {
				$value_caption = $value;
				if($c_infos[$key] != $value) $value_caption .= ' - '.$c_infos[$key];
				$MenuSup[$i] .= 'Type: item; Caption: "'.$value_caption.'"; Action: multi; Actions: '.$action.$value.'
';
				if(strtolower($value) == 'choose') {
					$param_value = '%'.$phpParamsNotOnOff[$action]['title'].'%';
					$param_third = ' '.$phpParamsNotOnOff[$action]['title'];
					if($phpParamsNotOnOff[$action]['title'] == 'Integer')
						$param_third .= ' '.$phpParamsNotOnOff[$action]['min'].'^'.$phpParamsNotOnOff[$action]['max'].'^'.$phpParamsNotOnOff[$action]['default'];
					$c_phpRun = $c_phpExe;
				}
				else {
					$param_value = $value;
					$param_third = '';
					$c_phpRun = $c_phpCli;
				}
				$SubMenuSup[$i] .= <<< EOF
[${action}${value}]
Action: run; FileName: "${c_phpRun}";Parameters: "changePhpParam.php ${quoted} ${action} ${param_value}${param_third}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart_Php_Conf}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
			}
		$Apache_Restart = $Apache_Save_Restart;
		}
	$i++;
	}
}
// Is there commented php.ini directives ?
$phpConfTextCommentSub = $phpConfTextCommentSubMenu = "";
if(!empty($phpConfTextComment)) {
	$phpConfTextCommentSub .= 'Type: submenu; Caption: "'.$w_settings['iniCommented'].'"; Submenu: phpinicommented; Glyph: 9
';
	$phpConfTextCommentSubMenu .= <<< EOF
[phpinicommented]
${phpConfTextComment}

EOF;
}
$phpConfText .= $phpConfTextInfo.$phpConfTextCommentSub;

foreach ($params_for_wampini as $paramname=>$paramstatus) {
	if($params_for_wampini[$paramname] == '1' || $params_for_wampini[$paramname] == '0' || $params_for_wampini[$paramname] == 'on' || $params_for_wampini[$paramname] == 'off') {
		if($params_for_wampini[$paramname] == '1' || $params_for_wampini[$paramname] == '0')
			$SwitchAction = ($params_for_wampini[$paramname] == '1' ? '0' : '1');
		else
			$SwitchAction = ($params_for_wampini[$paramname] == 'on' ? 'off' : 'on');
  	$phpConfText .= <<< EOF
[${phpParams[$paramname]}]
Action: run; FileName: "${c_phpCli}";Parameters: "switchPhpParam.php ${phpParams[$paramname]} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
	}
  elseif($params_for_wampini[$paramname] == -2)  {//Parameter is neither 'on' nor 'off'
  	if($seeInfoMessage || $seeInfoGlyphException[$paramname]) {
  		$phpConfText .= '['.$phpParams[$paramname].']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 6 '.base64_encode($paramname).' '.base64_encode($phpErrorMsg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
	}
}
if(count($MenuSup) > 0) {
	for($i = 0 ; $i < count($MenuSup); $i++)
		$phpConfText .= $MenuSup[$i].$SubMenuSup[$i];
}
$phpConfText .= $phpConfTextCommentSubMenu;

$tpl = str_replace(';WAMPPHP_PARAMSSTART',$phpConfText,$tpl);
unset($phpConfText,$phpConfTextCommentSubMenu);
// **** END of PHP parameters configuration menu ****
// **************************************************

// *****************************
// Creating Apache versions menu
$apacheVersionList = listDir($c_apacheVersionDir,'checkApacheConf','apache');
array_walk($apacheVersionList,function(&$value, $key){$value = str_replace('apache','',$value);});
// Sort in versions number order
natcasesort($apacheVersionList);
create_wamp_versions($apacheVersionList,'apache');
if($doReport)	$wampReport['gen3'] .= "Apache versions seen by refresh listDir:\n".implode(' - ',$apacheVersionList)."\n";
$myPattern = ';WAMPAPACHEVERSIONSTART';
$myreplace = $myPattern."
";
$myreplacemenu = '';

foreach ($apacheVersionList as $oneApacheVersion)
{
  $apacheGlyph = '';
	//we check if Apache is compatible with the current version of PHP
  unset($phpConf);
  include $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampBinConfFiles;
  $apacheVersionTemp = $oneApacheVersion;
  while (!isset($phpConf['apache'][$apacheVersionTemp]) && $apacheVersionTemp != '')
  {
    $pos = strrpos($apacheVersionTemp,'.');
    $apacheVersionTemp = substr($apacheVersionTemp,0,$pos);
  }

  // Apache incompatible with the current version of PHP
  $incompatibleApache = 0;
  if(empty($apacheVersionTemp))
  {
    $incompatibleApache = -1;
    $apacheGlyph = '; Glyph: 19';
		$apacheErrorMsg = "apacheVersion = empty in wampmanager.conf file";
  }
  elseif(!isset($phpConf['apache'][$apacheVersionTemp]['LoadModuleFile'])
      || empty($phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']))
  {
    $incompatibleApache = -2;
    $apacheGlyph = '; Glyph: 19';
		$apacheErrorMsg = "\$phpConf['apache']['".$apacheVersionTemp."']['LoadModuleFile'] does not exists or is empty in ".$c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampBinConfFiles;
  }
  elseif(!file_exists($c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']))
  {
    $incompatibleApache = -3;
    $apacheGlyph = '; Glyph: 23';
		$apacheErrorMsg = $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']." does not exists.".PHP_EOL.PHP_EOL."First switch on a version of PHP that contains ".$phpConf['apache'][$apacheVersionTemp]['LoadModuleFile']." file before you change to Apache version ".$oneApacheVersion.".";
  }

  //File wamp/bin/apache/apachex.y.z/wampserver.conf
  //Update apache service name if it is modified.
  $ApacheConfFile = $c_apacheVersionDir.'/apache'.$oneApacheVersion.'/'.$wampBinConfFiles;
  $myApacheContents = file_get_contents($ApacheConfFile);
	if(substr_count($myApacheContents, " ".$c_apacheService." ") < 2) {
		$pattern = array(
			"/^.*apacheServiceInstallParams.*\n/m",
			"/^.*apacheServiceRemoveParams.*\n/m");
		$replace = array(
			"\$apacheConf['apacheServiceInstallParams'] = '-n ".$c_apacheService." -k install';\n",
			"\$apacheConf['apacheServiceRemoveParams'] = '-n ".$c_apacheService." -k uninstall';\n");
		$myApacheContents = preg_replace($pattern,$replace,$myApacheContents, 1, $count);
		if(!is_null($myApacheContents) && $count > 0) {
			if(WAMPTRACE_PROCESS) error_log("write ".$c_apacheConfFile." in ".__FILE__." line ". __LINE__."\n",3,WAMPTRACE_FILE);
			write_file($ApacheConfFile,$myApacheContents);
		}
	}

  unset($apacheConf);
  include $ApacheConfFile;

  if($oneApacheVersion === $wampConf['apacheVersion'])
    $apacheGlyph = '; Glyph: 13';

  $myreplace .= 'Type: item; Caption: "'.$oneApacheVersion.'"; Action: multi; Actions:switchApache'.$oneApacheVersion.$apacheGlyph.'
';

  if($incompatibleApache == 0)
  {
    $myreplacemenu .= <<< EOF
[switchApache${oneApacheVersion}]
Action: service; Service: ${c_apacheService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; Filename: "CMD"; Parameters: "/D /C sc stop ${c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; Filename: "CMD"; Parameters: "/D /C sc delete ${c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: closeservices; Flags: ignoreerrors
Action: run; Filename: "taskkill"; Parameters: "/FI ""IMAGENAME eq httpd.exe"" /T /F"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "switchApacheVersion.php ${oneApacheVersion}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "switchPhpVersion.php ${wampConf['phpVersion']}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_apacheVersionDir}/apache${oneApacheVersion}/${apacheConf['apacheExeDir']}/${apacheConf['apacheExeFile']}"; Parameters: "${apacheConf['apacheServiceInstallParams']}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; Filename: "CMD"; Parameters: "/D /C sc config ${c_apacheService} start= demand"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "switchWampPort.php ${c_UsedPort}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "CMD"; Parameters: "/D /C net start ${c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
  }
  else
  {
    $myreplacemenu .= '[switchApache'.$oneApacheVersion.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 2 '.base64_encode($oneApacheVersion).' '.base64_encode($apacheErrorMsg).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
  }
}
$myreplace .= 'Type: submenu; Caption: " "; Submenu: AddingVersions; Glyph: 1

';
$tpl = str_replace($myPattern,$myreplace.$myreplacemenu,$tpl);
// END of Apache versions menu
//****************************

// ************************************
// Creating the menu for Apache modules
$myhttpdContents = @file_get_contents($c_apacheConfFile);
// Recovering the extensions loading configuration
preg_match_all('~^LoadModule\s+([0-9a-z_]+)\s+(?:modules/|)(.+)\r?$~im',$myhttpdContents,$matchesON);
preg_match_all('~^#LoadModule\s+([0-9a-z_]+)\s+(?:modules/|)(.+)\\r?$~im',$myhttpdContents,$matchesOFF);
// Key = module_name - Value = Module loaded = 1, not loaded = 0
$mod = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');
// Key = module_name - Value = file name in modules/ folder
$mod_load = array_combine($matchesON[1],$matchesON[2]) + array_combine($matchesOFF[1],$matchesOFF[2]);
array_walk($mod_load,function(&$item){$item = trim($item);});
ksort($mod);
ksort($mod_load);

// Retrieve list of modules in the /modules/ folder
$modDirContents = glob($c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/modules/*.so');
array_walk($modDirContents,function(&$item){$item = basename($item);});
$mod_in_modules_dir = array_combine($modDirContents,$modDirContents);

// xxxxx.so file exists but no LoadModule line in httpd.conf
$noModLine = array_diff($mod_in_modules_dir,$mod_load);
foreach($noModLine as $value) {
	$value = str_replace(array("mod_",".so"),array("","_module"),$value);
	$mod[$value] = -2 ; //Module file exists but no loadModule line in httpd.conf
}

// LoadModule line exists in httpd.conf but no xxxxx.so file in modules/ folder
$noModFile = array_diff($mod_load,$mod_in_modules_dir);
foreach($noModFile as $key => $value) {
	$mod[$key] = -1 ; // loadModule line in httpd.conf but no file .so in modules dir
}
// LoadModule should not be disabled
foreach($apacheModNotDisable as $value) {
	if(array_key_exists($value,$mod))
		$mod[$value] = -3 ; //not to be switched in Apache Modules sub-menu
}

$httpdText = 'Type: separator; Caption: "'.$w_apacheModules.'"
';
$httpdTextInfo ="";
$httpdInfo = false;
$httpdTextNoModule ="";
$httpdNoModule = false;
$httpdTextNoLoad ="";
$httpdNoLoad = false;

foreach ($mod as $modname=>$modstatus)
{
  if($modstatus == 1)
    $httpdText .= 'Type: item; Caption: "'.$modname.'"; Glyph: 13; Action: multi; Actions: apache_mod_'.$modname.'
';
	elseif($modstatus == -1) { //Red square - No module file
		if(!$httpdNoModule) {
			$httpdTextNoModule .= 'Type: separator; Caption: "'.$w_no_module.'"
';
			$httpdNoModule = true;
		}
		$httpdTextNoModule .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.' ; Glyph: 11;
';
	}
	elseif($modstatus == -2) {// /!\ Warning - Module file exists but no loadModule line in httpd.conf
		if(!$httpdNoLoad) {
			$httpdTextNoLoad .= 'Type: separator; Caption: "'.$w_no_moduleload.'"
';
			$httpdNoLoad = true;
		}
		$httpdTextNoLoad .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.' ; Glyph: 19;
';
	}
	elseif($modstatus == -3) {//not to be switched in Apache Modules sub-menu
		if(!$httpdInfo) {
			$httpdTextInfo .= 'Type: separator; Caption: "'.$w_mod_fixed.'"
';
			$httpdInfo = true;
		}
		$httpdTextInfo .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.' ; Glyph: 22;
';
	}
  else
    $httpdText .= 'Type: item; Caption: "'.$modname.'"; Action: multi; Actions: apache_mod_'.$modname.'
';

}

$httpdText = ";WAMPAPACHE_MODSTART
".$httpdTextNoLoad.$httpdTextNoModule.$httpdText.$httpdTextInfo;

$NBmodApacheLines = 0;
foreach ($mod as $modname=>$modstatus)
{
	$NBmodApacheLines++;
	if($mod[$modname] == 1 || $mod[$modname] == 0) {
		$SwitchAction = ($mod[$modname] == 1 ? 'on' : 'off');
    $httpdText .= <<< EOF
[apache_mod_${modname}]
Action: run; FileName: "${c_phpCli}";Parameters: "switchApacheMod.php ${modname} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
	}
	elseif($mod[$modname] == -1 || $mod[$modname] == -2 || $mod[$modname] == -3) {
		if($mod[$modname] == -1) $msgNum = 7;
		elseif($mod[$modname] == -2) $msgNum = 8;
		elseif($mod[$modname] == -3) $msgNum = 12;
		$modFile = 'mod_'.str_replace('_module','',$modname).'.so';
    $httpdText .= '[apache_mod_'.$modname.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php '.$msgNum.' '.base64_encode($modname).' '.base64_encode($modFile).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}

$NBmodApacheLines = ceil(($NBmodApacheLines+2)/4);

$tpl = str_replace(';WAMPAPACHE_MODSTART',$httpdText,$tpl);
// END of menu for Apache modules
//*******************************

//***************************************************
// Creating Apache configuration compare version menu
if($wampConf['apacheCompareVersion'] == 'on' && count($apacheVersionList) > 1) {
	$httpdTextMenu = '';
	$httpdText = ';WAMPAPACHECOMPARE
Type: submenu; Caption: "'.$w_apache_compare.'"; Submenu: apachecompare-help; Glyph: 22
Type: submenu; Caption: "'.$w_compareApache.'"; Submenu: apache_compare; Glyph: 9
';
	$httpdTextSubMenu = ";WAMPAPACHEITEMCOMPARE
[apache_compare]
";
	foreach($apacheVersionList as $value) {
		if($value == $c_apacheVersion) continue;
    $httpdTextSubMenu .= '
Type: item; Caption: "Apache '.$c_apacheVersion.' '.$w_versus.' '.$value.'"; Action: multi; Actions: apache_comp_'.$value.'; Glyph: 23
';
		$httpdTextMenu .= <<< EOF
[apache_comp_${value}]
Action: run; FileName: "${c_phpExe}";Parameters: "switchApacheVersion.php ${c_apacheVersion} ${value} compare";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;

	}
	$tpl = str_replace(';WAMPAPACHECOMPARE',$httpdText,$tpl);
	$tpl = str_replace(';WAMPAPACHEITEMCOMPARE',$httpdTextSubMenu,$tpl);
	$tpl = str_replace(';WAMPAPACHEITEMMENUS',$httpdTextMenu,$tpl);
}
// END of Apache configuration compare version menu
//*************************************************

//*******************************************
// Creating Apache restore orignal files menu
//The base directory is Apache conf directory ie $c_apacheConfDir
//File were saved into:
$c_apacheOriginalDir = $c_apacheConfDir.'/original/wampserver/';
//To be restored into $c_apacheConfDir or $c_apacheConfDir/extra
if($wampConf['apacheRestoreFiles'] == 'on' && is_dir($c_apacheOriginalDir)) {
	$httpdTextMenu = '';
	$httpdText = ';WAMPAPACHERESTORE
Type: submenu; Caption: "'.$w_apache_restore.'"; Submenu: apacherestore-help; Glyph: 22
Type: submenu; Caption: "'.$w_restorefile.'"; Submenu: apache_restore; Glyph: 9
';
	$httpdTextSubMenu = ";WAMPAPACHEITEMRESTORE
[apache_restore]
";
$restoreFile = read_dir($c_apacheOriginalDir);
	if(count($restoreFile) > 0) {
		foreach($restoreFile as $value) {
			$info = pathinfo($value);
    	$httpdTextSubMenu .= '
Type: item; Caption: "'.$w_restore.' '.$info['basename'].'"; Action: multi; Actions: apache_rest_'.$info['basename'].'; Glyph: 23
';
			$c_extra = '';
			if($info['basename'] == 'httpd-vhosts.conf') $c_extra = "extra\\";
			$command = " /D /C COPY /Y ".str_replace('/','\\',$value)." ".str_replace('/','\\',$c_apacheConfDir).'\\'.$c_extra.$info['basename'];
			$httpdTextMenu .= <<< EOF
[apache_rest_${info['basename']}]
Action: run; Filename:"${c_apacheExe}"; Parameters: "-n ${c_apacheService} -k stop"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "CMD";Parameters: "${command}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: run; Filename:"${c_apacheExe}"; Parameters: "-n ${c_apacheService} -k start"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
		}
		$tpl = str_replace(';WAMPAPACHERESTORE',$httpdText,$tpl);
		$tpl = str_replace(';WAMPAPACHEITEMRESTORE',$httpdTextSubMenu,$tpl);
		$tpl = str_replace(';WAMPAPACHEMENUSRESTORE',$httpdTextMenu,$tpl);
	}
}
// END of Apache restore original files menu
//******************************************

//***********************************
// Creating Apache configuration menu
$httpdText = ";WAMPAPACHEPARAMSSTART
";
$httpdConfParams = array();
$ApacheDefaultOnlyTxt = '';
$ApacheDefault = false;
foreach($apacheParams as $key => $value ) {
	if(preg_match_all('~^('.$key.')[ \t]+('.$value.').*\r?$~mi',$myhttpdContents,$matches) > 0) {
		foreach($matches[1] as $key1 => $value1) {
			$httpdConfParams[][$matches[1][$key1]] = $matches[2][$key1];
		}
	}
	else {
		if(array_key_exists($key,$apacheParamsDefault)) {
		if(!$ApacheDefault) {
			$ApacheDefaultOnlyTxt .= 'Type: separator; Caption: "'.$w_apacheDefaults.'"
';
			$ApacheDefault = true;
		}
			$ApacheDefaultOnlyTxt .= "Type: item; Caption: \"".$key."  ".$apacheParamsDefault[$key]."\"; Action: multi; Actions: none
";
		}
		else {
			$httpdConfParams[][$key] = '>>>ERROR<<<';
		}
	}
}

$tab = "  ";
foreach($httpdConfParams as $key => $value) {
	foreach($value as $key1 => $value1) {
		if($value1 == '>>>ERROR<<<') {
			$readValue = 'unknown';
			if(preg_match('~^('.$key1.')[ \t]+(.+)\r?$~mi',$myhttpdContents,$matches) > 0) {
				$readValue = trim($matches[2]);
			}
			$message = " The value: '".$readValue."' for Apache httpd.conf directive '".$key1."' is not good.\n\n";
			$message .= " Accepted values are:\n".implode("\n",explode('|',$apacheParams[$key1]))."\n";
			$httpdText .= 'Type: item; Caption: "'.$key1.' '.$value1.'"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';

		}
		else {
			$httpdText .= "Type: item; Caption: \"".$key1."  ".$value1."\"; Action: multi; Actions: none
";
		}
	}
}

// Apache Compiled in modules
$ApacheCompiledModules = '';
$ApacheCompiledModules .= 'Type: separator; Caption: "Compiled in modules:"
';
$command = $c_apacheExe." -l";
$output = proc_open_output($command);
if(!empty($output)) {
	if(stripos($output,'Syntax error') !== false) {
		$ApacheCompiledModules .= 'Type: item; Caption: "*** WARNING - Syntax error ***"; Action: multi; Actions: none
';
	}
	else {
		if(preg_match_all('~^[ \t]+([a-zA-Z0-9_]+)\.c\r?$~mi',$output,$matches) > 0) {
			sort($matches[1]);
			foreach($matches[1] as $value) {
				$ApacheCompiledModules .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: none
';
			}
		}
	}
}
$tpl = str_replace(';WAMPAPACHEPARAMSSTART',$httpdText.$ApacheDefaultOnlyTxt.$ApacheCompiledModules,$tpl);
// END of Apache configuration menu
//*********************************

// **************************
// Creating alias Apache menu
$aliasDirContents = glob($aliasDir.'/*.conf');
array_walk($aliasDirContents,function(&$item){$item = basename($item);});

$myreplace = $myreplacemenu = $mydeletemenu = '';
foreach ($aliasDirContents as $one_alias)
{
  $mypattern = ';WAMPADDALIAS';
  $newalias_dir = str_replace('.conf','',$one_alias);
  $alias_contents = @file_get_contents ($aliasDir.$one_alias);
  preg_match('|^Alias /'.$newalias_dir.'/ "(.+)"|',$alias_contents,$match);
  $newalias_dest = (isset($match[1])  ? $match[1] : NULL);

	$newalias_dir_ori = $newalias_dir;
	$newalias_dir_del = str_replace(' ','-whitespace-',$newalias_dir);
	$newalias_dir = str_replace(' ','_',$newalias_dir);
  $myreplace .= 'Type: submenu; Caption: "http://localhost/'.$newalias_dir.'/"; SubMenu: alias_'.$newalias_dir.'; Glyph: 3
';

  $myreplacemenu .= <<< EOF

[alias_${newalias_dir}]
Type: separator; Caption: "${newalias_dir}"
Type: item; Caption: "${w_editAlias}"; Glyph: 33; Action: multi; Actions: edit_${newalias_dir}
Type: item; Caption: "${w_deleteAlias}"; Glyph: 26; Action: multi; Actions: delete_${newalias_dir}

EOF;

  $mydeletemenu .= <<< EOF

[delete_${newalias_dir}]
Action: run; FileName: "${c_phpExe}";Parameters: "deleteAlias.php ${newalias_dir_del}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig
[edit_${newalias_dir}]
Action: run; FileName: "${c_editor}"; parameters:"${c_installDir}/alias/${newalias_dir_ori}.conf"

EOF;

}

$tpl = str_replace($mypattern,$myreplace.$myreplacemenu.$mydeletemenu,$tpl);
// END of alias Apache menu
// ************************

//**************************************
// Creating DBMS (MySQL - MariaDB) menus
$myDBMSPattern = ';WAMPDBMSMENU';
$myDBMSreplace = $myDBMSPattern."
";
$TestPort3306 = '';
if($noDBMS) {
	$nbDBMS = 0;
	$myDBMSreplace .= <<< EOF
Type: separator; Caption: "${w_noDBMS}"
Type: separator
EOF;
}
else { // At least one DBMS MySQL and/or MariaDB
	$myDBMSreplacearray = array();
	$DBMSdefault = 'none';
	$NoDefaultDBMS = true;
	$DBMSList = array();
	// Arrange MariaDB and MySQL tools if both
	$myPattern = ';WAMPMYSQLMARIADBTOOLSORDER';
	$myreplace = $myPattern."
;WAMPMYSQLSUPPORTTOOLS
;WAMPMARIADBSUPPORTTOOLS
";
	// MySQL versions and settings
	if($wampConf['SupportMySQL'] == 'on') {
		$glyph = '28';
		$DBMSList[] = 'refreshMySQL.php';
		if($c_UsedMysqlPort == $c_DefaultMysqlPort) {
			$glyph = '36';
			$DBMSdefault = 'mysql';
			$NoDefaultDBMS = false;
		}
		$myDBMSreplacearray[] = <<< EOF
Type: submenu; Caption: "MySQL		${c_mysqlVersion}"; SubMenu: mysqlMenu; Glyph: ${glyph}

EOF;
	}
	//MariaDB versions and settings
	if($wampConf['SupportMariaDB'] == 'on') {
		$glyph = '28';
		$DBMSList[] = 'refreshMariadb.php';
		if($c_UsedMariaPort == $c_DefaultMysqlPort) {
			$glyph = '36';
			$DBMSdefault = 'mariadb';
			$NoDefaultDBMS = false;
		}
	$myDBMSreplacearray[] = <<< EOF
Type: submenu; Caption: "MariaDB		${c_mariadbVersion}"; SubMenu: mariadbMenu; Glyph: ${glyph}

EOF;
	}
	$nbDBMS = count($DBMSList);
	if($nbDBMS > 1 && $DBMSdefault == 'mariadb') {
		$myreplace = $myPattern."
;WAMPMARIADBSUPPORTTOOLS
;WAMPMYSQLSUPPORTTOOLS
";
		krsort($myDBMSreplacearray);
		krsort($DBMSList);
	}
	$tpl = str_replace($myPattern,$myreplace,$tpl);
	if($nbDBMS > 0) {
		$DBMSHeader = <<< EOF
Type: separator; Caption: "${w_defaultDBMS} ${DBMSdefault}"

EOF;
		$DBMSFooter = <<< EOF
Type: item; Caption: "${w_help} -> MariaDB - MySQL"; Action: run; Filename: "${c_editor}"; Parameters: "%AeTrayMenuPath%\mariadb_mysql.txt"; ShowCmd: Normal; Glyph: 31

EOF;
		array_unshift($myDBMSreplacearray,$DBMSHeader);
		array_push($myDBMSreplacearray,$DBMSFooter);

		if($NoDefaultDBMS) {
			$WarningsAtEnd = true;
			$message = color('red',"\r\nNeither MariaDB nor MySQL is configured as the default SGDB.\r\nSee Right-Click -> Help -> MariaDB - MySQL\r\n");
			if($doReport)	$wampReport['gen2'] .= $message;
			$WarningText .= 'Type: item; Caption: "No Default DBMS"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
		// Check that MySQL and MariaDB ports are not the same
		if($nbDBMS > 1) {
			if($c_mysqlPortUsed == $c_mariadbPortUsed) {
				$WarningsAtEnd = true;
				$message = color('red',"\r\nMySQL and MariaDB use the same port: ".$c_mysqlPortUsed."\r\nIt is not possible\r\nOne of the ports must be changed in wampmanager.conf and in the related my.ini file.");
				if($doReport)	$wampReport['gen2'] .= $message;
				$WarningText .= 'Type: item; Caption: "Same port for MySQL and MariaDB"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
			}
		}
	}
	foreach($DBMSList as $value) include $value;
	foreach($myDBMSreplacearray as $value) $myDBMSreplace .= $value;
}
$tpl = str_replace($myDBMSPattern,$myDBMSreplace,$tpl);

// END of DBMS (MySQL - MariaDB) menus
//************************************

//*************************
// Creating local test menu
if($wampConf['LocalTest'] == 'on') {
	$LOCALPattern = ';WAMPLOCALTEST';
	$LOCALreplace = $LOCALPattern."
";
	$LOCALreplace .= <<< EOF
Type: separator; Caption: "For local test only"
Type: item; Caption: "For local test only"; Action: run; FileName: "${c_phpExe}"; Parameters: "test.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated; Glyph: 9

EOF;
	$tpl = str_replace($LOCALPattern,$LOCALreplace,$tpl);
}
// END of local test menu
//***********************

//****************************************************************
// Creating tools menu to invert default DBMS if MySQL and MariaDB
if($nbDBMS > 1) {
	if($DBMSdefault == 'mariadb') {
		$myPattern = ';WAMPSWITCHMARIAMYSQLSTART';
		$myreplace = <<< EOF
;WAMPSWITCHMARIAMYSQLSTART
[SwitchMariaToMysql]
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpExe}"; Parameters: "switchMariaPort.php 3307 nocheck";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}"; Parameters: "switchMysqlPort.php 3306 nocheck";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: service; Service: ${c_mariadbService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: service; Service: ${c_mysqlService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}"; Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig
EOF;
		$tpl = str_replace($myPattern,$myreplace,$tpl);
	}
	else {
		$myPattern = ';WAMPSWITCHMYSQLMARIASTART';
		$myreplace = <<< EOF
;WAMPSWITCHMYSQLMARIASTART
[SwitchMysqlToMaria]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpExe}"; Parameters: "switchMysqlPort.php 3308 nocheck";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}"; Parameters: "switchMariaPort.php 3306 nocheck";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: service; Service: ${c_mariadbService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: service; Service: ${c_mysqlService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}"; Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig
EOF;
		$tpl = str_replace($myPattern,$myreplace,$tpl);
	}
}
// END of tools menu to invert default DBMS if MySQL and MariaDB
//**************************************************************

//***********************
// Creating Alias submenu
if($wampConf['AliasSubmenu'] == "on")
{
	//Add item for submenu
	$myPattern = ';WAMPALIASSUBMENU';
	$myreplace = $myPattern."
";
	$myreplacesubmenu = 'Type: submenu; Caption: "'.$w_aliasSubMenu.'"; Submenu: myAliasMenu; Glyph: 3
';
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);

	//Add submenu
	$myPattern = ';WAMPMENULEFTEND';
	$myreplace = $myPattern."
";
	$myreplacesubmenu = '

[myAliasMenu]
;WAMPALIASMENUSTART
;WAMPALIASMENUEND

';
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);

	//Construct submenu
	$myPattern = ';WAMPALIASMENUSTART';
	$myreplace = $myPattern."
Type: separator; Caption: \"".$w_aliasSubMenu."\"
";
	// Place alias into submenu
	$AliasContents = array();
	if(is_dir($aliasDir)) {
    $handle=opendir($aliasDir);
    while (false !== ($file = readdir($handle))) {
	    if(is_file($aliasDir.$file) && strstr($file, '.conf')) {
		    $AliasContents[] = str_replace('.conf','',$file);
	    }
    }
    closedir($handle);
	}
	$myreplacesubmenuAlias = '';
	if(count($AliasContents) > 0)	{
		foreach($AliasContents as $AliasValue) {
			$glyph = '5';
			if(strpos($AliasValue,'phpmyadmin') !== false || strpos($AliasValue,'adminer') !== false)
				$glyph = '28';
			$myreplacesubmenuAlias .= 'Type: item; Caption: "'.$AliasValue.'"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "';
			$myreplacesubmenuAlias .= $c_edge.'http://localhost'.$UrlPort.'/'.$AliasValue.'/"; Glyph: '.$glyph.'
';
		}
		$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenuAlias,$tpl);
	}
}
// END of Alias submenu
// ********************

//*******************************
// Creating Virtual Hosts submenu
if($wampConf['VirtualHostSubMenu'] == "on")
{
	//Add item for submenu
	$myPattern = ';WAMPVHOSTSUBMENU';
	$myreplace = $myPattern."
";
	$myreplacesubmenu = 'Type: submenu; Caption: "'.$w_virtualHostsSubMenu.'"; Submenu: myVhostsMenu; Glyph: 3
';
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);
	//Add submenu
	$myPattern = ';WAMPMENULEFTEND';
	$myreplace = $myPattern."
";
	$myreplacesubmenu = '

[myVhostsMenu]
;WAMPVHOSTMENUSTART
;WAMPVHOSTMENUEND

';
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenu,$tpl);
	$myPattern = ';WAMPVHOSTMENUSTART';
	$myreplace = $myPattern."
Type: separator; Caption: \"".$w_virtualHostsSubMenu."\"
";
	$myreplacesubmenuVhosts = '';

	$virtualHost = check_virtualhost();

	//is Include conf/extra/httpd-vhosts.conf uncommented?
	if($virtualHost['include_vhosts'] === false) {
		$myreplacesubmenuVhosts .= 'Type: item; Caption: "Virtual Host ERROR"; Action: multi; Actions: server_not_included; Glyph: 21
';
    $myreplacesubmenuVhosts .= '[server_not_included]
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 14";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
	else
	{
		if($virtualHost['vhosts_exist'] === false) {
			$myreplacesubmenuVhosts .= 'Type: item; Caption: "Virtual Host ERROR"; Action: multi; Actions: server_not_exists; Glyph: 21
';
    	$myreplacesubmenuVhosts .= '[server_not_exists]
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 15 '.base64_encode($virtualHost['vhosts_file']).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
		}
		else
		{
			$server_name = array();

			if($virtualHost['nb_Server'] > 0)
			{
				$nb_Server = $virtualHost['nb_Server'];
				$nb_Virtual = $virtualHost['nb_Virtual'];
				$nb_Document = $virtualHost['nb_Document'];
				$nb_Directory = $virtualHost['nb_Directory'];
				$nb_End_Directory = $virtualHost['nb_End_Directory'];

				$port_number = true;
				//Check number of <Directory equals to number of </Directory
				if($nb_End_Directory != $nb_Directory) {
					$value = "ServerName_Directory";
					$server_name[$value] = -2;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}
				//Check number of DocumentRoot equals to number of ServerName
				if($nb_Document != $nb_Server) {
					$value = "ServerName_Document";
					$server_name[$value] = -7;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}
				//Check validity of DocumentRoot
				$documentPathError = '';
				if($virtualHost['document'] === false) {
					foreach($virtualHost['documentPath'] as $value) {
						if($virtualHost['documentPathValid'][$value] === false) {
							$documentPathError = $value;
							break;
						}
					}
					$value = "DocumentRoot_error";
					$server_name[$value] = -8;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}
				//Check validity of Directory Path
				$directoryPathError = '';
				if($virtualHost['directory'] === false) {
					foreach($virtualHost['directoryPath'] as $value) {
						if($virtualHost['directoryPathValid'][$value] === false) {
							$directoryPathError = $value;
							break;
						}
					}
					$value = "Directory_Path_error";
					$server_name[$value] = -9;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}

				//Check number of <VirtualHost equals or > to number of ServerName
				if($nb_Server != $nb_Virtual && $wampConf['NotCheckDuplicate'] == 'off') {
					$value = "ServerName_Virtual";
					$server_name[$value] = -3;
					$port_number = false;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}

				//Check number of port definition of <VirtualHost *:xx> equals to number of ServerName
				if($virtualHost['nb_Virtual_Port'] != $nb_Virtual) {
					$value = "VirtualHost_Port";
					$server_name[$value] = -4;
					$port_number = false;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}
				//Check validity of port number
				if($port_number && $virtualHost['port_number'] === false) {
					$value = "VirtualHost_PortValue";
					$server_name[$value] = -5;
					$port_number = false;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}
				//Check if duplicate ServerName
				if($virtualHost['nb_duplicate'] > 0) {
					$DuplicateNames = '';
					$value = "Duplicate_ServerName";
					$server_name[$value] = -10;
					foreach($virtualHost['duplicate'] as $NameValue)
						$DuplicateNames .= "\r\n\t".$NameValue;
					$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
				}

				foreach($virtualHost['ServerName'] as $key => $value) {
					if($virtualHost['ServerNameValid'][$value] === false) {
						//Quote in ServerName ?
						$value_noquote = ($virtualHost['ServerNameQuoted'][$value]) ? str_replace('"','',$value) : $value;
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value_noquote.'"; Action: multi; Actions: server_'.$value_noquote.'; Glyph: 20
';
						$server_name[$value_noquote] = -1;
					}
					elseif($virtualHost['DocRootNotwww'][$value] === false) {
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
						$server_name[$value] = -14;
					}
					elseif($virtualHost['ServerNameDev'][$value] === true) {
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
						$server_name[$value] = -15;
					}
					elseif($virtualHost['ServerNameIntoHosts'][$value] === false) {
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
						$server_name[$value] = -16;
					}
					elseif($virtualHost['ServerNameValid'][$value] === true) {
						$UrlPortVH = ($virtualHost['ServerNamePort'][$value] != '80') ? ':'.$virtualHost['ServerNamePort'][$value] : '';
						if(!$virtualHost['port_listen'] && $virtualHost['ServerNamePortListen'][$value] !== true) {
							$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
							$server_name[$value] = -12;
						}
						elseif($virtualHost['port_listen'] && $virtualHost['ServerNamePortApacheVar'][$value] !== true) {
							$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 23
';
							$server_name[$value] = -13;
						}
						elseif($virtualHost['ServerNameIp'][$value] !== false) {
							$vh_ip = $virtualHost['ServerNameIp'][$value];
							if($virtualHost['ServerNameIpValid'][$value] !== false) {
								$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$vh_ip.' ('.$value.')"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://'.$vh_ip.$UrlPortVH.'/"; Glyph: 5
';
								$server_name[$value] = 1;
							}
							else {
								$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$vh_ip.' ('.$value.')"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
								$server_name[$value] = -11;
							}
						}
						else {
							$glyph = ($value == 'localhost') ? '27' : '5';
							$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
							$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://'.$value_url.$UrlPortVH.'/"; Glyph: '.$glyph.'
';
							if($virtualHost['ServerNameIDNA'][$value] === true && !empty($Windows_Charset)) {
								$value_trans = @iconv("UTF-8",$Windows_Charset."//TRANSLIT",$virtualHost['ServerNameUTF8'][$value]);
								if($value_trans !== false ) {
									$myreplacesubmenuVhosts .= 'Type: item; Caption: "IDNA-> '.$value_trans.'"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://'.$value_url.$UrlPortVH.'/"
';
								}
							}
							$server_name[$value] = 1;
						}
					}
					else {
						$myreplacesubmenuVhosts .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: server_'.$value.'; Glyph: 20
';
						$server_name[$value] = -6;
					}
				} //End foreach
				$myreplacesubmenuVhosts .= 'Type: separator
Type: item; Caption: "'.$w_add_VirtualHost.'"; Action: run; FileName: "'.$c_navigator.'"; Parameters: "'.$c_edge.'http://localhost'.$UrlPort.'/add_vhost.php"; Glyph: 33
';
				foreach($server_name as $name=>$value) {
					if($server_name[$name] != 1) {
						if($server_name[$name] == -1) {
    					$myreplacesubmenuVhosts .= '[server_'.$name.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 9 '.base64_encode($name).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
						}
						else {
							if($server_name[$name] == -2)
								$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe number of\r\n\r\n\t\t<Directory ...>\r\n\t\tis not equal to the number of\r\n\r\n\t\t</Directory>\r\n\r\nThey should be identical.";
							elseif($server_name[$name] == -3)
								$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe number of\r\n\r\n\t\t<VirtualHost ...>\r\n\tis not equal to the number of\r\n\r\n\t\tServerName\r\n\r\nThey should be identical.\r\n\r\n\tCorrect syntax is: <VirtualHost *:80>\r\n";
							elseif($server_name[$name] == -4)
								$message = "In the httpd-vhosts.conf file:\r\n\r\n\tPort number into <VirtualHost *:port>\r\n\tis not defined for all\r\n\r\n\t\t<VirtualHost...>\r\n\r\n\tCorrect syntax is: <VirtualHost *:xx>\r\n\r\n\t\twith xx = port number [80 by default]\r\n";
							elseif($server_name[$name] == -5)
								$message = "In the httpd-vhosts.conf file:\r\n\r\n\tPort number into <VirtualHost *:port>\r\n\thas not correct value\r\n\r\nValue are:".print_r($virtualHost['virtual_port'],true)."\r\n";
							elseif($server_name[$name] == -6)
								$message = "The httpd-vhosts.conf file has not been cleaned.\r\nThere remain VirtualHost examples like: dummy-host.example.com\r\n";
							elseif($server_name[$name] == -7)
								$message = "In the httpd-vhosts.conf file:\r\n\r\n\tThe number of\r\n\r\n\t\tDocumentRoot\r\n\tis not equal to the number of\r\n\r\n\t\tServerName\r\n\r\nThey should be identical.\r\n";
							elseif($server_name[$name] == -8)
								$message = "In the httpd-vhosts.conf file:\r\n\r\nThe DocumentRoot path\r\n\r\n\t".$documentPathError."\r\n\r\ndoes not exits\r\n";
							elseif($server_name[$name] == -9)
								$message = "In the httpd-vhosts.conf file:\r\n\r\nThe Directory path\r\n\r\n\t".$directoryPathError."\r\n\r\ndoes not exits\r\n";
							elseif($server_name[$name] == -10)
								$message = "In the httpd-vhosts.conf file:\r\n\r\nThere is duplicate ServerName\r\n".$DuplicateNames."\r\n";
							elseif($server_name[$name] == -11)
								$message = "In the httpd-vhosts.conf file:\r\n\r\nThe IP used for the VirtualHost is not valid local IP\r\n";
							elseif($server_name[$name] == -12)
								$message = "In the httpd-vhost.conf file:\r\n\r\nThe Port used (".$virtualHost['ServerNamePort'][$name].") for the VirtualHost ".$name." is not a Listen port\r\n";
							elseif($server_name[$name] == -13)
								$message = "In the httpd-vhost.conf file:\r\n\r\nThe Port used (".$virtualHost['ServerNamePort'][$name].") for the VirtualHost ".$name." is not from a Define Apache variable\r\n";
							elseif($server_name[$name] == -14)
								$message = "In the httpd-vhosts.conf file:\r\n\r\nThe DocumentRoot path\r\n\r\n\t'".$wwwDir."'\r\n\r\nused with '".$name."' VirtualHost\r\n\r\nis reserved for 'localhost' and should not be used for another VirtualHost\r\n";
							elseif($server_name[$name] == -15)
								$message = "In the httpd-vhosts.conf file:\r\n\r\nTLD '.dev' used with '".$name."' ServerName\r\n\r\nis monopolized by web browsers and should not be used locally.\r\nYou can use'.test' or'.prog' instead.\r\n";
							elseif($server_name[$name] == -16)
								$message = "In the httpd-vhosts.conf file:\r\n\r\n'".$name."' ServerName\r\n\r\nis not defined into '".$c_hostsFile."' file.\r\n";
    					$myreplacesubmenuVhosts .= '[server_'.$name.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
						}
					}
				}
			}
		}
	}
	$tpl = str_replace($myPattern,$myreplace.$myreplacesubmenuVhosts,$tpl);
}
// END of Virtual Hosts submenu
// ****************************

//**************************************
// Creating Wampmanager settings submenu
$params_for_wampconf = $wampConfParams = array();
foreach($wamp_Param as $value) {
	$endsub = false;
  if(strpos($value, '###') !== false) { // Last item in SubMenu
  	$value = substr($value,3);
  	$endsub = true;
  }
	$wampConfParams[$value] = $value;
	$wampConfParams['endsub'][$value] = $endsub;
  if(strpos($value, '##') !== false) { // Separator + submenu
  	$value = substr($value,2);
  	$params_for_wampconf[$value] = -6;
		$wampConfParams[$value] = $value;
		$wampConfParams['endsub'][$value] = $endsub;
  }
  elseif(strpos($value, '#') !== false) { // Separator
  	$value = substr($value,1);
  	$params_for_wampconf[$value] = -2;
		$wampConfParams[$value] = $value;
		$wampConfParams['endsub'][$value] = $endsub;
  }
  elseif(empty($wampConf[$value])) {//Parameter does not exist in wampmanager.conf
    $params_for_wampconf[$value] = -1;
  }
	elseif(array_key_exists($value, $wampParamsNotOnOff)) {
		if($wampConf[$wampParamsNotOnOff[$value]['dependance']] !== 'on') {
			$params_for_wampconf[$value] = -10;
		}
		else {
  		if($wampParamsNotOnOff[$value]['change'] !== true) {
  	 		$params_for_wampconf[$value] = -5;
  		 	$wampErrorMsg = "\nIf you want to change this value, you can do it directly in the file:\n".$c_installDir."/wampmanager.conf file\n";
  		}
  		else {
	  	 	$params_for_wampconf[$value] = -3;
  		}
  	}
  }
	else {
    if($wampConf[$value] == 'on')
      $params_for_wampconf[$value] = '1';
    elseif($wampConf[$value] == 'off')
      $params_for_wampconf[$value] = '0';
    else
      $params_for_wampconf[$value] = '-5';
  }
}
$wampConfActions = $wampConfTextInfo = $wampConfSub = '';
$action_sup = array();
$information_only = false;
$wampConfInto = 'wampConfText';
$$wampConfInto = ";WAMPSETTINGSSTART
Type: Separator; Caption: \"".$w_wampSettings."\"
";
foreach ($params_for_wampconf as $paramname => $paramstatus) {
  if($paramstatus == -5) {
		if(!$information_only) {
			$wampConfTextInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
    	$wampConfTextInfo .= 'Type: item; Caption: "'.$paramname.' = '.$wampConf[$paramname].'"; Glyph: 22; Action: multi; Actions: '.$paramname.'
';
	}
  elseif($paramstatus == -2) { //Separator
    $$wampConfInto .= 'Type: Separator; Caption: "'.$w_settings[$paramname].'"
';
	}
	elseif($paramstatus == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
		$action_sup[] = $paramname;
			$$wampConfInto .= 'Type: submenu; Caption: "'.$w_settings[$paramname].' = '.$wampConf[$paramname].'"; Submenu: '.$paramname.'; Glyph: 9
';
	}
	elseif($paramstatus == -4) { // I blue to indicate different from 0 or 1 or On or Off
		if(!$information_only) {
			$wampConfTextInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
    $wampConfTextInfo .= 'Type: item; Caption: "'.$w_settings[$paramname].' = '.$wampConf[$paramname].'"; Action: multi; Actions: '.$paramname.'
';
	}
  elseif($paramstatus == -6) { //Separator + submenu
    $$wampConfInto .= 'Type: Separator; Caption: "'.$w_settings[$paramname].'"
Type: submenu; Caption: "'.$w_settings[$paramname].'"; Submenu: '.$paramname.'; Glyph: 9
';
		$wampConfInto = 'wampConfSub';
		$$wampConfInto .= '['.$paramname.']
';
	}
	elseif($paramstatus == -10) {
		continue; //do nothing - No menu item
	}
	elseif(($paramstatus == 1 || $paramstatus == 0)) {
		$myGlyph = "";
		$SwitchAction = 'on';
		if($paramstatus == 1) {
			$myGlyph = "Glyph: 13; ";
			$SwitchAction = 'off';
		}
    $$wampConfInto .= 'Type: item; Caption: "'.$w_settings[$paramname].'"; '.$myGlyph.'Action: multi; Actions: '.$wampConfParams[$paramname].'
';
		$php_exe_type = (in_array($paramname,$wamp_ParamPhpExe)) ? $c_phpExe : $c_phpCli ;
  	$wampConfActions .= <<< EOF
[${wampConfParams[$paramname]}]
Action: run; FileName: "${php_exe_type}";Parameters: "switchWampParam.php ${wampConfParams[$paramname]} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
	}
	if($wampConfParams['endsub'][$paramname]) $wampConfInto = 'wampConfText';
}
//Check for supplemtary actions
$MenuSup = $SubMenuSup = array();
if(count($action_sup) > 0) {
	$i = 0;
	foreach($action_sup as $action) {
		$MenuSup[$i] = $SubMenuSup[$i] = '';
		$MenuSup[$i] .= '['.$action.']
Type: separator; Caption: "'.$wampParamsNotOnOff[$action]['title'].'"
';
		$c_values = $wampParamsNotOnOff[$action]['values'];
		if($wampParamsNotOnOff[$action]['quoted'])
			$quoted = 'quotes';
		else
			$quoted = 'noquotes';
		foreach($c_values as $value) {
			$MenuSup[$i] .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: '.$action.$value.'
';
			if(strtolower($value) == 'choose') {
				$param_value = '%'.$wampParamsNotOnOff[$action]['title'].'%';
				$param_third = ' '.$wampParamsNotOnOff[$action]['title'];
				if($wampParamsNotOnOff[$action]['title'] == 'Integer')
					$param_third .= ' '.$wampParamsNotOnOff[$action]['min'].'^'.$wampParamsNotOnOff[$action]['max'].'^'.$wampParamsNotOnOff[$action]['default'];
				$c_phpRun = $c_phpExe;
			}
			else {
				$param_value = $value;
				$param_third = '';
				$c_phpRun = $c_phpCli;
			}
			$SubMenuSup[$i] .= <<< EOF
[${action}${value}]
Action: run; FileName: "${c_phpRun}";Parameters: "changeWampParam.php ${quoted} ${action} ${param_value}${param_third}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
		}
	$i++;
	}
}

$wampConfText .= $wampConfTextInfo;
if(count($MenuSup) > 0) {
	for($i = 0 ; $i < count($MenuSup); $i++)
		$wampConfText .= $MenuSup[$i].$SubMenuSup[$i];
}

$tpl = str_replace(';WAMPSETTINGSSTART',$wampConfText.$wampConfSub.$wampConfActions,$tpl);
unset($wampConfText,$wampConfSub,$wampConfActions);
// END of Wampmanager settings submenu
// **********************************

//******************************************
// Creating tool change php CLI version menu
//All versions with USED or CLI added to version number
$Versions = ListAllVersions();
$PHP_versions = $Versions['php'];
//Delete item with CLI added to PHP version number
$PHP_versions = array_filter($PHP_versions,function($value){return (strpos($value,'CLI') === false);});
//Replace USED by '' for all items
array_walk($PHP_versions,function(&$value, $key){$value = str_replace('USED','',$value);});
$versionsPHP = array();
foreach($PHP_versions as $value) {
  if(version_compare($value,$phpCliMinVersion,'>='))
		$versionsPHP[] = $value;
}

if(count($versionsPHP) >= 1) {
	$changeVerCLIMenu = <<< EOF
;WAMPPHPCLIMENUSTART
Type: separator; Caption: "PHP CLI = ${c_phpCliVersion} - WEB = ${c_phpVersion}"
Type: submenu; Caption: "${w_changeCLI}"; Submenu: ChangePHPCLI; Glyph: 24

EOF;
	$changeVerCLISub = '';
	$changeVerCLIMenuSub = ";WAMPPHPCLIVERSIONSSTART
[ChangePHPCLI]
";
	foreach($versionsPHP as $PHP_Version) {
		$changeVerCLIMenuSub .= 'Type: item; Caption: "'.$PHP_Version.'"; Action: multi; Actions: change_CLI_'.$PHP_Version.'
';

		$changeVerCLISub .= <<< EOF
[change_CLI_${PHP_Version}]
Action: run; FileName: "${c_phpCli}";Parameters: "changeCLIVersion.php ${PHP_Version} ";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
	}
	$tpl = str_replace(';WAMPPHPCLIMENUSTART',$changeVerCLIMenu,$tpl);
	$tpl = str_replace(';WAMPPHPCLIVERSIONSSTART',$changeVerCLIMenuSub.$changeVerCLISub,$tpl);
	unset($changeVerCLIMenuSub,$changeVerCLISub);
}
// END of tool change php CLI version menu
//****************************************

// **************************************
// Creating tool delete old versions menu
$delOldVer = ";WAMPDELETEOLDVERSIONSSTART
Type: separator; Caption: \"".$w_deleteVer."\"
";
$delOldVerMenu = $delOldVerSub = '';
//All versions but USED or CLI
$Versions = ListAllVersions();
$VersionsNotUsed = array_filter_recursive($Versions,function($value){return (strpos($value,'CLI') === false && strpos($value,'USED') === false);});
foreach(array_keys($VersionsNotUsed) as $appli) {
	if(count($VersionsNotUsed[$appli]) > 0) {
		$delOldVerMenu .= "Type: separator; Caption: \" ".strtoupper($appli)." \"
";
		foreach ($VersionsNotUsed[$appli] as $appliVersion) {
  			$delOldVerMenu .= 'Type: item; Caption: "'.$w_delete.' '.$appli.' '.$appliVersion.'"; Glyph: 32; Action: multi; Actions: del_'.$appli.$appliVersion.'
';
				$delOldVerSub .= <<< EOF
[del_${appli}${appliVersion}]
Action: run; FileName: "${c_phpCli}";Parameters: "deleteVersion.php ${appli} ${appliVersion}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
		}
	}
}
$tpl = str_replace(';WAMPDELETEOLDVERSIONSSTART',$delOldVer.$delOldVerMenu.$delOldVerSub,$tpl);
unset($delOldVer,$delOldVerMenu,$delOldVerSub);
// END of tool delete old versions menu
//*************************************

//*********************************************
// Creating tool delete Listen Port Apache menu
if($ListenPortsExists) {
	$ForbidenToDel = array('80', '8080',$c_DefaultPort, $c_UsedPort);
	$delListenPort = ";WAMPDELETELISTENPORTSTART
Type: separator; Caption: \"".$w_deleteListenPort."\"
";
	$delListenPortMenu = $delListenPortSub = '';
	foreach($c_listenPort as $ListenPort) {
		if(!in_array($ListenPort,$ForbidenToDel)) {
 			$delListenPortMenu .= 'Type: item; Caption: "'.$w_delete.' Listen port Apache '.$ListenPort.'"; Glyph: 32; Action: multi; Actions: del_apache_port'.$ListenPort.'
';
			$delListenPortSub .= <<< EOF
[del_apache_port${ListenPort}]
Action: run; FileName: "${c_phpExe}";Parameters: "ListenPortApache.php delete ${ListenPort}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpExe}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig

EOF;
		}
	}
$tpl = str_replace(';WAMPDELETELISTENPORTSTART',$delListenPort.$delListenPortMenu.$delListenPortSub,$tpl);
unset($delListenPort,$delListenPortMenu,$delListenPortSub);
}
// END of tool delete Listen Port Apache menu
//*******************************************

//********************************************
// Create definitions of BigMenu (Column menus)
// From $AesBigMenu in config.inc.php
$BigKeys = "[BigMenu]\r\n";
foreach($AesBigMenu as $key => $value) {
	$BigKeys .= 'BigKey'.$key.'=';
	$BigKeys .= ((strpos($value[0],'$') === 0) ? ${$temp = substr($value[0],1)} : $value[0]).',';
	$BigKeys .= ((strpos($value[1],'$') === 0) ? ${$temp = substr($value[1],1)} : $value[1]).',';
	$BigKeys .= $value[2]."\r\n";
}
$BigKeys .="\r\n";
$search = ';WAMPBIGMENUSTART
';
$tpl = str_replace($search,$search.$BigKeys,$tpl);
unset($BigKeys);
// END of BigMenu
//***************

//******************************************************
// Create wampserver configuration report file if needed
if($doReport) {
	foreach($wampReport as $value) $wampReportTxt .= $value;
	$wampReportTxt .= "\n--------- wampmanager.ini (Last 4 lines) --------\n";
	$wampReportTxt .= implode(PHP_EOL, array_slice(file($wampserverIniFile), -4));
	$wampReportTxt .= @file_get_contents($c_installDir."/wampConfReportTemp.txt");
	@unlink($c_installDir."/wampConfReportTemp.txt");
	$wampReportTxt .= "\n--------------------------------------------------\n";
	//Error files to add (last 20 lines)
	$wampErrorReportTxt = '';
	$error_files = array(
		'Apache error log' => 'apache_error.log',
		'Apache access log' => 'access.log',
		'PHP error log' => 'php_error.log',
	);
	if($wampConf['SupportMySQL'] == 'on')
		$error_files += array('MySQL error log' => 'mysql.log');
	if($wampConf['SupportMariaDB'] == 'on')
		$error_files += array('MariaDB error log' => 'mariadb.log');
	foreach($error_files as $key => $value) {
		if(file_exists($c_installDir."/".$logDir."/".$value)){
			$wampErrorReportTxt .= "\n-------- ".$key." (Last 30 lines) --------\n";
			$wampErrorReportTxt .= implode(PHP_EOL, array_slice(file($c_installDir."/".$logDir."/".$value), -30));
			$wampErrorReportTxt .= "\n--------------------------------------------------";
		}
	}
	$wampReportTxt .= $wampErrorReportTxt;
	write_file($c_installDir."/wampConfReport.txt",color('clean',clean_file_contents($wampReportTxt,array(1,0)),true));
}

//Check if wampserver report configuration file exists
if(file_exists($c_installDir."/wampConfReport.txt")) {
	//Get timestamp of the report file
	$fp = fopen($c_installDir."/wampConfReport.txt","rb");
	$timestamp = (int)fgets($fp);
	fclose($fp);
	//$timestamp -= (86400*10);
	if((time() - $timestamp)/86400 > 10) {
		//Report file more than ten days old.
		unlink($c_installDir."/wampConfReport.txt");
	}
	else {
		$confFileExists = <<< EOF
;WAMPREPORTCONFFILE
Type: item; Caption: "${w_wampReport}"; Glyph: 33; Action: run; FileName: "${c_editor}"; parameters: "${c_installDir}/wampConfReport.txt"
EOF;
	$tpl = str_replace(';WAMPREPORTCONFFILE',$confFileExists,$tpl);
	}
}

//************************
// Clean tmp dir if needed
if($wampConf['AutoCleanTmp'] == 'on') {
	$fileTmp = glob($c_installDir.'/tmp/*');
	if(count($fileTmp) > $wampConf['AutoCleanTmpMax']) {
		foreach($fileTmp as $file){
   		if(is_file($file)) {
   			if(unlink($file) === false) {
   				error_log("Unable to delete file: ".$file);
   			}
   		}
		}
	}
	unset($fileTmp);
}


//*****************************************************
// Add warnings at the end of Left-Click menu if needed
if($WarningsAtEnd) {
	$WarningTextAll = '
Type: separator; Caption: ">>>>>    WARNING    <<<<<"
';
	$tpl = str_replace(';WAMPMENULEFTEND',$WarningMenu.$WarningTextAll.$WarningText,$tpl);
}

//***************************************************************
//The creation of wampmanager.ini file is complete, save the file.
write_file($wampserverIniFile,$tpl);
unset($tpl);
// END of load Template file as require
//*************************************

//Write last_versions_here.txt file
//to check updates from checkUpdates.php script
$writeNewFile = true;
$NewFileContents = '<?php'."\n\n".'$wamp_versions_here = '.var_export($wamp_versions_here, true).';'."\n\n".'?>';
if(file_exists('last_versions_here.txt')) {
	$FileContents = file_get_contents('last_versions_here.txt');
	if($NewFileContents == $FileContents)
		$writeNewFile = false;
}
if($writeNewFile) {
	write_file('last_versions_here.txt',$NewFileContents);
}

//Check alias and paths in httpd-autoindex.conf
check_autoindex();

if(!file_exists($c_installDir.'/logs/php_error.log'))
	error_log("No error - Only to create the file");

?>