<?php
// �������������ļ�
// ��Ŀ�� VirtualHosts �Ӳ˵�
// ���ú͹����Ҽ������Ӳ˵�
// 3.0.7 ���� $w_listenForApache - $w_AddListenPort - $w_deleteListenPort - $w_settings['SupportMariaDB']
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
// 3.2.2 ���޸� $w_MysqlMariaUser �� $w_EnterSize -  - $w_MySQLsqlmodeInfo $w_mysql_mode $w_phpMyAdminHelp $w_PhpMyAdMinHelpTxt
// 3.2.3 wampserver.aviatechno ����Ϊ https
// 3.2.5 $w_emptyLogs - $w_emptyPHPlog - $w_emptyApaErrLog - $w_emptyApaAccLog - $w_emptyMySQLog - $w_emptyMariaLog - $w_emptyAllLog
//       $w_testAliasDir - $w_verifyxDebugdll - $w_apacheLoadedIncludes - $w_settings 'ShowWWWdirMenu'
// 3.2.6 $w_compareApache - $w_versus - $w_restorefile - $w_restore - $w_apache_restore - $w_ApacheRestoreInfo - $w_apache_restore
//       $w_ApacheCompareInfo - $w_apacheDefineVariables - $w_Refresh_Restart - $w_Refresh_Restart_Info
//       $w_checkUpdates - $w_PhpMyAdminBigFileTxt - $w_apacheTools - $w_PHPloadedExt
//       $w_settings 	apacheCompareVersion - apacheRestoreFiles - apacheGracefulRestart - LinksOnProjectsHomePage
//                    ApacheWampParams - apachePhpCurlDll
//       Suppress : $w_enterServiceNameApache - $w_enterServiceNameMysql - $w_enterServiceNameAll
// 3.2.7 $w_showExcludedPorts
// 3.2.8 $w_phpNotExists - LinksOnProjectsHomeByIp - CheckVirtualHost - $w_PHPversionsUse - $w_All_Versions
//       $w_settings 	ScrollListsHomePage
// 3.2.9 $w_phpparam_obs - $w_ApacheCompiledIn - $w_ApacheDoesNotIf - $w_mod_not_disable
//       $w_NoDefaultDBMS
// 3.3.0 $w_settings Browser BrowserChange
//       Suppress apachePhpCurlDll
// 3.3.2 $w_PhpMyAdminGoHidedb - $w_PhpMyAdminGoNoPassword - $w_ConvertHttps - $w_wampHttpsHelp - $w_wampHttpsHelpTxt
//       $w_MariaDBMySQLHelp - $w_MariaDBMySQLHelpTxt - $w_settings httpsReady
//       suppress $w_settings['ShowphmyadMenu']

// ��Ŀ�Ӳ˵�
$w_projectsSubMenu = '��Ŀ�б�';
// ���������Ӳ˵�
$w_virtualHostsSubMenu = '���������б�';
$w_add_VirtualHost = '������������';
$w_aliasSubMenu = '������Alias���б�';
$w_portUsed = 'Apache ʹ�õĶ˿ڣ� ';
$w_portUsedMysql = 'MySQL ʹ�õĶ˿ڣ� ';
$w_portUsedMaria = 'MariaDB ʹ�õĶ˿ڣ� ';
$w_testPortUsed = '���Զ˿��Ƿ�ʹ�ã� ';
$w_portForApache = 'Apache ʹ�õĶ˿�';
$w_listenForApache = '���� Apache �˿ڼ���';
$w_portForMysql = 'MySQL �˿�';
$w_testPortMysql = '���� 3306 �˿�';
$w_testPortMysqlUsed = '���� MySQL ʹ�õĶ˿ڣ� ';
$w_testPortMariaUsed = '���� MariaDB ʹ�õĶ˿ڣ� ';

