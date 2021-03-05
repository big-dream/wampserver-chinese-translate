<?php
// 3.2.5 - Ask to delete folder associated with alias
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
$alias = $_SERVER['argv'][1];
$aliasToDelete = $alias.".conf";
if(file_exists($aliasDir.$aliasToDelete)) {
	echo "\n\nAlias : ".$aliasToDelete."\n\n\nDo you really want to delete this alias?\n\nType 'yes' to confirm : ";
	$confirm = trim(fgets(STDIN));
	$confirm = strtolower(trim($confirm ,'\''));
	if ($confirm == 'yes') {
		$alias_contents = @file_get_contents($aliasDir.$aliasToDelete);
	  preg_match('~^Alias\s+/'.$alias.'\s+"(.*)"\r?$~m',$alias_contents,$matches);
	  if(is_dir($matches[1])) {
	  	$deleteDir = false;
	  	echo "\n\nThe directory ".$matches[1]." is associated with alias ".$alias."\n\nDo you really want to delete also this directory?\n\nType 'yes' to confirm : ";
			$confirm = trim(fgets(STDIN));
			$confirm = strtolower(trim($confirm ,'\''));
			if($confirm == 'yes') {
				$deleteDir = true;
				$dirToDelete = $matches[1];
			}
	  }
		if(unlink($aliasDir.str_replace('-whitespace-',' ',$aliasToDelete))) {
			echo "\n\n\nAlias deleted\n";
			if($deleteDir) {
				if(file_exists($dirToDelete) && is_dir($dirToDelete)) {
					if(rrmdir($dirToDelete) === false)
						echo "\n\nFolder ".$dirToDelete." **not** deleted\n";
					else
						echo "\n\nFolder ".$dirToDelete." deleted\n";
				}
			}
		}
		else
			echo "\n\n**Unable to delete** alias with unlink()\n";
		echo "\n\n\n\nPress Enter to exit...";
		trim(fgets(STDIN));
	  exit();
	}
	else {
		echo "\n\nAlias not deleted.\n";
	}
}
else {
	echo "Alias: ".$aliasDir.$aliasToDelete." doesn't exist\n";
}
echo "按回车键（ENTER）退出...";
trim(fgets(STDIN));
exit();

?>
