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
    'NotCheckVirtualHost' => '��������������Ƿ��Ѷ���',
    'NotCheckDuplicate' => '����� ServerName �Ƿ��ظ�',
    'VhostAllLocalIp' => '����������������ʹ�ñ���IP���� 127.* ��',
    'SupportMySQL' => '���� MySQL',
    'SupportMariaDB' => '���� MariaDB',
    'DaredevilOptions' => '��������ֵ�з��գ�����Ϥ����ģ�',
    'ShowphmyadMenu' => '��ʾ PHPMyAdmin �˵�',
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
    'LinksOnProjectsHomePage' => '�������ӵ���Ŀ��ҳ',
    'apachePhpCurlDll' => '���� Apache ʹ�� PHP �е� libcrypto-*.dll �� libssl-*.dll',
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
$w_showExcludedPorts = 'Show the ports excluded by the system';
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

// ����
$w_ext_spec = 'ר����չ';
$w_ext_zend = 'Zend ��չ';
$w_phpparam_info = '�����ο�';
$w_ext_nodll = '�� dll �ļ�';
$w_ext_noline = "�� 'extension='";
$w_mod_fixed = "�̶�ģ��";
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
$w_ApacheCompareInfo = "--- �Ƚ� Apache �汾\r\n��������������ϰ汾�� Apache������Խ���ǰ�汾�������汾���жԱ�.\r\n�ɱȽ���������:\r\n- LoadModule\r\n- Include\r\n- httpd-vhosts.conf �ļ�\r\n- httpd-ssl.conf �ļ�\r\n- openssl.cnf �ļ�\r\n- Certs �ļ��м�������ļ�\r\n����Խ��ɰ汾�����ø��Ƶ���ǰ�汾.\r\n*** ���� *** ��������Զ����б���, ��Ҫ���ڸ�������֮ǰ���б�����������.";
$w_Refresh_Restart_Info = "--- '{$w_refresh}' �� '{$w_restartWamp}' ֮��Ĳ���\r\n-- ".$w_refresh.":\r\n- ִ�и��ּ��,\r\n- ���¶�ȡ Wampserver, Apache, PHP, MySQL �� MariaDB �������ļ�,\r\n- ��Ӧ���޸� Wampmanager �����ļ������²˵�,\r\n- ִ�� 'ƽ������ Apache',\r\n- ���¼��� Aestan Tray �˵�.\r\nApache, PHP, MySQL �� MariaDB ���Ӳ��ж�.\r\n\r\n-- ".$w_restartWamp.":\r\n- ֹͣ���� :".$c_apacheService.", ".$c_mysqlService." �� ".$c_mariadbService.",\r\n- �����־�ļ�,\r\n- �����ʱ�ļ�Ŀ¼,\r\n- �˳� Wampserver,\r\n- �������� Wampserver.\r\nͨ�������ķ�ʽ�ж� Apache, PHP, MySQL �� MariaDB �����ӣ�Ȼ�����»ָ�����";
?>