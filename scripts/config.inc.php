<?php
// Update 3.2.0
// PHP 7.4.0 support
// Possibility to trace Wampmanager processes

if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

$configurationFile = '../wampmanager.conf';
// Loading Wampserver configuration
$wampConf = @parse_ini_file($configurationFile);
$c_installDir = $wampConf['installDir'];
$configurationFile = $c_installDir.'/wampmanager.conf';
$templateFile = $c_installDir.'/wampmanager.tpl';
$wampserverIniFile = $c_installDir.'/wampmanager.ini';
$wwwDir = $c_installDir.'/www';
$langDir = $c_installDir.'/lang/';
$aliasDir = $c_installDir.'/alias/';
$modulesDir = 'modules/';
$logDir = 'logs/';
$wampBinConfFiles = 'wampserver.conf';
$phpConfFileForApache = 'phpForApache.ini';

//We enter the variables of the template with the local conf
$c_wampVersion = $wampConf['wampserverVersion'];
$c_wampMode = $wampConf['wampserverMode'];
$c_wampserverID = ($c_wampMode == '32bit') ? '{wampserver32}' : '{wampserver64}';
$c_wampVersionInstall = !empty($wampConf['installVersion']) ? $wampConf['installVersion'] : 'unknown';
$c_navigator = $wampConf['navigator'];
//For Windows 10 and Edge it is not the same as for other browsers
//It is not complete path to browser with parameter http://website/
//but by 'cmd.exe /c "start /b Microsoft-Edge:http://website/"'
$c_edge = "";
$c_edgeDefinedError = false;
if($c_navigator == "Edge") {
	//Check if Windows 10
	if(php_uname('r') < 10) {
		error_log("Edge should be defined as default navigator only with Windows 10");
		if(file_exists("c:/Program Files (x86)/Internet Explorer/iexplore.exe"))
			$c_navigator = "c:/Program Files (x86)/Internet Explorer/iexplore.exe";
		elseif(file_exists("c:/Program Files/Internet Explorer/iexplore.exe"))
			$c_navigator = "c:/Program Files/Internet Explorer/iexplore.exe";
		else
			$c_navigator = "iexplore.exe";
		$c_edgeDefinedError = true;
	}
	else {
	$c_navigator = "cmd.exe";
	$c_edge = "/c start /b Microsoft-Edge:";
	}
}
$c_editor = $wampConf['editor'];
$c_logviewer = $wampConf['logviewer'];

//Adding Variables for Ports
$c_DefaultPort = "80";
$c_UsedPort = $wampConf['apachePortUsed'];
$c_DefaultMysqlPort = $wampConf['mysqlDefaultPort'];
$c_UsedMysqlPort = $wampConf['mysqlPortUsed'];
$c_UsedMariaPort = $wampConf['mariaPortUsed'];

//Variables for Apache
$c_apacheService = $wampConf['ServiceApache'];
$c_apacheVersion = $wampConf['apacheVersion'];
$c_apacheServiceInstallParams = $wampConf['apacheServiceInstallParams'];
$c_apacheServiceRemoveParams = $wampConf['apacheServiceRemoveParams'];
$c_apacheVersionDir = $c_installDir.'/bin/apache';
$c_apacheBinDir = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'];
$c_apacheConfFile = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheConfDir'].'/'.$wampConf['apacheConfFile'];
$c_apacheVhostConfFile = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheConfDir'].'/extra/httpd-vhosts.conf';
$c_apacheAutoIndexConfFile = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheConfDir'].'/extra/httpd-autoindex.conf';
$c_apacheExe = $c_apacheBinDir.'/'.$wampConf['apacheExeFile'];

// We retrieve the Apache variables (Define)
$c_apacheDefineConf = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/wampdefineapache.conf';
if(file_exists($c_apacheDefineConf)) {
	$c_ApacheDefine = @parse_ini_file($c_apacheDefineConf);
}
else
	$c_ApacheDefine = array();

