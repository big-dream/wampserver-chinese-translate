<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

// Versions of MySQL
// Already done in refresh.php
//$mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql');
if(count($mysqlVersionList) == 0) {
	error_log("No version of MySQL is installed.");
	$glyph = '19';
	$WarningsAtEnd = true;
	$WarningText .= 'Type: item; Caption: "No version of MySQL is installed"; Glyph: '.$glyph.'; Action: multi; Actions: none
';
}
else {
// MySQL submenu
$typebase = 'mysql';
$myPattern = ';WAMPMYSQLMENUSTART';
$DBMSFooter = '';
if($nbDBMS > 1 && $DBMSdefault !== 'mysql') {
	$DBMSFooter = <<< EOF
Type: separator; Caption: "${w_defaultDBMS} ${DefaultDBMS}"
Type: item; Caption: "${w_help} -> MariaDB - MySQL"; Action: run; Filename: "%Windows%\Notepad.exe"; Parameters: "%AeTrayMenuPath%\mariadb_mysql.txt"; ShowCmd: Normal; Glyph: 31
EOF;
	$DBMSHeader = <<< EOF
Type: separator; Caption: "MySQL ${c_mysqlVersion}"
EOF;
}
else {
	$DBMSHeader = <<< EOF
Type: separator; Caption: "MySQL ${c_mysqlVersion}  (${w_defaultDBMS})"
EOF;
}
$myreplace = <<< EOF
;WAMPMYSQLMENUSTART
${DBMSHeader}
Type: submenu; Caption: "${w_version}"; SubMenu: mysqlVersion; Glyph: 3
Type: servicesubmenu; Caption: "${w_service} '${c_mysqlService}'"; Service: ${c_mysqlService}; SubMenu: mysqlService
Type: submenu; Caption: "${w_mysqlSettings}"; SubMenu: mysql_params; Glyph: 25
Type: item; Caption: "${w_mysqlConsole}"; Action: run; FileName: "${c_mysqlConsole}"; Parameters: "-u %MysqlUser% -p"; Glyph: 0
Type: separator; Caption: "${w_helpFile}";
Type: item; Caption: "my.ini"; Glyph: 33; Action: run; FileName: "${c_editor}"; parameters: "${c_mysqlConfFile}"
Type: item; Caption: "${w_mysqlLog}	(${logFilesSize['mysql.log']})"; Glyph: 33; Action: run; FileName: "${c_logviewer}"; parameters: "${c_installDir}/${logDir}mysql.log"
Type: item; Caption: "${w_mysqlDoc}"; Action: run; FileName: "${c_navigator}"; Parameters: "${c_edge}http://dev.mysql.com/doc/index.html"; Glyph: 35
${MysqlTestPortUsed}Type: separator; Caption: "${w_portUsedMysql}${c_UsedMysqlPort}"
${TestPort3306}${MysqlTestPortUsed}Type: item; Caption: "${w_testPortMysql}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php 3306 ${c_mysqlService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
${MysqlTestPortUsed}Type: item; Caption: "${w_AlternateMysqlPort}"; Action: multi; Actions: UseAlternateMysqlPort; Glyph: 24
${MysqlTestPortUsed}Type: item; Caption: "${w_testPortMysqlUsed}${c_UsedMysqlPort}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php ${c_UsedMysqlPort} ${c_mysqlService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
${DBMSFooter}
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// MySQL service submenu
$myPattern = ';WAMPMYSQLSERVICESTART';
$myreplace = <<< EOF
;WAMPMYSQLSERVICESTART
[MySqlService]
Type: separator; Caption: "${w_mysql}"
Type: item; Caption: "${w_startResume}"; Action: service; Service: ${c_mysqlService}; ServiceAction: startresume; Glyph: 9; Flags: ignoreerrors
;Type: item; Caption: "${w_pauseService}"; Action: service; Service: mysql; ServiceAction: pause; Glyph: 10
Type: item; Caption: "${w_stopService}"; Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Glyph: 11
Type: item; Caption: "${w_restartService}"; Action: service; Service: ${c_mysqlService}; ServiceAction: restart; Flags: ignoreerrors waituntilterminated; Glyph: 12
Type: separator
Type: item; Caption: "${w_installService}"; Action: multi; Actions: MySQLServiceInstall; Glyph: 8
Type: item; Caption: "${w_removeService}"; Action: multi; Actions: MySQLServiceRemove; Glyph: 26
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

$myPattern = ';WAMPMYSQLSERVICEINSTALLSTART';
$myreplace = <<< EOF
;WAMPMYSQLSERVICEINSTALLSTART
[MySQLServiceInstall]
{$mysqlMysqlService}Action: run; FileName: "${c_mysqlExe}"; Parameters: "${c_mysqlServiceInstallParams}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
{$mysqlCmdScService}Action: run; FileName: "CMD"; Parameters: "/D /C sc create ${c_mysqlService} binpath=""${c_mysqlExeAnti} --defaults-file=${c_mysqlConfFileAnti} ${c_mysqlService}"""; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: resetservices
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

$myPattern = ';WAMPMYSQLSERVICEREMOVESTART';
$myreplace = <<< EOF
;WAMPMYSQLSERVICEREMOVESTART
[MySQLServiceRemove]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
{$mysqlMysqlService}Action: run; FileName: "${c_mysqlExe}"; Parameters: "${c_mysqlServiceRemoveParams}"; ShowCmd: hidden; Flags: waituntilterminated
{$mysqlCmdScService}Action: run; FileName: "CMD"; Parameters: "/D /C sc delete ${c_mysqlService}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: resetservices
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// Mysql use alternate port submenu
$myPattern = ';WAMPALTERNATEMYSQLPORTSTART';
$myreplace = <<< EOF
;WAMPALTERNATEMYSQLPORTSTART
[UseAlternateMysqlPort]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpExe}"; Parameters: "switchMysqlPort.php %MysqlPort%";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: service; Service: ${c_mysqlService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpCli}"; Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// MySQL console prompt submenu
if($MysqlMariaPromptBool) {
	$myPattern = ';WAMPMYSQLUSECONSOLEPROMPTSTART';
	$myreplace = <<< EOF
;WAMPMYSQLUSECONSOLEPROMPTSTART
[mysqlUseConsolePrompt]
Action: run; FileName: "${c_phpExe}";Parameters: "switchWampParam.php mysqlUseConsolePrompt ${mysqlConsolePromptChange}"; WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated
${Apache_Restart}
Action: run; FileName: "${c_phpExe}";Parameters: "refresh.php"; WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated
Action: readconfig
EOF;
	$tpl = str_replace($myPattern,$myreplace,$tpl);
}

