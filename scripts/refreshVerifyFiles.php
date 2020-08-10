<?php
// - 3.2.1 add ThreadStackSize into httpd.conf
//
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

//*************************************************************
//****** Verify some files before generate wampmanager.ini file
//Check all lines are DOS (CR/LF) ending - Modify if not - Don't return contents
// Apache httpd-vhosts.conf file
file_get_contents_dos($c_apacheVhostConfFile, false);

// Verify DO NOT EDIT in wamp(64)/bin/php/phpx.y.z/php.ini file - Insert if not
$do_not_edit = <<< NOTEDITEOF
[PHP]
; **************************************************************
; ****** DO NOT EDIT THIS FILE **** DO NOT EDIT THIS FILE ******
; ************  此文件仅PHP CLI(命令行)模式下生效  *************
; * that is to say by Wampserver internal PHP scripts          *
; * THE CORRECT FILE TO EDIT is Wampmanager Icon->PHP->php.ini *
; * that is wamp/bin/apache/apache2.4.x/bin/php.ini            *
; **************************************************************

NOTEDITEOF;
$iniFileContents = file_get_contents($c_phpVersionDir."/php".$wampConf['phpVersion']."/".$wampConf['phpConfFile']);
if(strpos($iniFileContents,"* DO NOT EDIT THIS FILE *") === false) {
	$iniFileContents = str_replace("[PHP]", $do_not_edit, $iniFileContents);
	write_file($c_phpVersionDir."/php".$wampConf['phpVersion']."/".$wampConf['phpConfFile'],$iniFileContents);
}

//Check if the file wamp/bin/php/DO_NOT_DELETE_x.y.z.txt match CLI php version used
if(!file_exists($c_phpVersionDir."/DO_NOT_DELETE_".$c_phpCliVersion.".txt")) {
	$do_not_delete_txt = "This PHP version ".$c_phpCliVersion." is used by WampServer in CLI mode.\r\nIf you delete it, WampServer won't work anymore.";
	if ($handle = opendir($c_phpVersionDir))	{
		while (false !== ($file = readdir($handle)))	{
			if ($file != "." && $file != ".." && !is_dir($c_phpVersionDir.'/'.$file)) {
				$list[] = $file;
			}
		}
		closedir($handle);
	}
	if(!empty($list)) {
		foreach($list as $value) {
			if(strpos($value,"DO_NOT_DELETE") !== false)
				unlink($c_phpVersionDir."/".$value);
		}
	}
	write_file($c_phpVersionDir."/DO_NOT_DELETE_".$c_phpCliVersion.".txt",$do_not_delete_txt);
}

