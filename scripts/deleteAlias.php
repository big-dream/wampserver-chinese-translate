<?php

//v1.0 by Romain Bourdon


require 'config.inc.php';

echo '


Alias : \''.$_SERVER['argv'][1].'.conf\'





Do you really want to delete this alias?
Type \'yes\' to confirm : ';
$confirm =    trim(fgets(STDIN));
$confirm =    trim($confirm ,'\'');

if ($confirm == 'yes')
{
    unlink ($aliasDir.str_replace('-whitespace-',' ',$_SERVER['argv'][1]).'.conf');
    echo '






























Alias deleted. Press Enter to exit...';
    trim(fgets(STDIN));
    exit();
}
else
{
    echo'
Alias not deleted. Press Enter to exit...';
    trim(fgets(STDIN));
    exit();
}

?>