// MySQL Tools menu
$myPattern = ';WAMPMYSQLSUPPORTTOOLS';
$myreplace = <<< EOF
;WAMPMYSQLSUPPORTTOOLS
Type: separator; Caption: "${w_portUsedMysql}${c_UsedMysqlPort}"
${TestPort3306}Type: item; Caption: "${w_testPortMysql}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php 3306 ${c_mysqlService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
${MysqlTestPortUsed}Type: item; Caption: "${w_testPortMysqlUsed}${c_UsedMysqlPort}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php ${c_UsedMysqlPort} ${c_mysqlService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
Type: item; Caption: "${w_AlternateMysqlPort}"; Action: multi; Actions: UseAlternateMysqlPort; Glyph: 24
${MysqlMariaPrompt}Type: item; Caption: "${w_settings['mysqlUseConsolePrompt']}: ${mysqlConsolePromptUsed}"; Glyph: 24; Action: multi; Actions: mysqlUseConsolePrompt
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// *****************
// versions of MySQL
// Already done in refresh.php
// $mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql');
// Sort in versions number order
natcasesort($mysqlVersionList);

$myPattern = ';WAMPMYSQLVERSIONSTART';
$myreplace = $myPattern."
";
$myreplacemenu = '';
foreach ($mysqlVersionList as $oneMysqlVersion) {
	$count = 0;
  //File wamp/bin/mysql/mysqlx.y.z/wampserver.conf
  $myConfFile = $c_mysqlVersionDir.'/mysql'.$oneMysqlVersion.'/'.$wampBinConfFiles;
  unset($mysqlConf);
  include $myConfFile;

	//Check name of the group [wamp...] under '# The MySQL server' in my.ini file
	//    must be the name of the mysql service.
	$myIniFile = $c_mysqlVersionDir.'/mysql'.$oneMysqlVersion.'/'.$mysqlConf['mysqlConfFile'];
	$myIniContents = file_get_contents($myIniFile);

	if(strpos($myIniContents, "[".$c_mysqlService."]") === false) {
		$myIniContents = preg_replace("/^\[wamp.*\].*\n/m", "[".$c_mysqlService."]\r\n", $myIniContents, 1, $count);
		if(!is_null($myIniContents) && $count == 1) {
			write_file($myIniFile,$myIniContents);
			$mysqlServer[$oneMysqlVersion] = 0;
		}
		else { //The MySQL server has not the same name as mysql service
			$mysqlServer[$oneMysqlVersion] = -1;
		}
	}
	else
		$mysqlServer[$oneMysqlVersion] = 0;
	unset($myIniContents);

	if($oneMysqlVersion === $wampConf['mysqlVersion'] && $mysqlServer[$oneMysqlVersion] == 0)
  	$mysqlServer[$oneMysqlVersion] = 1;

	if($mysqlServer[$oneMysqlVersion] == 1) {
    $myreplace .= 'Type: item; Caption: "'.$oneMysqlVersion.'"; Action: multi; Actions:switchMysql'.$oneMysqlVersion.'; Glyph: 13
';
	}
  elseif($mysqlServer[$oneMysqlVersion] == 0) {
    $myreplace .= 'Type: item; Caption: "'.$oneMysqlVersion.'"; Action: multi; Actions:switchMysql'.$oneMysqlVersion.'
';
  	$myreplacemenu .= <<< EOF
[switchMysql${oneMysqlVersion}]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "CMD"; Parameters: "/D /C net stop ${c_mysqlService}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
{$mysqlMysqlService}Action: run; FileName: "${c_mysqlExe}"; Parameters: "${c_mysqlServiceRemoveParams}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
{$mysqlCmdScService}Action: run; FileName: "CMD"; Parameters: "/D /C sc delete ${c_mysqlService}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: closeservices;
Action: run; FileName: "${c_phpCli}";Parameters: "switchMysqlVersion.php ${oneMysqlVersion}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "switchMysqlPort.php ${c_UsedMysqlPort}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated

EOF;
		if(isset($mysqlConf['mysqlServiceCmd']) && $mysqlConf['mysqlServiceCmd'] == 'windows') {
			$binpath = str_replace('/','\\',$c_mysqlVersionDir.'/mysql'.$oneMysqlVersion.'/'.$mysqlConf['mysqlExeDir'].'/'.$mysqlConf['mysqlExeFile']);
			$default = str_replace('/','\\',$c_mysqlVersionDir.'/mysql'.$oneMysqlVersion.'/'.$mysqlConf['mysqlConfDir'].'/'.$mysqlConf['mysqlConfFile']);
			$myreplacemenu .= <<< EOF
Action: run; FileName: "CMD"; parameters: "/D /C sc create ${c_mysqlService} binpath=""${binpath} --defaults-file=${default} ${c_mysqlService}"""; ShowCmd: hidden; Flags: waituntilterminated

EOF;
		}
		else {
			$myreplacemenu .= <<< EOF
Action: run; FileName: "${c_mysqlVersionDir}/mysql${oneMysqlVersion}/${mysqlConf['mysqlExeDir']}/${mysqlConf['mysqlExeFile']}"; Parameters: "${mysqlConf['mysqlServiceInstallParams']}"; ShowCmd: hidden; Flags: waituntilterminated

EOF;
		}
		$myreplacemenu .= <<< EOF
Action: run; FileName: "CMD"; Parameters: "/D /C net start ${c_mysqlService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
	}
  elseif($mysqlServer[$oneMysqlVersion] == -1) {
    $myreplace .= 'Type: item; Caption: "'.$oneMysqlVersion.'"; Action: multi; Actions:switchMysql'.$oneMysqlVersion.'; Glyph: 19
';
  	$myreplacemenu .= '[switchMysql'.$oneMysqlVersion.']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 13 '.base64_encode($myIniFile).' '.base64_encode($c_mysqlService).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}

}
$myreplace .= 'Type: submenu; Caption: " "; Submenu: AddingVersions; Glyph: 1

