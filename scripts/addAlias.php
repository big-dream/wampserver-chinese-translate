<?php
//3.0.6
require 'config.inc.php';

echo "\n\n\n\n\n\n\n\n\n\n\n\n\n请输入要添加的别名。\n例如：\n\n'test'\n\n成功后，会为网址添加一个别名\n
http://localhost/test/\n: ";
$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');
if (is_file($aliasDir.$newAliasDir.'.conf')) {
 echo "\n\n别名已存在！按回车键（ENTER）退出...";
 trim(fgets(STDIN));
 exit();
}
if(empty($newAliasDir)) {
  echo "\n\n别名未能创建！按回车键（ENTER）退出...";
  trim(fgets(STDIN));
  exit();
}
echo "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n
请输入别名对应的文件夹路径。\n例如：\n\n'c:/test/'\n\n
会将 http://localhost/".$newAliasDir."/ 指向\n\n
c:/test/\n:";
$newAliasDest = trim(fgets(STDIN));
$newAliasDest = trim($newAliasDest,'\'');
if($newAliasDest[strlen($newAliasDest)-1] != '/')
	$newAliasDest .= '/';
if(!is_dir($newAliasDest)) {
	echo "\n输入的路径不存在.\n";
  $newAliasDest = '';
}
if(empty($newAliasDest)) {
	echo "\n\n别名未能创建！按回车键（ENTER）退出...\n";
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
echo "\n\n别名创建成功！按回车键（ENTER）退出...";
trim(fgets(STDIN));
exit();

?>