// �Ҽ��Ӳ˵�-����
$w_wampSettings = 'Wamp ����';
$w_settings = array(
    'urlAddLocalhost' => '�� URL ��� localhost',
    'VirtualHostSubMenu' => '��ʾ���������˵�',
    'AliasSubmenu' => '��ʾ������Alias���˵�',
    'ProjectSubMenu' => '��ʾ��Ŀ�˵�',
    'HomepageAtStartup' => '����ʱ����ҳ',
    'MenuItemOnline' => '��ʾ�л�����/���߲˵�',
    'ItemServicesNames' => '���߲˵���: ���ķ�������',
    'CheckVirtualHost' => 'Check VirtualHost definitions',
    'NotCheckVirtualHost' => '��������������Ƿ��Ѷ���',
    'NotCheckDuplicate' => '����� ServerName �Ƿ��ظ�',
    'VhostAllLocalIp' => '����������������ʹ�ñ���IP���� 127.* ��',
    'SupportMySQL' => '���� MySQL',
    'SupportMariaDB' => '���� MariaDB',
    'DaredevilOptions' => '��������ֵ�з��գ�����Ϥ����ģ�',
    'ShowadminerMenu' => '��ʾ Adminer �˵�',
    'mariadbUseConsolePrompt' => '�޸�Ĭ�ϵ� MariaDB ����̨��ʾ',
    'mysqlUseConsolePrompt' => '�޸�Ĭ�ϵ� MySQL ����̨��ʾ',
    'NotVerifyPATH' => '������ PATH',
    'NotVerifyTLD' => '������ TLD',
    'NotVerifyHosts' => '������ hosts �ļ�',
    'Cleaning' => '�Զ�����',
    'AutoCleanLogs' => '�Զ�������־�ļ�',
    'AutoCleanLogsMax' => '����ǰ��־����',// ��־����>=�趨ֵʱ����
    'AutoCleanLogsMin' => '�������־����',// ���������־����
    'AutoCleanTmp' => '�Զ�������ʱ��tmp��Ŀ¼',
    'AutoCleanTmpMax' => '����ǰ�ļ���',// �ļ���>=�趨ֵʱ����
    'ForTestOnly' => '�������ڲ��Ի������������ԣ�',
    'iniCommented' => '��ע�� php.ini ����',
    'BackupHosts' => '���� hosts �ļ�',
    'ShowWWWdirMenu' => '��ʾ www �ļ��в˵�',
    'ApacheWampParams' => 'Wampserver �� Apache ����',
    'apacheCompareVersion' => '����Ƚ� Apache ����',
    'apacheRestoreFiles' => '����ָ� Apache �ļ�',
    'apacheGracefulRestart' => '���� Apache ƽ������',
    'LinksOnProjectsHomePage' => '������Ŀ���ӵ���ҳ',
    'LinksOnProjectsHomeByIp' => 'ͨ������ IP ������Ŀ',
    'ScrollListsHomePage' => '������ҳ����Ŀ�б����',
    'WampserverBrowser' => 'Wampserver �����',
    'BrowserChange' => '���� Wampserver �����',
    'httpsReady' => '֧�� HTTPS',
);

// �Ҽ��Ӳ˵�-����
$w_wampTools = '����';
$w_restartDNS = '���� DNS';
$w_testConf = '��� httpd.conf �﷨';
$w_testServices = '������״̬';
$w_changeServices = '���ķ�������';
$w_compilerVersions = '��� VC ���п������������Ժ� ini �ļ�';
$w_UseAlternatePort = 'ʹ�� %s ����Ķ˿�';
$w_AddListenPort = '���� Apache �����˿�';
$w_vhostConfig = '��ʾ Apache ����Ч����������';
$w_apacheLoadedModules = '��ʾ Apache �Ѽ��ص�ģ��';
$w_apacheLoadedIncludes = '��ʾ Apache �Ѽ��صĶ��������ļ�';
$w_apacheDefineVariables = '��ʾ Apache ���� (�Ѷ���)';
$w_showExcludedPorts = '��ʾϵͳռ�õĶ˿�';
$w_testAliasDir = '��������Alias����Ŀ¼�Ĺ���';
$w_verifyxDebugdll = '���δʹ�õ� xDebug ��չ dll';
$w_empty = '���';
$w_misc = '����';
$w_emptyAll = '�������';

