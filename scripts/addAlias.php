<?php
//3.0.6
require 'config.inc.php';

echo "\n\n\n\n\n\n\n\n\n\n\n\n\n������Ҫ��ӵı�����\n���磺\n\n'test'\n\n�ɹ��󣬻�Ϊ��ַ���һ������\n
http://localhost/test/\n: ";
$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');
if (is_file($aliasDir.$newAliasDir.'.conf')) {
 echo "\n\n�����Ѵ��ڣ����س�����ENTER���˳�...";
 trim(fgets(STDIN));
 exit();
}
if(empty($newAliasDir)) {
  echo "\n\n����δ�ܴ��������س�����ENTER���˳�...";
  trim(fgets(STDIN));
  exit();
}
echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
�����������Ӧ���ļ���·����\n���磺\n\n'c:/test/'\n\n
�Ὣ http://localhost/".$newAliasDir."/ ָ��\n\n
c:/test/\n:";
$newAliasDest = trim(fgets(STDIN));
$newAliasDest = trim($newAliasDest,'\'');
if($newAliasDest[strlen($newAliasDest)-1] != '/')
	$newAliasDest .= '/';
if(!is_dir($newAliasDest)) {
	echo "\n�����·��������.\n";
  $newAliasDest = '';
}
if(empty($newAliasDest)) {
	echo "\n\n����δ�ܴ��������س�����ENTER���˳�...\n";
  trim(fgets(STDIN));
  exit();
}

$newConfFileContents = <<< ALIASEOF
Alias /${newAliasDir} "${newAliasDest}"

<Directory "${newAliasDest}">
	Options +Indexes +FollowSymLinks +MultiViews
  AllowOverride all
	Require local
</Directory>

ALIASEOF;

file_put_contents($aliasDir.$newAliasDir.'.conf',$newConfFileContents) or die ("unable to create conf file");
echo "\n\n���������ɹ������س�����ENTER���˳�...";
trim(fgets(STDIN));
exit();

?>