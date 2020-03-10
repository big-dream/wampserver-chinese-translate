<?php
//3.0.7 Delete unused versions PHP, MySQL, Apache or Mariadb
if(!defined('WAMPTRACE_PROCESS')) require('config.trace.php');
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
}

require 'config.inc.php';


$type = $_SERVER['argv'][1];
$version = $_SERVER['argv'][2];

if($type == 'apache')
	$delDir = $c_apacheVersionDir.'/apache'.$version;
elseif($type == 'php')
	$delDir = $c_phpVersionDir.'/php'.$version;
elseif($type == 'mysql')
	$delDir = $c_mysqlVersionDir.'/mysql'.$version;
elseif($type == 'mariadb')
	$delDir = $c_mariadbVersionDir.'/mariadb'.$version;
else {
	exit();
}
if(file_exists($delDir) && is_dir($delDir)) {
	//exec("rd /s /q {$delDir}");
	if(rrmdir($delDir) === false)
		error_log("Folder ".$delDir." not deleted");
}

?>