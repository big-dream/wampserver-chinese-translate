<?php

//v1.0 by Romain Bourdon


require 'config.inc.php';

echo '


别名 : \''.$_SERVER['argv'][1].'.conf\'





确定要删除该别名吗?
输入 \'yes\' 确认删除 : ';
$confirm =    trim(fgets(STDIN));
$confirm =    trim($confirm ,'\'');

if ($confirm == 'yes')
{
    unlink ($aliasDir.str_replace('-whitespace-',' ',$_SERVER['argv'][1]).'.conf');
    echo '










别名删除成功！按回车键（ENTER）退出...';
    trim(fgets(STDIN));
    exit();
}
else
{
    echo'
别名未能删除！按回车键（ENTER）退出...';
    trim(fgets(STDIN));
    exit();
}

?>
