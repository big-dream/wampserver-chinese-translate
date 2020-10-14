<?php
//3.0.6
require 'config.inc.php';

echo '


请输入要添加的别名.
例如：test

成功后，会为网址添加一个别名.
例如：
http://localhost/test/

请输入，输入完成后按回车键(Enter)继续
: ';

$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');


if (is_file($aliasDir.$newAliasDir.'.conf')) {
    echo '

别名已存在！按回车键(Enter)退出...';
    trim(fgets(STDIN));
    exit();
}
if (empty($newAliasDir)) {
    echo '

别名未能创建！按回车键(Enter)退出...';
    trim(fgets(STDIN));
    exit();
}

echo '


请输入别名对应的文件夹路径.
例如：c:/test/

会将 http://localhost/'.$newAliasDir.'/ 指向

c:/test/

请输入，输入完成后按回车键(Enter)继续
: ';
$newAliasDest = trim(fgets(STDIN));
if ($newAliasDest[strlen($newAliasDest)-1] != '/')
    $newAliasDest .= '/';
if (!is_dir($newAliasDest)) {
    echo '
输入的路径不存在.
';
    $newAliasDest = '';
}

if (empty($newAliasDest)) {
    echo '

别名未能创建！按回车键(Enter)退出...';
    trim(fgets(STDIN));
    exit();
}

$newConfFileContents = <<< ALIASEOF
Alias /${newAliasDir} "${newAliasDest}"

<Directory "${newAliasDest}">
	Options Indexes FollowSymLinks MultiViews
    AllowOverride all
    <ifDefine APACHE24>
		Require local
	</ifDefine>
	<ifDefine !APACHE24>
		Order Deny,Allow
        Deny from all
        Allow from localhost ::1 127.0.0.1
	</ifDefine>
</Directory>

ALIASEOF;

file_put_contents($aliasDir.$newAliasDir.'.conf',$newConfFileContents) or die ("unable to create conf file");


echo '
别名创建成功！按回车键(Enter)退出...';
    trim(fgets(STDIN));
    exit();


?>