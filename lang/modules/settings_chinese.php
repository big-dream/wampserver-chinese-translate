<?php
// Default English language file for
// Projects and VirtualHosts sub-menus
// Settings and Tools right-click sub-menus
// 3.0.7 add $w_listenForApache - $w_AddListenPort - $w_deleteListenPort - $w_settings['SupportMariaDB']
// $w_settings['DaredevilOptions']
// $w_Size - $w_EnterSize - $w_Time - $w_EnterTime - $w_Integer - $w_EnterInteger - $w_add_VirtualHost
// 3.0.8 $w_settings['SupportMySQL'] - $w_portUsedMaria - $w_testPortMariaUsed
// 3.0.9 $w_ext_zend
// 3.1.1 $w_defaultDBMS - $w_invertDefault - $w_changeCLI - $w_misc
// $w_settings['ShowphmyadMenu'] - $w_settings['ShowadminerMenu']
// 3.1.2 $w_reinstallServices - $w_settings['mariadbUseConsolePrompt'] - $w_settings['mysqlUseConsolePrompt']
// $w_enterServiceNameAll - $w_settings['NotVerifyPATH'] - $w_MysqlMariaUser
// 3.1.4 $w_settings 'NotVerifyTLD' 'Cleaning' 'AutoCleanLogs' 'AutoCleanLogsMax' 'AutoCleanLogsMax' 'AutoCleanTmp' 'AutoCleanTmpMax' 'iniCommented'
// $w_wampReport - $w_dowampReport
// 3.1.9 $w_settings 'BackupHosts'
// 3.2.0 $w_verifySymlink  - $w_settings['NotVerifyHosts']

// Projects sub-menu
$w_projectsSubMenu = '��Ŀ�б�';
// VirtualHosts sub-menu
$w_virtualHostsSubMenu = '���������б�';
$w_add_VirtualHost = '������������';
$w_aliasSubMenu = '����(Alias)�б�';
$w_portUsed = 'Apacheʹ�ö˿�: ';
$w_portUsedMysql = 'MySQLʹ�ö˿�: ';
$w_portUsedMaria = 'MariaDBʹ�ö˿�: ';
$w_testPortUsed = '���Զ˿��Ƿ�ʹ��: ';
$w_portForApache = 'Apacheʹ�ö˿�';
$w_listenForApache = '���Apache�¶˿ڼ���';
$w_portForMysql = 'MySQL�˿�';
$w_testPortMysql = '����3306�˿�';
$w_testPortMysqlUsed = 'Test MySQL port used: ';
$w_testPortMariaUsed = 'Test MariaDB port used: ';
$w_enterPort = 'Enter the desired port number';

// Right-click Settings
$w_wampSettings = 'Wamp����';
$w_settings = array(
	'urlAddLocalhost' => 'Add localhost in url',
	'VirtualHostSubMenu' => '��ʾ���������˵�',
	'AliasSubmenu' => '��ʾ����(Alias)�˵�',
	'ProjectSubMenu' => '��ʾ��Ŀ�˵�',
	'HomepageAtStartup' => '����Wampserver�Զ���localhost',
	'MenuItemOnline' => '��ʾ�л�����/���߲˵�',
	'ItemServicesNames' => 'Tools menu item: Change services names',
	'NotCheckVirtualHost' => '��������������Ƿ��Ѷ���',
	'NotCheckDuplicate' => '�����ServerName�Ƿ��ظ�',
	'VhostAllLocalIp' => 'Allow VirtualHost local IP\'s others than 127.*',
	'SupportMySQL' => '����MySQL',
	'SupportMariaDB' => '����MariaDB',
	'DaredevilOptions' => 'Caution: Risky! Only for experts.',
	'ShowphmyadMenu' => '�ڲ˵���ʾPhpMyAdmin',
	'ShowadminerMenu' => '�ڲ˵���ʾAdminer',
	'mariadbUseConsolePrompt' => 'Modify default Mariadb console prompt',
	'mysqlUseConsolePrompt' => 'Modify default Mysql console prompt',
	'NotVerifyPATH' => '������PATH',
	'NotVerifyTLD' => '������TLD',
	'NotVerifyHosts' => '������hosts�ļ�',
	'Cleaning' => '�Զ�����',
	'AutoCleanLogs' => '�Զ�������־�ļ�',
	'AutoCleanLogsMax' => 'Number of lines before cleaning',
	'AutoCleanLogsMin' => 'Number of lines after cleaning',
	'AutoCleanTmp' => '�Զ�������ʱ(tmp)Ŀ¼',
	'AutoCleanTmpMax' => 'Number of files before cleaning',
	'ForTestOnly' => '�������ڲ��Ի���(��������)',
	'iniCommented' => 'Commented php.ini directives (; at the beginning of the line)',
	'BackupHosts' => '����hosts�ļ�',
);

// Right-click Tools
$w_wampTools = '����';
$w_restartDNS = '����DNS';
$w_testConf = '���httpd.conf�﷨';
$w_testServices = '������״̬';
$w_changeServices = '���ķ�������';
$w_enterServiceNameApache = "Enter an index number for the Apache service. It will be added to 'wampapache'";
$w_enterServiceNameMysql = "Enter an index number for the Mysql service. It will be added to 'wampmysqld'";
$w_enterServiceNameAll = "Enter a number for the suffix of service names (empty to return original services)";
$w_compilerVersions = 'Check Compiler VC, compatibility and ini files';
$w_UseAlternatePort = 'Use a port other than %s';
$w_AddListenPort = 'Add a Listen port for Apache';
$w_vhostConfig = 'Show VirtualHost examined by Apache';
$w_apacheLoadedModules = '��ʾApache�Ѽ���ģ��';
$w_empty = '���';
$w_misc = 'Miscellaneous';
$w_emptyAll = '�������';
$w_dnsorder = 'Check DNS search order';
$w_deleteVer = 'ɾ��δʹ�ð汾';
$w_deleteListenPort = 'Delete a Listen port Apache';
$w_delete = 'ɾ��';
$w_defaultDBMS = 'Ĭ��DBMS:';
$w_invertDefault = '����Ĭ��DBMS ';
$w_changeCLI = '����PHP CLI�汾';
$w_reinstallServices = '���°�װ���з���';
$w_wampReport = 'Wampserver Configuration Report';
$w_dowampReport = '���� '.$w_wampReport;
$w_verifySymlink = '��֤������(symbolic links)';

//miscellaneous
$w_ext_spec = 'Special extensions';
$w_ext_zend = 'Zend extensions';
$w_phpparam_info = 'For information only';
$w_ext_nodll = 'No dll file';
$w_ext_noline = "No 'extension='";
$w_mod_fixed = "Irreversible module";
$w_no_module = 'No module file';
$w_no_moduleload = "No 'LoadModule'";
$w_mysql_none = "none";
$w_mysql_user = "user mode";
$w_mysql_default = "by default";
$w_Size = "Size";
$w_EnterSize = "Enter Size: xxxx followed by M for Mega or G for Giga";
$w_Time = "Time";
$w_EnterTime = "Enter time in seconds";
$w_Integer = "Integer Value";
$w_EnterInteger = "Enter an integer";
$w_MysqlMariaUser = "Enter a valid username. If you don't know, keep 'root' by default.";

