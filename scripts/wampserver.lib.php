<?php
//Change 3.2.0
// function write_file
//
//
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

// Write string ($string) into file ($file)
// If $clipboard == true copy contents into the clipoard
// WARNING In case of clipborad copy, file will be deleted unless $delete = false
function write_file($file, $string, $clipboard = false, $delete = true, $mode = 'wb') {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' file='.$file."\n",3,WAMPTRACE_FILE);
	$writeFileOK = true;
	if(is_writable($file) || !file_exists($file)) {
		$nbsize = strlen($string);
		$fp = fopen($file,$mode);
		if($fp !== false) {
			$nbwrite = fwrite($fp,$string);
			fclose($fp);
			if($nbwrite === false) {
				$errorTxt = "**** ERROR while writting file ".$file." ****";
				error_log($errorTxt);
				if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
				$writeFileOK = false;
			}
			else {
				if($nbwrite <> $nbsize) {
					$errorTxt = "**** ERROR ".$nbwrite." bytes written in file ".$file." should have been ".$nbsize." ****";
					error_log($errorTxt);
					if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
					$writeFileOK = false;
				}
				else {
					if(WAMPTRACE_PROCESS) error_log("File ".$file." -+- HAS BEEN WRITTEN ".(($mode == 'ab') ? ' (contents added) ' : '')."-+-\n",3,WAMPTRACE_FILE);
				}
			}
		}
		else {
			$errorTxt = "**** ERROR while open file ".$file.' ****';
			error_log($errorTxt);
			if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
			$writeFileOK = false;
		}
	}
	else {
		$errorTxt = "***** ERROR the file ".$file." is not writable *****";
		error_log($errorTxt);
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		$writeFileOK = false;
	}
	if($clipboard) {
		$command = 'type '.$file.' | clip';
		`$command`;
		if($delete) {
			$command = 'del '.$file;
			`$command`;
		}
	}
	return $writeFileOK;
}


function wampIniSet($iniFile, $params) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$iniFileContents = @file_get_contents($iniFile);
	$count = false;
	foreach ($params as $param => $value) {
		if(preg_match('|^'.$param.'[ \t]*=[ \t]*"?([^"]+)"?\r?$|m',$iniFileContents,$matches) > 0) {
			if($matches[1] !== $value) {
				$iniFileContents = preg_replace('|^'.$param.'[ \t]*=.*|m',$param.' = '.'"'.$value.'"',$iniFileContents);
				$count = true;
			}
		}
		else {
			$iniFileContents = preg_replace('|^'.$param.'[ \t]*=.*|m',$param.' = '.'"'.$value.'"',$iniFileContents);
			$count = true;
		}
	}
	if($count) {
		write_file($iniFile,$iniFileContents);
	}
}

function listDir($dir,$toCheck = '',$racine='') {
	$list = array();
	if(is_dir($dir)) {
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && is_dir($dir.'/'.$file)) {
					if (!empty($toCheck)) {
						if(call_user_func($toCheck,$dir,$file,$racine))
							$list[] = $file;
					}
				}
			}
			closedir($handle);
		}
	}
	else {
		error_log("*** WARNING is_dir(".$dir.") is not a directory");
	}
	return $list;
}

function checkPhpConf($baseDir,$version,$racine) {
	global $wampBinConfFiles, $phpConfFileForApache;
  if(strpos($version,$racine) === 0)
		return (file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles) && file_exists($baseDir.'/'.$version.'/'.$phpConfFileForApache));
	else
		return false;
}

function checkApacheConf($baseDir,$version,$racine) {
	global $wampBinConfFiles;
  if(strpos($version,$racine) === 0)
		return file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles);
	else
		return false;
}

function checkMysqlConf($baseDir,$version,$racine) {
	global $wampBinConfFiles;
  if(strpos($version,$racine) === 0)
		return file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles);
	else
		return false;
}

function checkMariaDBConf($baseDir,$version,$racine) {
  global $wampBinConfFiles;
  if(strpos($version,$racine) === 0)
		return file_exists($baseDir.'/'.$version.'/'.$wampBinConfFiles);
	else
		return false;
}

function linkPhpDllToApacheBin($php_version) {
	global $phpDllToCopy, $c_phpVersionDir, $c_apacheVersionDir, $wampConf, $phpConfFileForApache;
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$errorTxt = '';
	//Create symbolic link or copy dll's files
	clearstatcache();
	foreach ($phpDllToCopy as $dll)
	{
		$target = $c_phpVersionDir.'/php'.$php_version.'/'.$dll;
		$link = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'].'/'.$dll;
		//File or symlink deleted if exists
		if(is_file($link) || is_link($link)) {
			unlink($link);
		}
		//Symlink created if file exists in phpx.y.z directory
		if (is_file($target)) {
			if($wampConf['CreateSymlink'] == 'symlink') {
				if(symlink($target, $link) === false) {
					$errorTxt .= "Error while creating symlink '".$link."' to '".$target."' using php symlink function\n";
				}
			}
			elseif($wampConf['CreateSymlink'] == 'copy') {
				if(copy($target, $link) === false) {
					$errorTxt .= "Error while copy '".$target."' to '".$link."' using php copy() function\n";
				}
			}
		}
	}
	//Create apache/apachex.y.z/bin/php.ini link to phpForApache.ini file of active version of PHP
	$target = $c_phpVersionDir."/php".$php_version."/".$phpConfFileForApache;
	$link = $c_apacheVersionDir."/apache".$wampConf['apacheVersion']."/".$wampConf['apacheExeDir']."/php.ini";
	//php.ini deleted if exists
	if(is_file($link) || is_link($link)) {
		unlink($link);
	}
	if($wampConf['CreateSymlink'] == 'symlink') {
		if(symlink($target, $link) === false) {
			$errorTxt .= "Error while creating symlink '".$link."' to '".$target."' using php symlink function\n";
		}
	}
	elseif($wampConf['CreateSymlink'] == 'copy') {
		if(copy($target, $link) === false) {
			$errorTxt .= "Error while copy '".$target."' to '".$link."' using php copy() function\n";
		}
	}
	if(empty($errortxt)) {
		return true;
	}
	else {
		error_log($errorTxt);
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		return $errorTxt;
	}
}