';

$tpl = str_replace($myPattern,$myreplace.$myreplacemenu,$tpl);

// **********************
// Configuration of MySQL
// Retrieves the values of the [wampmysqld] or [wampmysqld64] section
$mysqliniS = parse_ini_file($c_mysqlConfFile, true,INI_SCANNER_RAW);
//To correct MySQL 8.0 bug
$mysqlini = (count($mysqliniS[$c_mysqlService]) > 0) ? $mysqliniS[$c_mysqlService] : $mysqliniS['mysqld'];
// Retrieve the three values of port used
$MysqlPort['client'] = $mysqliniS['client']['port'];
$MysqlPort[$c_mysqlService] = $mysqliniS[$c_mysqlService]['port'];
$MysqlPort['mysqld'] =$mysqliniS['mysqld']['port'];
// Check if three values are identical and equal to port used in wampmanager.conf
// $wampConf['mysqlPortUsed']
if($MysqlPort['client'] <> $MysqlPort[$c_mysqlService] || $MysqlPort['client'] <> $MysqlPort['mysqld'] || $MysqlPort['mysqld'] <> $MysqlPort[$c_mysqlService]) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nThe three 'port=number' directives in the MySQL my.ini file:\r\n[client], [".$c_mysqlService."], [mysqld]\r\ndo not have the same port number.\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "Not same MySQL port"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}
if($MysqlPort['client'] <> $wampConf['mysqlPortUsed'] || $MysqlPort[$c_mysqlService] <> $wampConf['mysqlPortUsed'] || $MysqlPort['mysqld'] <> $wampConf['mysqlPortUsed']) {
	$WarningsAtEnd = true;
	$message = color('red',"\r\nThe three 'port=number' directives in the MySQL my.ini file:\r\n[client], [".$c_mysqlService."], [mysqld]\r\ndo not have the port number defined in wampmanager.conf: mysqlPortUsed\r\n");
	if($doReport)	$wampReport['gen2'] .= $message;
	$WarningText .= 'Type: item; Caption: "MySQL port not equal"; Glyph: 19; Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 11 '.base64_encode($message).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
}