//Variables for PHP
$c_phpVersion = $wampConf['phpVersion'];
$c_phpCliVersion = $wampConf['phpCliVersion'];
$c_phpVersionDir = $c_installDir.'/bin/php';
$c_phpConfFile = $c_apacheVersionDir.'/apache'.$wampConf['apacheVersion'].'/'.$wampConf['apacheExeDir'].'/'.$wampConf['phpConfFile'];
$c_phpConfFileIni = $c_phpVersionDir.'/php'.$c_phpVersion.'/'.$wampConf['phpConfFile'];
$c_phpCliConfFile = $c_phpVersionDir.'/php'.$c_phpCliVersion.'/'.$wampConf['phpConfFile'];
$c_phpExe = $c_phpVersionDir.'/php'.$c_phpCliVersion.'/'.$wampConf['phpExeFile'];
$c_phpCli = $c_phpVersionDir.'/php'.$c_phpCliVersion.'/'.$wampConf['phpCliFile'];
$phpExtDir = $c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/ext/';
$phpCliMinVersion = "5.5.0";

//Variables for MySQL
$c_mysqlService = $wampConf['ServiceMysql'];
$c_mysqlPortUsed = $wampConf['mysqlPortUsed'];
$c_mysqlVersion = $wampConf['mysqlVersion'];
$c_mysqlServiceInstallParams = $wampConf['mysqlServiceInstallParams'];
$c_mysqlServiceRemoveParams = $wampConf['mysqlServiceRemoveParams'];
$c_mysqlVersionDir = $c_installDir.'/bin/mysql';
$c_mysqlBinDir = $c_mysqlVersionDir.'/mysql'.$wampConf['mysqlVersion'].'/'.$wampConf['mysqlExeDir'];
$c_mysqlExe = $c_mysqlBinDir.'/'.$wampConf['mysqlExeFile'];
$c_mysqlConfFile = $c_mysqlVersionDir.'/mysql'.$wampConf['mysqlVersion'].'/'.$wampConf['mysqlConfDir'].'/'.$wampConf['mysqlConfFile'];
$c_mysqlConsole = $c_mysqlVersionDir.'/mysql'.$c_mysqlVersion.'/'.$wampConf['mysqlExeDir'].'/mysql.exe';

// Variables for MariaDB
$c_mariadbService = $wampConf['ServiceMariadb'];
$c_mariadbPortUsed = $wampConf['mariaPortUsed'];
$c_mariadbVersion = $wampConf['mariadbVersion'];
$c_mariadbServiceInstallParams = $wampConf['mariadbServiceInstallParams'];
$c_mariadbServiceRemoveParams = $wampConf['mariadbServiceRemoveParams'];
$c_mariadbVersionDir = $c_installDir.'/bin/mariadb';
$c_mariadbBinDir = $c_mariadbVersionDir.'/mariadb'.$wampConf['mariadbVersion'].'/'.$wampConf['mariadbExeDir'];
$c_mariadbExe = $c_mariadbBinDir.'/'.$wampConf['mariadbExeFile'];
$c_mariadbConfFile = $c_mariadbVersionDir.'/mariadb'.$wampConf['mariadbVersion'].'/'.$wampConf['mariadbConfDir'].'/'.$wampConf['mariadbConfFile'];
$c_mariadbConsole = $c_mariadbVersionDir.'/mariadb'.$c_mariadbVersion.'/'.$wampConf['mariadbExeDir'].'/mysql.exe';

//Check symlink or copy PHP dll into Apache bin folder
if(empty($wampConf['CreateSymlink']) || $wampConf['CreateSymlink'] != 'symlink' && $wampConf['CreateSymlink'] != 'copy')
	$wampConf['CreateSymlink'] = 'symlink';