function CheckSymlink($php_version) {
	global $phpDllToCopy, $c_phpVersionDir, $c_apacheVersionDir, $wampConf, $phpConfFileForApache;
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$errorTxt = '';
	//Check if necessary symlinks exists
	clearstatcache();
	foreach ($phpDllToCopy as $dll)
	{
		$target = $c_phpVersionDir.'/php'.$php_version.'/'.$dll;
		$link = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'].'/'.$dll;
		//Check Symlink if file exists in phpx.y.z directory
		if(is_file($target)) {
			if(is_link($link)) {
				$real_link = str_replace("\\", "/",readlink($link));
				if(strtolower($real_link) != strtolower($target)) {
					$errorTxt .= "Symbolic link ".$link."\n      is: ".$real_link."\nshould be ".$target."\n\n";
				}
			}
			elseif(is_file($link)) {
				if($wampConf['CreateSymlink'] != 'copy')
					$errorTxt .= "File ".$link." exists.\nShould be a symbolic link\n";
			}
			else {
				$errorTxt .= "Symbolic link or file ".$link." does not exist\n";
			}
		}
	}
	//Verify apache/apachex.y.z/bin/php.ini link to phpForApache.ini file of active version of PHP
	$target = $c_phpVersionDir."/php".$php_version."/".$phpConfFileForApache;
	$link = $c_apacheVersionDir."/apache".$wampConf['apacheVersion']."/".$wampConf['apacheExeDir']."/php.ini";
	if(is_link($link)) {
		$real_link = str_replace("\\", "/",readlink($link));
		if(strtolower($real_link) != strtolower($target)) {
			$errorTxt .= "Symbolic link: ".$link."\nTarget is       : ".$real_link."\nTarget should be: ".$target."\n";
		}
	}
	elseif(is_file($link)) {
		if($wampConf['CreateSymlink'] != 'copy')
			$errorTxt .= "File ".$link." exists.\nShould be a symbolic link\n";
	}
	else {
		$errorTxt .= "Symbolic link or file ".$link." does not exist\n";
	}
	if(empty($errortxt)) {
		return true;
	}
	else {
		error_log($errorTxt);
		if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		return $errorTxt;
	}
}

function switchPhpVersion($newPhpVersion) {
	require 'config.inc.php';
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);

	//loading the configuration file of the new version
	require $c_phpVersionDir.'/php'.$newPhpVersion.'/'.$wampBinConfFiles;

	//the httpd.conf texts depending on the version of apache is determined
	$apacheVersion = $wampConf['apacheVersion'];
	while (!isset($phpConf['apache'][$apacheVersion]) && $apacheVersion != '')
	{
		$pos = strrpos($apacheVersion,'.');
		$apacheVersion = substr($apacheVersion,0,$pos);

	}

	// modifying the conf apache file
	$httpdContents = file($c_apacheConfFile);
	$newHttpdContents = '';
	$change = false;
	foreach ($httpdContents as $line)
	{
		if (strpos($line,'LoadModule') !== false && (strpos($line,'php5_module') !== false || strpos($line,'php7_module') !== false))
		{
			$c_phpVersionDirA = (substr($c_apacheVersion,0,3) == '2.4') ? str_replace($c_installDir, '${INSTALL_DIR}',$c_phpVersionDir) : $c_phpVersionDir ;
			$newline = 'LoadModule '.$phpConf['apache'][$apacheVersion]['LoadModuleName'].' "'.$c_phpVersionDirA.'/php'.$newPhpVersion.'/'.$phpConf['apache'][$apacheVersion]['LoadModuleFile'].'"'."\r\n";
			if($line !== $newline) {
				$newHttpdContents .= $newline;
				$change = true;
			}
		}
    elseif (!empty($phpConf['apache'][$apacheVersion]['AddModule']) && strstr($line,'AddModule') && strstr($line,'php')) {
    	$newHttpdContents .= 'AddModule '.$phpConf['apache'][$apacheVersion]['AddModule']."\r\n";
    	$change = true;
    }
		else
			$newHttpdContents .= $line;
	}
	if($change) {
		$fileput = file_put_contents($c_apacheConfFile,$newHttpdContents);
		if($fileput === false) {
			error_log("Error file_put_contents for file ".$c_apacheConfFile);
		}
		else {
			if(WAMPTRACE_PROCESS) error_log("File ".$c_apacheConfFile." -+- HAS BEEN REWRITTEN -+- (file_put_contents)\n",3,WAMPTRACE_FILE);
		}
	}

	//modifying the conf of WampServer
	$wampIniNewContents['phpIniDir'] = $phpConf['phpIniDir'];
	$wampIniNewContents['phpExeDir'] = $phpConf['phpExeDir'];
	$wampIniNewContents['phpConfFile'] = $phpConf['phpConfFile'];
	$wampIniNewContents['phpVersion'] = $newPhpVersion;
	wampIniSet($configurationFile, $wampIniNewContents);

	//Create symbolic link to php dll's and to phpForApache.ini of new version
	linkPhpDllToApacheBin($newPhpVersion);

}

