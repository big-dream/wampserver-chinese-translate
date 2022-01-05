<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$newApacheVersion = $_SERVER['argv'][1];
$apacheNew = $newApacheVersion;
$apacheOld = $c_apacheVersion;
$compareOnly = false;
if(!empty($_SERVER['argv'][2]) && !empty($_SERVER['argv'][3]) && trim($_SERVER['argv'][3]) == 'compare') {
	$apacheOld = $_SERVER['argv'][2];
	$compareOnly = true;
}

if(!$compareOnly) {
	// loading the configuration file of the current php
	require $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampBinConfFiles;

	// it is verified that the new version of Apache is compatible with the current php
	$newApacheVersionTemp = $newApacheVersion;
	while (!isset($phpConf['apache'][$newApacheVersionTemp]) && $newApacheVersionTemp != '')
	{
	    $pos = strrpos($newApacheVersionTemp,'.');
	    $newApacheVersionTemp = substr($newApacheVersionTemp,0,$pos);
	}
	if($newApacheVersionTemp == '')
	{
	    exit();
	}
	//Restore some wampmanager.conf to default value before switching Apache version
	$wampIniNewContents = array();
	if($wampConf['apacheCompareVersion'] == 'on') {
		$wampIniNewContents['apacheCompareVersion'] = 'off';
		$wampConf['apacheCompareVersion'] = 'off';
	}
	if($wampConf['apacheRestoreFiles'] == 'on') {
		$wampIniNewContents['apacheRestoreFiles'] = 'off';
		$wampConf['apacheRestoreFiles'] = 'off';
	}
	if($wampConf['apachePhpCurlDll'] == 'on') {
		$wampIniNewContents['apachePhpCurlDll'] = 'off';
		$wampConf['apachePhpCurlDll'] = 'off';
		linkPhpDllToApacheBin($c_phpVersion);
	}
	if(count($wampIniNewContents) > 0) {
		wampIniSet($configurationFile, $wampIniNewContents);
	}

	// loading Wampserver configuration file of the new version of Apache
	require $c_apacheVersionDir.'/apache'.$newApacheVersion.'/'.$wampBinConfFiles;
}