//Check hosts file writable
$c_hostsFile = str_replace("\\","/",getenv('WINDIR').'/system32/drivers/etc/hosts');
$c_hostsFile_writable = true;
$WarningMsg = '';
if(file_exists($c_hostsFile)) {
	if(!is_file($c_hostsFile)) {
		$WarningMsg .= $c_hostsFile." is not a file\r\n";
	}
	elseif(!is_writable($c_hostsFile)) {
		if(chmod($c_hostsFile, 0644) === false) {
			$WarningMsg .= "Impossible to modify the file ".$c_hostsFile." to be writable\r\n";
		}
		if(!is_writable($c_hostsFile)) {
			$WarningMsg .= "The file ".$c_hostsFile." is not writable";
		}
	}
}
else {
	$WarningMsg .= "The file ".$c_hostsFile." does not exists\r\n";
}
if(!empty($WarningMsg)) {
	$c_hostsFile_writable = false;
	error_log($WarningMsg);
	if(WAMPTRACE_PROCESS) error_log("script ".__FILE__."\n*** ".$WarningMsg."\n",3,WAMPTRACE_FILE);
}
//Check last number of wampsave hosts
$next_hosts_save = 0;
if($wampConf['BackupHosts'] == 'on') {
	$hosts_wampsave = @glob($c_hostsFile.'_wampsave.*');
	if(count($hosts_wampsave) > 0) {
		$next_hosts_save = pathinfo(end($hosts_wampsave),PATHINFO_EXTENSION) + 1;
	}
}
//End check hosts writable

//dll to create symbolic links from php to apache/bin
//Versions of ICU are 38, 40, 42, 44, 46, 48 to 57, 60 (PHP 7.2), 61 (PHP 7.2.5), 62 (PHP 7.2.8), 63 (PHP 7.2.12), 64 (PHP 7.2.20), 65 (PHP 7.4.0)
$icu = array(
	'number' => array('65','64', '63', '62', '61', '60', '57', '56', '55', '54', '53', '52', '51', '50', '49', '48', '46', '44', '42', '40', '38'),
	'name' => array('icudt', 'icuin', 'icuio', 'icule', 'iculx', 'icutest', 'icutu', 'icuuc'),
	);
$php_icu_dll = array();
foreach($icu['number'] as $icu_number) {
	foreach($icu['name'] as $icu_name) {
		$php_icu_dll[] = $icu_name.$icu_number.".dll";
	}
}

$phpDllToCopy = array_merge(
	$php_icu_dll,
	array (
	'libmysql.dll',
	'libeay32.dll',
	'ssleay32.dll',
	'libsasl.dll',
	'libpq.dll',
	'libssh2.dll', //For php 5.5.17
	'libsodium.dll', //For php 7.2.0
	'libsqlite3.dll', //For php 7.4.0
	'php5isapi.dll',
	'php5nsapi.dll',
	'php5ts.dll',
	'php7ts.dll', //For PHP 7
	)
);

//Values must be the same as in php.ini - xdebug parameters must be the latest
$phpParams = array (
	'allow_url_fopen',
	'allow_url_include',
	'auto_globals_jit',
	'date.timezone',
	'default_charset',
	'display_errors',
	'display_startup_errors',
	'ignore_repeated_errors',
	'ignore_repeated_source',
	'report_memleaks',
	'log_errors',
	'expose_php',
	'file_uploads',
	'implicit_flush',
	'intl.default_locale',
	'max_execution_time',
	'max_input_time',
	'max_input_vars',
	'memory_limit',
	'output_buffering',
	'post_max_size',
	'realpath_cache_size',
	'realpath_cache_ttl',
	'register_argc_argv',
	'session.save_path',
	'short_open_tag',
	'upload_max_filesize',
	'upload_tmp_dir',
	'auto_detect_line_endings',
	'error_reporting',
	'filter.default',
	'include_path',
	'opcache.enable',
	'zend.enable_gc',
	'zlib.output_compression',
	'zlib.output_compression_level',
	'xdebug.profiler_enable_trigger',
	'xdebug.profiler_enable',
	'xdebug.remote_enable',
	'xdebug.overload_var_dump',
	);

