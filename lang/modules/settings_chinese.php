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
$w_projectsSubMenu = '项目列表';
// VirtualHosts sub-menu
$w_virtualHostsSubMenu = '虚拟主机列表';
$w_add_VirtualHost = '虚拟主机管理';
$w_aliasSubMenu = '别名(Alias)列表';
$w_portUsed = 'Apache使用端口: ';
$w_portUsedMysql = 'MySQL使用端口: ';
$w_portUsedMaria = 'MariaDB使用端口: ';
$w_testPortUsed = '测试端口是否被使用: ';
$w_portForApache = 'Apache使用端口';
$w_listenForApache = '添加Apache新端口监听';
$w_portForMysql = 'MySQL端口';
$w_testPortMysql = '测试3306端口';
$w_testPortMysqlUsed = '测试MySQL使用的端口: ';
$w_testPortMariaUsed = '测试MariaDB使用的端口: ';

// Right-click Settings
$w_wampSettings = 'Wamp设置';
$w_settings = array(
    'urlAddLocalhost' => 'Add localhost in url',
    'VirtualHostSubMenu' => '显示虚拟主机菜单',
    'AliasSubmenu' => '显示别名(Alias)菜单',
    'ProjectSubMenu' => '显示项目菜单',
    'HomepageAtStartup' => '启动Wampserver自动打开localhost',
    'MenuItemOnline' => '显示切换在线/离线菜单',
    'ItemServicesNames' => '工具菜单项: 更改服务名称',
    'NotCheckVirtualHost' => '不检查虚拟主机是否已定义',
    'NotCheckDuplicate' => '不检查ServerName是否重复',
    'VhostAllLocalIp' => '允许在虚拟主机中使用本地IP（非127.*）',
    'SupportMySQL' => '启用MySQL',
    'SupportMariaDB' => '启用MariaDB',
    'DaredevilOptions' => '更改下列值有风险，不熟悉者勿改！',
    'ShowphmyadMenu' => '在菜单显示PHPMyAdmin',
    'ShowadminerMenu' => '在菜单显示Adminer',
    'mariadbUseConsolePrompt' => '修改默认的MariaDB控制台提示',
    'mysqlUseConsolePrompt' => '修改默认的MySQL控制台提示',
    'NotVerifyPATH' => '不检验PATH',
    'NotVerifyTLD' => '不检验TLD',
    'NotVerifyHosts' => '不检验hosts文件',
    'Cleaning' => '自动清理',
    'AutoCleanLogs' => '自动清理日志文件',
    'AutoCleanLogsMax' => '清理前日志行数',// 日志行数>=设定值时清理
    'AutoCleanLogsMin' => '清理后日志行数',// 清理后保留日志行数
    'AutoCleanTmp' => '自动清理临时(tmp)目录',
    'AutoCleanTmpMax' => '清理前文件数',// 文件数>=设定值时清理
    'ForTestOnly' => '仅适用于测试环境(开发调试)',
    'iniCommented' => '已注释 php.ini 配置',
    'BackupHosts' => '备份hosts文件',
);

// Right-click Tools
$w_wampTools = '工具';
$w_restartDNS = '重启DNS';
$w_testConf = '检查httpd.conf语法';
$w_testServices = '检查服务状态';
$w_changeServices = '更改服务名称';
$w_enterServiceNameApache = "Enter an index number for the Apache service. It will be added to 'wampapache'";
$w_enterServiceNameMysql = "Enter an index number for the Mysql service. It will be added to 'wampmysqld'";
$w_enterServiceNameAll = "Enter a number for the suffix of service names (empty to return original services)";
$w_compilerVersions = '检查VC运行库依赖、兼容性和ini文件';
$w_UseAlternatePort = '使用%s以外的端口';
$w_AddListenPort = '新增Apache监听端口';
$w_vhostConfig = '显示Apache中有效的虚拟主机';
$w_apacheLoadedModules = '显示Apache已加载模块';
$w_empty = '清空';
$w_misc = '杂项';
$w_emptyAll = '清空所有';
$w_dnsorder = '检查DNS搜索顺序';
$w_deleteVer = '删除未使用版本';
$w_addingVer = '添加 Apache, PHP, MySQL, MariaDB等版本.';
$w_deleteListenPort = '删除Apache监听端口';
$w_delete = '删除';
$w_defaultDBMS = '默认DBMS:';
$w_invertDefault = '调换默认DBMS ';
$w_changeCLI = '更改PHP CLI版本';
$w_reinstallServices = '重新安装所有服务';
$w_wampReport = 'Wampserver配置报告';
$w_dowampReport = '创建 '.$w_wampReport;
$w_verifySymlink = '验证软链接(symbolic links)';
$w_goto = '转到:';
$w_FileRepository = 'Wampserver文件储存库网站';

//miscellaneous
$w_ext_spec = '专用扩展';
$w_ext_zend = 'Zend 扩展';
$w_phpparam_info = '仅供参考';
$w_ext_nodll = '无 dll 文件';
$w_ext_noline = "无 'extension='";
$w_mod_fixed = "固定模块";
$w_no_module = '无模块文件';
$w_no_moduleload = "无 'LoadModule'";
$w_mysql_none = "不配置";
$w_mysql_user = "用户配置";
$w_mysql_default = "默认配置";
$w_mysql_mode = "sql-mode 说明";
$w_Size = "大小";
$w_Time = "时间";
$w_Integer = "整数数值";
$w_phpMyAdminHelp = "PHPMyAdmin帮助";

// PromptText for Aestan Tray Menu type: prompt variables
// May have \r\n for multilines
$w_EnterInteger = "输入整数数值";
$w_enterPort = '输入要使用的端口号';
$w_EnterSize = "输入大小: **M 或 **G (**代表整数)";
$w_EnterTime = "输入秒数";
$w_MysqlMariaUser = "请输入一个有效的用户名. 如果你不知道用途, 请保留为默认的 'root'.";

// Long texts - Quotation marks " in texts must be escaped: \"
$w_addingVerTxt ="所有“附加组件”，即 Apache, PHP, MySQL 或 MariaDB 所有版本的安装程序，以及更新程序 (Wampserver, Aestan Tray Menu, xDebug 等) 和 Web应用程序 (PhpMyAdmin, Adminer) 都可在 Sourceforge 下载.\r\n\r\n".
	"'https://sourceforge.net/projects/wampserver/'\r\n\r\n".
	"只需要下载所需的安装程序文件，右键单击下载的安装程序文件，然后选择“以管理员身份运行”，安装完之后，插件或应用就会添加到您的Wampserver中.\r\n\r\n".
	"然后只需要用鼠标操作几下，即可更改 Apache, PHP, MySQL 或 MariaDB 的版本:\r\n".
	"左键菜单 -> PHP|Apache|MySQL|MariaDB -> 版本 -> 选择版本\r\n\r\n".
	"版本更改后，旧版本的参数配置/扩展配置和数据都不会自动转移到新版本，需要自行迁移.\r\n\r\n".
	"除了 Sourceforge ，我们还有更好更方便的储存库网站:\r\n\r\n".
	"1. http://wampserver.aviatechno.net\r\n\r\n".
	"2. http://wampserver.site\r\n\r\n".
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

