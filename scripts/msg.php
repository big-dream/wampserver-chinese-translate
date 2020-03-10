<?php
//Update 3.2.0
// Check Visual C++ 2019 (PHP 7.4)
// Check Thread Safe for all PHP versions
// Check BINARY_PATH_NAME all services

$msgId = $_SERVER['argv'][1];
$doReport = false;
$iw = 2;while(!empty($_SERVER['argv'][$iw])){if($_SERVER['argv'][$iw] == 'doreport') $doReport = true;$iw++;};
$nb_arg = $_SERVER['argc'] - 1;
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

if(is_numeric($msgId) && $msgId > 0 && $msgId < 17) {
	$msgExtName = '';
	if($nb_arg >= 2)
		$msgExtName = base64_decode($_SERVER['argv'][2]);
	$msgExplain = '';
	if($nb_arg >= 3)
		$msgExplain = base64_decode($_SERVER['argv'][3]);

	$message = array(
	1 => "This PHP version ".$msgExtName." doesn't seem to be compatible with your actual Apache Version.

".$msgExplain,
	2 => "This Apache version ".$msgExtName." doesn't seem to be compatible with your actual PHP Version.

".$msgExplain,
	3 => "The '".$msgExtName.".dll' extension file exists but there is no 'extension=".$msgExtName.".dll' line in php.ini.",
	4 => "The line 'extension=".$msgExtName.".dll' exists in php.ini file but there is no ".$msgExtName.".dll' file in ext/ directory.",
	5 => "The '".$msgExtName."' extension cannot be loaded by 'extension=".$msgExtName.".dll' in php.ini. Must be loaded by 'zend_extension='.",
	6 => $msgExtName."

cannot be changed by the Wampmanager menus.
	".$msgExplain,
	7 => "There is 'LoadModule ".$msgExtName." modules/".$msgExplain."' line in httpd.conf file
but there no '".$msgExplain."' file in apachex.y.z/modules/ directory.",
  8 => "There is '".$msgExplain."' file in apachex.y.z/modules/ directory
but there is no 'LoadModule ".$msgExtName." modules/".$msgExplain."' line in httpd.conf file",
  9 => "The ServerName '".$msgExtName."' has syntax error.

 Characters accepted [a-zA-Z0-9.-]
 Letter or number at the beginning. Letter or number at the end
 Minimum of two characters
 . or - characters neither at the beginning nor at the end
 . or - characters not followed by . or -
 ServerName should be not quoted",
 10 => "States of services:\n\n".$msgExtName,
 11 => "There is an error.\n".$msgExtName,
 12 => "The module ".$msgExtName." must not be disables.",
 13 => "In ".$msgExtName." file,
 MySQL Server has not the same name as MySQL service: ".$msgExplain."

 The content of the file (about line 25) must be:

 # The MySQL server
 [".$msgExplain."]
 ",
 14 => "To have the VirtualHost, the line:\n\n#Include conf/extra/httpd-vhosts.conf\n\nmust be uncommented in httpd.conf file",
 15 => "The file:\n\n".$msgExtName."\n\ndoes not exists.",
 16 => "The line 'zend_extension=".$msgExtName.".dll' exists in php.ini file but there is no ".$msgExtName.".dll' file",
	);

function message_add(&$array) {
	$array = "Sorry,

".$array."

Press ENTER to continue...
";
}
array_walk($message, 'message_add');

echo $message[$msgId];
}
elseif(is_string($msgId)) {
	$complete_result = $msg_index = '';
	if($msgId == "stateservices") {
		$services_OK = $service_PATH = true;
		$message['stateservices'] = ($doReport ? "--------------------------------------------------\n" : '');
		$message['stateservices'] .= "State of services:\n\n";
		$message['binarypath'] = '';
		//echo $message['stateservices'];
		require 'config.inc.php';
		require_once 'wampserver.lib.php';
		$services = array($c_apacheService);
		$service_path_correct[$c_apacheService] = $c_installDir.'/bin/apache/apache'.$c_apacheVersion.'/bin/httpd.exe -k runservice';
		if($wampConf['SupportMySQL'] == 'on') {
			$services[] = $c_mysqlService;
			$service_path_correct[$c_mysqlService] = $c_installDir.'/bin/mysql/mysql'.$c_mysqlVersion.'/bin/mysqld.exe '.$c_mysqlService;
		}
		if($wampConf['SupportMariaDB'] == 'on'){
			$services[] = $c_mariadbService;
			$service_path_correct[$c_mariadbService] = $c_installDir.'/bin/mariadb/mariadb'.$c_mariadbVersion.'/bin/mysqld.exe '.$c_mariadbService;
		}
		foreach($services as $value) {
			$message['stateservices'] .= " The service '".$value."'";
			$command = 'sc query '.$value.' | FINDSTR "STOPPED RUNNING"';
			$output = `$command`;
			if(stripos($output, "RUNNING") !== false) {
				$message['stateservices'] .= " is started\n\n";
				// Checks if the service matches the Apache, MySQL or MariaDB version used.
				// Command is: sc qc service | findstr "BINARY_PATH_NAME"
				// For Apache :        BINARY_PATH_NAME   : "J:\wamp\bin\apache\apache2.4.39\bin\httpd.exe" -k runservice
				// For MySQL  :        BINARY_PATH_NAME   : J:\wamp\bin\mysql\mysql5.7.27\bin\mysqld.exe wampmysqld
				// For MariaDB:        BINARY_PATH_NAME   : J:\wamp\bin\mariadb\mariadb10.4.6\bin\mysqld.exe wampmariadb
				$command = 'sc qc '.$value.' | FINDSTR "BINARY_PATH_NAME"';
				$output = `$command`;
				if(preg_match("/[ \t]+BINARY_PATH_NAME[ \t]+:[ \t]+(.+)$/m", $output, $matches) > 0) {
					$service_path = str_replace(array("\\",'"'),array("/",""),$matches[1]);
					if(strcasecmp($service_path_correct[$value],$service_path) <> 0) {
						$message['binarypath'] .= "*** BINARY_PATH_NAME of the service ".$value." is not the good one:\n";
						$message['binarypath'] .= $service_path."\n*** should be:\n";
						$message['binarypath'] .= $service_path_correct[$value]."\n\n";
						$service_PATH = false;
					}
				}
			}
			elseif(stripos($output, "STOPPED") !== false) {
				$message['stateservices'] .= " is NOT started\n";
				$services_OK = false;
				$command = 'sc queryex '.$value.' | FINDSTR "WIN32_EXIT_CODE"';
				$output = `$command`;
				if(preg_match("/[ \t]*WIN32_EXIT_CODE[ \t]*: ([0-9]{1,5}).*$/m", $output, $matches) > 0 ) {
					$message['stateservices'] .= " EXIT error code:".$matches[1]."\n";
					$command = 'net helpmsg '.$matches[1];
					$output = `$command`;
					$message['stateservices'] .= " Help message for error code ".$matches[1]." is:".$output."\n\n";
				}
			}
			else {
				$message['stateservices'] .= " is not RUNNING nor STOPPED.\n";
				$services_OK = false;
				$command = 'sc queryex '.$value;
				$output = `$command`;
				if(stripos($output, "1060")) {
					$message['stateservices'] .= " [SC] EnumQueryServicesStatus:OpenService failure(s) 1060 :\n The specified service does not exist as an installed service.\n";
				}
				$message['stateservices'] .= " ********* The service '".$value."' does not exist ********\n\n";
			}
		}
		if(!$services_OK) {
			$message['stateservices'] .= "WampServer (Apache, PHP and MySQL) will not function properly if any service\n";
			foreach($services as $value) {
				$message['stateservices'] .= "'".$value."'\n";
			}
			$message['stateservices'] .= " is not started.\n\n";
		}
		else
			$message['stateservices'] .= "\tall services are started - it is OK\n\n";
		if(!$service_PATH) {
			$message['stateservices'] .= "***** One or more BINARY_PATH_NAME is incorrect *****\n";
			$message['stateservices'] .= $message['binarypath'];
			$message['stateservices'] .= "You should reinstall the services using the integrated Wampserver's tool:\nRight-click -> Tools -> Reinstall all services\n\n";
		}
		else
			$message['stateservices'] .= "\tall services BINARY_PATH_NAME are OK\n";

		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['stateservices'],false,false,'ab');
			exit;
		}
	echo $message['stateservices'];
	$msg_index = 'stateservices';
	$complete_result = $message['stateservices'];
	}
	elseif($msgId == "dnsorder") {
	require 'config.inc.php';
	require_once 'wampserver.lib.php';
	//Check values of DNS priorities
	$message['dnscheckorder'] = ($doReport ? "--------------------------------------------------\n" : '');
	$message['dnscheckorder'] .= "*** Checking the DNS search order ***\n";
	echo $message['dnscheckorder'];
	$command = 'reg query HKLM\SYSTEM\CurrentControlSet\Services\Tcpip\ServiceProvider';
	$output = `$command`;
	$dns = array(
		'DnsPriority'=>'none',
		'HostsPriority'=>'none',
		'LocalPriority'=>'none',
		'NetbtPriority'=>'none',
		);
	$dnsdec = array(
		'DnsPriority'=>'none',
		'HostsPriority'=>'none',
		'LocalPriority'=>'none',
		'NetbtPriority'=>'none',
		);
	foreach($dns as $key=>$value) {
		if(preg_match("/^[ \t]*".$key."[ \t]*REG_DWORD[ \t]*0x([0-9a-fA-F]*)$/m", $output, $matches) > 0 ) {
			$dns[$key] = $matches[1];
			$dnsdec[$key] = intval(hexdec($matches[1]));
		}
	}
	if(in_array('none',$dns)
		|| ((int)$dnsdec['HostsPriority'] <= (int)$dnsdec['LocalPriority'])
		|| ((int)$dnsdec['DnsPriority'] <= (int)$dnsdec['HostsPriority'])
		|| ((int)$dnsdec['NetbtPriority'] <= (int)$dnsdec['DnsPriority'])
		) {
		$message['dnscheckorder'] .= "\n**** Values of registry keys for\nHKLM\SYSTEM\CurrentControlSet\Services\Tcpip\ServiceProvider\nare not in correct order\n";
		asort($dnsdec);
		foreach($dnsdec as $key=>$value) {
			$message['dnscheckorder'] .= $key." REG_DWORD 0x".$dns[$key]."(".$value.")\n";
		}
		$message['dnscheckorder'] .= "\nOriginal value must be:\nLocalPriority 0x1f3 (499) (Local DNS cache)\nHostsPriority 0x1f4 (500) (Hosts file)\nDnsPriority 0x7d0 (2000) (DNS servers)\nNetbtPriority 0x7d1 (2001) (NetBIOS)\n";
	  $complete_result = $message['dnscheckorder'];
	}
	else
		$message['dnscheckorder'] .= "\nValues of registry keys for\nHKLM\SYSTEM\CurrentControlSet\Services\Tcpip\ServiceProvider\nare in correct order\n\n";
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['dnscheckorder'],false,false,'ab');
			exit;
		}
	echo $message['dnscheckorder'];
	$msg_index = 'dnscheckorder';
	$complete_result = $message['dnscheckorder'];
	}
	elseif($msgId == "compilerversions") {
		echo "Check compiler's versions...\n";
		echo "It may take a while ...\n";
		$phpCompiler = array();
		$phpVer = $phpVC = $phpTS = array();
		$apacheCompiler = array();
		$apacheVC = array();
		$apacheVersion = array();
		$apacheVersionTot = array();
		$phpApacheDll = array();
		$phpErrorMsg = array();
		$mysqlVersion = array();
		$mariadbVersion = array();
		$v32 = array();
		$v64 = array();
		$nb_v = 0;
		require_once 'config.inc.php';
		require_once 'wampserver.lib.php';
		$message['compilerversions'] = ($doReport ? "--------------------------------------------------\n" : '');
		$message['compilerversions'] .= 'Wampmanager (Aestan Tray Menu) '.trim($_SERVER['argv'][2]).' - '.trim($_SERVER['argv'][3])."\n\n";
		$message['compilerversions'] .= "Compiler Visual C++ versions used:\n\n";
		$apacheVersionList = listDir($c_apacheVersionDir,'checkApacheConf','apache');
		$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php');
		$mysqlVersionList = listDir($c_mysqlVersionDir,'checkMysqlConf','mysql');
		$mariadbVersionList = listDir($c_mariadbVersionDir,'checkMariaDBConf','mariadb');

		// Apache versions
		foreach($apacheVersionList as $oneApache) {
    	$oneApacheVersion = str_ireplace('apache','',$oneApache);
			echo "Apache ".$oneApacheVersion." to check";
    	$pos = strrpos($oneApacheVersion,'.');
    	$apacheVersion[] = substr($oneApacheVersion,0,$pos);
    	$apacheVersionTot[] = $oneApacheVersion;
			unset($result);
			$command = 'start /b /wait '.$c_apacheVersionDir.'/apache'.$oneApacheVersion.'/'.$wampConf['apacheExeDir'].'/'.$wampConf['apacheExeFile'].' -V';
			exec($command, $result);
			$built = $archi = false;
			foreach($result as $value) {
				if(strpos($value, 'built') !== false) {
					$output_1 = $value;
					preg_match('~^.*Lounge V[C|S]([0-9]{1,2}).*$~',$value, $matches);
					$apacheVC[$oneApacheVersion] = $matches[1];
					$built = true;
				}
				if(strpos($value, 'Architecture') !== false) {
					$output_2 = $value;
					$archi = true;
					if(strpos($output_2, "32-bit") !== false)
						$v32[] = $oneApache;
					elseif(strpos($output_2, "64-bit") !== false)
						$v64[] = $oneApache;
				}
				if($built && $archi) {
					unset($result);
					break;
				}
			}
			$apacheCompiler[$oneApacheVersion] = $output_1."\n\t".$output_2;
			$nb_v++;
			echo " done\n";
			//echo ".";
    }

		// PHP versions
		$NTSversion = $DIRversion = false;
		foreach($phpVersionList as $onePhp) {
			$onePhpVersion = str_ireplace('php','',$onePhp);
			echo "PHP ".$onePhpVersion." to check ";
			$command = 'start /b /wait '.$c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampConf['phpExeFile'].' -i | FINDSTR ';
			$commandAdd = array('/C:"PHP Version"','"Compiler Architecture"','/C:"Thread Safety"');
			unset($result);
			foreach($commandAdd as $value) {
				exec($command.$value, $result);
			}
			$phpFindVer = $Compil = $Archi = $Thread = false;
			foreach($result as $value){
				if(strpos($value, "PHP Version") !== false) {
					$output_1 = $value;
					if(preg_match('~^PHP Version => ([0-9\.]*).*$~',$value,$matches) > 0) {
						$phpVer[$onePhpVersion] = $matches[1];
					}
					$phpFindVer = true;
				}
				if(strpos($value, "Compiler") !== false) {
					$output_1 = $value;
					if(preg_match('~^.*MSVC([0-9]{1,2}).*$~',$value,$matches) == 0) {
						preg_match('~^.*Visual C\+\+ ([0-9]+).*$~',$value,$matches);
					}
					$phpVC[$onePhpVersion] = $matches[1];
					$Compil = true;
				}
				elseif(strpos($value, "Architecture") !== false) {
					$Archi = true;
					$output_2 = $value;
					if(strpos($value, "x86") !== false)
						$v32[] = $onePhp;
					elseif(strpos($output_2, "x64") !== false)
						$v64[] = $onePhp;
				}
				elseif(strpos($value, "Thread Safety") !== false) {
					$Thread = true;
					if(stripos($value, "Enabled") !== false)
						$phpTS[$onePhpVersion] = "TS";
					else {
						$phpTS[$onePhpVersion] = "NTS";
						$NTSversion = true;
					}
				}
				if($phpFindVer && $Compil && $Archi && $Thread) {
					unset($result);
					break;
				}
			}
			$phpCompiler[$onePhpVersion] = $output_1."\n\t".$output_2;
			//Search compatibility with Apache
			unset($phpConf);
		  include $c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampBinConfFiles;
			foreach($apacheVersion as $value) {
				if(!empty($phpConf['apache'][$value]['LoadModuleFile']) && file_exists($c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$value]['LoadModuleFile']))
					$phpApacheDll[$onePhpVersion][$value] = true;
				else {
				$phpApacheDll[$onePhpVersion][$value] = false;
				if(empty($phpConf['apache'][$value]['LoadModuleFile']))
					$phpErrorMsg[$onePhpVersion][$value] = "\$phpConf['apache']['".$value."']['LoadModuleFile'] does not exists or is empty in ".$c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampBinConfFiles;
				elseif(!file_exists($c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$value]['LoadModuleFile']))
					$phpErrorMsg[$onePhpVersion][$value] = $c_phpVersionDir.'/php'.$onePhpVersion.'/'.$phpConf['apache'][$value]['LoadModuleFile']." file does not exists.";
				}
			}
			$nb_v++;
			echo " done\n";
			//echo ".";
		}

		// MySQL versions
		foreach($mysqlVersionList as $oneMysql) {
			$oneMysqlVersion = str_ireplace('mysql','',$oneMysql);
			echo "MySQL ".$oneMysqlVersion." to check";
    	$command = 'start /b /wait '.$c_mysqlVersionDir.'/mysql'.$oneMysqlVersion.'/'.$wampConf['mysqlExeDir'].'/'.$wampConf['mysqlExeFile'].' -V';
			unset($result);
			$output = exec($command, $result);
			$pos = strrpos($output,'Ver ');
			$output = substr($output,$pos);
			if(strpos($output, "x86 ") !== false)
				$v32[] = $oneMysql;
			elseif(strpos($output, "x86_64") !== false)
				$v64[] = $oneMysql;
			$mysqlVersion[$oneMysqlVersion] = $output;
			$nb_v++;
			echo " done\n";
			//echo ".";
		}

		// MariaDB versions
		foreach($mariadbVersionList as $oneMaria) {
			$oneMariaVersion = str_ireplace('mariadb','',$oneMaria);
			echo "MariaDB ".$oneMariaVersion." to check";
			unset($result);
    	$command = 'start /b /wait '.$c_mariadbVersionDir.'/mariadb'.$oneMariaVersion.'/'.$wampConf['mariadbExeDir'].'/'.$wampConf['mariadbExeFile'].' -V';
			$output = exec($command, $result);
			$pos = strrpos($output,'Ver ');
			$output = substr($output,$pos);
			if(strpos($output, "x86 ") !== false)
				$v32[] = $oneMaria;
			elseif(strpos($output, "x86_64") !== false)
				$v64[] = $oneMaria;
			$mariaVersion[$oneMariaVersion] = $output;
			$nb_v++;
			echo " done\n";
			//echo ".";
		}

    foreach($phpCompiler as $key=>$value) {
    	$message['compilerversions'] .= "PHP ".$key." ".$value."\n";
    	reset($apacheVersionTot);
    	foreach($apacheVersion as $apache) {
    		$apacheTot = each($apacheVersionTot);
    		//error_log("php ".$key." VC=".$phpVC[$key]." - Apache ".$apacheTot[1]." VC=".$apacheVC[$apacheTot[1]]);
    		if($phpApacheDll[$key][$apache]) {
    			$message['compilerversions'] .= "\tis compatible with Apache ".$apacheTot[1]."\n";
    			if($apacheVC[$apacheTot[1]] <= 11 && $phpVC[$key] >= 15) {
    				$message['compilerversions'] .= "There could be some problems between Apache VC".$apacheVC[$apacheTot[1]]." and PHP VC".$phpVC[$key]."\n";
    			}
    		}
    		else {
    			$message['compilerversions'] .= "\tis NOT COMPATIBLE with Apache ".$apacheTot[1]."\n";
    			$message['compilerversions'] .= "\t".$phpErrorMsg[$key][$apache]."\n";
    		}
    	}
    	if($phpTS[$key] != "TS") {
    		$message['compilerversions'] .= "\tis *** NON THREAD SAFE ***\n";
    	}
    	if($phpVer[$key] != $key) {
    		$message['compilerversions'] .= "\tis *** NOT RIGHT VERSION ***\n\t   *** Folder=".$key." - php -i =".$phpVer[$key]."\n";
    		$DIRversion = true;
    	}
    	//error_log("key=".$key);
    	$message['compilerversions'] .= "\n";
		//echo ".";
    }
		$message['compilerversions'] .= "\n\n";

    foreach($mysqlVersion as $key=>$value) {
    	$message['compilerversions'] .= "MySQL ".$value."\n";
    }
		$message['compilerversions'] .= "\n\n";
    foreach($mariaVersion as $key=>$value) {
    	$message['compilerversions'] .= "MariaDB ".$value."\n";
    }

		$message['compilerversions'] .= "\n\n";
    foreach($apacheCompiler as $key=>$value)
    	$message['compilerversions'] .= "Apache ".$key." ".$value."\n";
		$nb_v32 = count($v32);
		$nb_v64 = count($v64);
    if(($nb_v32 > 0 && $nb_v64 != 0) || ($nb_v64 > 0 && $nb_v32 !=0)) {
    	$message['compilerversions'] .= "\n\t\tWARNING - WARNING - WARNING\nIt is IMPERATIVE that all versions are the SAME TYPE\nThere are:\n\t".$nb_v32." version(s) for x86 (32-bit)\n\t".$nb_v64." version(s) for x64 (64-bit)\n";
    	$message['compilerversions'] .= "32 bit versions are\n";
    	foreach($v32 as $value)
    		$message['compilerversions'] .= "\t".$value."\n";
    	$message['compilerversions'] .= "64 bit versions are\n";
    	foreach($v64 as $value)
    		$message['compilerversions'] .= "\t".$value."\n";
    }
    //Are all PHP versions TS ?
    if($NTSversion) {
    	$message['compilerversions'] .= "\n\t\tWARNING - WARNING - WARNING\nIt is IMPERATIVE that all PHP versions are the SAME TYPE 'Thread Safe'\nThere is at least one PHP version Non Thread Safe (NTS)\n";
    }
    //Are all PHP folder == PHP version ?
    if($DIRversion) {
    	$message['compilerversions'] .= "\n\t\tWARNING - WARNING - WARNING\nOne or more PHP folder name is not equal PHP version\n";
    }
  	//What is the php.ini file loaded?
  	$message['inifiles'] = '';
		ob_start();
		phpinfo(1);
		$output = ob_get_contents();
		ob_end_clean();

		preg_match('/^Loaded Configuration File => (.*)$/m', $output, $matches);
		$matches[1] = str_replace("\\","/",$matches[1]);
		if(strtolower($matches[1]) != strtolower($c_phpCliConfFile))
			$message['inifiles'] .= "*** ERROR *** The PHP configuration loaded file is:\n\t".$matches[1]."\nshould be for PHP CLI\n\t".$c_phpCliConfFile."\n";
		preg_match('/^Scan this dir for additional .ini files => (.*)$/m', $output, $matches);
		if($matches[1] != "(none)")
			$message['inifiles'] .= "*** ERROR *** There are too much php.ini files\n".$matches[0]."\n";
		preg_match('/^Additional .ini files parsed => (.*)$/m', $output, $matches);
		if($matches[1] != "(none)")
			$message['inifiles'] .= "*** ERROR *** There are other php.ini files\n".$matches[0]."\n";
		if(!empty($message['inifiles']))
			$message['compilerversions'] .= "\n----- Verify what php.ini file is loaded for PHP CLI -----\n\n".$message['inifiles'];
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['compilerversions'],false,false,'ab');
			exit;
		}

		echo "\n\n".$message['compilerversions'];
		$msg_index = 'compilerversions';
		$complete_result = $message['compilerversions'];
	}
	elseif($msgId == "vhostconfig") {
		$message['apachevhosts'] = ($doReport ? "--------------------------------------------------\n" : '');
		$message['apachevhosts'] .= "VirtualHost configuration:\n\n";
		echo $message['apachevhosts'];
		require_once 'config.inc.php';
		require_once 'wampserver.lib.php';
		$myhttpd_contents = file_get_contents($c_apacheConfFile);
		if(preg_match("~^[ \t]*#[ \t]*Include[ \t]*conf/extra/httpd-vhosts.conf.*$~m",$myhttpd_contents) > 0) {
			$message['apachevhosts'] .= "*** WARNING: It is impossible to get VirtualHost\n#Include conf/extra/httpd-vhosts.conf\nline is commented in httpd.conf\n";
		}
		else {
			$c_vhostConfFile = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheConfDir'].'/extra/httpd-vhosts.conf';
			if(!file_exists($c_vhostConfFile)) {
				$message['apachevhosts'] .= "*** WARNING: The file\n".$c_vhostConfFile."\ndoes not exist\n";
			}
			else {
				$default_server = false;
				$virtual_host = false;
				$default_localhost = false;

				$command = 'start /b /wait '.$c_apacheExe.'  -t -D DUMP_VHOSTS';
				ob_start();
				passthru($command);
				$output = ob_get_contents();
				ob_end_clean();
				if(!empty($output)) {
					if(preg_match_all("~^[ \t]*default server (.*) \(.*\)$~m",$output, $matches) > 0 ) {
						foreach($matches[1] as $value) {
							$message['apachevhosts'] .= "\tDefault server: ".$value."\n";
							$default_server = true;
							if($value == "localhost")
								$default_localhost = true;
						}
					}
					else { // No default server - May be only one VirtualHost localhost
						if(preg_match("~^.*:.*localhost.*$~m",$output, $matches) > 0 ) {
							$default_server = true;
							$default_localhost = true;
						}
					}
					$virtualNames = array();
					//Check on port other than 80
					$nb_vhost = preg_match_all("~^.*:([0-9]{2,5})[ \t]*(.*)[ \t]+\(.*\)$~m",$output,$matchesPort);
					if($nb_vhost > 0) {
						$virtual_host = true;
						for($i = 0 ; $i < $nb_vhost ; $i++) {
							$message['apachevhosts'] .= ($matchesPort[1][$i] != '80' ? "On port ".$matchesPort[1][$i]." " : '')."Virtual Host: ".$matchesPort[2][$i]."\n";
							$virtualName[] = $matchesPort[2][$i];
						}
					}
					$nb_vhost = preg_match_all("~^.*port ([0-9]{2,5}).*namevhost (.*) \(.*\)$~m",$output, $matches);
					if($nb_vhost > 0 ) {
						$virtual_host = true;
						for($i = 0 ; $i < $nb_vhost ; $i++) {
							$message['apachevhosts'] .= ($matches[1][$i] != '80' ? "On port ".$matches[1][$i]." " : '')."Virtual Host: ".$matches[2][$i]."\n";
							$virtualName[] = $matches[2][$i];
						}
					}
					if(!$default_server && !$virtual_host) {
						if(preg_match("~^(?:\*|[0-9\.]*):[0-9]{2,5}[ \t]*(.*) \(.*\)$~m",$output, $matches) > 0) {
							$message['apachevhosts'] .= "\tDefault server: ".$matches[1]."\n\n";
							$default_server = true;
							if($matches[1] == "localhost")
								$default_localhost = true;
						}
					}

					if(!$default_localhost)
						$message['apachevhosts'] .= "*** WARNING: The name of the default server must be 'localhost'\n\n";
					if(!$default_server)
						$message['apachevhosts'] .= "*** WARNING: There is no default server\n\n";
					if(!$virtual_host)
						$message['apachevhosts'] .= "*** WARNING: No VirtualHost defined\n\n";
					if(!$default_server || !$virtual_host)
						$message['apachevhosts'] .= "\n================== COMPLETE RESULT ==================\n".$output;
					else { // Check if each Apache VirtualHost name is in hosts file
						$myHostsContents = file_get_contents($c_hostsFile);
						for($i = 0 ; $i < $nb_vhost ; $i++) {
							if(stripos($myHostsContents, $virtualName[$i]) === false)
								$message['apachevhosts'] .= "*** WARNING: Apache VirtualHost '".$virtualName[$i]."'\n*** is not defined in ".$c_hostsFile." file\n\n";
						}
					}
				}
			}
		}
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['apachevhosts'],false,false,'ab');
			exit;
		}
		echo $message['apachevhosts'];
		$msg_index = 'apachevhosts';
		$complete_result = $message['apachevhosts'];
	}
	elseif($msgId == "apachemodules") {
		require_once 'config.inc.php';
		require_once 'wampserver.lib.php';
		$command = 'start /b /wait '.$c_apacheExe.'  -t -D DUMP_MODULES';
		ob_start();
		passthru($command);
		$output = ob_get_contents();
		ob_end_clean();
		if(!empty($output)) {
			$message['apachemodules'] = "Apache loaded modules\n";
			$nb_static = preg_match_all("~^[ \t]*(.*) \(static\).*$~m",$output, $matches);
			if($nb_static > 0) {
				$message['apachemodules'] .= "Core:\n";
				foreach($matches[1] as $value)
					$message['apachemodules'] .= $value."\n";
				}
				$message['apachemodules'] .= "\n";
			$nb_shared = preg_match_all("~^[ \t]*(.*) \(shared\).*$~m",$output, $matches);
			if($nb_shared > 0) {
				$message['apachemodules'] .= "Shared modules:\n";
				foreach($matches[1] as $value)
					$message['apachemodules'] .= $value."\n";
				$message['apachemodules'] .= "\n";
			}
			echo $message['apachemodules'];
			$msg_index = 'apachemodules';
			$complete_result = $message['apachemodules'];
		}
	}
	elseif($msgId == "changeServiceName") {
		require_once 'config.inc.php';
		require_once 'wampserver.lib.php';
		echo "\n***************************************************************\n";
		echo "*************** SERVICE NAMES HAVE BEEN CHANGED ***************\n";
		echo "***************************************************************\n";
		echo "\n  Apache -> ".$c_apacheService."\n";
		echo "  MySQL  -> ".$c_mysqlService."\n";
		echo "  MariaDB  -> ".$c_mariadbService."\n\n";
		echo "***************** WAMPSERVER WILL BE SHUTDOWN *****************\n\n";
		echo "* YOU MUST RESTART WampServer  for the changes to take effect *\n";
	}
	elseif($msgId == "refreshLogs") {
		$logToClean = array();
		echo "\nLog file(s) to be cleaned:\n\n";
		for($i = 2 ; $i <= $nb_arg ; $i++) {
			$logToClean[$i] = trim($_SERVER['argv'][$i]);
			echo "\t".$logToClean[$i]."\n";
		}
		echo "\nDo you want to clean these file(s)? (y/n)\n\n";
		$touche = strtoupper(trim(fgets(STDIN)));
		if($touche === "Y") {
			foreach($logToClean as $value) {
				if(file_exists($value)) {
					$fp = fopen($value, "w");
  				fclose($fp);
				}
			}
		}
		exit;
	}

	if(!empty($complete_result)) {
		echo "\n--- Do you want to copy the results into Clipboard?
--- Type 'y' to confirm - Press ENTER to continue... ";
    $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if ($confirm == 'y') {
			write_file("temp.txt",$complete_result, true);
		}
		exit(0);
 	}
	echo "\nPress ENTER to continue...";
}

trim(fgets(STDIN));

?>