//PHP parameters with values not On or Off cannot be switched on or off
//Can be changed if 'change' = true && 'title' && 'values'
//Parameter name must be also into $phpParams array
//To manualy enter value, 'Choose' must be the last 'values' and 'title' must be 'Size' or 'Seconds' or 'Integer'
//Warning : specific treatment for date.timezone - Don't modify.
$phpParamsNotOnOff = array(
	'date.timezone' => array(
		'change' => true,
		'title' => 'Timezone',
		'quoted' => true,
		'values' => array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'),
		),
	'default_charset' => array('change' => false),
	'memory_limit' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', '128M', '256M', '512M', '1G', 'Choose'),
		),
	'output_buffering' => array('change' => false),
	'error_reporting' => array('change' => false),
	'max_execution_time' => array(
		'change' => true,
		'title' => 'Seconds',
		'quoted' => false,
		'values' => array('20', '30', '60', '120', '180', '240', '300', 'Choose'),
		),
	'max_input_time' => array(
		'change' => true,
		'title' => 'Seconds',
		'quoted' => false,
		'values' => array('20', '30', '60', '120', '180', '240', '300', 'Choose'),
		),
	'max_input_vars' => array(
		'change' => true,
		'title' => 'Integer',
		'quoted' => false,
		'values' => array('1000', '2000', '2500', '5000', '10000', 'Choose'),
		'min' => '1000',
		'max' => '20000',
		'default' => '2500',
		),
	'post_max_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('2M', '4M', '8M', '16M','32M', '64M', '128M', '256M', 'Choose'),
		),
	'realpath_cache_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('4M', '8M', '16M', '32M', '64M'),
		),
	'upload_max_filesize' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('2M', '4M', '8M', '16M','32M', '64M', '128M', '256M', 'Choose'),
		),
	'session.save_path' => array('change' => false),
	'upload_tmp_dir' => array('change' => false),
	'zlib.output_compression_level' => array('change' => false),
	'xdebug.overload_var_dump' => array('change' => false),
);
//Parameters to be changed into php.ini CLI the same way as for php.ini
$phpCLIparams = array(
	'date.timezone',
);

// Extensions can not be loaded by extension =
// for example zend_extension
$phpNotLoadExt = array(
	'php_opcache',
	'php_xdebug',
	);

$zend_extensions = array(
	'php_opcache' => array('loaded' => '0','content' => '', 'version' => ''),
	'php_xdebug' => array('loaded' => '0','content' =>'', 'version' => ''),
	);