$w_emptyLogs = '�����־';
$w_emptyPHPlog = '��� PHP ������־';
$w_emptyApaErrLog = '��� Apache ������־';
$w_emptyApaAccLog = '��� Apache ������־';
$w_emptyMySQLog = '��� MySQL ��־';
$w_emptyMariaLog = '��� MariaDB ��־';
$w_emptyAllLog ='���������־�ļ�';

$w_dnsorder = '��� DNS ����˳��';
$w_deleteVer = 'ɾ��δʹ�ð汾';
$w_addingVer = '��� Apache, PHP, MySQL, MariaDB �Ȱ汾';
$w_deleteListenPort = 'ɾ�� Apache �����˿�';
$w_delete = 'ɾ��';
$w_defaultDBMS = 'Ĭ�� DBMS:';
$w_NoDefaultDBMS = 'Default DBMS : none';
$w_invertDefault = '����Ĭ�� DBMS ';
$w_changeCLI = '���� PHP CLI �汾';
$w_reinstallServices = '���°�װ���з���';
$w_wampReport = 'Wampserver ���ñ���';
$w_dowampReport = '���� '.$w_wampReport;
$w_verifySymlink = '��֤�����ӣ�symbolic links��';
$w_goto = 'ת����';
$w_FileRepository = 'Wampserver �ļ��������վ';
$w_compareApache = 'Apache ���ñȽ�';
$w_versus = 'versus';
$w_restorefile = '��ԭ��װ Apache ʱ������ļ�';
$w_restore = '����';
$w_checkUpdates = '������';
$w_apacheTools = 'Apache ����';
$w_PHPloadedExt = '��ʾ PHP �Ѽ�����չ';
$w_PHPversionsUse = '��ʾʹ�õ� PHP �汾';

// ����
$w_ext_spec = 'ר����չ';
$w_ext_zend = 'Zend ��չ';
$w_phpparam_info = '�����ο�';
$w_ext_nodll = '�� dll �ļ�';
$w_ext_noline = "�� 'extension='";
$w_mod_fixed = "�̶�ģ��";
$w_mod_not_disable = "���ܽ��õ�ģ��";
$w_no_module = '��ģ���ļ�';
$w_no_moduleload = "�� 'LoadModule'";
$w_mysql_none = "none";
$w_mysql_user = "user mode";
$w_mysql_default = "by default";
$w_mysql_mode = "sql-mode ˵��";
$w_apache_restore = "[����] Apache �ָ�;";
$w_apache_compare = "[����] Apache ���ñȽ�";
$w_Refresh_Restart = "���� ".$w_refresh.' - '.$w_restartWamp;
$w_Size = "��С";
$w_Time = "ʱ��";
$w_Integer = "������ֵ";
$w_phpMyAdminHelp = "PHPMyAdmin ����";
$w_wampHttpsHelp = 'HTTPS ģʽ����';
$w_phpNotExists = 'PHP �汾������';
$w_All_Versions = '���а汾';
$w_phpparam_obs = '���� | ��ɾ�� | �� ������';
$w_ApacheCompiledIn = '����ģ��';
$w_ApacheDoesNotIf = '����Ҫ <IfModule ModName>.';
$w_PhpMyAdminGoHidedb = '����ԭ�����ݿ�';
$w_PhpMyAdminGoNoPassword = '��������������';
$w_ConvertHttps = 'VirtualHost �� HTTPS ģʽ';
$w_MariaDBMySQLHelp = 'MariaDB/MySQL ����';

// Aestan Tray �˵� PromptText �� ������ʾ
// ���з���ʹ�� \r\n
$w_EnterInteger = "����������ֵ";
$w_enterPort = '����Ҫʹ�õĶ˿ں�';
$w_EnterSize = "�����С�� **M �� **G ��**������������\r\n���磺64M ; 256M ; 1G";
$w_EnterTime = "��������";
$w_MysqlMariaUser = "������һ����Ч���û���. ����㲻֪����;, �뱣��ΪĬ�ϵ� ��root����";

