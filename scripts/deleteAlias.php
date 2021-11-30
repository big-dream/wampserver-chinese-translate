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
$message = '';
if(file_exists($aliasDir.$aliasToDelete)) {
	$message .= "Alias : ".$aliasToDelete."\n\nDo you really want to delete this alias?\nType Y key for yes: ";
	Command_Windows($message,80,-1,0,'delete an Alias');
	$confirm = trim(fgets(STDIN));
	$confirm = strtoupper(trim($confirm ,'\''));
	if($confirm == 'Y') {
		$alias_contents = @file_get_contents($aliasDir.$aliasToDelete);
	  preg_match('~^Alias\s+/'.$alias.'\s+"(.*)"\r?$~m',$alias_contents,$matches);
	  if(is_dir($matches[1])) {
	  	$deleteDir = false;
	  	$message .= "\n\nThe directory ".$matches[1]." is associated with alias ".$alias."\n\nDo you really want to delete also this directory?\n\nType Y key for yes: ";
			Command_Windows($message,80,-1,0,'Delete an Alias');
			$confirm = trim(fgets(STDIN));
			$confirm = strtoupper(trim($confirm ,'\''));
			if($confirm == 'Y') {
				$deleteDir = true;
				$dirToDelete = $matches[1];
			}
	  }
		if(unlink($aliasDir.str_replace('-whitespace-',' ',$aliasToDelete))) {
			$message .= "\n\n\nAlias deleted\n";
			if($deleteDir) {
				if(file_exists($dirToDelete) && is_dir($dirToDelete)) {
					if(rrmdir($dirToDelete) === false)
						$message .= "\n\nFolder ".$dirToDelete." **not** deleted\n";
					else
						$message .= "\n\nFolder ".$dirToDelete." deleted\n";
				}
			}
		}
		else
			$message .= "\n\n**Unable to delete** alias with unlink()\n";
		$message .=  "\n\nPress Enter to exit ";
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
