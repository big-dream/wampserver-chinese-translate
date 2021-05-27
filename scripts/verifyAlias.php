<?php
// 3.2.5 - Verifies that a folder exists in wamp/apps/ for each alias
//         and that each folder in wamp/apps/ corresponds to an alias.
if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir")
					rrmdir($dir."/".$object);
				else unlink($dir."/".$object);
			}
		}
		reset($objects);
		return rmdir($dir);
	}
	return false;
}

require 'config.inc.php';
$allOK = true;
echo "\n\nCheck relationships Alias  <-> Directory\n\n";
//Get alias files & directory
$aliasList = array();
if(is_dir($aliasDir)) {
  $handle=opendir($aliasDir);
  $i = 0;
  while(false !== ($file = readdir($handle))) {
  	if (is_file($aliasDir.$file) && stripos($file, '.conf') !== false) {
    	$aliasList[$i]['file'] = $file;
			$alias_contents = @file_get_contents($aliasDir.$file);
	  	if(preg_match('~^Alias\s+/(.+)\s+"(.+)"\r?$~m',$alias_contents,$matches) > 0) {
    		$aliasList[$i]['alias'] = $matches[1];
	  		$aliasList[$i]['dir'] = $matches[2];
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
  			echo "\n\nIn alias file: '".$aliasList[$key]['file']."'\n";
  			echo "Alias '".$aliasList[$key]['alias']."' request to use the directory '".$aliasList[$key]['dir']."' that doesn't exist.\n";
  			echo "The alias is therefore inoperative.\n\n";
  			echo "Do you want to delete alias file : '".$aliasList[$key]['file']."'\n\nType 'yes' to confirm : ";
				$confirm = trim(fgets(STDIN));
				$confirm = strtolower(trim($confirm ,'\''));
				if($confirm == 'yes') {
					if(unlink($aliasDir.str_replace('-whitespace-',' ',$aliasList[$key]['file']))) {
						echo "\n Alias file '".$aliasList[$key]['file']."' deleted.\n";
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
		echo "\n\n'".$value."' directory is not used by any alias\n";
  	echo "Do you want to delete directory : '".$value."'\n\nType 'yes' to confirm : ";
		$confirm = trim(fgets(STDIN));
		$confirm = strtolower(trim($confirm ,'\''));
		if($confirm == 'yes') {
			if(rrmdir($value) === false)
				echo "\n\nFolder ".$value." **not** deleted\n";
			else
				echo "\n\nFolder ".$value." deleted\n";
		}
	}
}
if($allOK) echo "\n\nNo faults were detected\n";
echo "\nPress Enter to exit...";
trim(fgets(STDIN));
exit();

?>
