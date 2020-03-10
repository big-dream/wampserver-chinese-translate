<?php
//3.0.6
require 'config.inc.php';

echo '














Enter your alias.
For example,

\'test\'

would create an alias for the url

http://localhost/test/

: ';
$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');


if (is_file($aliasDir.$newAliasDir.'.conf')) {
    echo '

Alias already exists. Press Enter to exit...';
    trim(fgets(STDIN));
    exit();
}
if (empty($newAliasDir)) {
    echo '

Alias not created. Press Enter to exit...';
    trim(fgets(STDIN));
    exit();
}

echo '


















Enter the destination path of your alias.
For example,

\'c:/test/\'

would make http://localhost/'.$newAliasDir.'/ point to

c:/test/

: ';
$newAliasDest = trim(fgets(STDIN));
if ($newAliasDest[strlen($newAliasDest)-1] != '/')
    $newAliasDest .= '/';
if (!is_dir($newAliasDest)) {
    echo '
This directory doesn\'t exist.
';
    $newAliasDest = '';
}

if (empty($newAliasDest)) {
    echo '

Alias not created. Press Enter to exit...';
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
Alias created. Press Enter to exit...';
    trim(fgets(STDIN));
    exit();


?>