<?php

$msgId = $_SERVER['argv'][1];
$doReport = false;
$iw = 2;while(!empty($_SERVER['argv'][$iw])){if($_SERVER['argv'][$iw] == 'doreport') $doReport = true;$iw++;};
$nb_arg = $_SERVER['argc'] - 1;
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}
require 'config.inc.php';
require 'wampserver.lib.php';

if(is_numeric($msgId) && $msgId > 0 && $msgId < 17) {
	$msgExtName = '';
	if($nb_arg >= 2)
		$msgExtName = base64_decode($_SERVER['argv'][2]);
	$msgExplain = '';
	if($nb_arg >= 3)
		$msgExplain = base64_decode($_SERVER['argv'][3]);

	$message = array(
	1 => "This PHP version ".$msgExtName." doesn't seem to be compatible with your actual Apache Version.\n\n".$msgExplain,
	2 => "This Apache version ".$msgExtName." doesn't seem to be compatible with your actual PHP Version.\n".$msgExplain,
	3 => "The '".$msgExtName.".dll' extension file exists but there is no 'extension=".$msgExtName.".dll' line in php.ini.",
	4 => "The line 'extension=".$msgExtName.".dll' exists in php.ini file but there is no ".$msgExtName.".dll' file in ext/ directory.",
	5 => "The '".$msgExtName."' extension cannot be loaded by 'extension=".$msgExtName.".dll' in php.ini. Must be loaded by 'zend_extension='.",
	6 => $msgExtName."\ncannot be changed by the Wampmanager menus.\n".$msgExplain,
	7 => "There is 'LoadModule ".$msgExtName." modules/".$msgExplain."' line in httpd.conf file\nbut there no '".$msgExplain."' file in apachex.y.z/modules/ directory.",
  8 => "There is '".$msgExplain."' file in apachex.y.z/modules/ directory\nbut there is no 'LoadModule ".$msgExtName." modules/".$msgExplain."' line in httpd.conf file",
  9 => "The ServerName '".$msgExtName."' has syntax error.\n\n Characters accepted [a-zA-Z0-9.-]
 Letter or number at the beginning. Letter or number at the end
 Minimum of two characters
 . or - characters neither at the beginning nor at the end
 . or - characters not followed by . or -
 ServerName should be not quoted",
 10 => "States of services:\n\n".$msgExtName,
 11 => "Warning\n\n".$msgExtName,
 12 => "The module: ".$msgExtName."\nmust not be disable.",
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
	$array = $array."\nPress ENTER to continue ";
}
array_walk($message, 'message_add');
Command_Windows($message[$msgId],-1,-1,0,'Error/Explanation message');
}
elseif(is_string($msgId)) {
	$complete_result = $msg_index = '';
	if($msgId == "stateservices") {
		Command_Windows('Check states of services',40,2,0,'Check states of services');
		$services_OK = $service_PATH = true;
		$message['stateservices'] = ($doReport ? "--------------------------------------------------\n" : '');
		$message['stateservices'] .= "State of services:\n\n";
		$message['binarypath'] = '';
		$services = array($c_apacheService);
		$service_path_correct[$c_apacheService] = $c_installDir.'/bin/apache/apache'.$c_apacheVersion.'/bin/httpd.exe';
		if($wampConf['SupportMySQL'] == 'on') {
			$services[] = $c_mysqlService;
			$service_path_correct[$c_mysqlService] = $c_installDir.'/bin/mysql/mysql'.$c_mysqlVersion.'/bin/mysqld.exe';
		}
		if($wampConf['SupportMariaDB'] == 'on'){
			$services[] = $c_mariadbService;
			$service_path_correct[$c_mariadbService] = $c_installDir.'/bin/mariadb/mariadb'.$c_mariadbVersion.'/bin/mysqld.exe';
		}
		foreach($services as $value) {
			$message['stateservices'] .= " The service '".$value."'";
			$command = 'CMD /D /C sc query '.$value.' | FINDSTR "STOPPED RUNNING"';
			$output = `$command`;
			if(stripos($output, "RUNNING") !== false) {
				$message['stateservices'] .= " is started\n";
				// Checks if the service matches the Apache, MySQL or MariaDB version used.
				// Command is: sc qc service | findstr "BINARY_PATH_NAME"
				// For Apache :        BINARY_PATH_NAME   : "J:\wamp\bin\apache\apache2.4.39\bin\httpd.exe"
				// For MySQL  :        BINARY_PATH_NAME   : J:\wamp\bin\mysql\mysql5.7.27\bin\mysqld.exe
				// For MariaDB:        BINARY_PATH_NAME   : J:\wamp\bin\mariadb\mariadb10.4.6\bin\mysqld.exe
				$command = 'CMD /D /C sc qc '.$value.' | FINDSTR "BINARY_PATH_NAME SERVICE_START_NAME"';
				$output = `$command`;
				if(preg_match("/[ \t]+BINARY_PATH_NAME[ \t]+:[ \t]+(.+\.exe).*$/m", $output, $matches) > 0) {
					//error_log(print_r($matches,true));
					$service_path = str_replace(array("\\",'"'),array("/",""),$matches[1]);
					if(strcasecmp($service_path_correct[$value],$service_path) <> 0) {
						$message['binarypath'] .= color('red')."*** BINARY_PATH_NAME of the service ".$value." is not the good one:".color('black')."\n";
						$message['binarypath'] .= $service_path."\n*** should be:\n";
						$message['binarypath'] .= $service_path_correct[$value]."\n";
						$service_PATH = false;
					}
				}
				// Checks service session : LocalSystem by default
				//Command is: sc qc service | findstr "SERVICE_START_NAME" (done before, see upper)
				if(preg_match("/[ \t]+SERVICE_START_NAME[ \t]+:[ \t]+(.+)$/m", $output, $matches) > 0) {
					$message['stateservices'] .= " Service Session : ".$matches[1]."\n";
				}
				else {
					$message['stateservices'] .= " Service Session : not found\n";
				}
			}
			elseif(stripos($output, "STOPPED") !== false) {
				$message['stateservices'] .= " is NOT started\n";
				$services_OK = false;
				$command = 'CMD /D /C sc queryex '.$value.' | FINDSTR "WIN32_EXIT_CODE"';
				$output = `$command`;
				if(preg_match("/[ \t]*WIN32_EXIT_CODE[ \t]*: ([0-9]{1,5}).*$/m", $output, $matches) > 0 ) {
					$message['stateservices'] .= " EXIT error code:".$matches[1]."\n";
					$command = 'CMD /D /C net helpmsg '.$matches[1];
					$output = `$command`;
					$message['stateservices'] .= " Help message for error code ".$matches[1]." is:".str_replace(array("\r","\n"),"",$output)."\n";
				}
				//Specific check for STOPPED Apache Service in Event Viewer
				if($value == $c_apacheService) {
					$command = "CMD /D /C wevtutil qe Application /c:2 /rd:true /f:text /q:\"*[System[Provider[@Name='Apache Service'] and (Level=2)]]\"";
					$output = `$command`;
					//Check if there is 'Apache Service' in the result
					if(stripos($output,"Apache Service") !== false) {
						if(preg_match_all("~>>>.*~",$output,$matches) > 0) {
							foreach($matches[0] as $errorVal) $message['stateservices'] .= $errorVal."\n";
						}
					}
				}
			}
			else {
				$message['stateservices'] .= " is not RUNNING nor STOPPED.\n";
				$services_OK = false;
				$command = 'CMD /D /C sc queryex '.$value;
				$output = `$command`;
				if(stripos($output, "1060")) {
					$message['stateservices'] .= " [SC] EnumQueryServicesStatus:OpenService failure(s) 1060 :\n The specified service does not exist as an installed service.\n";
				}
				$message['stateservices'] .= color('red')." ********* The service '".$value."' does not exist ********".color('black')."\n";
			}
			$message['stateservices'] .= "\n";
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
			$message['stateservices'] .= color('red')."***** One or more BINARY_PATH_NAME is incorrect *****\n";
			$message['stateservices'] .= $message['binarypath'];
			$message['stateservices'] .= "You should reinstall the services using the integrated Wampserver's tool:\nLeft-Click-> Apache or MySQL or MariaDB -> Service administration then four steps: Stop, Remove, Install, Start then Right-Click -> Refresh".color('black')."\n\n";
		}
		else
			$message['stateservices'] .= "\tall services BINARY_PATH_NAME are OK\n";

		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['stateservices'],false,false,'ab');
			exit;
		}
	$message_title = "State of services";
	$msg_index = 'stateservices';
	$complete_result = $message['stateservices'];
	}
	elseif($msgId == "dnsorder") {
	Command_Windows('Check DNS search order',40,2,0,'Check DNS search order');
	//Check values of DNS priorities
	$message['dnscheckorder'] = ($doReport ? "--------------------------------------------------\n" : '');
	$message['dnscheckorder'] .= "*** Checking the DNS search order ***\n";
	$command = 'CMD /D /C reg query HKLM\SYSTEM\CurrentControlSet\Services\Tcpip\ServiceProvider';
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
		$message['dnscheckorder'] .= "\n".color('red')."**** Values of registry keys for\nHKLM\SYSTEM\CurrentControlSet\Services\Tcpip\ServiceProvider\nare not in correct order".color('black')."\n";
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
	$message_title = "DNS search order";
	$msg_index = 'dnscheckorder';
	$complete_result = $message['dnscheckorder'];
	}
	elseif($msgId == "compilerversions") {
		Command_Windows("Check Compiler's versions\nIt may take a while...\n",40,30,0,'Check Compiler\'s versions');
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
			echo "Apache ".$oneApacheVersion." to check\n";
    	$pos = strrpos($oneApacheVersion,'.');
    	$apacheVersion[] = substr($oneApacheVersion,0,$pos);
    	$apacheVersionTot[] = $oneApacheVersion;
			unset($result);
			$command = 'CMD /D /C '.$c_apacheVersionDir.'/apache'.$oneApacheVersion.'/'.$wampConf['apacheExeDir'].'/'.$wampConf['apacheExeFile'].' -V';
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
    }

		// PHP versions
		$NTSversion = $DIRversion = false;
		foreach($phpVersionList as $onePhp) {
			$onePhpVersion = str_ireplace('php','',$onePhp);
			echo "PHP ".$onePhpVersion." to check\n";
			$command = 'CMD /D /C '.$c_phpVersionDir.'/php'.$onePhpVersion.'/'.$wampConf['phpExeFile'].' -i | FINDSTR ';
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
			$phpCompiler[$onePhpVersion] = $output_1." - ".$output_2;
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
		}

		// MySQL versions
		if($wampConf['SupportMySQL'] == 'on') {
			foreach($mysqlVersionList as $oneMysql) {
				$oneMysqlVersion = str_ireplace('mysql','',$oneMysql);
				echo "MySQL ".$oneMysqlVersion." to check\n";
    		$command = 'CMD /D /C '.$c_mysqlVersionDir.'/mysql'.$oneMysqlVersion.'/'.$wampConf['mysqlExeDir'].'/'.$wampConf['mysqlExeFile'].' -V';
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
			}
		}
		// MariaDB versions
		if($wampConf['SupportMariaDB'] == 'on') {
			foreach($mariadbVersionList as $oneMaria) {
				$oneMariaVersion = str_ireplace('mariadb','',$oneMaria);
				echo "MariaDB ".$oneMariaVersion." to check\n";
				unset($result);
    		$command = 'CMD /D /C '.$c_mariadbVersionDir.'/mariadb'.$oneMariaVersion.'/'.$wampConf['mariadbExeDir'].'/'.$wampConf['mariadbExeFile'].' -V';
				$output = exec($command, $result);
				$pos = strrpos($output,'Ver ');
				$output = substr($output,$pos);
				if(strpos($output, "x86 ") !== false)
					$v32[] = $oneMaria;
				elseif(strpos($output, "x86_64") !== false)
					$v64[] = $oneMaria;
				$mariaVersion[$oneMariaVersion] = $output;
				$nb_v++;
			}
		}
    foreach($phpCompiler as $key=>$value) {
    	$message['compilerversions'] .= "PHP ".$key." ".$value."\n";
    	reset($apacheVersionTot);
    	foreach($apacheVersion as $apache) {
    		$apacheTot = current($apacheVersionTot);
    		next($apacheVersionTot);
    		if($phpApacheDll[$key][$apache]) {
    			if($apacheVC[$apacheTot] <= 11 && $phpVC[$key] >= 15) {
    				$message['compilerversions'] .= "".color('red')."There could be some problems between Apache VC".$apacheVC[$apacheTot]." and PHP VC".$phpVC[$key].color('black')."\n";
    			}
    		}
    		else {
    			$message['compilerversions'] .= "\t".color('red')."is NOT COMPATIBLE with Apache ".$apacheTot.color('black')."\n";
    			$message['compilerversions'] .= "\t".$phpErrorMsg[$key][$apache]."\n";
    		}
    	}
    	if($phpTS[$key] != "TS") {
    		$message['compilerversions'] .= "\t".color('red')."is *** NON THREAD SAFE ***".color('black')."\n";
    	}
    	if($phpVer[$key] != $key) {
    		$message['compilerversions'] .= "\t".color('red')."is *** NOT RIGHT VERSION ***\n\t   *** Folder=".$key." - php -i =".$phpVer[$key].color('black')."\n";
    		$DIRversion = true;
    	}
    	//error_log("key=".$key);
    	$message['compilerversions'] .= "\n";
    }
		$message['compilerversions'] .= "\n";

    foreach($mysqlVersion as $key=>$value) {
    	$message['compilerversions'] .= "MySQL ".$value."\n";
    }
		$message['compilerversions'] .= "\n";
    foreach($mariaVersion as $key=>$value) {
    	$message['compilerversions'] .= "MariaDB ".$value."\n";
    }

		$message['compilerversions'] .= "\n";
    foreach($apacheCompiler as $key=>$value)
    	$message['compilerversions'] .= "Apache ".$key." ".$value."\n";
		$nb_v32 = count($v32);
		$nb_v64 = count($v64);
    if(($nb_v32 > 0 && $nb_v64 != 0) || ($nb_v64 > 0 && $nb_v32 !=0)) {
    	$message['compilerversions'] .= "\n\t\t".color('red')."WARNING - WARNING - WARNING\nIt is IMPERATIVE that all versions are the SAME TYPE\nThere are:\n\t".$nb_v32." version(s) for x86 (32-bit)\n\t".$nb_v64." version(s) for x64 (64-bit)".color('black')."\n";
    	$message['compilerversions'] .= "32 bit versions are\n";
    	foreach($v32 as $value)
    		$message['compilerversions'] .= "\t".$value."\n";
    	$message['compilerversions'] .= "64 bit versions are\n";
    	foreach($v64 as $value)
    		$message['compilerversions'] .= "\t".$value."\n";
    }
    //Are all PHP versions TS ?
    if($NTSversion) {
    	$message['compilerversions'] .= "\n\t\t".color('red')."WARNING - WARNING - WARNING\nIt is IMPERATIVE that all PHP versions are the SAME TYPE 'Thread Safe'\nThere is at least one PHP version Non Thread Safe (NTS)".color('black')."\n";
    }
    //Are all PHP folder == PHP version ?
    if($DIRversion) {
    	$message['compilerversions'] .= "\n\t\t".color('red')."WARNING - WARNING - WARNING\nOne or more PHP folder name is not equal PHP version".color('black')."\n";
    }
  	//What is the php.ini file loaded?
  	$message['inifiles'] = '';
		ob_start();
		phpinfo(1);
		$output = ob_get_contents();
		ob_end_clean();

		preg_match('/^Loaded Configuration File => (.*)$/m', $output, $matches);color('black').
		$matches[1] = str_replace("\\","/",$matches[1]);
		if(strtolower($matches[1]) != strtolower($c_phpCliConfFile))
			$message['inifiles'] .= "".color('red')."*** ERROR *** The PHP configuration loaded file is:\n\t".$matches[1]."\nshould be for PHP CLI\n\t".$c_phpCliConfFile.color('black')."\n";
		preg_match('/^Scan this dir for additional .ini files => (.*)$/m', $output, $matches);
		if($matches[1] != "(none)")
			$message['inifiles'] .= "".color('red')."*** ERROR *** There are too much php.ini files\n".$matches[0].color('black')."\n";
		preg_match('/^Additional .ini files parsed => (.*)$/m', $output, $matches);
		if($matches[1] != "(none)")
			$message['inifiles'] .= "".color('red')."*** ERROR *** There are other php.ini files\n".$matches[0].color('black')."\n";
		if(!empty($message['inifiles']))
			$message['compilerversions'] .= "\n----- Verify what php.ini file is loaded for PHP CLI -----\n\n".$message['inifiles'];
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['compilerversions'],false,false,'ab');
			exit;
		}

		$message_title = "Compiler VC compatibility & php.ini";
		$msg_index = 'compilerversions';
		$message['compilerversions'] = str_ireplace("\n\n","\n",$message['compilerversions']);
		$complete_result = $message['compilerversions'];
		echo "exit\n";
	}
	elseif($msgId == "vhostconfig") {
		Command_Windows("Show VirtualHost examined by Apache",40,30,0,'Show VirtualHost examined by Apache');
		$message['apachevhosts'] = ($doReport ? "--------------------------------------------------\n" : '');
		$message['apachevhosts'] .= "VirtualHost configuration:\n\n";
		$myhttpd_contents = file_get_contents($c_apacheConfFile);
		if(preg_match("~^[ \t]*#[ \t]*Include[ \t]*conf/extra/httpd-vhosts.conf.*$~m",$myhttpd_contents) > 0) {
			$message['apachevhosts'] .= color('red')."*** WARNING: It is impossible to get VirtualHost\n#Include conf/extra/httpd-vhosts.conf\nline is commented in httpd.conf".color('black')."\n";
		}
		else {
			$c_vhostConfFile = $c_apacheConfDir.'/extra/httpd-vhosts.conf';
			if(!file_exists($c_vhostConfFile)) {
				$message['apachevhosts'] .= color('red')."*** WARNING: The file\n".$c_vhostConfFile."\ndoes not exist".color('black')."\n";
			}
			else {
				$default_server = false;
				$virtual_host = false;
				$default_localhost = false;

				$command = $c_apacheExe.'  -t -D DUMP_VHOSTS';
				$output = proc_open_output($command);
				if(!empty($output)) {
					if(stripos($output,'Syntax error') !== false){
						$message['apachevhosts'] .= color('red',"\n *** WARNING ***\n".$output);
					}
					else {
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
							$message['apachevhosts'] .= color('red')."*** WARNING: The name of the default server must be 'localhost'".color('black')."\n\n";
						if(!$default_server)
							$message['apachevhosts'] .= color('red')."*** WARNING: There is no default server".color('black')."\n\n";
						if(!$virtual_host)
							$message['apachevhosts'] .= color('red')."*** WARNING: No VirtualHost defined".color('black')."\n\n";
						if(!$default_server || !$virtual_host)
							$message['apachevhosts'] .= "\n================== COMPLETE RESULT ==================\n".$output;
						else { // Check if each Apache VirtualHost name is in hosts file
							$myHostsContents = file_get_contents($c_hostsFile);
							for($i = 0 ; $i < $nb_vhost ; $i++) {
								if(stripos($myHostsContents, $virtualName[$i]) === false)
									$message['apachevhosts'] .= color('red')."*** WARNING: Apache VirtualHost '".$virtualName[$i]."'\n*** is not defined in ".$c_hostsFile." file".color('black')."\n\n";
							}
						}
					}
				}
			}
		}
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['apachevhosts'],false,false,'ab');
			exit;
		}
		$message_title = "VirtualHost examined by Apache";
		$msg_index = 'apachevhosts';
		$complete_result = $message['apachevhosts'];
	}
	elseif($msgId == "apachesyntax") {
		Command_Windows("Syntax check for Apache conf files",40,30,0,'Syntax check for Apache conf files');
		$command = $c_apacheExe.'  -t';
		$output = proc_open_output($command);
		if(!empty($output)) {
			$message['apachesyntax'] = "Syntax check for Apache config files\n\n";
			if(stripos($output,'Syntax error') !== false)
				$message['apachesyntax'] .= color('red',"\n *** WARNING ***\n".$output);
			else $message['apachesyntax'] .= $output;
		}
		else $message['apachesyntax'] = color('red',"\n *** WARNING ***\nNo result\n");
		$message_title = "Syntax check for Apache conf files";
		$msg_index = 'apachesyntax';
		$complete_result = $message['apachesyntax'];
	}
	elseif($msgId == "apachemodules") {
		Command_Windows("Show Apache loaded modules",40,30,0,'Show Apache loaded modules');
		$command = $c_apacheExe.'  -t -D DUMP_MODULES';
		$output = proc_open_output($command);
		$message['apachemodules'] = ($doReport ? "--------------------------------------------------\n" : '');
		if(!empty($output)) {
			$message['apachemodules'] .= "-- Apache loaded modules\n";
			if(stripos($output,'Syntax error') !== false) {
				$message['apachemodules'] .= color('red',"\n *** WARNING ***\n".$output);
			}
			else {
				$nb_static = preg_match_all("~^[ \t]*(.*) \(static\).*$~m",$output, $matches);
				if($nb_static > 0) {
					$message['apachemodules'] .= "- Core:\n";
					$nbbyline = 0;
					foreach($matches[1] as $value) {
						$message['apachemodules'] .= str_pad(' '.$value,18);
						if(++$nbbyline >= 3) {
							$message['apachemodules'] .= "\n";
							$nbbyline = 0;
						}
					}
					}
					$message['apachemodules'] .= "\n";
				$nb_shared = preg_match_all("~^[ \t]*(.*) \(shared\).*$~m",$output, $matches);
				if($nb_shared > 0) {
					$message['apachemodules'] .= "\n- Shared modules:\n";
					$nbbyline = 0;
					foreach($matches[1] as $value) {
						$message['apachemodules'] .= str_pad(' '.$value,24);
						if(++$nbbyline >= 3) {
							$message['apachemodules'] .= "\n";
							$nbbyline = 0;
						}
					}
					$message['apachemodules'] .= "\n";
				}
			}
		}
		else $message['apachemodules'] = color('red',"\n *** WARNING ***\nNo result\n");
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['apachemodules'],false,false,'ab');
			exit;
		}
		$message_title = "Apache loaded Modules";
		$msg_index = 'apachemodules';
		$complete_result = $message['apachemodules'];
	}
	elseif($msgId == "apacheincludes") {
		Command_Windows("Show Apache Includes loaded",40,30,0,'Show Apache Includes loaded');
		$command = $c_apacheExe.'  -t -D DUMP_INCLUDES';
		$output = proc_open_output($command);
		$message['apacheincludes'] = ($doReport ? "--------------------------------------------------\n" : '');
		if(!empty($output)) {
			$message['apacheincludes'] .= "Apache includes\n";
			if(stripos($output,'Syntax error') !== false)
				$message['apacheincludes'] .= color('red',"\n *** WARNING ***\n".$output);
			else
				$message['apacheincludes'] .= $output;
			if($doReport){
				write_file($c_installDir."/wampConfReportTemp.txt",$message['apacheincludes'],false,false,'ab');
				exit;
			}
			$message_title = "Apache loaded Includes";
			$msg_index = 'apacheincludes';
			$complete_result = $message['apacheincludes'];
		}
	}
	elseif($msgId == "apachedefine") {
		Command_Windows("Show Apache Define",40,30,0,'Show Apache variables');
		//Retrieve Apache variables from file wamp(64)\bin\apache\apache2.4.xx\wampdefineapache.conf
		$ApacheDefineMsg = retrieve_apache_define($c_apacheDefineConf);
		//Retrieve Apache variables from Apache itself (Define)
		$ApacheDefineVerifMsg = retrieve_apache_define($c_apacheDefineConf,true);
		$message['apachedefine'] = ($doReport ? "--------------------------------------------------\n" : '');
		$message['apachedefine'] .= "         Apache variables (Define)\n\n";
		$message['apachedefine'] .= "   - With command httpd.exe -t -D DUMP_RUN_CFG\n";
		if(empty($c_apacheError)) {
			foreach($ApacheDefineVerifMsg as $key => $value){
				$message['apachedefine'] .= $key." = ".$value."\n";
			}
			if($ApacheDefineMsg != $ApacheDefineVerifMsg) {
				$message['apachedefine'] .= color('red')."\n *** WARNING ***\nThere are differences between Define Apache and wampdefineapache.conf\n".color('black');
				$message['apachedefine'] .= "\n   - From wampdefineapache.conf file\n";
				foreach($ApacheDefineMsg as $key => $value){
					$message['apachedefine'] .= $key." = ".$value."\n";
				}
				$diff = array_diff($ApacheDefineMsg,$ApacheDefineVerifMsg) + array_diff($ApacheDefineVerifMsg,$ApacheDefineMsg);
				$message['apachedefine'] .= color('red')."   *** Differences\n";
				foreach($diff as $key => $value) {
					$message['apachedefine'] .= $key." = ".$value."\n";
				}
				$message['apachedefine'] .= color('black')."\n";
			}
		}
		else {
			$message['apachedefine'] .= color('red',"\n *** WARNING ***\n".$c_apacheError."\n");
		}
		if($doReport){
			write_file($c_installDir."/wampConfReportTemp.txt",$message['apachedefine'],false,false,'ab');
			exit;
		}
		$message_title = "Apache variables (Define)";
		$msg_index = 'apachedefine';
		$complete_result = $message['apachedefine'];
	}
	elseif($msgId == "phploadedextensions") {
		Command_Windows("Show PHP Loaded Extensions",40,30,0,'Show PHP Loaded Extensions');
		$command = $c_phpWebExe.' -c '.$c_phpConfFile.' -r print(var_export(get_loaded_extensions(),true));';
		$output = proc_open_output($command);
		$NewFileContents = '<?php'."\n\n".'$loaded_extensions = '.$output.';'."\n\n".'?>';
		write_file('loaded_extensions.php',$NewFileContents);
		include 'loaded_extensions.php';
		unlink('loaded_extensions.php');
		unset($NewFileContents,$output);
		natcasesort($loaded_extensions);
		$message['phpLoadedExtensions'] = ($doReport ? "--------------------------------------------------\n" : '');
		if(count($loaded_extensions) > 0) {
			$message['phpLoadedExtensions'] .= "-- PHP Loaded Extensions\n With function get_loaded_extensions()\n\n";
			$nbbyline = 0;
			foreach ($loaded_extensions as $extension) {
				$message['phpLoadedExtensions'] .= str_pad(' '.$extension,14);
				if(++$nbbyline >= 6) {
					$message['phpLoadedExtensions'] .= "\n";
					$nbbyline = 0;
				}
			}
			$message['phpLoadedExtensions'] .= "\n";
			if($doReport){
				write_file($c_installDir."/wampConfReportTemp.txt",$message['phpLoadedExtensions'],false,false,'ab');
				exit;
			}
			$message_title = "PHP Loaded Extensions";
			$msg_index = 'phpLoadedExtensions';
			$complete_result = $message['phpLoadedExtensions'];
		}
	}
	elseif($msgId == "refreshLogs") {
		$logToClean = array();
		$message = "\nLog file(s) to be cleaned:\n\n";
		$automaticAll = false;
		$date = IntlDateFormatter::formatObject(new DateTime('now'),"Y-MM-dd HH:mm");
		if(trim($_SERVER['argv'][2]) ==  'alllogs') {
			if(!empty($_SERVER['argv'][3]) && trim($_SERVER['argv'][3] == 'automatic')) {
				$automaticAll = true;
			}
			foreach($logFilesList as $value) {
				$logToClean[] = $value;
				$message .= "\t".$value."\n";
			}
		}
		else {
			for($i = 2 ; $i <= $nb_arg ; $i++) {
				$logToClean[$i] = trim($_SERVER['argv'][$i]);
				$message .= "\t".$logToClean[$i]."\n";
			}
		}
		if($automaticAll) {
			$touche = 'Y';
		}
		else {
			$message .= "\nDo you want to clean these file(s)? (Y/N)";
			Command_Windows($message,-1,-1,0,'Clean log files');
			$touche = strtoupper(trim(fgets(STDIN)));
		}
		if($touche == 'Y') {
			foreach($logToClean as $value) {
				if(file_exists($value)) {
					$fp = fopen($value, "wb");
					fwrite($fp,"--- File cleaned up by Wampserver ---\r\n");
					fwrite($fp,"--- on ".$date."\r\n");
  				fclose($fp);
				}
			}
			if($automaticAll) {
				// Clean tmp dir
				$fileTmp = glob($c_installDir.'/tmp/*');
				foreach($fileTmp as $file){
 					if(is_file($file)) {
 						if(unlink($file) === false) {
 							error_log("Unable to delete file: ".$file);
 						}
 					}
				}
			}
		}
		exit;
	}
	elseif($msgId == "checkXdebug") {
		Command_Windows('Check unused PHP xDebug dll\'s',40,-1,0,'Check unused PHP xDebug dll\'s');
		$Nbfiles = 0;
		$message = "\nCheck unused PHP xDebug dll's\n";
		// Delete unused xdebug dll's following successive updates of xDebug
		// Get all php versions
		$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php');
		foreach($phpVersionList as $phpVersion) {
			if(($files = glob($c_phpVersionDir.'/'.$phpVersion.'/zend_ext/php_xdebug-*.dll')) !== false) {
				if(count($files) > 1) {
					// Get php_xdebug...dll used by php version
					$phpIni = parse_ini_file($c_phpVersionDir.'/'.$phpVersion.'/phpForApache.ini',true);
					if(!empty($phpIni['xdebug']['zend_extension'])) {
						// Get files to delete
						foreach($files as $value) {
							if($value != $phpIni['xdebug']['zend_extension']) {
								// Delete file
								if(unlink($value) !== false) {
									$message .= $value." deleted\n";
									$Nbfiles++;
								}
							}
						}
					}
				}
			}
		}
		if($Nbfiles == 0) {
			$message .= "No unused xDebug dll file was found.\n";
		}
		$message .= "\nPress ENTER to continue ";
		Command_Windows($message,-1,-1,0,'Check unused PHP xDebug dll\'s');
		trim(fgets(STDIN));
		exit;
	}
	if(!empty($complete_result)) {
		$complete_result .= "\n--- Do you want to copy the results into Clipboard?\n--- Press the Y key to confirm - Press ENTER to continue...";
		$linesSup = 0;
		//if($msg_index == 'compilerversions') $linesSup = 3;
		if(!isset($message_title)) $message_title = 'Wampserver';
		Command_Windows($complete_result,-1,-1,$linesSup,$message_title);
    $confirm = trim(fgetc(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if($confirm == 'y') {
			write_file("temp.txt",color('clean',$complete_result), true);
		}
		exit(0);
 	}
}
//Command_Windows("\nPress ENTER to continue",30,3,0,' ');
trim(fgets(STDIN));

?>