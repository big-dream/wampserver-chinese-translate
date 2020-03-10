<?php
// 3.2.0 use write_file instead of fwrite, fclose

if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

// Versions of MariaDB
// Already done in refresh.php
//$mariadbVersionList = listDir($c_mariadbVersionDir,'checkMariaDBConf','mariadb');
if(count($mariadbVersionList) == 0) {
	error_log("No version of MariaDB is installed.");
	$glyph = '19';
	$WarningsAtEnd = true;
	if(!isset($WarningMariadb)) {
		$WarningMariadb = true;
		$WarningText .= 'Type: separator; Caption: "Warning MariaDB"
';
	}
	$WarningText .= 'Type: item; Caption: "No version of MariaDB is installed"; Glyph: '.$glyph.'; Action: multi; Actions: none
';
}
else {
//MariaDB submenu
$typebase = 'mariadb';
$myPattern = ';WAMPMARIADBMENUSTART';
$myreplace = <<< EOF
;WAMPMARIADBMENUSTART
Type: submenu; Caption: "${w_version}"; SubMenu: mariadbVersion; Glyph: 3
Type: servicesubmenu; Caption: "${w_service} '${c_mariadbService}'"; Service: ${c_mariadbService}; SubMenu: mariadbService
Type: submenu; Caption: "${w_mariaSettings}"; SubMenu: mariadb_params; Glyph: 25
Type: item; Caption: "${w_mariadbConsole}"; Action: run; FileName: "${c_mariadbConsole}";Parameters: "-u %MariaUser% -p"; Glyph: 0
Type: item; Caption: "my.ini"; Glyph: 6; Action: run; FileName: "${c_editor}"; parameters: "${c_mariadbConfFile}"
Type: item; Caption: "${w_mariadbLog}"; Glyph: 6; Action: run; FileName: "${c_logviewer}"; parameters: "${c_installDir}/${logDir}mariadb.log"
Type: item; Caption: "${w_mariadbDoc}"; Action: run; FileName: "${c_navigator}"; Parameters: "${c_edge}http://mariadb.com/kb/en/mariadb/documentation"; Glyph: 35
${MariaTestPortUsed}Type: separator; Caption: "${w_portUsedMaria}${c_UsedMariaPort}"
${TestPort3306}${MariaTestPortUsed}Type: item; Caption: "${w_testPortMysql}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php 3306 ${c_mariadbService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
${MariaTestPortUsed}Type: item; Caption: "${w_AlternateMariaPort}"; Action: multi; Actions: UseAlternateMariaPort; Glyph: 24
${MariaTestPortUsed}Type: item; Caption: "${w_testPortMariaUsed}${c_UsedMariaPort}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php ${c_UsedMariaPort} ${c_mariadbService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// MariaDB submenu Service
$myPattern = ';WAMPMARIADBSERVICESTART';
$myreplace = <<< EOF
;WAMPMARIADBSERVICESTART
[MariaDBService]
Type: separator; Caption: "MariaDB"
Type: item; Caption: "${w_startResume}"; Action: service; Service: ${c_mariadbService}; ServiceAction: startresume; Glyph: 9 ;Flags: ignoreerrors
;Type: item; Caption: "${w_pauseService}"; Action: service; Service: mariadb; ServiceAction: pause; Glyph: 10
Type: item; Caption: "${w_stopService}"; Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Glyph: 11
Type: item; Caption: "${w_restartService}"; Action: service; Service: ${c_mariadbService}; ServiceAction: restart; Glyph: 12
Type: separator
Type: item; Caption: "${w_installService}"; Action: multi; Actions: MariaDBServiceInstall; Glyph: 8
Type: item; Caption: "${w_removeService}"; Action: multi; Actions: MariaDBServiceRemove; Glyph: 26
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

$myPattern = ';WAMPMARIADBSERVICEINSTALLSTART';
$myreplace = <<< EOF
;WAMPMARIADBSERVICEINSTALLSTART
[MariaDBServiceInstall]
Action: run; FileName: "${c_mariadbExe}"; Parameters: "${c_mariadbServiceInstallParams}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: resetservices
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

$myPattern = ';WAMPMARIADBSERVICEREMOVESTART';
$myreplace = <<< EOF
;WAMPMARIADBSERVICEREMOVESTART
[MariaDBServiceRemove]
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_mariadbExe}"; Parameters: "${c_mariadbServiceRemoveParams}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// MariaDB use alternate port submenu
$myPattern = ';WAMPALTERNATEMARIAPORTSTART';
$myreplace = <<< EOF
;WAMPALTERNATEMARIAPORTSTART
[UseAlternateMariaPort]
Action: service; Service: ${c_apacheService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpExe}"; Parameters: "switchMariaPort.php %MariaPort%";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: service; Service: ${c_apacheService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: service; Service: ${c_mariadbService}; ServiceAction: startresume; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_phpCli}"; Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