// ���ı�
// ����ת��˫����(\")
$w_addingVerTxt ="���С�������������� Apache, PHP, MySQL �� MariaDB ���а汾�İ�װ�����Լ����³��� (Wampserver, Aestan Tray Menu, xDebug ��) �� WebӦ�ó��� (PhpMyAdmin, Adminer) ������ Sourceforge ����.\r\n\r\n".
	"'https://sourceforge.net/projects/wampserver/'\r\n\r\n".
	"ֻ��Ҫ��������İ�װ�����ļ����Ҽ��������صİ�װ�����ļ���Ȼ��ѡ���Թ���Ա������С�����װ��֮�󣬲����Ӧ�þͻ���ӵ�����Wampserver��.\r\n\r\n".
	"Ȼ��ֻ��Ҫ�����������£����ɸ��� Apache, PHP, MySQL �� MariaDB �İ汾:\r\n".
	"����˵� -> PHP|Apache|MySQL|MariaDB -> �汾 -> ѡ��汾\r\n\r\n".
	"�汾���ĺ󣬾ɰ汾�Ĳ�������/��չ���ú����ݶ������Զ�ת�Ƶ��°汾����Ҫ����Ǩ��.\r\n\r\n".
	"���� Sourceforge �����ǻ��и��ø�����Ĵ������վ:\r\n\r\n".
	"1. https://wampserver.aviatechno.net\r\n\r\n".
	"2. https://wampserver.site\r\n\r\n".
	"�������վ�����ӻ������� �Ҽ��˵� -> ���� ���\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\n".
	"SQL ����������� sql-mode �������Բ�ͬ��ģʽ����.\r\n".
	"����һ������ģʽ������һЩ�÷���������Ҫ���SQL�﷨�����ݽ����ϸ����֤�ͼ�飬������ܵ���SQL����޷�ִ��.\r\n".
	"��ͬģʽ�£���Ӧ my.ini �ļ�����������.\r\n\r\n".
	"- sql-mode: Ĭ��ģʽ\r\n".
	"sql-mode ��������ڻ��ѱ�ע�� (;sql-mode=\"...\")\r\n".
	"��ʹ�� MySQL/MariaDB ��Ĭ������\r\n\r\n".
	"- sql-mode: �Զ���ģʽ\r\n".
	"����ѡ���ģʽ������ sql-mode ���ã����磺\r\n".
	"sql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n".
	"- sql-mode: ��ģʽ\r\n".
	"sql-mode ����Ϊ�գ����������:\r\n".
	"sql-mode=\"\"\r\n".
	"�� SQL ģʽ.";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\n".
	"����PhpMyAdminʱ����Ҫ���������û���������.\r\n".
	"��װ Wampserver 3 ��, ���ݿ����ϵͳĬ���û����� root ������Ϊ�գ�����������ղ�����д.\r\n\r\n".
	"������� PhpMyAdmin ������� MySQL �� MariaDB ��ֻ��Ҫ�ڵ�¼����ѡ�񼴿�.\r\n".
	"���ֻ������һ�����ݿ����ϵͳ����û��ѡ�������ѡ�����һ��ΪĬ�����ݿ����ϵͳ.\r\n".
	"��ס��������в�ͬ���û��ʻ��������Ϊ��ѡ�����ݿ����ϵͳʹ����ȷ���û��ʻ�.\r\n".
	"����: �������ݿ����ϵͳ֮����û������ݲ���ͨ.\r\n";
