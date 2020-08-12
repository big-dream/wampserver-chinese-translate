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
// 3.2.1 $w_addingVer - $w_addingVerTxt - $w_goto - $w_FileRepository
// 3.2.2 $w_MysqlMariaUser and $w_EnterSize modified -  - $w_MySQLsqlmodeInfo $w_mysql_mode $w_phpMyAdminHelp $w_PhpMyAdMinHelpTxt
// 3.2.3 https for wampserver.aviatechno

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
$w_testPortMysqlUsed = '����MySQLʹ�õĶ˿�: ';
$w_testPortMariaUsed = '����MariaDBʹ�õĶ˿�: ';

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
    'VhostAllLocalIp' => '����������������ʹ�ñ���IP����127.*��',
    'SupportMySQL' => '����MySQL',
    'SupportMariaDB' => '����MariaDB',
    'DaredevilOptions' => '��������ֵ�з��գ�����Ϥ����ģ�',
    'ShowphmyadMenu' => '�ڲ˵���ʾPHPMyAdmin',
    'ShowadminerMenu' => '�ڲ˵���ʾAdminer',
    'mariadbUseConsolePrompt' => '�޸�Ĭ�ϵ�MariaDB����̨��ʾ',
    'mysqlUseConsolePrompt' => '�޸�Ĭ�ϵ�MySQL����̨��ʾ',
    'NotVerifyPATH' => '������PATH',
    'NotVerifyTLD' => '������TLD',
    'NotVerifyHosts' => '������hosts�ļ�',
    'Cleaning' => '�Զ�����',
    'AutoCleanLogs' => '�Զ�������־�ļ�',
    'AutoCleanLogsMax' => '����ǰ��־����',// ��־����>=�趨ֵʱ����
    'AutoCleanLogsMin' => '�������־����',// ���������־����
    'AutoCleanTmp' => '�Զ�������ʱ(tmp)Ŀ¼',
    'AutoCleanTmpMax' => '����ǰ�ļ���',// �ļ���>=�趨ֵʱ����
    'ForTestOnly' => '�������ڲ��Ի���(��������)',
    'iniCommented' => '��ע�� php.ini ����',
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
$w_compilerVersions = '���VC���п������������Ժ�ini�ļ�';
$w_UseAlternatePort = 'ʹ��%s����Ķ˿�';
$w_AddListenPort = '����Apache�����˿�';
$w_vhostConfig = '��ʾApache����Ч����������';
$w_apacheLoadedModules = '��ʾApache�Ѽ���ģ��';
$w_empty = '���';
$w_misc = '����';
$w_emptyAll = '�������';
$w_dnsorder = '���DNS����˳��';
$w_deleteVer = 'ɾ��δʹ�ð汾';
$w_addingVer = '��� Apache, PHP, MySQL, MariaDB�Ȱ汾.';
$w_deleteListenPort = 'ɾ��Apache�����˿�';
$w_delete = 'ɾ��';
$w_defaultDBMS = 'Ĭ��DBMS:';
$w_invertDefault = '����Ĭ��DBMS ';
$w_changeCLI = '����PHP CLI�汾';
$w_reinstallServices = '���°�װ���з���';
$w_wampReport = 'Wampserver���ñ���';
$w_dowampReport = '���� '.$w_wampReport;
$w_verifySymlink = '��֤������(symbolic links)';
$w_goto = 'ת��:';
$w_FileRepository = 'Wampserver�ļ��������վ';

//miscellaneous
$w_ext_spec = 'ר����չ';
$w_ext_zend = 'Zend ��չ';
$w_phpparam_info = '�����ο�';
$w_ext_nodll = '�� dll �ļ�';
$w_ext_noline = "�� 'extension='";
$w_mod_fixed = "������ģ��";
$w_no_module = '��ģ���ļ�';
$w_no_moduleload = "�� 'LoadModule'";
$w_mysql_none = "none";
$w_mysql_user = "user mode";
$w_mysql_default = "�ָ�Ĭ��";
$w_mysql_mode = "Explanations of sql-mode";
$w_Size = "��С";
$w_Time = "ʱ��";
$w_Integer = "������ֵ";
$w_phpMyAdminHelp = "Help PhpMyAdmin";

// PromptText for Aestan Tray Menu type: prompt variables
// May have \r\n for multilines
$w_EnterInteger = "����������ֵ";
$w_enterPort = '����Ҫʹ�õĶ˿ں�';
$w_EnterSize = "�����С: **M �� **G (**��������)";
$w_EnterTime = "��������";
$w_MysqlMariaUser = "Enter a valid username. If you don't know, keep 'root' by default.";

// Long texts - Quotation marks " in texts must be escaped: \"
$w_addingVerTxt ="���С�������������� Apache, PHP, MySQL �� MariaDB ���а汾�İ�װ�����Լ����³��� (Wampserver, Aestan Tray Menu, xDebug ��) �� WebӦ�ó��� (PhpMyAdmin, Adminer) ������ Sourceforge ����.\r\n\r\n".
	"'https://sourceforge.net/projects/wampserver/'\r\n\r\n".
	"ֻ��Ҫ��������İ�װ�����ļ����Ҽ��������صİ�װ�����ļ���Ȼ��ѡ���Թ���Ա������С�����װ��֮�󣬲����Ӧ�þͻ���ӵ�����Wampserver��.\r\n\r\n".
	"Ȼ��ֻ��Ҫ�����������£����ɸ��� Apache, PHP, MySQL �� MariaDB �İ汾:\r\n".
	"����˵� -> PHP|Apache|MySQL|MariaDB -> �汾 -> ѡ��汾\r\n\r\n".
	"�汾���ĺ󣬾ɰ汾�Ĳ�������/��չ���ú����ݶ������Զ�ת�Ƶ��°汾����Ҫ����Ǩ��.\r\n\r\n".
	"���� Sourceforge �����ǻ��и��ø�����Ĵ������վ:\r\n\r\n".
	"1. http://wampserver.aviatechno.net\r\n\r\n".
	"2. http://wampserver.site\r\n\r\n".
	"�������վ�����ӻ������� �Ҽ��˵� -> ���� ���\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\nThe SQL server may run in different SQL modes depending on the value of the sql-mode directive.\r\nSetting one or more modes restricts certain possibilities and requires greater rigor in SQL syntax and data validation.\r\nThe operation of the sql-mode directive in the my.ini file is as follows.\r\n\r\n- sql-mode: by default\r\nThe sql-mode directive does not exist or is commented out (;sql-mode=\"...\")\r\nThe default modes of the MySQL/MariaDB version are applied\r\n\r\n- sql-mode: user mode\r\nThe sql-mode directive is populated with user-defined modes, for example :\r\nsql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n- sql-mode: none\r\nThe sql-mode directive is empty but must exist:\r\nsql-mode=\"\"\r\nno SQL mode is applied.";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\nWhen starting phpMyAdmin, you will be asked for a user name and password.\r\nAfter installing Wampserver 3, the default username is \"root\" (without quotes) and there is no password, which means that you must leave the form Password box empty.\r\n\r\nPhpMyAdmin is configured to allow you access to either MySQL or MariaDB depending on which ones are active.\r\nIf both DBMS's are activated, you will see a dropdown on the Login screen, called \"Server Choice\", the default server will be shown first in the dropdown list. Select the DBMS you want to use here as part of the login process.\r\nREMEMBER, if you have different user accounts you must use the correct one for the selected DBMS.\r\nALSO: If you have the same account i.e. `root` on both DBMS's, if you have set different passwords, you need to use the right password for the account and DBMS.\r\n";

