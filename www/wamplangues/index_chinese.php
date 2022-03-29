<?php
// 3.2.5 - documentation-of added for languages requiring it
// for English is identical to documentation
// 3.2.6 - txtNoHosts

$langues = array(
    'langue' => '中文',
    'locale' => 'chinese',
    'titreHtml' => 'WAMPSERVER主页',
    'titreConf' => '服务器配置',
    'versa' => 'Apache 版本：',
    'doca2.2' => 'httpd.apache.org/docs/2.2/zh-cn/',
    'doca2.4' => 'httpd.apache.org/docs/2.4/zh-cn/',
    'versp' => 'PHP 版本：',
    'server' => '服务器软件：',
    'documentation' => '文档',
    'documentation-of' => '文档',
    'docp' => 'www.php.net/manual/zh/',
    'versm' => 'MySQL 版本：',
    'docm' => 'dev.mysql.com/doc/index.html',
    'versmaria' => 'MariaDB 版本：',
    'docmaria' => 'mariadb.com/kb/zh-cn/mariadb-documentation/',
    'phpExt' => 'PHP已加载扩展：',
    'titrePage' => '工具',
    'txtProjet' => '你的项目',
    'txtNoProjet' => '尚无项目<br/>如果想创建项目，可以在“wamp/www”目录下创建一个文件夹',
    'txtProjects' => '这些都是“%s”目录下的文件夹。<br />如果你想通过域名直接访问这些文件夹，你需要将这些文件夹在“httpd-vhost.conf”里将它们定义为虚拟主机',
    'txtAlias' => '你的别名',
    'txtNoAlias' => '尚无别名<br />如果想创建别名，可以在“wamp/alias”目录下创建',
    'txtVhost' => '你的虚拟主机',
    'txtServerName' => '主机名 %s 存在语法错误，文件：%s',
    'txtDocRoot' => '%s 域名使用了 %s 目录，此目录应该保留给 localhost',
    'txtTLDdev' => '%s 使用了 %s 顶级域，该顶级域被浏览器做了强制策略，不适合用来本地使用！请使用其它顶级域，例如：.test',
    'txtNoHosts' => '主机名 %s 未在 hosts 文件定义.',
    'txtServerNameIp' => 'IP %s 无效，主机名： %s ，文件： %s',
    'txtVhostNotClean' => '%s 文件尚未清理，里面还包含一些虚拟主机配置示例，例如: dummy-host.example.com',
    'txtNoVhost' => '还没有虚拟主机，您可以在 wamp/bin/apache/apache%s/conf/extra/httpd-vhosts.conf 文件里添加一个',
    'txtNoIncVhost' => '请在 wamp/bin/apache/apache%s/conf/httpd.conf 文件添加 <i>Include conf/extra/httpd-vhosts.conf</i> 配置项，或取消该项的注释',
    'txtNoVhostFile' => '文件“ %s ”不存在',
    'txtNoPath' => '路径%s不存在，定义自%s（文件：%s）',
    'txtNotWritable' => '文件（%s）不可写',
    'txtNbNotEqual' => '%s 的数量与 %s 的数量不匹配，文件：%s',
    'txtAddVhost' => '添加一个虚拟主机',
    'txtPortNumber' => '%s 的端口号不正确，或与 %s 文件中的端口号不一致',
    'txtCorrected' => '可以纠正某些虚拟主机的错误',
    'forum' => 'Wampserver 论坛',
    'forumLink' => 'http://forum.wampserver.com/list.php?2',
    'portUsed' => 'Apache 端口：',
    'mysqlportUsed' => 'MySQL 端口：',
    'mariaportUsed' => 'MariaDB 端口：',
    'defaultDBMS' => '默认DBMS',
    'HelpMySQLMariaDB' => '如何使用 MySQL 和 MariaDB?<br>什么是默认数据库管理系统?<br>如何更改默认数据库管理系统?<br>可以阅读我们的相关帮助信息: 右键单击 Wampmanager 图标 -> 帮助 -> MariaDB - MySQL',
    'nolocalhost' => '最好在 wamp/bin/apache/apache%s/conf/extra/httpd-vhosts.conf 文件里定义虚拟主机而不是在网址中使用 localhost 目录',
);