//MySQL parameters
$mysqlParams = array (
	'basedir',
	'datadir',
	'key_buffer_size',
	'lc-messages',
	'log_error_verbosity',
	'max_allowed_packet',
	'innodb_lock_wait_timeout',
	'innodb_buffer_pool_size',
	'myisam_sort_buffer_size',
	'innodb_log_file_size',
	'query_cache_size',
	'sql-mode',
	'sort_buffer_size',
	'prompt',
	'skip-grant-tables',
);
//MySQL parameters with values not On or Off cannot be switched on or off
//Can be changed if 'change' = true && 'title' && 'values'
//Parameter name must be also into $mysqlParams array
//To manualy enter value, 'Choose' must be the last 'values' and 'title' must be 'Size' or 'Seconds' or 'Number'
$mysqlParamsNotOnOff = array(
	'basedir' => array(
		'change' => false,
		'msg' => "\nThis setting should not be changed, otherwise you risk losing your existing databases.\n",
		),
	'datadir' => array(
		'change' => false,
		'msg' => "\nThis setting should not be changed, otherwise you risk losing your existing databases.\n",
		),
	'key_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', 'Choose'),
		),
	'lc-messages' => array(
		'change' => false,
		'msg' => "\nTo set the Error Message Language see:\n\nhttp://dev.mysql.com/doc/refman/5.7/en/error-message-language.html\n",
		),
	'log_error_verbosity' => array(
		'change' => true,
		'title' => 'Number',
		'quoted' => false,
		'values' => array('1', '2', '3'),
		'text' => array('1' => 'Errors only', '2' => 'Errors and warnings', '3' => 'Errors, warnings, and notes'),
		),
	'max_allowed_packet' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', 'Choose'),
		),
	'innodb_lock_wait_timeout' => array(
		'change' => true,
		'title' => 'Seconds',
		'quoted' => false,
		'values' => array('20', '30', '50', '120', 'Choose'),
		),
	'innodb_buffer_pool_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', '128M', '256M', 'Choose'),
		),
	'innodb_log_file_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('4M', '8M', '16M', '32M', '64M', 'Choose'),
		),
	'myisam_sort_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', 'Choose'),
		),
	'innodb_log_file_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('4M', '8M', '16M', '32M', '64M', 'Choose'),
		),
	'query_cache_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('4M', '8M', '16M', 'Choose'),
		),
	'sql-mode' => array(
		'change' => true,
		'title' => 'Special',
		'quoted' => true,
		),
	'sort_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('2M', '4M', '16M', 'Choose'),
		),
	'prompt' => array(
		'change' => false,
		'msg' => "\nTo set the console prompt see:\n\nhttps://dev.mysql.com/doc/refman/5.7/en/mysql-commands.html\n",
		),
	'skip-grant-tables' => array(
		'change' => false,
		'msg' => "\n\nWARNING!! WARNING!!\nThis option causes the server to start without using the privilege system at all, WHICH GIVES ANYONE WITH ACCESS TO THE SERVER UNRESTRICTED ACCESS TO ALL DATABASES.\nThis option also causes the server to suppress during its startup sequence the loading of user-defined functions (UDFs), scheduled events, and plugins that were installed.\n\nYou should leave this option 'uncommented' ONLY for the time required to perform certain operations such as the replacement of a lost password for 'root'.\n",
		),
);

//MariaDB parameters
$mariadbParams = array (
	'basedir',
	'datadir',
	'key_buffer_size',
	'lc-messages',
	'max_allowed_packet',
	'innodb_lock_wait_timeout',
	'innodb_buffer_pool_size',
	'myisam_sort_buffer_size',
	'innodb_log_file_size',
	'query_cache_size',
	'sql-mode',
	'sort_buffer_size',
	'prompt',
	'skip-grant-tables',
);
//MariaDB parameters with values not On or Off cannot be switched on or off
//Can be changed if 'change' = true && 'title' && 'values'
//Parameter name must be also into $mariadbParams array
//To manualy enter value, 'Choose' must be the last 'values' and 'title' must be 'Size' or 'Seconds' or 'Number'
$mariadbParamsNotOnOff = array(
	'basedir' => array(
		'change' => false,
		'msg' => "\nThis setting should not be changed, otherwise you risk losing your existing databases.\n",
		),
	'datadir' => array(
		'change' => false,
		'msg' => "\nThis setting should not be changed, otherwise you risk losing your existing databases.\n",
		),
	'key_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', '128M', '256M', 'Choose'),
		),
	'lc-messages' => array(
		'change' => false,
		'msg' => "\nTo set the Error Message Language see:\n\nhttps://mariadb.com/kb/en/mariadb/server-system-variables/#lc_messages\n",
		),
	'prompt' => array(
		'change' => false,
		'msg' => "\nTo set the console prompt see:\n\nhttps://dev.mysql.com/doc/refman/5.7/en/mysql-commands.html\n",
		),
	'max_allowed_packet' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', 'Choose'),
		),
	'innodb_lock_wait_timeout' => array(
		'change' => true,
		'title' => 'Seconds',
		'quoted' => false,
		'values' => array('20', '30', '50', '120', 'Choose'),
		),
	'innodb_buffer_pool_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', '128M', '256M', 'Choose'),
		),
	'innodb_log_file_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('4M', '8M', '16M', '32M', '64M', 'Choose'),
		),
	'key_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', '128M', '256M', 'Choose'),
		),
	'myisam_sort_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('16M', '32M', '64M', 'Choose'),
		),
	'query_cache_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('4M', '8M', '16M', 'Choose'),
		),
	'sql-mode' => array(
		'change' => true,
		'title' => 'Special',
		'quoted' => 'true',
		),
	'sort_buffer_size' => array(
		'change' => true,
		'title' => 'Size',
		'quoted' => false,
		'values' => array('2M', '4M', '16M', 'Choose'),
		),
	'skip-grant-tables' => array(
		'change' => false,
		'msg' => "\n\nWARNING!! WARNING!!\nThis option causes the server to start without using the privilege system at all, WHICH GIVES ANYONE WITH ACCESS TO THE SERVER UNRESTRICTED ACCESS TO ALL DATABASES.\nThis option also causes the server to suppress during its startup sequence the loading of user-defined functions (UDFs), scheduled events, and plugins that were installed.\n\nYou should leave this option 'uncommented' ONLY for the time required to perform certain operations such as the replacement of a lost password for 'root'.\n",
		),
);