// Create parameter in $configurationFile file
// $name = parameter name -- $value = parameter value
// $section = name of the section to add parameter after
function createWampConfParam($name, $value, $section, $configurationFile) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	$wampConfFileContents = @file_get_contents($configurationFile) or die ($configurationFile."file not found");
	$addTxt = $name.' = "'.$value.'"';
	$wampConfFileContents = str_replace($section,$section."\r\n".$addTxt,$wampConfFileContents);
	write_file($configurationFile,$wampConfFileContents);
}

//**** Functions to check if IP is valid and/or in a range ****
/*
 * ip_in_range.php - Function to determine if an IP is located in a
 * specific range as specified via several alternative formats.
 *
 * Network ranges can be specified as:
 * 1. Wildcard format:     1.2.3.*
 * 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
 * 3. Start-End IP format: 1.2.3.0-1.2.3.255
 *
 * Return value BOOLEAN : ip_in_range($ip, $range);
 *
 * Copyright 2008: Paul Gregg <pgregg@pgregg.com>
 * 10 January 2008
 * Version: 1.2
 *
 * Source website: http://www.pgregg.com/projects/php/ip_in_range/
 * Version 1.2
 * Please do not remove this header, or source attibution from this file.
 */

// decbin32
// In order to simplify working with IP addresses (in binary) and their
// netmasks, it is easier to ensure that the binary strings are padded
// with zeros out to 32 characters - IP addresses are 32 bit numbers
function decbin32 ($dec) {
  return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
}

// ip_in_range
// This function takes 2 arguments, an IP address and a "range" in several
// different formats.
// Network ranges can be specified as:
// 1. Wildcard format:     1.2.3.*
// 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
// 3. Start-End IP format: 1.2.3.0-1.2.3.255
// The function will return true if the supplied IP is within the range.
// Note little validation is done on the range inputs - it expects you to
// use one of the above 3 formats.
function ip_in_range($ip, $range) {
  if (strpos($range, '/') !== false) {
    // $range is in IP/NETMASK format
    list($range, $netmask) = explode('/', $range, 2);
    if (strpos($netmask, '.') !== false) {
      // $netmask is a 255.255.0.0 format
      $netmask = str_replace('*', '0', $netmask);
      $netmask_dec = ip2long($netmask);
      return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
    } else {
      // $netmask is a CIDR size block
      // fix the range argument
      $x = explode('.', $range);
      while(count($x)<4) $x[] = '0';
      list($a,$b,$c,$d) = $x;
      $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
      $range_dec = ip2long($range);
      $ip_dec = ip2long($ip);

      # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
      #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

      # Strategy 2 - Use math to create it
      $wildcard_dec = pow(2, (32-$netmask)) - 1;
      $netmask_dec = ~ $wildcard_dec;

      return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
    }
  } else {
    // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
    if (strpos($range, '*') !==false) { // a.b.*.* format
      // Just convert to A-B format by setting * to 0 for A and 255 for B
      $lower = str_replace('*', '0', $range);
      $upper = str_replace('*', '255', $range);
      $range = "$lower-$upper";
    }

    if (strpos($range, '-')!==false) { // A-B format
      list($lower, $upper) = explode('-', $range, 2);
      $lower_dec = (float)sprintf("%u",ip2long($lower));
      $upper_dec = (float)sprintf("%u",ip2long($upper));
      $ip_dec = (float)sprintf("%u",ip2long($ip));
      return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
    }

    error_log('Range argument is not in 1.2.3.4/24 or 1.2.3.4/255.255.255.0 format');
    return false;
  }
}
function check_IP($ip, $local_ip = true, $all_local = false) {
	global $wampConf;
	$valid = false;
	if(preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]|[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9][0-9]?|[0-9])$/', $ip) == 0)
		return false;
	if($local_ip) {
		$range = '127.0.0.1-127.255.255.255';
		if(ip_in_range($ip,$range))
			$valid = true;
		if($wampConf['VhostAllLocalIp'] == 'on')
			$all_local = true;
		if($all_local && !$valid) {
			$ranges = array('10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16');
			foreach($ranges as $value) {
				if(ip_in_range($ip, $value)) {
					$valid = true;
					break;
				}
			}
		}
	}
	return $valid;
}
//Function to check if it is Apache variable
function is_apache_var($a_var) {
	global $c_ApacheDefine;
	if(preg_match('~\${(.+)}~',$a_var,$var) > 0) {
		if(array_key_exists($var[1],$c_ApacheDefine))
			return true;
	}
  return false;
}
//Function to replace Apache variable name by it contents
function replace_apache_var($chemin) {
	global $c_ApacheDefine,$c_apacheService;
	if(preg_match('~\${(.+)}~',$chemin,$var) > 0) {
		if(array_key_exists($var[1],$c_ApacheDefine)) {
			$chemin = str_replace($var[0],trim($c_ApacheDefine[$var[1]]),$chemin);
		}
		else {
			$errorTxt = "Apache variable '".$var[0]."' is not defined.\n\tMay be there is syntax error in httpd.conf\n\tCheck it by right-click Wampmanager tray icon -> Tools -> Check httpd.conf syntax.\n\tMay be Apache service '".$c_apacheService."' is not started.\n\tCheck it by right-click Wampmanager tray icon -> Tools -> Check state of services\n";
			error_log($errorTxt);
			if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n*** ".$errorTxt."\n",3,WAMPTRACE_FILE);
		}
	}
	return $chemin;
}
// Function to retrieve Apache Listen ports
function listen_ports() {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	global $c_apacheConfFile;
	$httpdFileContents = file_get_contents($c_apacheConfFile);
	preg_match_all("~^Listen[ \t]+.*:(\S*)\s*$~m",$httpdFileContents, $matches);
	$c_listenPort = array_values(array_map('replace_apache_var',array_unique($matches[1])));
	sort($c_listenPort);
	return $c_listenPort;
}

