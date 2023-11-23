<?php
// 简体中文语言文件
// 项目和 VirtualHosts 子菜单
// 设置和工具右键单击子菜单
// 3.0.7 新增 $w_listenForApache - $w_AddListenPort - $w_deleteListenPort - $w_settings['SupportMariaDB']
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
// 3.2.2 已修改 $w_MysqlMariaUser 和 $w_EnterSize -  - $w_MySQLsqlmodeInfo $w_mysql_mode $w_phpMyAdminHelp $w_PhpMyAdMinHelpTxt
// 3.2.3 wampserver.aviatechno 调整为 https
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

// 项目子菜单
$w_projectsSubMenu = '项目列表';
// 虚拟主机子菜单
$w_virtualHostsSubMenu = '虚拟主机列表';
$w_add_VirtualHost = '虚拟主机管理';
$w_aliasSubMenu = '别名（Alias）列表';
$w_portUsed = 'Apache 使用的端口： ';
$w_portUsedMysql = 'MySQL 使用的端口： ';
$w_portUsedMaria = 'MariaDB 使用的端口： ';
$w_testPortUsed = '测试端口是否被使用： ';
$w_portForApache = 'Apache 使用的端口';
$w_listenForApache = '新增 Apache 端口监听';
$w_portForMysql = 'MySQL 端口';
$w_testPortMysql = '测试 3306 端口';
$w_testPortMysqlUsed = '测试 MySQL 使用的端口： ';
$w_testPortMariaUsed = '测试 MariaDB 使用的端口： ';

// 右键子菜单-设置
$w_wampSettings = 'Wamp 设置';
$w_settings = array(
    'urlAddLocalhost' => '在 URL 添加 localhost',
    'VirtualHostSubMenu' => '显示虚拟主机菜单',
    'AliasSubmenu' => '显示别名（Alias）菜单',
    'ProjectSubMenu' => '显示项目菜单',
    'HomepageAtStartup' => '启动时打开主页',
    'MenuItemOnline' => '显示切换在线/离线菜单',
    'ItemServicesNames' => '工具菜单项: 更改服务名称',
    'CheckVirtualHost' => 'Check VirtualHost definitions',
    'NotCheckVirtualHost' => '不检查虚拟主机是否已定义',
    'NotCheckDuplicate' => '不检查 ServerName 是否重复',
    'VhostAllLocalIp' => '允许在虚拟主机中使用本地IP（非 127.* ）',
    'SupportMySQL' => '启用 MySQL',
    'SupportMariaDB' => '启用 MariaDB',
    'DaredevilOptions' => '更改下列值有风险，不熟悉者勿改！',
    'ShowadminerMenu' => '显示 Adminer 菜单',
    'mariadbUseConsolePrompt' => '修改默认的 MariaDB 控制台提示',
    'mysqlUseConsolePrompt' => '修改默认的 MySQL 控制台提示',
    'NotVerifyPATH' => '不检验 PATH',
    'NotVerifyTLD' => '不检验 TLD',
    'NotVerifyHosts' => '不检验 hosts 文件',
    'Cleaning' => '自动清理',
    'AutoCleanLogs' => '自动清理日志文件',
    'AutoCleanLogsMax' => '清理前日志行数',// 日志行数>=设定值时清理
    'AutoCleanLogsMin' => '清理后日志行数',// 清理后保留日志行数
    'AutoCleanTmp' => '自动清理临时（tmp）目录',
    'AutoCleanTmpMax' => '清理前文件数',// 文件数>=设定值时清理
    'ForTestOnly' => '仅适用于测试环境（开发调试）',
    'iniCommented' => '已注释 php.ini 配置',
    'BackupHosts' => '备份 hosts 文件',
    'ShowWWWdirMenu' => '显示 www 文件夹菜单',
    'ApacheWampParams' => 'Wampserver 的 Apache 设置',
    'apacheCompareVersion' => '允许比较 Apache 设置',
    'apacheRestoreFiles' => '允许恢复 Apache 文件',
    'apacheGracefulRestart' => '允许 Apache 平滑重启',
    'LinksOnProjectsHomePage' => '允许项目链接到主页',
    'LinksOnProjectsHomeByIp' => '通过本地 IP 链接项目',
    'ScrollListsHomePage' => '启用主页上项目列表滚动',
    'WampserverBrowser' => 'Wampserver 浏览器',
    'BrowserChange' => '设置 Wampserver 浏览器',
    'httpsReady' => '支持 HTTPS',
);

