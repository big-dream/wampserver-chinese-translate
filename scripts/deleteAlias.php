<?php

//v1.0 by Romain Bourdon


require 'config.inc.php';

echo '


���� : \''.$_SERVER['argv'][1].'.conf\'





ȷ��Ҫɾ���ñ�����?
���� \'yes\' ȷ��ɾ�� : ';
$confirm =    trim(fgets(STDIN));
$confirm =    trim($confirm ,'\'');

if ($confirm == 'yes')
{
    unlink ($aliasDir.str_replace('-whitespace-',' ',$_SERVER['argv'][1]).'.conf');
    echo '










����ɾ���ɹ������س���(Enter)�˳�...';
    trim(fgets(STDIN));
    exit();
}
else
{
    echo'
����δ��ɾ�������س���(Enter)�˳�...';
    trim(fgets(STDIN));
    exit();
}

?>