//Check if there is prompt directive into [mysql] section
if(!empty($mysqliniS['mysql']['prompt'])) {
	$mysqlini += array('prompt' => $mysqliniS['mysql']['prompt']);
	$mysqlPrompt = true;
}
else {
	$mysqlini += array('prompt' => 'default');
	$mysqlPrompt = false;
}

//Check if default sql-mode
if(!array_key_exists('sql-mode', $mysqlini))
	$mysqlini += array('sql-mode' => 'default');

$myIniFileContents = @file_get_contents($c_mysqlConfFile) or die ("my.ini file not found");
//Check if there is a commented or not user sql-mode
$UserSqlMode = (preg_match('/^[;]?sql-mode[ \t]*=[ \t]*"[^"].*$/m',$myIniFileContents) > 0 ? true : false);
//Check if skip-grant-tables is on (uncommented)
if(preg_match('/^skip-grant-tables[\r]?$/m',$myIniFileContents) > 0) {
	$mysqlini += array('skip-grant-tables' => 'MySQL On - !! WARNING !!');
}
if($wampConf['mysqlUseConsolePrompt'] == 'on') {
	if(!$mysqlPrompt) {
		//Add prompt = prompt = "\\h-MySQL\\v-['\\d']>" under [mysql] section
		$search = '[mysql]
';
		$add = "prompt = \"".str_replace('\\','\\\\', $wampConf['mysqlConsolePrompt'])."\"
";
		$myIniFileContents = str_replace($search, $search.$add, $myIniFileContents, $count);
		if($count > 0) {
			write_file($c_mysqlConfFile,$myIniFileContents);
			$mysqlini['prompt'] = str_replace('\\','\\\\', $wampConf['mysqlConsolePrompt']);
			$mysqlPrompt = true;
		}
	}
}
else {
	if($mysqlPrompt) {
		$myIniFileContents = preg_replace('~(\[mysql\][\r]?\n)prompt[ \t]*=[ \t]*".*"[\r]?\n~',"$1",$myIniFileContents, -1, $count);
		if($count > 0) {
			write_file($c_mysqlConfFile,$myIniFileContents);
			$mysqlini['prompt'] = "default";
			$mysqlPrompt = false;
		}
	}
}

