<?php

require 'config.inc.php';
require 'wampserver.lib.php';

enter_alias_name:
$message = "Enter the name of your alias.\nFor example,\n\ntest\n\nwould create an alias for the url\nhttp://localhost/test/\n";
Command_Windows($message,-1,-1,0,'Add an Alias');
$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');
if(is_file($aliasDir.$newAliasDir.'.conf')) {
	$message .= "\nAlias '".$aliasDir.$newAliasDir.".conf' already exists.\nPress Enter to exit or R key to retry";
	Command_Windows($message,-1,-1,0,'Add an Alias');
	$rep = strtoupper(trim(fgets(STDIN)));
	if($rep == 'R') {
		$message .= "\n-----------------------------------------------\n";
		goto enter_alias_name;
	}
	exit();
}

if(empty($newAliasDir)) {
  $message .= "\nAlias given is empty. Press Enter to exit or R key to retry: ";
  Command_Windows($message,80,-1,0,'Add an Alias');
	$rep = strtoupper(trim(fgets(STDIN)));
	if($rep == 'R') {
		$message .= "\n-----------------------------------------------\n";
		goto enter_alias_name;
	}
  exit();
}

enter_alias_path:
$message .= "\n---------------------------------------------------\n";
$message .= "Enter the destination path of your alias.\nFor example,\nc:/test/\n";
$message .= "would make http://localhost/".$newAliasDir."/ point to\nc:/test/\n:";
Command_Windows($message,80,-1,0,'Add an Alias');
$newAliasDest = trim(fgets(STDIN));
$newAliasDest = trim($newAliasDest,'\'');
if($newAliasDest[strlen($newAliasDest)-1] != '/')
	$newAliasDest .= '/';
if(!is_dir($newAliasDest)) {
	$message .= "\nThis directory doesn\'t exist.\n";
  $newAliasDest = '';
}

if(empty($newAliasDest)) {
	$message .= "\n\nAlias not created. Press Enter to exit or R key to retry: ";
	Command_Windows($message,80,-1,0,'Add an Alias');
	$rep = strtoupper(trim(fgets(STDIN)));
	if($rep == 'R') {
		$message .= "\n-----------------------------------------------\n";
		goto enter_alias_path;
	}
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
$message .= "\n\nAlias '".$aliasDir.$newAliasDir.".conf' created. Press Enter to exit ";
Command_Windows($message,-1,-1,0,'Add an Alias');
trim(fgets(STDIN));
exit();

?>