// Function to check if VirtualHost exist and are valid
function check_virtualhost($check_files_only = false) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__."\n",3,WAMPTRACE_FILE);
	global $wampConf, $c_apacheConfFile, $c_apacheVhostConfFile, $c_DefaultPort, $c_UsedPort, $wwwDir;
	clearstatcache();
	$virtualHost = array(
		'include_vhosts' => true,
		'vhosts_exist' => true,
		'nb_Server' => 0,
		'Server' => array(),
		'DocRootNotwww' => array(),
		'ServerName' => array(),
		'ServerNameDev' => array(),
		'ServerNameIp' => array(),
		'ServerNamePort' => array(),
		'ServerNameValid' => array(),
		'ServerNameQuoted' => array(),
		'ServerNameIDNA' => array(),
		'ServerNameUTF8' => array(),
		'ServerNameIpValid' => array(),
		'ServerNamePortValid' => array(),
		'ServerNamePortListen' => array(),
		'ServerNamePortApacheVar' => array(),
		'FirstServerName' => '',
		'nb_Virtual' => 0,
		'nb_Virtual_Port' => 0,
		'virtual_port' => array(),
		'virtual_ip' => array(),
		'nb_Document' => 0,
		'documentPath' => array(),
		'documentPathValid' => array(),
		'document' => true,
		'nb_Directory' => 0,
		'nb_End_Directory' => 0,
		'directoryPath' => array(),
		'directoryPathValid' => array(),
		'directory' => true,
		'port_number' => true,
		'nb_duplicate' => 0,
		'duplicate' => array(),
		'nb_duplicateIp' => 0,
		'duplicateIp' => array(),
		'nb_NotListenPort' => 0,
		'port_listen' => true,
		'NotListenPort' => array(),
	);
	$httpConfFileContents = file_get_contents($c_apacheConfFile);
	//is Include conf/extra/httpd-vhosts.conf uncommented?
	if(preg_match("~^[ \t]*#[ \t]*Include[ \t]+conf/extra/httpd-vhosts.conf.*$~m",$httpConfFileContents) > 0) {
		$virtualHost['include_vhosts'] = false;
		return $virtualHost;
	}

	$virtualHost['vhosts_file'] = $c_apacheVhostConfFile;
	if(!file_exists($virtualHost['vhosts_file'])) {
		$virtualHost['vhosts_exist'] = false;
		return $virtualHost;
	}
	if($check_files_only) {
		return $virtualHost;
	}

	$myVhostsContents = file_get_contents($virtualHost['vhosts_file']);
	// Extract values of ServerName (without # at the beginning of the line)
	$nb_Server = preg_match_all("/^(?![ \t]*#).*ServerName[ \t]+(.*?\r?)$/m", $myVhostsContents, $Server_matches);
	// Extract values of <VirtualHost *:xx> or <VirtualHost ip:xx> port number
	$nb_Virtual = preg_match_all("/^(?![ \t]*#).*<VirtualHost[ \t]+(?:\*|([0-9.]*)):(.*)>\R/m", $myVhostsContents, $Virtual_matches);
	// Extract values of DocumentRoot path
	$nb_Document = preg_match_all("/^(?![ \t]*#).*DocumentRoot[ \t]+(.*?\r?)$/m", $myVhostsContents, $Document_matches);
	// Count number of <Directory that has to match the number of ServerName
	$nb_Directory = preg_match_all("/^(?![ \t]*#).*<Directory[ \t]+(.*)>\R/m", $myVhostsContents, $Dir_matches);
	$nb_End_Directory = preg_match_all("~^(?![ \t]*#).*</Directory.*$~m", $myVhostsContents, $end_Dir_matches);
	$server_name = array();
	if($nb_Server == 0) {
		$virtualHost['nb_server'] = 0;
		return $virtualHost;
	}
	$virtualHost['nb_Server'] = $nb_Server;
	$virtualHost['nb_Virtual'] = $nb_Virtual;
	$virtualHost['nb_Virtual_Port'] = count($Virtual_matches[2]);
	$virtualHost['nb_Document'] = $nb_Document;
	$virtualHost['nb_Directory'] = $nb_Directory;
	$virtualHost['nb_End_Directory'] = $nb_End_Directory;
	//Check validity of port number
	$virtualHost['virtual_port'] = array_merge($Virtual_matches[2]);
	$virtualHost['virtual_ip'] = array_merge($Virtual_matches[1]);
	for($i = 0 ; $i < count($Virtual_matches[1]) ; $i++) {
		$value = trim($Server_matches[1][$i]);
		$port_ori = $virtualHost['virtual_port'][$i];
		$virtualHost['virtual_port'][$i] = replace_apache_var($virtualHost['virtual_port'][$i]);
		$port = $virtualHost['virtual_port'][$i];
		$virtualHost['Server'][$i]['Port'] = $port;
		if($port <> '80')
			$value .= ':'.$port;
		$virtualHost['ServerNamePort'][$value] = $port;
		$virtualHost['ServerNamePortValid'][$value]	= true;
		$virtualHost['ServerNamePortListen'][$value]	= true;
		$virtualHost['ServerNamePortApacheVar'][$value] = true;
		if($port_ori <> $c_DefaultPort  && $port_ori <> $c_UsedPort && !is_apache_var($port_ori))
			$virtualHost['ServerNamePortApacheVar'][$value] = false;
		if(empty($port) || !is_numeric($port) || $port < 80 || $port > 65535) {
			$virtualHost['ServerNamePortValid'][$value]	= false;
			$virtualHost['port_number'] = false;
		}
	}

	//Check validity of DocumentRoot
	for($i = 0 ; $i < $nb_Document ; $i++) {
		$chemin = trim($Document_matches[1][$i], " \t\n\r\0\x0B\"");
		$chemin = replace_apache_var($chemin);
		$virtualHost['Server'][$i]['DocumentRoot'] = $chemin;
		$virtualHost['documentPath'][$i] = $chemin;
		if((!file_exists($chemin) || !is_dir($chemin)) && $wampConf['NotCheckVirtualHost'] == 'off') {
			$virtualHost['documentPathValid'][$chemin] = false;
			$virtualHost['document'] = false;
		}
		else
			$virtualHost['documentPathValid'][$chemin] = true;
	}

	//Check validity of Directory path
	for($i = 0 ; $i < $nb_Directory ; $i++) {
		$chemin = trim($Dir_matches[1][$i], " \t\n\r\0\x0B\"");
		$chemin = replace_apache_var($chemin);
		$virtualHost['directoryPath'][$i] = $chemin;
		$virtualHost['Server'][$i]['directoryPath'] = $chemin;
		if((!file_exists($chemin) || !is_dir($chemin)) && $wampConf['NotCheckVirtualHost'] == 'off') {
			$virtualHost['directoryPathValid'][$chemin] = false;
			$virtualHost['directory'] = false;
		}
		else
			$virtualHost['directoryPathValid'][$chemin] = true;
	}

	//Check validity of ServerName
	$TempServerName = array();
	$TempServerIp = array();
	for($i = 0 ; $i < $nb_Server ; $i++) {
		$value = trim($Server_matches[1][$i]);
		$virtualHost['Server'][$i]['ServerName'] = $value;
		$nameToCheck = $value;
		//First server name without :port if not 80
		if($i == 0)
			$virtualHost['FirstServerName'] = $value;
		if($virtualHost['virtual_port'][$i] <> '80') {
			$value .= ':'.$virtualHost['virtual_port'][$i];
		}
		$TempServerName[] = $value;
		$virtualHost['ServerName'][$value] = $value;
		$virtualHost['ServerNameDev'][$value] = false;
		$virtualHost['ServerNameIp'][$value] = false;
		$virtualHost['ServerNameIpValid'][$value] = false;

		//Validity of ServerName (Like domain name)
		// IDNA (Punycode) /^xn--[a-zA-Z0-9\-\.]+$/
		// Non IDNA  /^[A-Za-z]+([-.](?![-.])|[A-Za-z0-9]){1,60}[A-Za-z0-9]$/
		if(
			(preg_match('/^xn--[a-zA-Z0-9\-\.]+$/',$nameToCheck,$matchesIDNA) == 0)
			&& (preg_match('/^
			(?=.*[A-Za-z])  # at least one letter somewhere
		  [A-Za-z0-9]+ 		# letter or number in first place
			([-.](?![-.])		#  a . or - not followed by . or -
						|					#   or
			[A-Za-z0-9]			#  a letter or a number
			){0,60}					# this, repeated from 0 to 60 times - at least two characters
			[A-Za-z0-9]			# letter or number at the end
			$/x',$nameToCheck) == 0)
			&& $wampConf['NotCheckVirtualHost'] == 'off') {
			$virtualHost['ServerNameValid'][$value] = false;
			$virtualHost['ServerNameQuoted'][$value] = false;
			if(strpos($value,'"') !== false) {
				$virtualHost['ServerNameQuoted'][$value] = true;
				$virtualHost['ServerNameIDNA'][$value] = false;
				$virtualHost['ServerNameUTF8'][$value] = $value;
			}
		}
		elseif(strpos($value,"dummy-host") !== false || strpos($value,"example.com") !== false) {
			$virtualHost['ServerNameValid'][$value] = 'dummy';
		}
		else {
			$virtualHost['ServerNameValid'][$value] = true;
			$virtualHost['ServerNameQuoted'][$value] = false;
			if(empty($matchesIDNA[0])) {
				$virtualHost['ServerNameIDNA'][$value] = false;
				$virtualHost['ServerNameUTF8'][$value] = $value;
			}
			else {
				$virtualHost['ServerNameIDNA'][$value] = true;
				$virtualHost['ServerNameUTF8'][$value] = idn_to_utf8($value,IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46);
			}
			//Check optionnal IP
			if(!empty($virtualHost['virtual_ip'][$i])) {
				$Virtual_IP = $virtualHost['virtual_ip'][$i];
				$virtualHost['Server'][$i]['ip'] = $Virtual_IP;
				$virtualHost['ServerNameIp'][$value] = $Virtual_IP;
				if(check_IP($Virtual_IP)) {
					$virtualHost['ServerNameIpValid'][$value] = true;
					$TempServerIp[] = $Virtual_IP;
				}
			}
			else {
				$virtualHost['Server'][$i]['ip'] = '';
			}
		}
	} //End for

	//Check if tld is .dev
	if($wampConf['NotVerifyTLD'] == 'off') {
		foreach($virtualHost['ServerNameDev'] as $keydev => &$valuedev) {
			$tld = substr($keydev,-4);
			if($tld !== false && (strtolower($tld) == '.dev'))
				$valuedev = true;
		}
	}

	//Check if duplicate ServerName
	if($wampConf['NotCheckDuplicate'] == 'off' && $wampConf['NotCheckVirtualHost'] == 'off') {
		$array_unique = array_unique($TempServerName);
		if (count($TempServerName) - count($array_unique) != 0 ){
			$virtualHost['nb_duplicate'] = count($TempServerName) - count($array_unique);
    	for ($i=0; $i < count($TempServerName); $i++) {
    		if (!array_key_exists($i, $array_unique))
      		$virtualHost['duplicate'][] = $TempServerName[$i];
    	}
		}
		//Check duplicate Ip
		$array_unique = array_unique($TempServerIp);
		if (count($TempServerIp) - count($array_unique) != 0 ){
			$virtualHost['nb_duplicateIp'] = count($TempServerIp) - count($array_unique);
    	for ($i=0; $i < count($TempServerIp); $i++) {
    		if (!array_key_exists($i, $array_unique))
      		$virtualHost['duplicateIp'][] = $TempServerIp[$i];
    	}
		}
	}

	//Check VirtualHost port not Listen port in httpd.conf
	$diffVL = array_diff(array_values(array_unique(array_values($virtualHost['ServerNamePort']))),listen_ports());
	if(count($diffVL) > 0) {
		$virtualHost['port_listen'] = false;
		$virtualHost['nb_NotListenPort'] = count($diffVL);
	foreach($diffVL as $value)
		$virtualHost['NotListenPort'] += array_fill_keys(array_keys($virtualHost['ServerNamePort'],$value),$value);
	foreach($virtualHost['NotListenPort'] as $key => $value)
		$virtualHost['ServerNamePortListen'][$key] = $value;
	}
	//Check if some VirtualHost use $wwwDir DocumentRoot reserved for localhost
	foreach($virtualHost['Server'] as $key => $value) {
		$SerName = $virtualHost['Server'][$key]['ServerName'];
		$DocRoot = $virtualHost['Server'][$key]['DocumentRoot'];
		if($value['Port'] != '80')
			$SerName .= ':'.$value['Port'];
		$virtualHost['DocRootNotwww'][$SerName] = true;
		if(strtolower($DocRoot) == strtolower($wwwDir) && stripos($SerName,'localhost') === false) {
			$virtualHost['DocRootNotwww'][$SerName] = false;
		}
	}

	if($wampConf['NotCheckVirtualHost'] == 'on') {
		$virtualHost['nb_Server'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_Document'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_Directory'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_End_Directory'] = $virtualHost['nb_Virtual'];
		$virtualHost['nb_duplicateIp'] = 0;
		$virtualHost['nb_duplicate'] = 0;
		$virtualHost['port_number'] = true;
		$virtualHost['port_listen'] = true;
		$virtualHost['nb_NotListenPort'] = 0;
	}
	//error_log(print_r($virtualHost, true));
	return $virtualHost;
}

// List all versions PHP, MySQL, MariaDB, Apache into array
// with USED or CLI added to version number
// like  5.6.40CLI - 7.3.10USED - 2.4.41USED - 5.7.27USED
function ListAllVersions() {
	global $c_phpVersionDir, $c_phpVersion,$c_phpCliVersion,$phpVersionList,
		$c_apacheVersionDir,$c_apacheVersion, $apacheVersionList,
		$c_mysqlVersionDir,$c_mysqlVersion, $mysqlVersionList,
		$c_mariadbVersionDir,$c_mariadbVersion, $mariadbVersionList,
		$wampConf;
	$Versions = array(
		'apache' => array(),
		'php' => array(),
		'mysql' => array(),
		'mariadb' => array(),
	);
	//Apache versions
	if(!isset($apacheVersionList)) {
		$apacheVersionList = listDir($c_apacheVersionDir,'checkApacheConf','apache');
		array_walk($apacheVersionList,function(&$value, $key){$value = str_replace('apache','',$value);});
	}
	foreach ($apacheVersionList as $oneApacheVersion) {
  	if($oneApacheVersion == $c_apacheVersion)
  		$oneApacheVersion .= 'USED';
  	$Versions['apache'][] = $oneApacheVersion;
	}
	//PHP versions
	if(!isset($phpVersionList)) {
		//error_log("phpVersionList was not set");
		$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php');
		array_walk($phpVersionList,function(&$value, $key){$value = str_replace('php','',$value);});
	}
	foreach ($phpVersionList as $onePhpVersion) {
		if($onePhpVersion == $c_phpVersion)
			$onePhpVersion .= 'USED';
		if($onePhpVersion == $c_phpCliVersion)
			$onePhpVersion .= 'CLI';
		$Versions['php'][] = $onePhpVersion;
	}
	//MySQL versions
	if(!isset($mysqlVersionList)) {
		$mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql');
		array_walk($mysqlVersionList,function(&$value, $key){$value = str_replace('mysql','',$value);});
	}
	foreach ($mysqlVersionList as $oneMysqlVersion) {
  	if($wampConf['SupportMySQL'] == 'on' && $oneMysqlVersion == $c_mysqlVersion)
  		$oneMysqlVersion .= 'USED';
  	$Versions['mysql'][] = $oneMysqlVersion;
	}
	//MariaDB versions
	if(!isset($mariadbVersionList)) {
		$mariadbVersionList = listDir($c_mariadbVersionDir,'checkMariaDBConf','mariadb');
		array_walk($mariadbVersionList,function(&$value, $key){$value = str_replace('mariadb','',$value);});
	}
	foreach ($mariadbVersionList as $oneMariadbVersion) {
  	if($wampConf['SupportMariaDB'] == 'on' && $oneMariadbVersion == $c_mariadbVersion)
  		$oneMariadbVersion .= 'USED';
  	$Versions['mariadb'][] = $oneMariadbVersion;
	}
	return $Versions;
}

// Callback function must exist and return true or false
// False to delete array item - True to not delete
function array_filter_recursive($array, $callback) {
	foreach ($array as $key => &$value) { // Warning, $value is by reference
		if (is_array($value))
			$value = array_filter_recursive($value, $callback);
		elseif (!$callback($value)) unset($array[$key]);
	}
	unset($value); // Suppress the reference
	return $array;
}

// Get content of file and set lines end to DOS (CR/LF) if needed
function file_get_contents_dos($file, $retour = true) {
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' - '.$file."\n",3,WAMPTRACE_FILE);
	$check_DOS = @file_get_contents($file) or die ($file."file not found");
	//Check if there is \n without previous \r
	if(preg_match("/(?<!\r)\n/",$check_DOS) > 0) {
		$count = 0;
		$check_DOS = preg_replace(array('/\r\n?/','/\n/'),array("\n","\r\n"), $check_DOS, -1, $count);
		if($count > 0) {
			write_file($file,$check_DOS);
		}
	}
	if($retour) return $check_DOS;
}

// Clean file contents
function clean_file_contents($contents, $twoToNone = array(2,0), $all_spaces = false, $save=false, $file='') {
	global $clean_count;
	if(WAMPTRACE_PROCESS) error_log("function ".__FUNCTION__.' '.$file."\n",3,WAMPTRACE_FILE);
	$clean_count = false;
	if($all_spaces) {
		//more than one space into one space
		$contents = preg_replace("~[ \t]{2,}~",' ',$contents,-1, $count);
		if($count > 0) $clean_count = true;
	}
	//suppress spaces or tabs at the end of lines
	$contents = preg_replace('~[ \t]+(\r?)$~m',"$1",$contents, -1, $count);
	if($count > 0) $clean_count = true;
	//suppress more than $twoToNone[0] empty line into $twoToNone[1] empty lines
	// For Unix, Windows, Mac OS X & old Mac OS Classic
	/* "/^(?:[\t ]*(?>\r?\n|\r)){2,}/m" */
	// For Unix, Windows & Mac OS X (Without old Mac OS Classic)
	// "/^(?:[\t\r ]*\n){2,}/m"
	$contents = preg_replace("/^(?:[\t\r ]*\n){".$twoToNone[0].",}/m",str_repeat("\r\n",$twoToNone[1]),$contents,-1, $count);
	if($count > 0) $clean_count = true;

	if($save && $clean_count) {
		write_file($file,$contents);
	}
	return $contents;
}

//Check alias and paths in httpd-autoindex.conf
// Alias /icons/ "c:/Apache24/icons/" => Alias /icons/ "icons/"
// <Directory "c:/Apache24/icons"> => <Directory "icons">
// Don't modify if there is ${SRVROOT} variable (Apache 2.4.35)
function check_autoindex() {
	global $c_apacheAutoIndexConfFile;
	$autoindexContents = @file_get_contents($c_apacheAutoIndexConfFile) or die ("httpd-autoindex.conf file not found");
	if(strpos($autoindexContents, '${SRVROOT}') === false) {
		$autoindexContents = preg_replace("~^(Alias /icons/) (\".+icons/\")\r?$~m","$1 ".'"icons/"',$autoindexContents,1,$count1);
		$autoindexContents = preg_replace("~^(<Directory) (\".+icons\")>\r?$~m","$1 ".'"icons">',$autoindexContents,1,$count2);

		if($count1 == 1 || $count2 == 1) {
			write_file($c_apacheAutoIndexConfFile,$autoindexContents);
		}
	}
}

//Check if a folder exists then create it if not
function checkDir($dir) {
	$message = '';
	if(!file_exists($dir)) {
		if(mkdir($dir) === false) {
			$message = 'Can not create the '.$dir.' folder';
			error_log($message);
			return $message;
		}
	}
	elseif(!is_dir($dir)) {
		if(unlink($dir) === false) {
			$message = 'Can not delete the '.$dir.' file';
			error_log($message);
			return $message;
		}
		else {
			if(mkdir($dir) === false) {
				$message = 'Can not create the '.$dir.' folder';
				error_log($message);
				return $message;
			}
		}
	}
	if(!is_writable($dir)) {
		$message = 'The '.$dir.' folder is not writable';
		error_log($message);
		return $message;
	}
	return 'OK';
}

//Return error_reporting from integer into string
function errorLevel($error_number) {
	$error_description = $error_comment = array();
	//The ampersand "&" are doubled into strings to be displayed and not to be considered as a key prefix by Aestran Tray Menu.
	$error_codes = array(
	E_ALL => array('str' => "E_ALL", 'comment' => "Development value^Show all errors, warnings and notices including coding standards."),	//32767 - Development value
	(E_ALL & ~E_ERROR) => array('str' => "E_ALL && ~E_ERROR", 'comment' =>'Show all errors, except for fatal run-time errors'), //32766
	(E_ALL & ~E_WARNING)	=> array('str' => "E_ALL && ~E_WARNING", 'comment' => 'Show all errors, except for warnings'), //32765
	(E_ALL & ~E_NOTICE) => array('str' => "E_ALL && ~E_NOTICE",	'comment' => 'Show all errors, except for notices'), //32759
	(E_ALL & ~E_NOTICE & ~E_STRICT)	=> array('str' => "E_ALL && ~E_NOTICE && ~E_STRICT", 'comment' =>'Show all errors, except for notices and coding standards warnings'), //30711
	(E_ALL & ~E_DEPRECATED & ~E_STRICT)	=> array('str' => "E_ALL && ~E_DEPRECATED && ~E_STRICT", 'comment' =>'Production value^Show all errors, except for notices .'), // 22527
	(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED) => array('str' => "E_ALL && ~E_NOTICE && ~E_STRICT && ~E_DEPRECATED", 'comment' => 'Default value^Show all errors, except for notices and coding standards warnings and code that will not work in future versions of PHP'), // 22519 Default value
	E_USER_DEPRECATED => array('str' => "E_USER_DEPRECATED", 'comment' => 'user-generated deprecation warnings'), //16384
	E_DEPRECATED => array('str' => "E_DEPRECATED", 'comment' => 'warn about code that will not work in future versions of PHP'), // 8192
	(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR) => array('str' => 'E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR', 'comment' => 'Show only errors'), //4177
	E_RECOVERABLE_ERROR => array('str' => "E_RECOVERABLE_ERROR", 'comment' => 'almost fatal run-time errors'),// 4096
	E_STRICT => array('str' => "E_STRICT", 'comment' => 'run-time notices, enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code'), // 2048
	E_USER_NOTICE => array('str' => "E_USER_NOTICE", 'comment' => 'user-generated notice message'), // 1024
	E_USER_WARNING => array('str' => "E_USER_WARNING", 'comment' => 'user-generated warning message'), // 512
	E_USER_ERROR => array('str' => "E_USER_ERROR", 'comment' => 'user-generated error message'), // 256
	E_COMPILE_WARNING => array('str' => "E_COMPILE_WARNING", 'comment' => 'compile-time warnings (non-fatal errors)'), // 128
	E_COMPILE_ERROR => array('str' => "E_COMPILE_ERROR", 'comment' => 'fatal compile-time errors'), // 64
	E_CORE_WARNING => array('str' => "E_CORE_WARNING", 'comment' => 'warnings (non-fatal errors) that occur during PHP\'s initial startup'), // 32
	E_CORE_ERROR => array('str' => "E_CORE_ERROR", 'comment' => 'fatal errors that occur during PHP\'s initial startup'), // 16
	E_NOTICE => array('str' => "E_NOTICE", 'comment' => 'run-time notices (these are warnings which often result from a bug in your code, but it\'s possible that it was intentional (e.g., using an uninitialized variable and relying on the fact it is automatically initialized to an empty string)'), // 8
	E_PARSE => array('str' => "E_PARSE", 'comment' => 'compile-time parse errors'), // 4
	E_WARNING => array('str' => "E_WARNING", 'comment' => 'run-time warnings (non-fatal errors)'), // 2
	E_ERROR => array('str' => "E_ERROR", 'comment' => 'fatal run-time errors'), // 1
	);
	$i = 0;
	foreach( $error_codes as $number => $description ) {
 		if (($number & $error_number) >= $number ) {
  		$error_description[$i]['str'] = $description['str'];
  		$error_description[$i]['comment'] = $description['comment'];
  		$error_number -= $number;
  		$i++;
 		}
	}
	return $error_description;
}

// Wrap texte into multi lines for Aestran Menu's
function menu_multi_lines($texte, $limit = 70) {
	$ConfTextInfo = '';
	$lines_report = explode('^',wordwrap($texte,$limit,'^'));
	foreach($lines_report as $value) {
 		$ConfTextInfo .= 'Type: item; Caption: "'.$value.'"; Action: multi; Actions: none
';
	}
	return $ConfTextInfo;
}

// Function test of IPv6 support
function test_IPv6() {
	if (extension_loaded('sockets')) {
		//Create socket IPv6
		$socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
		if($socket === false) {
			$errorcode = socket_last_error() ;
			$errormsg = socket_strerror($errorcode);
			//echo "<p>Error socket IPv6: ".$errormsg."</p>\n" ;
			error_log("For information only: IPv6 not supported");
			return false;
		}
		else {
			//echo "<p>IPv6 supported</p>\n" ;
			socket_close($socket);
			error_log("For information only: IPv6 supported");
			return true;
		}
	}
	else {
		error_log("Extension PHP 'sockets' not loaded, cannot check support of IPv6");
		return false;
	}
}

?>