//Verify some Apache variables into httpd.conf - Add if not
$c_ApacheDefineVerif = array();
$ApacheDefineError = false;
if(version_compare($c_apacheVersion,'2.4.0','>=')){
	$tryfind = 'Define VERSION_APACHE';
	$search = 'Define APACHE24 Apache2.4
';
	$replace = <<< EOF
# Apache variable names used by Apache conf files:
# The names and contents of variables:
# APACHE24, VERSION_APACHE, INSTALL_DIR, APACHE_DIR, SRVROOT
# should never be changed.
Define APACHE24 Apache2.4
Define VERSION_APACHE ${c_apacheVersion}
Define INSTALL_DIR ${c_installDir}
Define APACHE_DIR \${INSTALL_DIR}/bin/apache/apache\${VERSION_APACHE}
Define SRVROOT \${INSTALL_DIR}/bin/apache/apache\${VERSION_APACHE}

EOF;
	$httpdFileContents = file_get_contents_dos($c_apacheConfFile);
	$count = $counts = 0;
	if(strpos($httpdFileContents,$tryfind) === false) {
  	$httpdFileContents = str_replace($search, $replace, $httpdFileContents, $count);
  	$counts += $count;
	}
	else { // Variables exists - Verify contents
		$search = array(
			'Define APACHE24',
			'Define VERSION_APACHE',
			'Define INSTALL_DIR',
			'Define APACHE_DIR',
			'Define SRVROOT',
		);
		$verify = array(
			'Apache2.4',
			$c_apacheVersion,
			$c_installDir,
			'${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}',
			'${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}',
		);
		$LastLineFound = '';
		for($i = 0 ; $i < count($search) ; $i++) {
			$searchpreg = '~^('.$search[$i].'[ \t]*)(.*)\r$~m';
			$res_preg = preg_match($searchpreg,$httpdFileContents,$matches);
			if($res_preg === false || $res_preg === 0) {
				if(!empty($LastLineFound)){
					$httpdFileContents = str_replace($LastLineFound, $LastLineFound."\r\n".$search[$i].' '.$verify[$i],$httpdFileContents,$count);
					$counts += $count;
				}
			}
			else {
				$LastLineFound = $matches[0];
			if($matches[2] != $verify[$i]) {
					$httpdFileContents = preg_replace($searchpreg,'${1}'.$verify[$i],$httpdFileContents,1,$count);
				$counts += $count;
			}
		}
	}
	}
	//Modify ServerRoot and move it after Define's ServerRoot "j:/wamp/bin/apache/apache2.4.xx"
	if(preg_match('~^ServerRoot[ \t]*"'.$c_installDir.'.*$~m',$httpdFileContents,$matches) > 0) {
		$search = array(
			$matches[0],
			'Define SRVROOT ${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}',
		);
		$replace = array(
			'',
			'Define SRVROOT ${INSTALL_DIR}/bin/apache/apache${VERSION_APACHE}

ServerRoot "${SRVROOT}"
',
		);
		$httpdFileContents = str_replace($search,$replace,$httpdFileContents,$count);
		$counts += $count;
	}

	//Replace all install paths like "c:/wamp(64) by "${INSTALL_DIR}
	$httpdFileContents = str_replace('"'.$c_installDir,'"${INSTALL_DIR}',$httpdFileContents, $count);
	$counts += $count;

	//Check ThreadStackSize
	if(preg_match('~^ThreadStackSize[ \t]+[0-9]+.*\r?$~mi',$httpdFileContents,$matches) === 0) {
		$search = "AcceptFilter https none
";
		$replace = <<< EOF

# The ThreadStackSize directive sets the size of the stack (for autodata)
# of threads which handle client connections and call modules to help process
# those connections. In most cases the operating system default for stack size
# is reasonable, but there are some conditions where it may need to be adjusted.
# Apache httpd may crash when using some third-party modules which use a
# relatively large amount of autodata storage or automatically restart with
# message like: child process 12345 exited with status 3221225725 -- Restarting.
# This type of crash is resolved by setting ThreadStackSize to a value higher
# than the operating system default.
ThreadStackSize 8388608

EOF;
		$httpdFileContents = str_replace($search,$search.$replace,$httpdFileContents,$count);
		$counts += $count;
	}

	if($counts > 0) {
		if(WAMPTRACE_PROCESS) error_log("write ".$c_apacheConfFile." in ".__FILE__." line ". __LINE__."\n",3,WAMPTRACE_FILE);
  	write_file($c_apacheConfFile,$httpdFileContents);
	}

	//Get Apache variables (Define)
	$command = $c_apacheExe." -t -D DUMP_RUN_CFG";
	$output = `$command`;
	if(!empty($output)) {
		if(preg_match_all("~^Define: (.+)=(.+)\r?$~m",$output, $matches) > 0 )
			$c_ApacheDefineVerif = array_combine($matches[1], $matches[2]);
	}

	if($c_ApacheDefineVerif != $c_ApacheDefine) {
		if(defined(WAMPTRACE_PROCESS)) error_log("write ".$c_apacheDefineConf." in ".__FILE__." line ". __LINE__."\n",3,WAMPTRACE_FILE);
		$defineVar = "; Variables defined by Apache - To be used by some PHP scripts.\n\n";
		if(count($c_ApacheDefineVerif) > 0) {
			foreach($c_ApacheDefineVerif as $key => $value)
				$defineVar .= $key.' = "'.$value.'"'."\n";
		}
		else {
			$ApacheDefineError = true;
			$errorTxt = "; Unable to find Apache variables.\n\n; There may be a syntax error in httpd.conf.\n; To be checked by the tool integrated in Wampserver:\n; Right-click -> Tools -> Check httpd.conf syntax.\n\n";
			error_log($errorTxt);
			$defineVar .= "; ".$errorTxt;
			if(WAMPTRACE_PROCESS) error_log("script ".__FILE__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		}
	write_file($c_apacheDefineConf,$defineVar);
	$c_ApacheDefine = @parse_ini_file($c_apacheDefineConf);
	}
}
//***************** End of verify files ***********************
//*************************************************************

?>