// 右键子菜单-工具
$w_wampTools = '工具';
$w_restartDNS = '重启 DNS';
$w_testConf = '检查 httpd.conf 语法';
$w_testServices = '检查服务状态';
$w_changeServices = '更改服务名称';
$w_compilerVersions = '检查 VC 运行库依赖、兼容性和 ini 文件';
$w_UseAlternatePort = '使用 %s 以外的端口';
$w_AddListenPort = '新增 Apache 监听端口';
$w_vhostConfig = '显示 Apache 中有效的虚拟主机';
$w_apacheLoadedModules = '显示 Apache 已加载的模块';
$w_apacheLoadedIncludes = '显示 Apache 已加载的额外配置文件';
$w_apacheDefineVariables = '显示 Apache 变量 (已定义)';
$w_showExcludedPorts = '显示系统占用的端口';
$w_testAliasDir = '检查别名（Alias）与目录的关联';
$w_verifyxDebugdll = '检查未使用的 xDebug 扩展 dll';
$w_empty = '清空';
$w_misc = '杂项';
$w_emptyAll = '清空所有';

$w_emptyLogs = '清空日志';
$w_emptyPHPlog = '清空 PHP 错误日志';
$w_emptyApaErrLog = '清空 Apache 错误日志';
$w_emptyApaAccLog = '清空 Apache 错误日志';
$w_emptyMySQLog = '清空 MySQL 日志';
$w_emptyMariaLog = '清空 MariaDB 日志';
$w_emptyAllLog ='清空所有日志文件';

$w_dnsorder = '检查 DNS 搜索顺序';
$w_deleteVer = '删除未使用版本';
$w_addingVer = '添加 Apache, PHP, MySQL, MariaDB 等版本';
$w_deleteListenPort = '删除 Apache 监听端口';
$w_delete = '删除';
$w_defaultDBMS = '默认 DBMS:';
$w_NoDefaultDBMS = 'Default DBMS : none';
$w_invertDefault = '调换默认 DBMS ';
$w_changeCLI = '更改 PHP CLI 版本';
$w_reinstallServices = '重新安装所有服务';
$w_wampReport = 'Wampserver 配置报告';
$w_dowampReport = '创建 '.$w_wampReport;
$w_verifySymlink = '验证软链接（symbolic links）';
$w_goto = '转到：';
$w_FileRepository = 'Wampserver 文件储存库网站';
$w_compareApache = 'Apache 设置比较';
$w_versus = 'versus';
$w_restorefile = '还原安装 Apache 时保存的文件';
$w_restore = '重置';
$w_checkUpdates = '检查更新';
$w_apacheTools = 'Apache 工具';
$w_PHPloadedExt = '显示 PHP 已加载扩展';
$w_PHPversionsUse = '显示使用的 PHP 版本';

// 杂项
$w_ext_spec = '专用扩展';
$w_ext_zend = 'Zend 扩展';
$w_phpparam_info = '仅供参考';
$w_ext_nodll = '无 dll 文件';
$w_ext_noline = "无 'extension='";
$w_mod_fixed = "固定模块";
$w_mod_not_disable = "不能禁用的模块";
$w_no_module = '无模块文件';
$w_no_moduleload = "无 'LoadModule'";
$w_mysql_none = "none";
$w_mysql_user = "user mode";
$w_mysql_default = "by default";
$w_mysql_mode = "sql-mode 说明";
$w_apache_restore = "[警告] Apache 恢复;";
$w_apache_compare = "[警告] Apache 设置比较";
$w_Refresh_Restart = "关于 ".$w_refresh.' - '.$w_restartWamp;
$w_Size = "大小";
$w_Time = "时间";
$w_Integer = "整数数值";
$w_phpMyAdminHelp = "PHPMyAdmin 帮助";
$w_wampHttpsHelp = 'HTTPS 模式帮助';
$w_phpNotExists = 'PHP 版本不存在';
$w_All_Versions = '所有版本';
$w_phpparam_obs = '弃用 | 已删除 | 新 的设置';
$w_ApacheCompiledIn = '内置模块';
$w_ApacheDoesNotIf = '不需要 <IfModule ModName>.';
$w_PhpMyAdminGoHidedb = '隐藏原生数据库';
$w_PhpMyAdminGoNoPassword = '允许无密码连接';
$w_ConvertHttps = 'VirtualHost 的 HTTPS 模式';
$w_MariaDBMySQLHelp = 'MariaDB/MySQL 帮助';