$w_PhpMyAdminBigFileTxt = "\r\n-- ������ļ�\r\n������ļ�ʱ�����ܻᳬ������ڴ�����г�ʱ��\r\n���ڴ��ʱ��������޸Ĳ�Ӧ�� php.ini ���޸ģ���Ӧ���� wamp(64)\\alias\\phpmyadmin.conf �ļ����޸ġ�\r\n";
$w_ApacheRestoreInfo = "--- �ָ� Apache �ļ�\r\n�� Apache 2.4.41 ��ʼ������ɰ�װʱ���Ḵ�� httpd.conf �� httpd-vhosts.conf �ļ��������ļ����С�\r\n��� Apache �����������Ҫ���޸ģ�����Խ���������ԭ��ȥ��\r\n��ǰ����ô����֮������ܻᶪʧ��װ֮�����޸ĵ����á�";
$w_ApacheCompareInfo = "--- Comparing Apache versions\r\nIf you have at least two versions of Apache, you have the possibility to compare the current version with a previous version.\r\nThe following are compared:\r\n- LoadModule\r\n- Include\r\n- httpd-vhosts.conf files\r\n- httpd-ssl.conf files\r\n- openssl.cnf files\r\n- Presence and content of the Certs folder\r\nYou have the possibility to copy the configuration of an old version on the current version.\r\n*** WARNING *** No backups will be made, it is your responsibility to make backups BEFORE copying the configurations.";
$w_Refresh_Restart_Info = "--- Differences between '".$w_refresh."' and '".$w_restartWamp."'\r\n-- ".$w_refresh.":\r\n- Performs various checks,\r\n- Rereads the configuration files of Wampserver, Apache, PHP, MySQL and MariaDB,\r\n- Modifies the Wampmanager configuration file accordingly and updates the menus,\r\n- Performs a 'Graceful Restart Apache',\r\n- Reloads the Aestan Tray menu.\r\nThere is no interruption of the Apache, PHP, MySQL and MariaDB connections.\r\n\r\n-- ".$w_restartWamp.":\r\n- Stop the services :".$c_apacheService.", ".$c_mysqlService." and ".$c_mariadbService.",\r\n- Empty all the log files,\r\n- Empty the tmp folder,\r\n- Exit Wampserver,\r\n- Starts Wampserver 'normally'.\r\nThere is thus a total cut of the connections Apache, PHP, MySQL and MariaDB and put back in place these under other identifications";
$w_wampHttpsHelpTxt = "-- Wampserver HTTPS mode\r\nBefore you can create an HTTPS VirtualHost, Wampserver must be able to support it.\r\nTo do this, you need to modify some files, add others and create certificates.\r\nThis preparation will be carried out automatically by validating the menu item, if it is not already checked:\r\n\r\n   Right-click -> Wamp settings -> Wampserver ready to support https\r\n\r\nOnce this has been done, the menu item will be checked and remain so.\r\n\r\n-- Switch a VirtualHost to HTTPS mode\r\nA VirtualHost in http mode must exist and be operational.\r\nVirtualHosts that can be switched to HTTPS mode are displayed in the menu:\r\n\r\n   Left-click -> Your VirtualHosts -> HTTPS mode for VirtualHost\r\n\r\nSimply click on the VirtualHost you want to convert to HTTPS mode.\r\n   That's all there is to it!\r\n\r\n- Browser warning because self-signed certificate.\r\n-- Mozilla Firefox\r\nWarning: probable security risk\r\nAdvanced button: Error code: SEC_ERROR_UNKNOWN_ISSUER\r\nValidate: Accept risk and continue.\r\n-- Opera\r\nYour connection is not private\r\nNET::ERR_CERT_AUTHORITY_INVALID\r\nValidate: Help me understand\r\nValidate: Continue on site name (dangerous)\r\n-- Chrome\r\nYour connection is not private\r\nNET::ERR_CERT_AUTHORITY_INVALID\r\nValidate : Advanced settings\r\nValidate : Continue to site site name (dangerous)\r\n-- Edge\r\nYour connection is not private\r\nNET::ERR_CERT_AUTHORITY_INVALID\r\nValidate : Advanced\r\nContinue to site name (not secure)\r\n\r\nOf course, in principle, this will only work if the 'Force strict https mode' or similar option is not enabled in the browser settings.";
$w_MariaDBMySQLHelpTxt ="- 1 - MySQL and MariaDB\r\n- 2 - Database connection via MariaDB or MySQL\r\n- 3 - Replace the default DBMS MariaDB with MySQL and vice versa\r\n- 4 - Only one database manager - No DBMS manager\r\n- 5 - Default DBMS: none - /!\ No Default DBMS\r\n- 6 - PhpMyAdmin\r\n\r\n- 1 - MySQL and MariaDB\r\nMySQL has been supported since the beginning of Wampserver and MariaDB has been supported since Wampserver 3.0.8.\r\nFor SQL connections the default port is and always has been port 3306.\r\nBoth MySQL and MariaDB are installed by the full installer.\r\nDepending on the versions of the full installer of Wampserver you used, either MySQL or MariaDB will be the default database manager.\r\n- If MySQL is the default DBMS, it uses port 3306 and therefore MariaDB will use port 3307.\r\n- If MariaDB is the default DBMS, it uses port 3306 and therefore MySQL will use port 3308.\r\nThe default database manager is shown in the Left-Click menu of Wampmanager\r\n\r\n- 2 - Database connection via MariaDB or MySQL\r\nThe default Database manager port is 3306. Connections that do not specify the port number will always be on the default port.\r\nIf the database manager (MySQL or MariaDB) you want to use is not the default one, it is therefore imperative to specify the port number in connection requests since it will not be using the default port 3306. We repeat, without specifying the port, it will be the default port that will be used, so 3306.\r\nNormally, connection scripts do not mention the port to use. For example:\r\n\$mysqli = new mysqli('127.0.0.1', 'user', 'password', 'database');\r\nor, in procedural :\r\n\$mysqli = mysqli_connect('127.0.0.0.1', 'user', 'password', 'database');\r\n\r\nSo, to connect with a manager that doesn't use port 3306, you have to specify the port number on the connection request:\r\n\$mysqli = new mysqli('127.0.0.1', 'user', 'password', 'database', '3307');\r\nor, procedurally:\r\n\$mysqli = mysqli_connect('127.0.0.0.1', 'user', 'password', 'database', '3307');\r\n\r\nTo check the connections on the MySQL or MariaDB database manager, use the script:\r\nwamp(64)\www\testmysql.php\r\nby putting 'http://localhost/testmysql.php' in the browser address bar having first modified the script according to your parameters.\r\n\r\n- 3 - Replace the default DBMS MariaDB with MySQL and vice versa\r\nImportant note : If you want to move a database from MySQL to MariaDB or visa versa, it is IMPERATIVE to BACKUP your databases (phpMyAdmin -> EXPORT) in format - SQL before switching DBMS.\r\nThis is the only reliable way to transfer a database between MySQL and MariaDB.\r\n- There is a tool to reverse the default DBMS with one click if both are enabled (MySQL AND MariaDB):\r\n- If MySQL is the default DBMS\r\nRight-click Wampmanager icon -> Tools -> Invert default DBMS MySQL <-> MariaDB\r\nor\r\n- If MariaDB is the default DBMS\r\nRight-click Wampmanager icon -> Tools -> Invert default DBMS MariaDB <-> MySQL\r\nOf course, you still have to import your previously saved databases.\r\n\r\n- 4 - Only one database manager - No database manager\r\nYou don't have to keep both MySQL and MariaDB managers active, you can deactivate the one that you do not require. You can even disable both database managers completely if you wish :\r\nRight-Click Wampmanager Icon -> Wamp Settings -> Allow MariaDB to deactivate - removes the green Tick\r\nRight-Click Wampmanager Icon -> Wamp Settings -> Allow MySQL to deactivate - removes the green Tick\r\nYou can reactivate either of both at a leater date if you and when you want to. This does not uninstall the DBMS, it just unregisters the Windows Service for that DBMS.\r\n\r\n- 5 - Default DBMS : none - /!\ No Default DBMS\r\nThis means that none of the database managers (MariaDB and/or MySQL) use port 3306 and it is therefore imperative to specify the port number in connection requests since this is not the default port 3306.\r\nIt is then essential that you choose which DBMS you want to use by default; to do this, use the built-in tools (Right-click -> Tools) to assign port 3306 (Use a port other than xxxx) to the DBMS (MariaDB or MySQL) you want to set as default.\r\n\r\n- 6 - PhpMyAdmin\r\nPhpMyAdmin is configured to allow you access to either MySQL or MariaDB depending on which ones are active.\r\nIf both DBMS's are activated, you will see a dropdown on the Login screen, called \"Server Choice\", the default server will be shown first in the dropdown list. Select the DBMS you want to use here as part of the login process.\r\nREMEMBER, if you have different user accounts you must use the correct one for the selected DBMS.\r\nALSO: If you have the same account i.e. `root` on both DBMS's, if you have set different passwords, you need to use the right password for the account and DBMS.\r\n";

?>