// Verify new Apache version configuration from old Apache version
if($apacheNew != $apacheOld) {
	$majTodo = $majModules = $majIncludes = $majVhost = $majHttpdssl = $majOpenssl = $majCerts = $majListen = $majDefaultListen = false;
	$majModulesGo = $majIncludesGo = $majVhostGo = $majHttpdsslGo = $majOpensslGo = $majCertsGo = $majListenGo = $majDefaultListenGo = false;

	//--- File to save for LoadModule and Include arrays
	//    of old Apache and new Apache httpd.conf files
	$fp = fopen($c_installDir.'/bin/apache/save_apache.php', 'wb');
	fwrite($fp, "<?php\n\n");

	//--- Recover config of old Apache
	$apacheConfFile = $c_apacheVersionDir.'/apache'.$apacheOld.'/'.$wampConf['apacheConfDir'].'/'.$wampConf['apacheConfFile'];
	$httpdFileContents = @file_get_contents($apacheConfFile);
	// Recovering the extensions loading configuration
	preg_match_all('~^LoadModule\s+([0-9a-z_]+\s+modules/.+)\r?$~im',$httpdFileContents,$matchesON);
	preg_match_all('~^#LoadModule\s+([0-9a-z_]+\s+modules/.+)\r?$~im',$httpdFileContents,$matchesOFF);
	// Key = module_name - Value = Module loaded = 1, not loaded = 0
	$mod = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');
	// Key = module_name - Value = file name in modules/ folder
	ksort($mod);
	fwrite($fp, "\$modules_apache_old = ".var_export($mod, true).";\n\n");
	// Recovering the includes loading configuration
	preg_match_all('~^Include\s+(conf/.+)\r?$~im',$httpdFileContents,$matchesON);
	preg_match_all('~^#Include\s+(conf/.+)\r?$~im',$httpdFileContents,$matchesOFF);
	// Key = include_name - Value = Include loaded = 1, not loaded = 0
	$includes = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');
	ksort($includes);
	fwrite($fp, "\$includes_apache_old = ".var_export($includes, true).";\n\n");
	// Recovering default Listen Port
	preg_match('~^ServerName\s+localhost:([0-9]{2,5})~im',$httpdFileContents,$matches);
	$oldDefaultListenPort = $matches[1];
	unset($httpdFileContents);
	// Recovering Listen Ports
	$newListenPort = $oldListenPort = array();
	// We retrieve the 'Old' Apache variables (Define)
	$c_apacheDefineConf = $c_apacheVersionDir.'/apache'.$apacheOld.'/wampdefineapache.conf';
	$c_ApacheDefine = retrieve_apache_define($c_apacheDefineConf);
	$oldListenPort = listen_ports($apacheConfFile);
	foreach($oldListenPort as $key => $value) {
		if(strpos($value,'MYPORT') !== false) {
			$value = str_replace(array('${MYPORT','}'),'',$value);
			$oldListenPort[$key] = $value;
		}
	}

	//--- Recover config of new Apache
	$apacheConfFile = $c_apacheVersionDir.'/apache'.$apacheNew.'/'.$wampConf['apacheConfDir'].'/'.$wampConf['apacheConfFile'];
	$httpdFileContents = @file_get_contents($apacheConfFile);
	// Recovering the extensions loading configuration
	preg_match_all('~^LoadModule ([0-9a-z_]+ modules/.+)\r?$~im',$httpdFileContents,$matchesON);
	preg_match_all('~^#LoadModule ([0-9a-z_]+ modules/.+)\r?$~im',$httpdFileContents,$matchesOFF);
	// Key = module_name - Value = Module loaded = 1, not loaded = 0
	$mod = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');
	// Key = module_name - Value = file name in modules/ folder
	ksort($mod);
	fwrite($fp, "\$modules_apache_new = ".var_export($mod, true).";\n\n");
	// Recovering the includes loading configuration
	preg_match_all('~^Include (conf/.+)\r?$~im',$httpdFileContents,$matchesON);
	preg_match_all('~^#Include (conf/.+)\r?$~im',$httpdFileContents,$matchesOFF);
	// Key = include_name - Value = Include loaded = 1, not loaded = 0
	$includes = array_fill_keys($matchesON[1], '1') + array_fill_keys($matchesOFF[1], '0');
	ksort($includes);
	fwrite($fp, "\$includes_apache_new = ".var_export($includes, true).";\n\n");
	fwrite($fp, "?>\n");
	fclose($fp);
	// Recovering default Listen Port
	preg_match('~^ServerName\s+localhost:([0-9]{2,5})~im',$httpdFileContents,$matches);
	$newDefaultListenPort = $matches[1];
	unset($httpdFileContents);
	// We retrieve the 'New' Apache variables (Define)
	$c_apacheDefineConf = $c_apacheVersionDir.'/apache'.$apacheNew.'/wampdefineapache.conf';
	$c_ApacheDefine = retrieve_apache_define($c_apacheDefineConf);
	// Recovering Listen Ports
	$newListenPort = listen_ports($apacheConfFile);
	foreach($newListenPort as $key => $value) {
		if(strpos($value,'MYPORT') !== false) {
			$value = str_replace(array('${MYPORT','}'),'',$value);
			$newistenPort[$key] = $value;
		}
	}
	//Retrieve the Apache variables for the current version of Apache
	if($apacheNew <> $c_apacheVersion) {
		$c_apacheDefineConf = $c_apacheVersionDir.'/apache'.$c_apacheVersion.'/wampdefineapache.conf';
		$c_ApacheDefine = retrieve_apache_define($c_apacheDefineConf);
	}

	//--- Check difference between Default Listen Port
	if($newDefaultListenPort <> $oldDefaultListenPort) {
		$majTodo = $majDefaultListen = true;
	}

	//--- Check differences between new Apache and old Apache
	$moduleDiff = $includeDiff = array();
	$apacheNewConfFile = $c_apacheVersionDir.'/apache'.$apacheNew.'/'.$wampConf['apacheConfDir'].'/'.$wampConf['apacheConfFile'];
	$httpdNewFileContents = @file_get_contents($apacheNewConfFile);
	include $c_installDir.'/bin/apache/save_apache.php';
	$count = 0;
	$FindModuleTxt = $ReplaceModuleTxt = array();
	foreach($modules_apache_old as $key => $value) {
		//Does same LoadModule exist in New Apache
		if(array_key_exists($key, $modules_apache_new)) {
			//key exists - Same value - loaded (1) or not loaded (0) ?
			if($modules_apache_new[$key] <> $value) {
				$majTodo = $majModules = true;
				if($value == 1) {//Load module
					$FindModuleTxt[]  = '#LoadModule '.$key;
					$ReplaceModuleTxt[]  = 'LoadModule '.$key;
					$moduleDiff[$key] = false;
				}
				else {//Don't load module
					$FindModuleTxt[]  = 'LoadModule '.$key;
					$ReplaceModuleTxt[]  = '#LoadModule '.$key;
					$moduleDiff[$key] = true;
				}
			}
		}
	}

	// --- Compare new Apache include with old Apache
	$FindIncludeTxt = $ReplaceIncludeTxt = array();
	foreach($includes_apache_old as $key => $value) {
		//Does same include exist in New Apache
		if(array_key_exists($key,$includes_apache_new)) {
			//key exists - Same value - loaded (1) or not loaded (0) ?
			if($includes_apache_new[$key] <> $value) {
				$majTodo = $majIncludes = true;
				if($value == 1) {//Include
					$FindIncludeTxt[]  = '#Include '.$key;
					$ReplaceIncludeTxt[]  = 'Include '.$key;
					$includeDiff[$key] = false;
				}
				else {//Don't Include
					$FindIncludeTxt[]  = 'Include '.$key;
					$ReplaceIncludeTxt[]  = '#Include '.$key;
					$includeDiff[$key] = true;
				}
			}
		}
	}
	unlink($c_installDir.'/bin/apache/save_apache.php');

	//Compare httpd-vhosts.conf
	$oldVhost = $c_apacheVersionDir.'/apache'.$apacheOld.'/'.$wampConf['apacheConfDir'].'/extra/httpd-vhosts.conf';
	$newVhost = $c_apacheVersionDir.'/apache'.$apacheNew.'/'.$wampConf['apacheConfDir'].'/extra/httpd-vhosts.conf';
	//if identical files, copy no asked
	$content1 = file($oldVhost, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$content2 = file($newVhost, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$nbVhostOld = count($content1);
	$nbVhostNew = count($content2);
	$lineDiffVhost = false;
	if(abs($nbVhostOld - $nbVhostNew) > 3) {
		$majTodo = $majVhost = true;
	}
	else {
		$oldLineDiffVhost = $newLineDiffVhost = array();
		$count = 0;
		reset($content2);
		foreach($content1 as $key => $value) {
			$value2 = current($content2);
			if($value <> $value2) {
				$oldLineDiffVhost[$key] = $value;
				$newLineDiffVhost[$key] = $value2;
				$majTodo = $majVhost = $lineDiffVhost = true;
				if($count++ > 3) break;
			}
			next($content2);
		}
	}
	unset($content1,$content2);

	//Compare httpd-ssl.conf
	$oldSslConf = $c_apacheVersionDir.'/apache'.$apacheOld.'/'.$wampConf['apacheConfDir'].'/extra/httpd-ssl.conf';
	$newSslConf = $c_apacheVersionDir.'/apache'.$apacheNew.'/'.$wampConf['apacheConfDir'].'/extra/httpd-ssl.conf';
	//if identical files, copy no asked
	$content1 = file($oldSslConf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$content2 = file($newSslConf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$nbSslOld = count($content1);
	$nbSslNew = count($content2);
	$lineDiffSsl = false;
	if(abs($nbSslOld - $nbSslNew) > 3) {
		$majTodo = $majHttpdssl = true;
	}
	else {
		$oldLineDiffSsl = $newLineDiffSsl = array();
		$count = 0;
		reset($content2);
		foreach($content1 as $key => $value) {
			$value2 = current($content2);
			if($value <> $value2) {
				$oldLineDiffSsl[$key] = $value;
				$newLineDiffSsl[$key] = $value2;
				$majTodo = $majHttpdssl = $lineDiffSsl = true;
				if($count++ > 3) break;
			}
			next($content2);
		}
	}
	unset($content1,$content2);

	//Compare openssl.cnf
	$oldOpenssl = $c_apacheVersionDir.'/apache'.$apacheOld.'/'.$wampConf['apacheConfDir'].'/openssl.cnf';
	$newOpenssl = $c_apacheVersionDir.'/apache'.$apacheNew.'/'.$wampConf['apacheConfDir'].'/openssl.cnf';
	//if identical files, copy no asked
	$content1 = file($oldOpenssl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$content2 = file($newOpenssl, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$nbOpenOld = count($content1);
	$nbOpenNew = count($content2);
	$lineDiffOpen = false;
	if(abs($nbOpenOld - $nbOpenNew) > 3) {
		$majTodo = $majOpenssl = true;
	}
	else {
		$oldLineDiffOpen = $newLineDiffOpen = array();
		$count = 0;
		reset($content2);
		foreach($content1 as $key => $value) {
			$value2 = current($content2);
			if($value <> $value2) {
				$oldLineDiffOpen[$key] = $value;
				$newLineDiffOpen[$key] = $value2;
				$majTodo = $majOpenssl = $lineDiffOpen = true;
				if($count++ > 3) break;
			}
			next($content2);
		}
	}
	unset($content1,$content2);

	//Compare Certificats if exist
	$oldDirCerts = $c_apacheVersionDir.'/apache'.$apacheOld.'/'.$wampConf['apacheConfDir'].'/Certs';
	$newDirCerts = $c_apacheVersionDir.'/apache'.$apacheNew.'/'.$wampConf['apacheConfDir'].'/Certs';
	$oldDirCertsAnti = str_replace('/','\\',$oldDirCerts);
	$newDirCertsAnti = str_replace('/','\\',$newDirCerts);
	function short_path(&$item,$key){
		global $oldDirCerts,$newDirCerts;
		$item = str_ireplace($oldDirCerts.'/','',$item);
		$item = str_ireplace($newDirCerts.'/','',$item);
	}

	$files1 = $files2 = array();
	$CertsOld = $CertsNew = true;
	if(is_dir($oldDirCerts)) {
		$files1 = read_dir($oldDirCerts);
		array_walk($files1,'short_path');
	}
	else {
		$CertsOld = false;
	}
	if(is_dir($newDirCerts)) {
		$files2 = read_dir($newDirCerts);
		array_walk($files2,'short_path');
	}
	else {
		$CertsNew = false;
	}
	//Only one Apache version has Certs directory
	if($CertsNew !== $CertsOld) $majTodo = $majCerts = true;
	//Compare directories if all Apache Version have Certs directories
	$notCertsNew = $notCertsOld = array();
	if($CertsNew && $CertsOld){
		$notCertsNew = array_diff($files1, $files2);
		$notCertsOld = array_diff($files2, $files1);
		if(count($notCertsNew) > 0) {
			$majTodo = $majCerts = true;
			//Files on Old not found on New
		}
		if(count($notCertsOld) > 0) {
			$majTodo = $majCerts = true;
			//Files on New not found on Old
		}
	}
	unset($files1,$files2);

	//Compare Listen Port added
	$nbListenOld = count($oldListenPort);
	$nbListenNew = count($newListenPort);
	$notListenNew = $notListenOld = array();
	if($nbListenOld <> $nbListenNew) {
		$majTodo = $majListen = true;
		foreach($oldListenPort as $value) {
			if(!in_array($value,$newListenPort)) {
				$notListenNew[] = $value;
			}
		}
		foreach($newListenPort as $value) {
			if(!in_array($value,$oldListenPort)) {
				$notListenOld[] = $value;
			}
		}
	}
	// End of verify

	// Do we need to update some thing?
	if($majTodo) {
		$YESred = color('red','YES');
		$NOgreen = color('green','NO');
		generatemessage:
		$message = str_repeat('-',86)."\n";
		$message = color('blue');
		if($compareOnly) {
			$message .= str_repeat(' ',9)."比较 APACHE ".$apacheNew."（当前）和 APACHE ".$apacheOld." 的设置\n";
		}
		else {
			$message .= str_repeat(' ',9)."切换 APACHE 版本 - 从 APACHE ".$apacheOld." 到 ".$apacheNew."\n";
		}
		$message .= str_repeat(' ',12).color('red')."Apache ".$apacheNew." 和 Apache ".$apacheOld.color('black')."之间存在差异\n";
		$message .= str_repeat('-',86)."\n";
		if($majModules) {
			$message .= str_pad("  *** -> LoadModule",48).str_pad("按 'M' 选择 ".($majModulesGo ? 'NO' : 'YES'),16)."- 更新：".($majModulesGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad("   模块",30).str_pad($apacheNew,12).$apacheOld."\n";
			foreach($moduleDiff as $key => $value) {
				$key0 = explode(' ',$key);
				$temp = str_pad(trim($key0[0]),30).($value ? str_pad("已加载",12) : str_pad("未加载",12));
				$temp .= ($value ? "未加载 " : "已加载");
				$message .= $temp."\n";
			}
			$message .= str_repeat('-',86)."\n";
		}
		if($majIncludes) {
			$message .= str_pad("  *** -> Include",48).str_pad("按 'I' 选择 ".($majIncludesGo ? 'NO' : 'YES'),16)."- 更新：".($majIncludesGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad("   Include",30).str_pad($apacheNew,12).$apacheOld."\n";
			foreach($includeDiff as $key => $value) {
				$key = str_replace('conf/extra/','',trim($key));
				$temp = str_pad($key,30).($value ? str_pad("已加载",12) : str_pad("未加载",12));
				$temp .= ($value ? "未加载 " : "已加载");
				$message .= $temp."\n";
			}
			$message .= str_repeat('-',86)."\n";
		}
		if($majVhost) {
			$message .= str_pad("  *** -> httpd-vhosts.conf",48).str_pad("按 'V' 选择 ".($majVhostGo ? 'NO' : 'YES'),16)."- 更新：".($majVhostGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad(" ",30).str_pad($apacheNew,12).$apacheOld."\n";
			$message .= str_pad("行数",30).str_pad($nbVhostNew,12).$nbVhostOld."\n";
			if(($nbVhostNew == $nbVhostOld) || $lineDiffVhost) {
				$message .= str_repeat(' ',22)."至少有 1 行不同\n";
				if($lineDiffVhost) {
					reset($oldLineDiffVhost);
					foreach($newLineDiffVhost as $key => $value) {
						$value2 = current($oldLineDiffVhost);
						$message .= str_pad("行".$key,10).str_pad($apacheNew,8)." : ".$value."\n".str_pad(' ',10).str_pad($apacheOld,8)." : ".$value2."\n";
						next($oldLineDiffVhost);
					}
				}
			}
		$message .= str_repeat('-',86)."\n";
		}
		if($majDefaultListen){
			$message .= str_pad("  *** -> 使用的默认端口",48).str_pad("按 'P' 选择 ".($majDefaultListenGo ? 'NO' : 'YES'),16)."- 更新：".($majDefaultListenGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad(" ",30).str_pad($apacheNew,12).$apacheOld."\n";
			$message .= str_pad("端口",30).str_pad($newDefaultListenPort,12).$oldDefaultListenPort."\n";
			if(!$compareOnly){
				if($majVhost) $message .= color('blue')."  如果只有 httpd-vhosts.conf 文件的区别\n   是端口号 ".$c_DefaultPort." 还是 ".$c_UsedPort.color('black')."\n";
				$message .= color('blue')."   默认的 Apache 监听端口会自动更新\n   通过这个 Apache 版本切换程序.".color('black')."\n";
			}
			$message .= str_repeat('-',86)."\n";
		}
		if($majHttpdssl) {
			$message .= str_pad("  *** -> httpd-ssl.conf",48).str_pad("按 'H' 选择 ".($majHttpdsslGo ? 'NO' : 'YES'),16)."- 更新：".($majHttpdsslGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad(" ",30).str_pad($apacheNew,12).$apacheOld."\n";
			$message .= str_pad("行数",30).str_pad($nbSslNew,12).$nbSslOld."\n";
			if(($nbSslNew == $nbSslOld) || $lineDiffSsl) {
				$message .= str_repeat(' ',22)."至少有 1 行不同\n";
				if($lineDiffSsl) {
					reset($oldLineDiffSsl);
					foreach($newLineDiffSsl as $key => $value) {
						$value2 = current($oldLineDiffSsl);
						$message .= str_pad("行".$key,10).str_pad($apacheNew,8)." : ".$value."\n".str_pad(' ',10).str_pad($apacheOld,8)." : ".$value2."\n";
						next($oldLineDiffSsl);
					}
				}
			}
			$message .= str_repeat('-',86)."\n";
		}
		if($majOpenssl) {
			$message .= str_pad("  *** -> openssl.cnf",48).str_pad("按 'O' 选择 ".($majOpensslGo ? 'NO' : 'YES'),16)."- 更新：".($majOpensslGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad(" ",30).str_pad($apacheNew,12).$apacheOld."\n";
			$message .= str_pad("行数",30).str_pad($nbOpenNew,12).$nbOpenOld."\n";
			if(($nbOpenNew == $nbOpenOld) || $lineDiffOpen) {
				$message .= str_repeat(' ',22)."至少有 1 行不同\n";
				if($lineDiffOpen) {
					reset($oldLineDiffOpen);
					foreach($newLineDiffOpen as $key => $value) {
						$value2 = current($oldLineDiffOpen);
						$message .= str_pad("行".$key,10).str_pad($apacheNew,8)." : ".$value."\n".str_pad(' ',10).str_pad($apacheOld,8)." : ".$value2."\n";
						next($oldLineDiffOpen);
					}
				}
			}
			$message .= str_repeat('-',86)."\n";
		}
		if($majCerts) {
			$message .= str_pad("  *** -> 证书目录 (SSL 证书)",48).str_pad("按 'C' 选择 ".($majCertsGo ? 'NO' : 'YES'),16)."- 更新：".($majCertsGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad(" ",50).str_pad($apacheNew,12).$apacheOld."\n";
			$message .= str_pad("证书目录",50).($CertsNew ? str_pad("存在",12) : str_pad("不存在",12)).($CertsOld ? str_pad("存在",12) : str_pad("不存在",12))."\n";
			if(count($notCertsNew) > 0) {
				//Certs files not found on Apache New
				$message .= str_pad("文件或目录",50)."\n";
				foreach($notCertsNew as $value) {
					$message .= str_pad($value,50)."不存在\n";
				}
			}
			if(count($notCertsOld) > 0) {
				//Certs files not found on Apache New
				$message .= str_pad("文件或目录",50)."\n";
				foreach($notCertsOld as $value) {
					$message .= str_pad($value,62)."不存在\n";
				}
			}
			$message .= str_repeat('-',86)."\n";
		}
		$listenToAdd = $listenToDel = false;
		if($majListen) {
			$message .= str_pad("  *** -> 监听端口",48).str_pad("按 'L' 键选择 ".($majListenGo ? 'NO' : 'YES'),16)."- 更新：".($majListenGo ? $YESred : $NOgreen)."\n";
			$message .= str_pad(" ",10).$apacheNew." : ".implode(" - ",$newListenPort)."\n";
			if(!empty($notListenNew)) {
				$listenToAdd = true;
				$message .= str_pad(" ",19)."增加监听端口 : ".implode(" - ",$notListenNew)."\n";
			}
			if(!empty($notListenOld)) {
				$listenToDel = true;
				$message .= str_pad(" ",19)."取消监听端口 : ".implode(" - ",$notListenOld)."\n";
			}
			$message .= str_pad(" ",10).$apacheOld." : ".implode(" - ",$oldListenPort)."\n";
			$message .= str_repeat('-',86)."\n";
		}
		$message .= "    是否要复制或更新配置文件\n";
		$message .= "    ".color('red','从 Apache '.$apacheOld)." -> 到 Apache ".$apacheNew."\n\n";
		$message .= "要        ".color('blue','更新所有')."       按 ".color('blue',"'A'")." 键，然后按 Enter 键\n";
		$message .= "要        ".color('blue','重置选择')."       按 ".color('blue',"'R'")." 键，然后按 Enter 键\n";
		$message .= "要        ".color('blue',"取消更新")."       只需按 ".color('blue',"Enter")." 键\n";
		$message .= "如果      ".color('blue',"选择完毕")."       按 ".color('blue',"'G'")." 键，然后按 Enter 键\n";
		$message .= "要选择选项，按关联的键，然后按 Enter 键： ";
		//Write message in Command Windows
		Command_Windows($message,-1,-1,0,'比较 Apache 版本');
		$touche = strtoupper(trim(fgets(STDIN)));
		if($touche == 'A') {
			$majModulesGo = $majIncludesGo = $majVhostGo = $majHttpdsslGo = $majOpensslGo = $majCertsGo = $majListenGo = $majDefaultListenGo = true;
			goto generatemessage;
		}
		elseif($touche == 'R') {
			$majModulesGo = $majIncludesGo = $majVhostGo = $majHttpdsslGo = $majOpensslGo = $majCertsGo = $majListenGo = $majDefaultListen = false;
			goto generatemessage;
		}
		elseif($touche == 'M') {
			$majModulesGo = ($majModulesGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'I') {
			$majIncludesGo = ($majIncludesGo ? false :true);
			goto generatemessage;
		}
		elseif($touche == 'V') {
			$majVhostGo = ($majVhostGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'P') {
			$majDefaultListenGo = ($majDefaultListenGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'H') {
			$majHttpdsslGo = ($majHttpdsslGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'O') {
			$majOpensslGo = ($majOpensslGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'C') {
			$majCertsGo = ($majCertsGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'L') {
			$majListenGo = ($majListenGo ? false : true);
			goto generatemessage;
		}
		elseif($touche == 'G') {}
		else{
		$majTodo = $majModules = $majIncludes = $majVhost = $majHttpdssl = $majOpenssl = $majCerts = $majListen = $majDefaultListen = false;
		$majModulesGo = $majIncludesGo = $majVhostGo = $majHttpdsslGo = $majOpensslGo = $majCertsGo = $majListenGo = $majDefaultListenGo = false;
		}
	}
	elseif($compareOnly){
		$message = str_repeat('-',46)."\n";
		$message .= "  *** 没有配置差异\n";
		$message .= "  *** Apache ".$apacheOld." 和 Apache ".$apacheNew." 之间\n";
		$message .= "  *** 无需更新任何内容\n";
		$message .= "按 Enter 键继续 ";
		//Write message in Command Windows
		Command_Windows($message,-1,-1,0,'比较 Apache 版本');
		$touche = strtoupper(trim(fgets(STDIN)));
	}
	$copyConf = $FileToWrite = false;
	if($majTodo) {
		// Modify LoadModule?
		if($majModules &&$majModulesGo) {
			//Load or unload Module
			$httpdNewFileContents = str_replace($FindModuleTxt,$ReplaceModuleTxt,$httpdNewFileContents,$count);
			if($count > 0) $FileToWrite = true;
		}
		// Modify Include?
		if($majIncludes && $majIncludesGo) {
			//Load or unload Include
			$httpdNewFileContents = str_replace($FindIncludeTxt,$ReplaceIncludeTxt,$httpdNewFileContents,$count);
			if($count > 0) $FileToWrite = true;
		}
		if($majListen && $majListenGo) {
			if($listenToAdd) {
				foreach($notListenNew as $value) {
					//Check validity
					if($value <= 80 || $value == 8080 || ($value > 81 && $value < 1025) || $value > 65535) continue;
					$count = 0;
					$search = array(
						"~^([ \t]*Define[ \t]+APACHE_DIR[ \t]+.*)\s?$~m",
						"~^([ \t]*Listen[ \t]+\[::0\]:".$c_UsedPort.")\s?$~m",
					);
					$replace = array (
						'${1}'."\r\n".'Define MYPORT'.$value.' '.$value,
						'${1}'."\r\n".'Listen 0.0.0.0:${MYPORT'.$value.'}'."\r\n".'Listen [::0]:${MYPORT'.$value.'}',
					);
					$httpdNewFileContents = preg_replace($search,$replace,$httpdNewFileContents, -1, $count);
					if($count == 2) $FileToWrite = true;
				}
			}
			if($listenToDel) {
				foreach($notListenOld as $value) {
					//Check validity
					if($value <= 80 || $value == 8080 || ($value > 81 && $value < 1025) || $value > 65535) continue;
					$count = 0;
					$search = array(
						"~^Define[ \t]+MYPORT".$value."[ \t]+.*\s?$~m",
						"~^Listen[ \t]+.*MYPORT".$value.".*\s?$~m",
					);
					$replace = array (
						'',
						'',
					);
					$httpdNewFileContents = preg_replace($search,$replace,$httpdNewFileContents, -1, $count);
					if($count == 3) $FileToWrite = true;
				}
			}

		}//end of MajListen
		if($majDefaultListenGo){
			//Update httpd.conf
			$findTxtRegex = array(
			'/^(Listen 0.0.0.0:)[0-9]{2,5}/m',
			'/^(Listen \[::0\]:)[0-9]{2,5}/m',
			'/^(ServerName localhost:)[0-9]{2,5}/m',
			);
			$search = $replace = array();
			foreach($findTxtRegex as $value) {
				if(preg_match_all($value,$httpdNewFileContents,$matches,PREG_SET_ORDER) > 0) {
					foreach($matches as $key => $value) {
						if($value[0] <> $value[1].$oldDefaultListenPort) {
							$search[] = $value[0];
							$replace[] = $value[1].$oldDefaultListenPort;
						}
					}
				}
			}
			if(count($search) > 0) {
				$httpdNewFileContents = str_replace($search,$replace,$httpdNewFileContents,$count);
				if($count > 0) $FileToWrite = true;
			}

			//Update httpd-vhosts.conf
			$virtualHost = check_virtualhost(true);
			if($virtualHost['include_vhosts'] && $virtualHost['vhosts_exist']) {
				$c_vhostConfFile = $virtualHost['vhosts_file'];
				$myVhostsContents = file_get_contents($c_vhostConfFile) or die ("httpd-vhosts.conf 文件不存在");
				$findTxtRegex = '/^([ \t]*<VirtualHost[ \t]+.+:)[0-9]{2,5}>/m';
				$replaceTxtRegex = '${1}'.$oldDefaultListenPort.'>';

				$myVhostsContents = preg_replace($findTxtRegex,$replaceTxtRegex, $myVhostsContents, -1, $count);
				if($count > 0) write_file($c_vhostConfFile,$myVhostsContents);
			}

			//Update wampmanager.conf
			$apacheConf['apachePortUsed'] = $oldDefaultListenPort;
			if($oldDefaultListenPort == $c_DefaultPort) {
				$apacheConf['apacheUseOtherPort'] = "off";
			}
			else {
				$apacheConf['apacheUseOtherPort'] = "on";
			}
			wampIniSet($configurationFile, $apacheConf);
		}
		if($FileToWrite) {
			//Save Apache new version httpd.conf file
			write_file($apacheNewConfFile,$httpdNewFileContents);
			$copyConf = true;
		}
		unset($httpdNewFileContents);
		// Rewrite httpd-vhosts.conf's?
		if($majVhost && $majVhostGo) {
			if(copy($oldVhost,$newVhost) === false) {
				error_log("**** 复制错误 ****\n".$oldVhost."\nto\n".$newVhost."\n");
			}
			else $copyConf = true;
		}
		// Rewrite httpd-ssl.conf's?
		if($majHttpdssl && $majHttpdsslGo) {
			if(copy($oldSslConf,$newSslConf) === false) {
				error_log("**** 复制错误 ****\n".$oldSslConf."\nto\n".$newSslConf."\n");
			}
			else $copyConf = true;
		}
		// Rewrite openssl.cnf's?
		if($majOpenssl && $majOpensslGo) {
			if(copy($oldOpenssl,$newOpenssl) === false) {
				error_log("**** 复制错误 ****\n".$oldOpenssl."\nto\n".$newOpenssl."\n");
			}
			else $copyConf = true;
		}
		// Do we need to copy certificates SSL?
		if($majCerts && $majCertsGo) {
			$command = "xcopy ".$oldDirCertsAnti." ".$newDirCertsAnti." /E /Y /I /Q";
			`$command`;
			$copyConf = true;
		}
	}
}

if(!$compareOnly) {
	$apacheConf['apacheVersion'] = $newApacheVersion;
	wampIniSet($configurationFile, $apacheConf);
}

?>