// Aestan Tray 菜单 PromptText 的 输入提示
// 换行符请使用 \r\n
$w_EnterInteger = "输入整数数值";
$w_enterPort = '输入要使用的端口号';
$w_EnterSize = "输入大小： **M 或 **G （**代表整数）。\r\n例如：64M ; 256M ; 1G";
$w_EnterTime = "输入秒数";
$w_MysqlMariaUser = "请输入一个有效的用户名. 如果你不知道用途, 请保留为默认的 “root”。";

// 长文本
// 必须转义双引号(\")
$w_addingVerTxt ="所有“附加组件”，即 Apache, PHP, MySQL 或 MariaDB 所有版本的安装程序，以及更新程序 (Wampserver, Aestan Tray Menu, xDebug 等) 和 Web应用程序 (PhpMyAdmin, Adminer) 都可在 Sourceforge 下载.\r\n\r\n".
	"'https://sourceforge.net/projects/wampserver/'\r\n\r\n".
	"只需要下载所需的安装程序文件，右键单击下载的安装程序文件，然后选择“以管理员身份运行”，安装完之后，插件或应用就会添加到您的Wampserver中.\r\n\r\n".
	"然后只需要用鼠标操作几下，即可更改 Apache, PHP, MySQL 或 MariaDB 的版本:\r\n".
	"左键菜单 -> PHP|Apache|MySQL|MariaDB -> 版本 -> 选择版本\r\n\r\n".
	"版本更改后，旧版本的参数配置/扩展配置和数据都不会自动转移到新版本，需要自行迁移.\r\n\r\n".
	"除了 Sourceforge ，我们还有更好更方便的储存库网站:\r\n\r\n".
	"1. https://wampserver.aviatechno.net\r\n\r\n".
	"2. https://wampserver.site\r\n\r\n".
	"储存库网站的链接还可以在 右键菜单 -> 帮助 里打开\r\n";
$w_MySQLsqlmodeInfo = "MySQL/MariaDB sql-mode\r\n".
	"SQL 服务器会根据 sql-mode 配置来以不同的模式运行.\r\n".
	"设置一个或多个模式会限制一些用法，并且需要你对SQL语法和数据进行严格的验证和检查，否则可能导致SQL语句无法执行.\r\n".
	"不同模式下，对应 my.ini 文件的配置如下.\r\n\r\n".
	"- sql-mode: 默认模式\r\n".
	"sql-mode 配置项不存在或已被注释 (;sql-mode=\"...\")\r\n".
	"将使用 MySQL/MariaDB 的默认配置\r\n\r\n".
	"- sql-mode: 自定义模式\r\n".
	"用你选择的模式来设置 sql-mode 配置，例如：\r\n".
	"sql-mode=\"NO_ZERO_DATE,NO_ZERO_IN_DATE,NO_AUTO_CREATE_USER\"\r\n\r\n".
	"- sql-mode: 空模式\r\n".
	"sql-mode 配置为空，但必须存在:\r\n".
	"sql-mode=\"\"\r\n".
	"无 SQL 模式.";
$w_PhpMyAdMinHelpTxt = "-- PhpMyAdmin\r\n".
	"启动PhpMyAdmin时，将要求你输入用户名和密码.\r\n".
	"安装 Wampserver 3 后, 数据库管理系统默认用户名是 root ，密码为空，即密码框留空不用填写.\r\n\r\n".
	"你可以在 PhpMyAdmin 里面管理 MySQL 或 MariaDB ，只需要在登录界面选择即可.\r\n".
	"如果只启用了一个数据库管理系统，则没有选择。如果有选择，则第一个为默认数据库管理系统.\r\n".
	"记住，如果您有不同的用户帐户，则必须为所选的数据库管理系统使用正确的用户帐户.\r\n".
	"另外: 两个数据库管理系统之间的用户和数据不互通.\r\n";
