<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$alias = $_SERVER['argv'][1];
$aliasToDelete = $alias.".conf";
$aliasToDelete = str_replace('-whitespace-',' ',$aliasToDelete);
$message = '';
$deleteAlias = $deleteDir = false;
if(file_exists($aliasDir.$aliasToDelete)) {
	$message .= "Alias : ".$aliasDir.$aliasToDelete."\n\nDo you really want to delete this alias?\nType Y key then Enter for yes: ";
	Command_Windows($message,80,-1,0,'delete an Alias');
	$confirm = trim(fgets(STDIN));
	$confirm = strtoupper(trim($confirm ,'\''));
	if($confirm == 'Y') {
		$deleteAlias = true;
		$alias_contents = @file_get_contents($aliasDir.$aliasToDelete);
	  if(preg_match('~^Alias\s+/'.$alias.'\s+"(.*)"\r?$~m',$alias_contents,$matches) === 1){
	  	$dirToDelete = replace_apache_var($matches[1]);
	  	if(is_dir($dirToDelete)) {
	  		$deleteDir = false;
	  		$message .= "\n\nThe directory ".$dirToDelete."\nis associated with alias ".$aliasDir.$aliasToDelete."\nDo you really want to delete also this directory?\n\nType Y key then Enter for yes: ";
	  		$message1 = "\n\nThe directory ".$dirToDelete."\nis associated with alias ".$aliasDir.$aliasToDelete."\n\nDo you really want to delete also this directory?\n\nType Y key then Enter for yes: ";
				//Command_Windows($message,80,-1,0,'Delete an Alias');
				echo $message1;
				$confirm = trim(fgets(STDIN));
				$confirm = strtoupper(trim($confirm ,'\''));
				if($confirm == 'Y') {
					$deleteDir = true;
				}
	  	}
		}
	  $message = '';
	  if($deleteAlias) {
			if(unlink($aliasDir.str_replace('-whitespace-',' ',$aliasToDelete))) {
				$message .= color('blue',"\nAlias ".$aliasDir.$aliasToDelete." deleted\n");
				if($deleteDir) {
					if(file_exists($dirToDelete) && is_dir($dirToDelete)) {
						if(rrmdir($dirToDelete) === false)
							$message .= color('red',"\n\nFolder ".$dirToDelete." **not** deleted\n");
						else
							$message .= color('blue',"\nFolder ".$dirToDelete." deleted\n");
					}
				}
			}
			else
				$message .= color('red',"\n\n**Unable to delete** alias with unlink()\n");
		}

		$message .=  "\nPress Enter to exit ";
		Command_Windows($message,80,-1,0,'Delete an Alias');
		trim(fgets(STDIN));
	  exit();
	}
	else {
		$message .= "\n\nAlias not deleted.\n";
	}
}
else {
	$message .= "Alias: ".$aliasDir.$aliasToDelete." doesn't exist\n";
}
$message .= "Press Enter to exit ";
Command_Windows($message,80,-1,0,'Delete an Alias');
trim(fgets(STDIN));
exit();

?>