unset($myIniFileContents);

$mysqlErrorMsg = array();
$mysqlParams = array_combine($mysqlParams,$mysqlParams);
foreach($mysqlParams as $next_param_name=>$next_param_text)
{
  if(isset($mysqlini[$next_param_text]))
  {
  	if(array_key_exists($next_param_name, $mysqlParamsNotOnOff)) {
  		if($mysqlParamsNotOnOff[$next_param_name]['change'] !== true) {
  	  	$params_for_mysqlini[$next_param_name] = -2;
  	  	if(!empty($mysqlParamsNotOnOff[$next_param_name]['msg']))
  	  		$mysqlErrorMsg[$next_param_name] = "\n".$mysqlParamsNotOnOff[$next_param_name]['msg']."\n";
   	  	else
					$mysqlErrorMsg[$next_param_name] = "\nThe value of this MySQL parameter must be modified in the file:\n".$c_mysqlConfFile."\n";
  		}
  		else {
  	  $params_for_mysqlini[$next_param_name] = -3;
  	  if($mysqlParamsNotOnOff[$next_param_name]['title'] == 'Special')
  	  	$params_for_mysqlini[$next_param_name] = -4;
  		}
  	}
  	elseif($mysqlini[$next_param_text] == "Off")
  		$params_for_mysqlini[$next_param_name] = '0';
  	elseif($mysqlini[$next_param_text] == 0)
  		$params_for_mysqlini[$next_param_name] = '0';
  	elseif($mysqlini[$next_param_text] == "On")
  		$params_for_mysqlini[$next_param_name] = '1';
  	elseif($mysqlini[$next_param_text] == 1)
  		$params_for_mysqlini[$next_param_name] = '1';
  	else
  	  $params_for_mysqlini[$next_param_name] = -2;
  }
  else //Parameter in $mysqlParams (config.inc.php) does not exist in my.ini
    $params_for_mysqlini[$next_param_name] = -1;
}