//MariaDB console prompt submenu
$myPattern = ';WAMPMARIADBUSECONSOLEPROMPTSTART';
$myreplace = <<< EOF
;WAMPMARIADBUSECONSOLEPROMPTSTART
[mariadbUseConsolePrompt]
Action: service; Service: ${c_apacheService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "switchWampParam.php mariadbUseConsolePrompt ${mariadbConsolePromptChange}"; WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "refresh.php"; WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated
Action: run; FileName: "net"; Parameters: "start ${c_apacheService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

//MariaDB Tools menu
$myPattern = ';WAMPMARIADBSUPPORTTOOLS';
$myreplace = <<< EOF
;WAMPMARIADBSUPPORTTOOLS
Type: separator; Caption: "${w_portUsedMaria}${c_UsedMariaPort}"
${TestPort3306}Type: item; Caption: "${w_testPortMysql}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php 3306 ${c_mariadbService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
${MariaTestPortUsed}Type: item; Caption: "${w_testPortMariaUsed}${c_UsedMariaPort}"; Action: run; FileName: "${c_phpExe}"; Parameters: "testPort.php ${c_UsedMariaPort} ${c_mariadbService}";WorkingDir: "$c_installDir/scripts"; Flags: waituntilterminated; Glyph: 24
Type: item; Caption: "${w_AlternateMariaPort}"; Action: multi; Actions: UseAlternateMariaPort; Glyph: 24
Type: item; Caption: "${w_settings['mariadbUseConsolePrompt']}: ${mariadbConsolePromptUsed}"; Glyph: 24; Action: multi; Actions: mariadbUseConsolePrompt
EOF;
$tpl = str_replace($myPattern,$myreplace,$tpl);

// ************************
$maPattern = ';WAMPMARIADBVERSIONSTART';
$mareplace = $maPattern."
";
$mareplacemenu = '';
foreach ($mariadbVersionList as $oneMariaDBVersion) {
  //File wamp/bin/mariadb/mariadbx.y.z/wampserver.conf
  //Check service name if it is modified
  $maConfile = $c_mariadbVersionDir.'/mariadb'.$oneMariaDBVersion.'/'.$wampBinConfFiles;
  $mariaConfContents = file_get_contents($maConfile);

  if(preg_match("~^(.*'mariadbServiceInstallParams'.*--install-manual )(wampmariadb[0-9]*)(.*\r?)$~mi",$mariaConfContents,$matches) > 0) {
  	if($matches[2] != $c_mariadbService)
  		$mariaConfContents = str_ireplace($matches[0],$matches[1].$c_mariadbService.$matches[3],$mariaConfContents, $count);
		$count += $count;
	}
  if(preg_match("~^(.*mariadbServiceRemoveParams.*--remove )(wampmariadb[0-9]*)(.*\r?)$~mi",$mariaConfContents,$matches) > 0) {
  	if($matches[2] != $c_mariadbService)
  		$mariaConfContents = str_ireplace($matches[0],$matches[1].$c_mariadbService.$matches[3],$mariaConfContents, $count);
		$count += $count;
	}

		if(!is_null($mariaConfContents) && $count > 0) {
			write_file($maConfile,$mariaConfContents);
		}

  unset($mariadbConf);
  include $maConfile;

	//Check name of the group [wamp...] under '# The MariaDB server' in my.ini file
	//    must be the name of the mariadb service.
	$maIniFile = $c_mariadbVersionDir.'/mariadb'.$oneMariaDBVersion.'/'.$mariadbConf['mariadbConfFile'];
	$maIniContents = file_get_contents($maIniFile);

	if(strpos($maIniContents, "[".$c_mariadbService."]") === false) {
		$maIniContents = preg_replace("/^\[wamp.*\].*\n/m", "[".$c_mariadbService."]\r\n", $maIniContents, 1, $count);
		if(!is_null($maIniContents) && $count == 1) {
			write_file($maIniFile,$maIniContents);
			$mariaServer[$oneMariaDBVersion] = 0;
		}
		else { //The MariaDB server has not the same name as mariadb service
			$mariaServer[$oneMariaDBVersion] = -1;
		}
	}
	else
		$mariaServer[$oneMariaDBVersion] = 0;
	unset($maIniContents);

	if ($oneMariaDBVersion === $wampConf['mariadbVersion'] && $mariaServer[$oneMariaDBVersion] == 0)
  	$mariaServer[$oneMariaDBVersion] = 1;

	if ($mariaServer[$oneMariaDBVersion] == 1) {
    $mareplace .= 'Type: item; Caption: "'.$oneMariaDBVersion.'"; Action: multi; Actions:switchMariaDB'.$oneMariaDBVersion.'; Glyph: 13
';
	}
  elseif($mariaServer[$oneMariaDBVersion] == 0) {
    $mareplace .= 'Type: item; Caption: "'.$oneMariaDBVersion.'"; Action: multi; Actions:switchMariaDB'.$oneMariaDBVersion.'
';

    $mareplacemenu .= <<< EOF
[switchMariaDB${oneMariaDBVersion}]
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: ignoreerrors waituntilterminated
Action: run; FileName: "${c_mariadbExe}"; Parameters: "${c_mariadbServiceRemoveParams}"; ShowCmd: hidden; Flags: ignoreerrors waituntilterminated
Action: closeservices;
Action: run; FileName: "{$c_phpCli}";Parameters: "switchMariaDBVersion.php ${oneMariaDBVersion}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "{$c_phpCli}";Parameters: "switchMariaPort.php ${c_UsedMariaPort}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_mariadbVersionDir}/mariadb${oneMariaDBVersion}/${mariadbConf['mariadbExeDir']}/${mariadbConf['mariadbExeFile']}"; Parameters: "${mariadbConf['mariadbServiceInstallParams']}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "net"; Parameters: "start ${c_mariadbService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: run; FileName: "{$c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
	}
}

$tpl = str_replace($maPattern,$mareplace.$mareplacemenu,$tpl);

// Configuration of MariaDB
// Retrieves the values of the [wampmariadb] or [wampmariadb64] section
$mariadbiniS = parse_ini_file($c_mariadbConfFile, true);
$mariadbini = $mariadbiniS[$c_mariadbService];
//Check if there is prompt directive into [mysql] section
if(!empty($mariadbiniS['mysql']['prompt'])) {
	$mariadbini += array('prompt' => $mariadbiniS['mysql']['prompt']);
	$mariadbPrompt = true;
}
else {
	$mariadbini += array('prompt' => 'default');
	$mariadbPrompt = false;
}

//Check if default sql-mode
if(!array_key_exists('sql-mode', $mariadbini))
	$mariadbini = $mariadbini + array('sql-mode' => 'default');

$myIniFileContents = @file_get_contents($c_mariadbConfFile) or die ("my.ini file not found");
//Check if there is a commented or not user sql-mode
$UserSqlMode = (preg_match('/^[;]?sql-mode[ \t]*=[ \t]*"[^"].*$/m',$myIniFileContents) > 0 ? true : false);
//Check if skip-grant-tables is on (uncommented)
if(preg_match('/^skip-grant-tables[\r]?$/m',$myIniFileContents) > 0) {
	$mariadbini = $mariadbini + array('skip-grant-tables' => 'On - !! WARNING !!');
}
if($wampConf['mariadbUseConsolePrompt'] == 'on') {
	if(!$mariadbPrompt) {
		//Add prompt = prompt = "\\h-\\v-['\\d']>" under [mysql] section
		$search = '[mysql]
';
		$add = "prompt = \"".str_replace('\\','\\\\', $wampConf['mariadbConsolePrompt'])."\"
";
		$myIniFileContents = str_replace($search, $search.$add, $myIniFileContents, $count);
		if($count > 0) {
			write_file($c_mariadbConfFile,$myIniFileContents);
			$mariadbini['prompt'] = str_replace('\\','\\\\', $wampConf['mariadbConsolePrompt']);
			$mariadbPrompt = true;
		}
	}
}
else {
	if($mariadbPrompt) {
		$myIniFileContents = preg_replace('~(\[mysql\][\r]?\n)prompt[ \t]*=[ \t]*".*"[\r]?\n~',"$1",$myIniFileContents, -1, $count);
		if($count > 0) {
			write_file($c_mariadbConfFile,$myIniFileContents);
			$mariadbini['prompt'] = "default";
			$mariadbPrompt = false;
		}
	}
}

unset($myIniFileContents);

$mariadbErrorMsg = array();
$mariadbParams = array_combine($mariadbParams,$mariadbParams);
foreach($mariadbParams as $next_param_name=>$next_param_text)
{
  if (isset($mariadbini[$next_param_text]))
  {
  	if(array_key_exists($next_param_name, $mariadbParamsNotOnOff)) {
  		if($mariadbParamsNotOnOff[$next_param_name]['change'] !== true) {
  	  	$params_for_mariadb[$next_param_name] = -2;
  	  	if(!empty($mariadbParamsNotOnOff[$next_param_name]['msg']))
  	  		$mariadbErrorMsg[$next_param_name] = "\n".$mariadbParamsNotOnOff[$next_param_name]['msg']."\n";
   	  	else
					$mariadbErrorMsg[$next_param_name] = "\nThe value of this MariaDB parameter must be modified in the file:\n".$c_mariadbConfFile."\nNot to change the wrong file, the best way to access this file is:\nWampmanager icon->MariaDB->my.ini\n";
  		}
  		else {
  	  	$params_for_mariadb[$next_param_name] = -3;
  	  	if($mariadbParamsNotOnOff[$next_param_name]['title'] == 'Special')
  	  		$params_for_mariadb[$next_param_name] = -4;
  		}
  	}
  	elseif($mariadbini[$next_param_text] == "Off")
  		$params_for_mariadb[$next_param_name] = '0';
  	elseif($mariadbini[$next_param_text] == 0)
  		$params_for_mariadb[$next_param_name] = '0';
  	elseif($mariadbini[$next_param_text] == "On")
  		$params_for_mariadb[$next_param_name] = '1';
  	elseif($mariadbini[$next_param_text] == 1)
  		$params_for_mariadb[$next_param_name] = '1';
  	else
  	  $params_for_mariadb[$next_param_name] = -2;
  }
  else //Parameter in $mariadbParams (config.inc.php) does not exist in my.ini
    $params_for_mariadb[$next_param_name] = -1;
}

$mariadbConfText = ";WAMPMARIADB_PARAMSSTART
";
$mariadbConfTextInfo = $mariadbConfForInfo = "";
$action_sup = array();
$information_only = false;
foreach ($params_for_mariadb as $paramname=>$paramstatus)
{
	if($params_for_mariadb[$paramname] == 0 || $params_for_mariadb[$paramname] == 1) {
		$glyph = ($params_for_mariadb[$paramname] == 1 ? '13' : '22');
    $mariadbConfText .= 'Type: item; Caption: "'.$paramname.'"; Glyph: '.$glyph.'; Action: multi; Actions: maria_'.$mariadbParams[$paramname].'
';
	}
	elseif ($params_for_mariadb[$paramname] == -2) { // I blue to indicate different from 0 or 1 or On or Off
		if(!$information_only) {
			$mariadbConfForInfo .= 'Type: separator; Caption: "'.$w_phpparam_info.'"
';
			$information_only = true;
		}
		$glyph = '22';
		if($paramname == 'skip-grant-tables') {
			$glyph = '19';
			$WarningsAtEnd = true;
			if(!isset($WarningMariadb)) {
				$WarningMariadb = true;
				$WarningText .= 'Type: separator; Caption: "Warning MariaDB"
';
			}
			$WarningText .= 'Type: item; Caption: "'.$paramname.' = '.$mariadbini[$paramname].'"; Glyph: '.$glyph.'; Action: multi; Actions: maria_'.$mariadbParams[$paramname].'
';
		}
     $mariadbConfForInfo .= 'Type: item; Caption: "'.$paramname.' = '.$mariadbini[$paramname].'"; Glyph: '.$glyph.'; Action: multi; Actions: maria_'.$mariadbParams[$paramname].'
';
		if($doReport && ($paramname == 'basedir' || $paramname == 'datadir')) $wampReport['mariadb'] .= "\nMariaDB ".$paramname." = ".$mariadbini[$paramname];
	}
	elseif ($params_for_mariadb[$paramname] == -3) { // Indicate different from 0 or 1 or On or Off but can be changed
		$action_sup[] = $paramname;
		$text = ($mariadbParamsNotOnOff[$paramname]['title'] == 'Number' ? ' - '.$mariadbParamsNotOnOff[$paramname]['text'][$mariadbini[$paramname]] : '');
		$mariadbConfText .= 'Type: submenu; Caption: "'.$paramname.' = '.$mariadbini[$paramname].$text.'"; Submenu: maria_'.$paramname.'; Glyph: 9
';
	}
	elseif ($params_for_mariadb[$paramname] == -4) { // Indicate different from 0 or 1 or On or Off but can be changed with Special treatment
		$action_sup[] = $paramname;
		if($paramname == 'sql-mode') {
			$mariadbConfTextMode = '';
			$default_modes = array(
				'10.1' => array('NONE'),
				'10.2.3' => array('NO_ENGINE_SUBSTITUTION','NO_AUTO_CREATE_USER'),
				'10.2.4' => array('NO_ENGINE_SUBSTITUTION','STRICT_TRANS_TABLES','ERROR_FOR_DIVISION_BY_ZERO','NO_AUTO_CREATE_USER'),
				'valid' => array('ALLOW_INVALID_DATES','ANSI','ANSI_QUOTES','DB2','ERROR_FOR_DIVISION_BY_ZERO','HIGH_NOT_PRECEDENCE','IGNORE_BAD_TABLE_OPTIONS','IGNORE_SPACE','MAXDB','MSSQL','MYSQL323','MYSQL40','NO_AUTO_CREATE_USER','NO_AUTO_VALUE_ON_ZERO','NO_BACKSLASH_ESCAPES','NO_DIR_IN_CREATE','NO_ENGINE_SUBSTITUTION','NO_FIELD_OPTIONS','NO_KEY_OPTIONS','NO_TABLE_OPTIONS','NO_UNSIGNED_SUBTRACTION','NO_ZERO_DATE','NO_ZERO_IN_DATE','ONLY_FULL_GROUP_BY','ORACLE','PAD_CHAR_TO_FULL_LENGTH','PIPES_AS_CONCAT','POSTGRESQL','REAL_AS_FLOAT','STRICT_ALL_TABLES','STRICT_TRANS_TABLES','TRADITIONAL'),
				);
				//Memorize default values
				if(version_compare($c_mariadbVersion, '10.2', '<'))
					$default_valeurs = $default_modes['10.1'];
				elseif(version_compare($c_mariadbVersion, '10.2.4', '>='))
					$default_valeurs = $default_modes['10.2.4'];
				else
					$default_valeurs = $default_modes['10.2.3'];

			if(empty($mariadbini['sql-mode'])) {
				$valeurs[0] = 'NONE';
				$m_valeur = 'none';
				$mariadbini['sql-mode'] = 'none';
      	$mariadbConfTextInfo .= 'Type: separator; Caption: "sql-mode: '.$w_mysql_none.'"
';
				$mariadbConfTextMode = 'Type: submenu; Caption: "'.$paramname.'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
			}
			elseif($mariadbini['sql-mode'] == 'default') {
				$valeurs = $default_valeurs;
      	$mariadbConfTextInfo .= 'Type: separator; Caption: "sql-mode:  '.$w_mysql_default.'"
';
				foreach($valeurs as $val) {
					$mariadbConfTextInfo .= 'Type: item; Caption: "'.$val.'"; Action: multi; Actions: none
';
				}
				$m_valeur = 'default';
				$mariadbConfTextMode = 'Type: submenu; Caption: "'.$paramname.'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
			}
			else {
				$valeurs = explode(',',$mariadbini['sql-mode']);
				$valeurs = array_map('trim',$valeurs);
     		$mariadbConfTextInfo .= 'Type: separator; Caption: "sql-mode: '.$w_mysql_user.'"
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
					$mariadbConfTextInfo .= 'Type: item; Caption: "'.$val.$notValid.'"; Action: multi; Actions: none'.$UserGlyph.'
';
				}
				$m_valeur = 'user';
				$mariadbini['sql-mode'] = 'user';
				$mariadbConfTextMode = 'Type: submenu; Caption: "'.$paramname.'"; Submenu: '.$paramname.$typebase.'; Glyph: 9
';
			}
			$mariadbConfTextInfo .= $mariadbConfTextMode;
		}
		else {
			$mariadbConfText .= 'Type: submenu; Caption: "'.$paramname.' = '.$mariadbini[$paramname].'"; Submenu: maria_'.$paramname.$typebase.'; Glyph: 9
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
		if($mariadbParamsNotOnOff[$action]['title'] == 'Special') {
			if($action == 'sql-mode') {
				$actionToDo = $actionName = $param_value = array();
				if($mariadbini['sql-mode'] == 'default') {
					if($UserSqlMode) {
						$actionToDo[] = 'user';
						$actionName[] = $w_mysql_user;
						$param_value[] = 'user';
					}
					$actionToDo[] = 'none';
					$actionName[] = $w_mysql_none;
					$param_value[] = 'none';
				}
				elseif($mariadbini['sql-mode'] == 'none') {
					if($UserSqlMode) {
						$actionToDo[] = 'user';
						$actionName[] = $w_mysql_user;
						$param_value[] = 'user';
					}
					$actionToDo[] = 'default';
					$actionName[] = $w_mysql_default;
					$param_value[] = 'default';
				}
				if($mariadbini['sql-mode'] == 'user') {
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

Type: separator; Caption: "MariaDB ${c_mariadbVersion}"
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
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpExe}";Parameters: "changeMariadbParam.php noquotes ${action} ${param_value[$j]}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "net"; Parameters: "start ${c_mariadbService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
				}
			}
		}
		else {
			$MenuSup[$i] .= '[maria_'.$action.']
Type: separator; Caption: "'.$mariadbParamsNotOnOff[$action]['title'].'"
';
			$c_values = $mariadbParamsNotOnOff[$action]['values'];
			if($mariadbParamsNotOnOff[$action]['quoted'])
				$quoted = 'quotes';
			else
				$quoted = 'noquotes';
			foreach($c_values as $value) {
				$text = ($mariadbParamsNotOnOff[$action]['title'] == 'Number' ? " - ".$mariadbParamsNotOnOff[$action]['text'][$value] : "");
				$MenuSup[$i] .= 'Type: item; Caption: "'.$value.$text.'"; Action: multi; Actions: maria_'.$action.$value.'
';
				if(strtolower($value) == 'choose') {
					$param_value = '%'.$mariadbParamsNotOnOff[$action]['title'].'%';
					$param_third = ' '.$mariadbParamsNotOnOff[$action]['title'];
					$c_phpRun = $c_phpExe;
				}
				else {
					$param_value = $value;
					$param_third = '';
					$c_phpRun = $c_phpCli;
				}
				$SubMenuSup[$i] .= <<< EOF
[maria_${action}${value}]
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpRun}";Parameters: "changeMariadbParam.php ${quoted} ${action} ${param_value}${param_third}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "net"; Parameters: "start ${c_mariadbService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
			}
		}
	$i++;
	}
}
$mariadbConfText .= $mariadbConfTextInfo.$mariadbConfForInfo;

