<?php
//3.0.6
require 'config.inc.php';

echo '


������Ҫ��ӵı���.
���磺test

�ɹ��󣬻�Ϊ��ַ���һ������.
���磺
http://localhost/test/

�����룬������ɺ󰴻س���(Enter)����
: ';

$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');


if (is_file($aliasDir.$newAliasDir.'.conf')) {
    echo '

�����Ѵ��ڣ����س���(Enter)�˳�...';
    trim(fgets(STDIN));
    exit();
}
if (empty($newAliasDir)) {
    echo '

����δ�ܴ��������س���(Enter)�˳�...';
    trim(fgets(STDIN));
    exit();
}

echo '


�����������Ӧ���ļ���·��.
���磺c:/test/

�Ὣ http://localhost/'.$newAliasDir.'/ ָ��

c:/test/

�����룬������ɺ󰴻س���(Enter)����
: ';
$newAliasDest = trim(fgets(STDIN));
if ($newAliasDest[strlen($newAliasDest)-1] != '/')
    $newAliasDest .= '/';
if (!is_dir($newAliasDest)) {
    echo '
�����·��������.
';
    $newAliasDest = '';
}

if (empty($newAliasDest)) {
    echo '

����δ�ܴ��������س���(Enter)�˳�...';
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
���������ɹ������س���(Enter)�˳�...';
    trim(fgets(STDIN));
    exit();


?>