$mysqlConfText = ";WAMPMYSQL_PARAMSSTART
";
$mysqlConfTextInfo = $mysqlConfForInfo = "";
$action_sup = array();
$information_only = false;
foreach ($params_for_mysqlini as $paramname=>$paramstatus)
{
	if($params_for_mysqlini[$paramname] == 0 || $params_for_mysqlini[$paramname] == 1) {
		$glyph = ($params_for_mysqlini[$paramname] == 1 ? '13' : '22');
    $mysqlConfText .= 'Type: item; Caption: "'.$paramname.'"; Glyph: '.$glyph.'; Action: multi; Actions: '.$mysqlParams[$paramname].'
';
	}
	elseif($params_for_mysqlini[$paramname] == -2) { // I blue to indicate different from 0 or 1 or On or Off
		if(!$information_only) {
			$mysqlConfForInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
		if($paramname == 'skip-grant-tables') {
			$WarningsAtEnd = true;
			$WarningText .= 'Type: item; Caption: "'.$paramname.' = '.$mysqlini[$paramname].'"; Glyph: 19; Action: multi; Actions: '.$mysqlParams[$paramname].'
';
		}
		if($seeInfoMessage) {
    	$mysqlConfForInfo .= 'Type: item; Caption: "'.$paramname.' = '.$mysqlini[$paramname].'"; Action: multi; Actions: '.$mysqlParams[$paramname].'
';
		}
		else {
    	$mysqlConfForInfo .= 'Type: item; Caption: "'.$paramname.' = '.$mysqlini[$paramname].'"; Action: multi; Actions: none
';
		}
		if($doReport && ($paramname == 'basedir' || $paramname == 'datadir')) $wampReport['mysql'] .= "\nMySQL ".$paramname." = ".$mysqlini[$paramname];
	}
	elseif($params_for_mysqlini[$paramname] == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
		$action_sup[] = $paramname;
		$text = ($mysqlParamsNotOnOff[$paramname]['title'] == 'Number' ? ' - '.$mysqlParamsNotOnOff[$paramname]['text'][$mysqlini[$paramname]] : '');
		$mysqlConfText .= 'Type: submenu; Caption: "'.$paramname.' = '.$mysqlini[$paramname].$text.'"; Submenu: '.$paramname.'; Glyph: 9
';
	}
	elseif($params_for_mysqlini[$paramname] == -4) { // Indicate different from 0 or 1 or On or Off but can be changed with Special treatment
		$action_sup[] = $paramname;
		if($paramname == 'sql-mode') {
			$mysqlConfTextMode = '';
			$default_modes = array(
				'5.5' => array('NONE'),
				'5.6' => array('NO_ENGINE_SUBSTITUTION'),
				'5.7' => array('ONLY_FULL_GROUP_BY', 'STRICT_TRANS_TABLES', 'NO_ZERO_IN_DATE', 'NO_ZERO_DATE', 'ERROR_FOR_DIVISION_BY_ZERO', 'NO_AUTO_CREATE_USER', 'NO_ENGINE_SUBSTITUTION'),
				'8.0' => array('ONLY_FULL_GROUP_BY', 'STRICT_TRANS_TABLES', 'NO_ZERO_IN_DATE', 'NO_ZERO_DATE', 'ERROR_FOR_DIVISION_BY_ZERO', 'NO_ENGINE_SUBSTITUTION'),
				'valid' => array('ALLOW_INVALID_DATES','ANSI_QUOTES','ERROR_FOR_DIVISION_BY_ZERO','HIGH_NOT_PRECEDENCE','IGNORE_SPACE','NO_AUTO_CREATE_USER','NO_AUTO_VALUE_ON_ZERO','NO_BACKSLASH_ESCAPES','NO_DIR_IN_CREATE','NO_ENGINE_SUBSTITUTION','NO_FIELD_OPTIONS','NO_KEY_OPTIONS','NO_TABLE_OPTIONS','NO_UNSIGNED_SUBTRACTION','NO_ZERO_DATE','NO_ZERO_IN_DATE','ONLY_FULL_GROUP_BY','PAD_CHAR_TO_FULL_LENGTH','PIPES_AS_CONCAT','REAL_AS_FLOAT','STRICT_ALL_TABLES','STRICT_TRANS_TABLES'),
				);
				//Memorize default values
				if(version_compare($c_mysqlVersion, '8.0.11', '>='))
					$default_valeurs = $default_modes['8.0'];
				elseif(version_compare($c_mysqlVersion, '5.7.0', '>='))
					$default_valeurs = $default_modes['5.7'];
				elseif(version_compare($c_mysqlVersion, '5.6.0', '>='))
					$default_valeurs = $default_modes['5.6'];
				elseif(version_compare($c_mysqlVersion, '5.5.0', '>='))
					$default_valeurs = $default_modes['5.5'];
				else
					$default_valeurs = $default_modes['5.5'];

			if(empty($mysqlini['sql-mode'])) {
				$valeurs[0] = 'NONE';
				$m_valeur = 'none';
				$mysqlini['sql-mode'] = 'none';
      	$mysqlConfTextInfo .= 'Type: separator; Caption: "sql-mode: '.$w_mysql_none.'"
';
				$mysqlConfTextInfo .= 'Type: submenu; Caption: "'.$w_mysql_mode.'"; Submenu: mysql-mode; Glyph: 22
';
				$mysqlConfTextMode = 'Type: submenu; Caption: "'.$paramname.'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
			}
			elseif($mysqlini['sql-mode'] == 'default') {
				$valeurs = $default_valeurs;
      	$mysqlConfTextInfo .= 'Type: separator; Caption: "sql-mode:  '.$w_mysql_default.'"
';
				$mysqlConfTextInfo .= 'Type: submenu; Caption: "'.$w_mysql_mode.'"; Submenu: mysql-mode; Glyph: 22
';
				foreach($valeurs as $val) {
					$mysqlConfTextInfo .= 'Type: item; Caption: "'.$val.'"; Action: multi; Actions: none
';
				}
				$m_valeur = 'default';
				$mysqlConfTextMode = 'Type: submenu; Caption: "'.$paramname.'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
			}
			else {
				$valeurs = explode(',',$mysqlini['sql-mode']);
				$valeurs = array_map('trim',$valeurs);
     		$mysqlConfTextInfo .= 'Type: separator; Caption: "sql-mode: '.$w_mysql_user.'"
';
				$mysqlConfTextInfo .= 'Type: submenu; Caption: "'.$w_mysql_mode.'"; Submenu: mysql-mode; Glyph: 22
';
				$MyUserError = false;
				foreach($valeurs as $val) {
					//Check if each user value is allowed
					if(in_array($val,$default_modes['valid'])) {
						$UserGlyph = '';
						$notValid = '';
					}
					else {
						$MyUserError = true;
						$UserGlyph = '; Glyph: 19';
						$notValid = ' - Not valid mode';
					}
					$mysqlConfTextInfo .= 'Type: item; Caption: "'.$val.$notValid.'"; Action: multi; Actions: none'.$UserGlyph.'
';
				}
				$m_valeur = 'user';
				$mysqlini['sql-mode'] = 'user';
				$mysqlConfTextMode = 'Type: submenu; Caption: "'.$paramname.'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
			}
			$mysqlConfTextInfo .= $mysqlConfTextMode;
		}
		else {
		$mysqlConfText .= 'Type: submenu; Caption: "'.$paramname.' = '.$mysqlini[$paramname].'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
		}
	}
}
//Check for supplemtary actions
$MenuSup = $SubMenuSup = array();
if(count($action_sup) > 0) {
	$i = 0;
	foreach($action_sup as $action) {
		$MenuSup[$i] = $SubMenuSup[$i] = '';
		if($mysqlParamsNotOnOff[$action]['title'] == 'Special') {
			if($action == 'sql-mode') {
				$actionToDo = $actionName = $param_value = array();
				if($mysqlini['sql-mode'] == 'default') {
					if($UserSqlMode) {
						$actionToDo[] = 'user';
						$actionName[] = $w_mysql_user;
						$param_value[] = 'user';
					}
					$actionToDo[] = 'none';
					$actionName[] = $w_mysql_none;
					$param_value[] = 'none';
				}
				elseif($mysqlini['sql-mode'] == 'none') {
					if($UserSqlMode) {
						$actionToDo[] = 'user';
						$actionName[] = $w_mysql_user;
						$param_value[] = 'user';
					}
					$actionToDo[] = 'default';
					$actionName[] = $w_mysql_default;
					$param_value[] = 'default';
				}
				if($mysqlini['sql-mode'] == 'user') {
					$actionToDo[] = 'none';
					$actionName[] = $w_mysql_none;
					$param_value[] = 'none';
					$actionToDo[] = 'default';
					$actionName[] = $w_mysql_default;
					$param_value[] = 'default';
				}
				$MenuSup[$i] .= '[sql-mode'.$typebase.']
Type: separator; Caption: "sql-mode"
';
				for($j = 0 ; $j < count($actionToDo) ; $j++) {
					if($actionToDo[$j] == 'default') {
						$MenuSup[$i] .= <<< EOF

Type: separator; Caption: "MySQL ${c_mysqlVersion}"
Type: separator; Caption: "sql-mode ${actionName[$j]} = "

EOF;
						foreach($default_valeurs as $val) {
						$MenuSup[$i] .= 'Type: item; Caption: "'.$val.'"; Action: multi; Actions: none
';
						}
						$MenuSup[$i] .= 'Type: separator
';
					}
				$MenuSup[$i] .= 'Type: item; Caption: "sql-mode -> '.$actionName[$j].'"; Action: multi; Actions: '.$action.$actionToDo[$j].$typebase.'
';
					$SubMenuSup[$i] .= <<< EOF
[${action}${actionToDo[$j]}${typebase}]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "changeMysqlParam.php noquotes ${action} ${param_value[$j]}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "CMD"; Parameters: "/D /C net start ${c_mysqlService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
				}
			}
		}
		else {
			$MenuSup[$i] .= '['.$action.']
Type: separator; Caption: "'.$mysqlParamsNotOnOff[$action]['title'].'"
';
			$c_values = $mysqlParamsNotOnOff[$action]['values'];
			if($mysqlParamsNotOnOff[$action]['quoted'])
				$quoted = 'quotes';
			else
				$quoted = 'noquotes';
			foreach($c_values as $value) {
				$text = ($mysqlParamsNotOnOff[$action]['title'] == 'Number' ? " - ".$mysqlParamsNotOnOff[$action]['text'][$value] : "");
				$MenuSup[$i] .= 'Type: item; Caption: "'.$value.$text.'"; Action: multi; Actions: '.$action.$value.'
';
				if(strtolower($value) == 'choose') {
					$param_value = '%'.$mysqlParamsNotOnOff[$action]['title'].'%';
					$param_third = ' '.$mysqlParamsNotOnOff[$action]['title'];
					$c_phpRun = $c_phpExe;
				}
				else {
					$param_value = $value;
					$param_third = '';
					$c_phpRun = $c_phpCli;
				}
				$SubMenuSup[$i] .= <<< EOF
[${action}${value}]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpRun}";Parameters: "changeMysqlParam.php ${quoted} ${action} ${param_value}${param_third}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "CMD"; Parameters: "/D /C net start ${c_mysqlService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
			}
		}
	$i++;
	}
}
$mysqlConfText .= $mysqlConfTextInfo.$mysqlConfForInfo;

foreach ($params_for_mysqlini as $paramname=>$paramstatus) {
	if($params_for_mysqlini[$paramname] == 1 || $params_for_mysqlini[$paramname] == 0) {
		$SwitchAction = ($params_for_mysqlini[$paramname] == 1 ? 'off' : 'on');
  	$mysqlConfText .= <<< EOF
[${mysqlParams[$paramname]}]
Action: service; Service: ${c_mysqlService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "switchMysqlParam.php ${mysqlParams[$paramname]} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "CMD"; Parameters: "/D /C net start ${c_mysqlService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
	}
  elseif($params_for_mysqlini[$paramname] == -2)  {//Parameter is neither 'on' nor 'off'
  	$mysqlConfText .= '['.$mysqlParams[$paramname].']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 6 '.base64_encode($paramname).' '.base64_encode($mysqlErrorMsg[$paramname]).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}
if(count($MenuSup) > 0) {
	for($i = 0 ; $i < count($MenuSup); $i++)
		$mysqlConfText .= $MenuSup[$i].$SubMenuSup[$i];
}

$tpl = str_replace(';WAMPMYSQL_PARAMSSTART',$mysqlConfText,$tpl);
$TestPort3306 = ';';
unset($mysqlConfText,$mysqlConfTextInfo,$mysqlConfForInfo);
}

?>