// Adding parameters to WampServer modifiable
// by "Settings" sub-menu on right-click Wampmanager icon
// Needs $w_settings['parameter'] in wamp\lang\modules\settings_english.php
// Obsolete settings will be completely deleted in future versions
//
$wamp_Param = array(
	'VirtualHostSubMenu',
	'AliasSubmenu',
	'NotCheckVirtualHost',
	'NotCheckDuplicate',
	'VhostAllLocalIp',
	'SupportMySQL',
	'SupportMariaDB',
	'HomepageAtStartup',
	'ShowphmyadMenu',
	'ShowadminerMenu',
	'BackupHosts',
	'##Cleaning',
	'AutoCleanLogs',
	'AutoCleanLogsMax',
	'AutoCleanLogsMin',
	'AutoCleanTmp',
	'AutoCleanTmpMax',
	'##DaredevilOptions',
	'NotVerifyPATH',
	'NotVerifyTLD',
	'NotVerifyHosts',
);
//Wampserver parameters with values not On or Off cannot be switched on or off
//Can be changed if 'change' = true && 'title' && 'values'
//Parameter name must be also into $wamp_Param array
//dependance is the name of Wampserver parameter that must be 'on' to see the parameter
//To manualy enter value, 'Choose' must be the last 'values' and 'title' must be 'Size' or 'Seconds' or 'Integer'
$wampParamsNotOnOff = array(
	'AutoCleanLogsMax' => array(
		'change' => true,
		'dependance' => 'AutoCleanLogs',
		'title' => 'Integer',
		'quoted' => true,
		'values' => array('1000', '2000', '5000', '10000'),
		'min' => '1000',
		'max' => '10000',
		'default' => '1000',
		),
	'AutoCleanLogsMin' => array(
		'change' => true,
		'dependance' => 'AutoCleanLogs',
		'title' => 'Integer',
		'quoted' => true,
		'values' => array('1', '10', '20', '50', '100'),
		'min' => '1',
		'max' => '100',
		'default' => '50',
		),
	'AutoCleanTmpMax' => array(
		'change' => true,
		'dependance' => 'AutoCleanTmp',
		'title' => 'Integer',
		'quoted' => true,
		'values' => array('1000', '2000', '5000', '10000'),
		'min' => '1000',
		'max' => '10000',
		'default' => '1000',
		),
);

//Wampserver parameters be switched by php.exe and not php-win.exe
$wamp_ParamPhpExe = array(
	'SupportMariaDB',
	'SupportMySQL',
);


// Apache modules which should not be disabled
$apacheModNotDisable = array(
	'authz_core_module',
	'authz_host_module',
	'php5_module',
	'php7_module',
	);

?>