$w_PhpMyAdminBigFileTxt = "\r\n-- 导入大文件\r\n导入大文件时，可能会超出最大内存或运行超时。\r\n对内存和时间的配置修改不应在 php.ini 中修改，而应该在 wamp(64)\\alias\\phpmyadmin.conf 文件中修改。\r\n";
$w_ApacheRestoreInfo = "--- 恢复 Apache 文件\r\n从 Apache 2.4.41 开始，在完成安装时，会复制 httpd.conf 和 httpd-vhosts.conf 文件到备份文件夹中。\r\n如果 Apache 出现问题或不需要的修改，你可以将这两个还原回去。\r\n当前，这么操作之后，你可能会丢失安装之后所修改的配置。";
$w_ApacheCompareInfo = "--- Comparing Apache versions\r\nIf you have at least two versions of Apache, you have the possibility to compare the current version with a previous version.\r\nThe following are compared:\r\n- LoadModule\r\n- Include\r\n- httpd-vhosts.conf files\r\n- httpd-ssl.conf files\r\n- openssl.cnf files\r\n- Presence and content of the Certs folder\r\nYou have the possibility to copy the configuration of an old version on the current version.\r\n*** WARNING *** No backups will be made, it is your responsibility to make backups BEFORE copying the configurations.";
$w_Refresh_Restart_Info = "--- Differences between '".$w_refresh."' and '".$w_restartWamp."'\r\n-- ".$w_refresh.":\r\n- Performs various checks,\r\n- Rereads the configuration files of Wampserver, Apache, PHP, MySQL and MariaDB,\r\n- Modifies the Wampmanager configuration file accordingly and updates the menus,\r\n- Performs a 'Graceful Restart Apache',\r\n- Reloads the Aestan Tray menu.\r\nThere is no interruption of the Apache, PHP, MySQL and MariaDB connections.\r\n\r\n-- ".$w_restartWamp.":\r\n- Stop the services :".$c_apacheService.", ".$c_mysqlService." and ".$c_mariadbService.",\r\n- Empty all the log files,\r\n- Empty the tmp folder,\r\n- Exit Wampserver,\r\n- Starts Wampserver 'normally'.\r\nThere is thus a total cut of the connections Apache, PHP, MySQL and MariaDB and put back in place these under other identifications";
$w_wampHttpsHelpTxt = "-- Wampserver HTTPS mode\r\nBefore you can create an HTTPS VirtualHost, Wampserver must be able to support it.\r\nTo do this, you need to modify some files, add others and create certificates.\r\nThis preparation will be carried out automatically by validating the menu item, if it is not already checked:\r\n\r\n   Right-click -> Wamp settings -> Wampserver ready to support https\r\n\r\nOnce this has been done, the menu item will be checked and remain so.\r\n\r\n-- Switch a VirtualHost to HTTPS mode\r\nA VirtualHost in http mode must exist and be operational.\r\nVirtualHosts that can be switched to HTTPS mode are displayed in the menu:\r\n\r\n   Left-click -> Your VirtualHosts -> HTTPS mode for VirtualHost\r\n\r\nSimply click on the VirtualHost you want to convert to HTTPS mode.\r\n   That's all there is to it!\r\n\r\n- Browser warning because self-signed certificate.\r\n-- Mozilla Firefox\r\nWarning: probable security risk\r\nAdvanced button: Error code: SEC_ERROR_UNKNOWN_ISSUER\r\nValidate: Accept risk and continue.\r\n-- Opera\r\nYour connection is not private\r\nNET::ERR_CERT_AUTHORITY_INVALID\r\nValidate: Help me understand\r\nValidate: Continue on site name (dangerous)\r\n-- Chrome\r\nYour connection is not private\r\nNET::ERR_CERT_AUTHORITY_INVALID\r\nValidate : Advanced settings\r\nValidate : Continue to site site name (dangerous)\r\n-- Edge\r\nYour connection is not private\r\nNET::ERR_CERT_AUTHORITY_INVALID\r\nValidate : Advanced\r\nContinue to site name (not secure)\r\n\r\nOf course, in principle, this will only work if the 'Force strict https mode' or similar option is not enabled in the browser settings.";
$w_MariaDBMySQLHelpTxt ="- 1 - MySQL and MariaDB\r\n- 2 - Database connection via MariaDB or MySQL\r\n- 3 - Replace the default DBMS MariaDB with MySQL and vice versa\r\n- 4 - Only one database manager - No DBMS manager\r\n- 5 - Default DBMS: none - /!\ No Default DBMS\r\n- 6 - PhpMyAdmin\r\n\r\n- 1 - MySQL and MariaDB\r\nMySQL has been supported since the beginning of Wampserver and MariaDB has been supported since Wampserver 3.0.8.\r\nFor SQL connections the default port is and always has been port 3306.\r\nBoth MySQL and MariaDB are installed by the full installer.\r\nDepending on the versions of the full installer of Wampserver you used, either MySQL or MariaDB will be the default database manager.\r\n- If MySQL is the default DBMS, it uses port 3306 and therefore MariaDB will use port 3307.\r\n- If MariaDB is the default DBMS, it uses port 3306 and therefore MySQL will use port 3308.\r\nThe default database manager is shown in the Left-Click menu of Wampmanager\r\n\r\n- 2 - Database connection via MariaDB or MySQL\r\nThe default Database manager port is 3306. Connections that do not specify the port number will always be on the default port.\r\nIf the database manager (MySQL or MariaDB) you want to use is not the default one, it is therefore imperative to specify the port number in connection requests since it will not be using the default port 3306. We repeat, without specifying the port, it will be the default port that will be used, so 3306.\r\nNormally, connection scripts do not mention the port to use. For example:\r\n\$mysqli = new mysqli('127.0.0.1', 'user', 'password', 'database');\r\nor, in procedural :\r\n\$mysqli = mysqli_connect('127.0.0.0.1', 'user', 'password', 'database');\r\n\r\nSo, to connect with a manager that doesn't use port 3306, you have to specify the port number on the connection request:\r\n\$mysqli = new mysqli('127.0.0.1', 'user', 'password', 'database', '3307');\r\nor, procedurally:\r\n\$mysqli = mysqli_connect('127.0.0.0.1', 'user', 'password', 'database', '3307');\r\n\r\nTo check the connections on the MySQL or MariaDB database manager, use the script:\r\nwamp(64)\www\testmysql.php\r\nby putting 'http://localhost/testmysql.php' in the browser address bar having first modified the script according to your parameters.\r\n\r\n- 3 - Replace the default DBMS MariaDB with MySQL and vice versa\r\nImportant note : If you want to move a database from MySQL to MariaDB or visa versa, it is IMPERATIVE to BACKUP your databases (phpMyAdmin -> EXPORT) in format - SQL before switching DBMS.\r\nThis is the only reliable way to transfer a database between MySQL and MariaDB.\r\n- There is a tool to reverse the default DBMS with one click if both are enabled (MySQL AND MariaDB):\r\n- If MySQL is the default DBMS\r\nRight-click Wampmanager icon -> Tools -> Invert default DBMS MySQL <-> MariaDB\r\nor\r\n- If MariaDB is the default DBMS\r\nRight-click Wampmanager icon -> Tools -> Invert default DBMS MariaDB <-> MySQL\r\nOf course, you still have to import your previously saved databases.\r\n\r\n- 4 - Only one database manager - No database manager\r\nYou don't have to keep both MySQL and MariaDB managers active, you can deactivate the one that you do not require. You can even disable both database managers completely if you wish :\r\nRight-Click Wampmanager Icon -> Wamp Settings -> Allow MariaDB to deactivate - removes the green Tick\r\nRight-Click Wampmanager Icon -> Wamp Settings -> Allow MySQL to deactivate - removes the green Tick\r\nYou can reactivate either of both at a leater date if you and when you want to. This does not uninstall the DBMS, it just unregisters the Windows Service for that DBMS.\r\n\r\n- 5 - Default DBMS : none - /!\ No Default DBMS\r\nThis means that none of the database managers (MariaDB and/or MySQL) use port 3306 and it is therefore imperative to specify the port number in connection requests since this is not the default port 3306.\r\nIt is then essential that you choose which DBMS you want to use by default; to do this, use the built-in tools (Right-click -> Tools) to assign port 3306 (Use a port other than xxxx) to the DBMS (MariaDB or MySQL) you want to set as default.\r\n\r\n- 6 - PhpMyAdmin\r\nPhpMyAdmin is configured to allow you access to either MySQL or MariaDB depending on which ones are active.\r\nIf both DBMS's are activated, you will see a dropdown on the Login screen, called \"Server Choice\", the default server will be shown first in the dropdown list. Select the DBMS you want to use here as part of the login process.\r\nREMEMBER, if you have different user accounts you must use the correct one for the selected DBMS.\r\nALSO: If you have the same account i.e. `root` on both DBMS's, if you have set different passwords, you need to use the right password for the account and DBMS.\r\n";

?>