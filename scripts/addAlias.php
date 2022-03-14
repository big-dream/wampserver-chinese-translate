<?php

require 'config.inc.php';
require 'wampserver.lib.php';

enter_alias_name:
$message = "Enter the name of your alias.\nFor example,\ntest\nwould create an alias for the url\nhttp://localhost/test/\n";
$message .= color('red','Warning:')." No space - No underscore(_)\n\n";
Command_Windows($message,-1,-1,0,'Add an Alias');
$newAliasDir = trim(fgets(STDIN));
$newAliasDir = trim($newAliasDir,'/\'');
//Check Alias name
$regexServerName = 	'/^
	(?=.*[A-Za-z]) # at least one letter somewhere
	[A-Za-z0-9]+ 	 # letter or number in first place
	([-.](?![-.])	 #  a . or - not followed by . or -
			|					 #   or
	[A-Za-z0-9]		 #  a letter or a number
	){0,60}				 # this, repeated from 0 to 60 times - at least two characters
	[A-Za-z0-9]		 # letter or number at the end
	$/x';
if(preg_match($regexServerName,$newAliasDir) == 0) {
	$message .= "\nAlias '".color('blue',$newAliasDir)."' is not a correct Alias name.\nPress Enter to exit or R key to retry";
	Command_Windows($message,-1,-1,0,'Add an Alias');
	$rep = strtoupper(trim(fgets(STDIN)));
	if($rep == 'R') {
		$message .= "\n-----------------------------------------------\n";
		goto enter_alias_name;
	}
	exit();

}

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