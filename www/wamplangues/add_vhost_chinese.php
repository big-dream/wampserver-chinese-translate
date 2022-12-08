<?php
//3.1.1 - NotwwwDir
//3.1.3 - VirtualHostPortNone
//3.1.4 - txtTLDdev
//3.1.9 - VirtualHostName modified - Accept diacritical characters (IDN)
//3.2.6 - HoweverWamp
//3.2.8 - phpNotExists - VirtualHostPhpFCGI - modifyForm - modifyVhost - modAliasForm
//      - modifyAlias - StartAlias - ModifiedAlias - NoModifyAlias - HoweverAlias
//  modified: VirtualHostPort (%s replaced by below ) - Start - VirtualCreated - However - HoweverWamp
//  array $langues_help added.
//3.3.0 - Modification of lines FcgidInitialEnv

$langues = array(
    'langue' => '简体中文',
    'locale' => 'chinese',
    'addVirtual' => '添加虚拟主机',
    'backHome' => '返回首页',
    'VirtualSubMenuOn' => '必须将 <code>Wampmanager右键菜单</code> - <code>Wamp设置</code> - <code>显示虚拟主机菜单</code> 勾选上，然后重新加载本页面才能正常使用',
    'UncommentInclude' => '取消 %s 文件 <code>#Include conf/extra/httpd-vhosts.conf</code> 内容的注释 <small>(删除 # 符号)</small>',
    'FileNotExists' => '文件 <code>%s</code> 不存在',
    'txtTLDdev' => '主机名 %s 使用了 %s 顶级域，该顶级域被浏览器做了强制策略，不适合用来本地使用！请使用其它顶级域，例如：.test',
    'FileNotWritable' => '文件 <code>%s</code> 无法写入',
    'DirNotExists' => '<code>%s</code> 不存在或不是文件夹',
    'NotwwwDir' => '<code>%s</code> 文件夹已保留给<code>localhost</code>，请使用其它文件夹！',
    'NotCleaned' => '<code>%s</code> 文件尚未清理，里面还包含一些虚拟主机配置示例，例如: dummy-host.example.com',
    'NoVirtualHost' => '<code>%s</code> 文件中没有定义虚拟主机<br/>它至少应该定义一个<code>localhost</code>的虚拟主机',
    'NoFirst' => '<code>%s</code> 文件里的第一个虚拟站点必须是 <code>localhost</code>',
    'ServerNameInvalid' => '无效主机名：<code>%s</code>',
    'LocalIpInvalid' => '无效本地IP：<code>%s</code>',
    'VirtualHostName' => '<code>主机名</code> 不能有空格或下划线(_) ',
    'VirtualHostFolder' => '虚拟主机站点目录（必须是完整的绝对路径）<i>示例: <code>C:/wamp/www/projet/</code> 或 <code>E:/www/site1/</code></i> ',
    'VirtualHostIP' => '如果你要通过其它本地IP使用虚拟主机，那么请输入要使用的本地IP(127.x.y.z)',
    'VirtualHostPhpFCGI' => '<code class="option">If</code> you want to use PHP in FCGI mode <code class="option">Accepted versions</code> below ',
    'VirtualHostPort' => '如果你要使用默认以外的端口，请在下方勾选并选择要使用的端口。可选端口：%s',
    'VirtualHostPortNone' => '如果你想监听更多端口，可以在右键菜单的工具里添加想监听的端口',
    'VirtualAlreadyExist' => '填写的域名 <code>%s</code> 已存在',
    'VirtualIpAlreadyUsed' => '本地IP <code>%s</code> 已存在',
    'VirtualPortNotExist' => '填写的 <code>%s</code> 端口未在Apache里监听 <code>"Listen port"</code>',
    'VirtualPortExist' => '<code>%s</code> 端口是默认监听的端口，它不应该包含在输入的内容里',
    'VirtualHostExists' => '已添加的虚拟主机:',
    'Start' => '开始创建虚拟主机(可能需要等待一些时间...)',
    'StartAlias' => 'Start the modification of the Alias',
    'GreenErrors' => '绿色框的错误可以自动纠正',
    'Correct' => '开始自动校正绿色边框面板内的错误',
    'NoModify' => '无法修改 <code>httpd-vhosts.conf</code> 或 <code>hosts</code> 文件',
    'NoModifyAlias' => 'Alias has not been modified',
    'VirtualCreated' => '文件已修改。虚拟主机<code>%s</code>已创建',
    'ModifiedAlias' => 'The alias <code>%s</code> have been modified',
    'CommandMessage' => '更新 DNS 提示:',
    'However' => '如果主机名无法正常使用，您可以尝试以下操作。<br>右键单击 <code>Wampmanager 图标</code> - <code>工具</code> - <code>重启DNS</code><br> <i>(以上步骤都需要您手动操作)</i>',
    'HoweverAlias' => 'You may modify another Alias by validate "Add a VirtualHost".<br>However, for these modified Alias is taken into account by Wampmanager (Apache), you must run item<br><code>Restart DNS</code><br>from Right-Click Tools menu of Wampmanager icon.</i>',
    'HoweverWamp' => 'The created VirtualHost has been taken into account by Apache.<br>You may add another VirtualHost by validate "Add a VirtualHost".<br>You can start working on this new VirtualHost<br>But in order for these new VirtualHosts to be taken into account by the Wampmanager menus, you must launch the item<br><code>Refresh</code><br>from Right-Click menu of Wampmanager icon.</i>',
    'suppForm' => '显示删除虚拟主机',
    'suppVhost' => '删除虚拟主机',
    'modifyForm' => 'Modify VirtualHost form',
    'modifyVhost' => 'Modify VirtualHost',
    'modAliasForm' => 'Modify Alias form',
    'modifyAlias' => 'Modify Alias',
    'Required' => '必填',
    'Optional' => '选填',
    'phpNotExists' => 'PHP version doesn\'t exist',
);