foreach ($params_for_mariadb as $paramname=>$paramstatus) {
	if ($params_for_mariadb[$paramname] == 1 || $params_for_mariadb[$paramname] == 0) {
		$SwitchAction = ($params_for_mariadb[$paramname] == 1 ? 'off' : 'on');
  	$mariadbConfText .= <<< EOF
[maria_${mariadbParams[$paramname]}]
Action: service; Service: ${c_mariadbService}; ServiceAction: stop; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "switchMariadbParam.php ${mariadbParams[$paramname]} ${SwitchAction}";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "${c_phpCli}";Parameters: "refresh.php";WorkingDir: "${c_installDir}/scripts"; Flags: waituntilterminated
Action: run; FileName: "net"; Parameters: "start ${c_mariadbService}"; ShowCmd: hidden; Flags: waituntilterminated
Action: resetservices
Action: readconfig

EOF;
	}
  elseif ($params_for_mariadb[$paramname] == -2)  {//Parameter is neither 'on' nor 'off'
  	$mariadbConfText .= '[maria_'.$mariadbParams[$paramname].']
Action: run; FileName: "'.$c_phpExe.'";Parameters: "msg.php 6 '.base64_encode($paramname).' '.base64_encode($mariadbErrorMsg[$paramname]).'";WorkingDir: "'.$c_installDir.'/scripts"; Flags: waituntilterminated
';
	}
}
if(count($MenuSup) > 0) {
	for($i = 0 ; $i < count($MenuSup); $i++)
		$mariadbConfText .= $MenuSup[$i].$SubMenuSup[$i];
}

$tpl = str_replace(';WAMPMARIADB_PARAMSSTART',$mariadbConfText,$tpl);
$TestPort3306 = ';';
}

?>
