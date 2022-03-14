<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

$allOK = true;
$message = "Relationship between Alias and Directories\n";
//Get alias files & directory
$aliasList = array();
if(is_dir($aliasDir)) {
  $handle=opendir($aliasDir);
  $i = 0;
  while(false !== ($file = readdir($handle))) {
  	if(is_file($aliasDir.$file) && stripos($file, '.conf') !== false) {
    	$aliasList[$i]['file'] = $file;
			$alias_contents = @file_get_contents($aliasDir.$file);
	  	if(preg_match('~^Alias\s+/(.+)\s+"(.+)"\r?$~m',$alias_contents,$matches) > 0) {
    		$aliasList[$i]['alias'] = $matches[1];
	  		$aliasList[$i]['dir'] = replace_apache_var($matches[2]);
	  		$i++;
	  	}
  	}
  }
  closedir($handle);
  $countAlias = $i--;
  //Check if directory exists for each alias
  if($countAlias > 0) {
  	foreach($aliasList as $key => $value) {
  		if(is_dir($aliasList[$key]['dir']) === false) {
  			$allOK = false;
  			$message .= "\n\nIn alias file: '".$aliasList[$key]['file']."'\n";
  			$message .= "Alias '".$aliasList[$key]['alias']."' request to use the directory '".$aliasList[$key]['dir']."' that doesn't exist.\n";
  			$message .= "The alias is therefore inoperative.\n\n";
  			$message .= "Do you want to delete alias file : '".$aliasList[$key]['file']."'\n\nType 'Y' key then Enter to confirm\nPress Enter key to exit: ";
				Command_Windows($message,-1,-1,0,'Reliationships Alias & Directories');
				$confirm = trim(fgets(STDIN));
				$confirm = strtoupper(trim($confirm ,'\''));
				if($confirm == 'Y') {
					if(unlink($aliasDir.str_replace('-whitespace-',' ',$aliasList[$key]['file']))) {
						$message .= "\n Alias file '".$aliasList[$key]['file']."' deleted.\n";
					}
				}
  		}
  	}
  }
}

//Get wamp/apps/* directories
$appsDir = $c_installDir.'/apps/';
$listAppsDir = array();
$listAppsDir = glob($appsDir.'*',GLOB_ONLYDIR);
//error_log("listAppsDirGlob=".print_r($listAppsDir,true));

// Check if each directory is used by an alias
$DirAlias = array_column($aliasList, 'dir');
foreach($listAppsDir as $value) {
	if($value[strlen($value)-1] != '/')	$value .= '/';
	if(!in_array($value, $DirAlias)) {
		$allOK = false;
		$message .= "\n'".$value."' directory is not used by any alias\n";
  	$message .= "Do you want to delete directory : '".$value."'\n\nType 'Y' key then Enter to confirm\nPress Enter to exit : ";
  	Command_Windows($message,-1,-1,0,'Reliationships Alias & Directories');
		$confirm = trim(fgets(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if($confirm == 'yes') {
			if(rrmdir($value) === false)
				$message .= "\n\nFolder ".$value." **not** deleted\n";
			else
				$message .= "\n\nFolder ".$value." deleted\n";
		}
	}
}
if($allOK) $message .= "\n\nNo faults were detected\n";
$message .= "\nPress Enter to exit...";
Command_Windows($message,-1,-1,0,'Reliationships Alias & Directories');
trim(fgets(STDIN));
exit();

?>