$langues_help['fcgi_mode_link'] = 'FCGI mode help';
$langues_help['fcgi_mode_help'] = <<< 'FCGIEOT'
- *** How to use PHP in Fast CGI mode with Wampserver ***
The CGI (Common Gateway Interface) defines a way for a web server to interact with external content-generating programs, which are often referred to as CGI programs or CGI scripts. It is a simple way to put dynamic content on your web site, using whatever programming language you're most familiar with.

- ** Only one PHP version as Apache module **
Since the beginning, Wampserver loads PHP as an Apache module:
LoadModule php_module "${INSTALL_DIR}/bin/php/php8.1.1/php8apache2_4.dll"
which makes all VirtualHost, Alias and Projects use the same PHP version.
If you change the PHP version via the PHP menu of Wampmanager, this new version will be used everywhere.

- ** Several PHP versions with FCGI mode **
Since Wampserver 3.2.8, it is possible to use PHP in CGI mode, i.e. you can define a different PHP version, whose addons have been previously installed, for each VirtualHost. This means that the VirtualHost are not obliged to use the same PHP version anymore.

The Apache fcgid_module (mod_fcgid.so) simplifies the implementation of CGI
The documentation is here: https://httpd.apache.org/mod_fcgid/mod/mod_fcgid.html

--- 1 *** Prerequisites ***
- 1.1 Presence of the mod_fcgid.so file in the Apache modules folder.
- 1.2 Presence of the module loading line in the httpd.conf file
LoadModule fcgid_module modules/mod_fcgid.so
- 1.3 Presence of the common configuration directives of the module fcgid_module in the file httpd.conf
<IfModule fcgid_module>
  FcgidMaxProcessesPerClass 300
  FcgidConnectTimeout 10
  FcgidProcessLifeTime 1800
  FcgidMaxRequestsPerProcess 0
  FcgidMinProcessesPerClass 0
  FcgidFixPathinfo 0
  FcgidZombieScanInterval 20
  FcgidMaxRequestLen 536870912
  FcgidBusyTimeout 120
  FcgidIOTimeout 120
  FcgidTimeScore 3
  FcgidPassHeader Authorization
  Define PHPROOT ${INSTALL_DIR}/bin/php/php
</IfModule>

These three points 1.1, 1.2 and 1.3 are done automatically with the Wampserver 3.2.8 update

--- 2 *** Creating a FCGI VirtualHost ***
- After the Wampserver 3.2.8 update, the http://localhost/add_vhost.php page allows the addition of a FCGI VirtualHost in all simplicity.
The choice of the version of PHP to use is limited to the versions of the PHP addons installed in your Wampserver what avoids an error of version PHP.
Indeed, to declare, in a VirtualHost, a non-existent PHP version in Wampserver will generate an Apache error and a "crash" of this one.

- If you want to modify an existing VirtualHost to add the FCGI mode with an existing PHP version already in the Wampserver PHP addons, you just have to go on the page http://localhost/add_vhost.php and launch the VirtualHost modification form to be able, in three clicks, to add the FCGI mode to the VirtualHost, to change the PHP version or to remove the FCGI mode.
It will be necessary to refresh Wampserver for that to be taken into account.
This same page http://localhost/add_vhost.php also allows, via the Alias modification form, to add the FCGI mode to an Alias, to change the PHP version or to remove the FCGI mode, always in three clicks.

+--------------+
| Some details |
+--------------+
To add FCGI mode to an existing VirtualHost, simply add the following directives just before the </VirtualHost> end of that VirtualHost:
  <IfModule fcgid_module>
    Define FCGIPHPVERSION "7.4.27"
    FcgidInitialEnv PHPRC "${PHPROOT}${FCGIPHPVERSION}/php.ini"
    <Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe" .php
    </Files>
  </IfModule>

The PHP version must exist as a PHP addon in your Wampserver and can be modified.
Conversely removing these lines causes the VirtualHost to revert to the PHP version used as an Apache module.

For Alias, it's a little less simple, you need to add the previous lines in two parts, the first part:
<IfModule fcgid_module>
  Define FCGIPHPVERSION "7.4.27"
  FcgidCmdOptions ${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe \
  InitialEnv PHPRC=${PHPROOT}${FCGIPHPVERSION}/php.ini
</IfModule>
just before the <Directory... directive.
The second part:
<IfModule fcgid_module>
  <Files ~ "\.php$">
    Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
    AddHandler fcgid-script .php
    FcgidWrapper "${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe" .php
  </Files>
</IfModule>
inside the <Directory...></Directory> context so as to obtain, for example for any Alias, the following structure:

Alias /myalias "g:/www/mydir/"
<IfModule fcgid_module>
  Define FCGIPHPVERSION "7.4.27"
  FcgidCmdOptions ${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe \
  InitialEnv PHPRC=${PHPROOT}${FCGIPHPVERSION}/php.ini
</IfModule>
<Directory "g:/www/mydir/">
  Options Indexes FollowSymLinks
  AllowOverride all
  Require local
  <IfModule fcgid_module>
    <Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe" .php
    </Files>
  </IfModule>
</Directory>

FCGIEOT;

?>