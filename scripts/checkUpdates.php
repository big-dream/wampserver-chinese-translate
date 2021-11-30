<?php

if(!defined('WAMPTRACE_PROCESS')) require 'config.trace.php';
if(WAMPTRACE_PROCESS) {
	$errorTxt = "script ".__FILE__;
	$iw = 1; while(!empty($_SERVER['argv'][$iw])) {$errorTxt .= " ".$_SERVER['argv'][$iw];$iw++;}
	error_log($errorTxt."\n",3,WAMPTRACE_FILE);
}

require 'config.inc.php';
require 'wampserver.lib.php';

//-- Check if file 'last32|64_versions.txt'
//   from 'https://wampserver.aviatechno.net'
//   can be loaded and that the contents is correct.
Command_Windows('Check Wampserver updates',55,2,0,'Check Wampserver updates');
$last_wamp_versions = false;
$message = $message_final = $message_question = '';
if($wampConf['wampserverMode'] == '32bit')
	$file = 'https://wampserver.aviatechno.net/last32_versions.txt';
else
	$file = 'https://wampserver.aviatechno.net/last64_versions.txt';

$contents = @file_get_contents($file);
if($contents !== false) {
	// Rewrite the file 'last_versions.txt' into a php file
	write_file('last_versions.php',$contents);
	// Include file to get $wamp_versions array
	include 'last_versions.php';
	if(isset($wamp_versions) && is_array($wamp_versions) && count($wamp_versions) > 20) {
		// Check if file 'last_versions_here.txt'
		// from wampserver refresh.php script
		// can be loaded and that the contents is correct
		$file = 'last_versions_here.txt';
		$contents = @file_get_contents($file);
		if($contents !== false) {
			write_file('last_versions_here.php',$contents);
			// Include file to get $wamp_versions_here array
			include 'last_versions_here.php';
			if(isset($wamp_versions_here) && is_array($wamp_versions_here) && count($wamp_versions_here) > 4) {
				$last_wamp_versions = true;
			}
			else {
				$message .= color('red',"The \$wamp_versions_here array does not exist \nor does not have a correct content in the file: '".$file."'\n");
				$message .= "\nPossibly: Right-click -> Refresh will correct this problem\n";
			}
		}
		else {
			$message .= color('red',"Cannot get contents of file: ".$file."\n");
			$message .= "\nPossibly: Right-click -> Refresh will correct this problem\n";
		}
	}
	else {
		$message .= color('red',"The \$wamp_versions array does not exist \nor does not have a correct content in the file: '".$file."'\n");
	}
}
else {
	if(empty($http_response_header)) $message .= color('red',"No response from the update server\n");
	$message .= color('red',"Cannot get contents of file: '".$file."\n");
}
if(!$last_wamp_versions) {
	$message_final .= color('red').color('bold','WARNING:')."Cannot check Wampserver updates\n".$message."\n";
	$message_question .= "Press ENTER to continue...";
	echo 'exit(0)';
	Command_Windows($message_final.$message_question,-1,-1,0,'Check Wampserver updates');
	trim(fgets(STDIN));
	exit(0);
}
//We can check if there are updates
$update_available = false;
$your_versions = '';
foreach($wamp_versions as $key => $last_version) {
	$version_used = color('red','not installed');
	$used = false;
	if($key == 'wamp_update') $key_txt = 'Wampserver';
	elseif($key == 'wamp_aestan') $key_txt = 'Aestan Tray Menu';
	elseif($key == 'wamp_phpmyadmin') $key_txt = 'PhpMyAdmin';
	elseif($key == 'wamp_adminer') $key_txt = 'Adminer';
	elseif($key == 'wamp_phpsysinfo') $key_txt = 'PhpSysInfo';
	elseif(strpos($key, 'php') === 0) $key_txt = substr($key,0,3).' '.substr($key,-2,1).'.'.substr($key,-1,1);
	elseif(strpos($key, 'mysql') === 0) $key_txt = substr($key,0,5).' '.substr($key,-2,1).'.'.substr($key,-1,1);
	elseif(strpos($key, 'mariadb') === 0) $key_txt = substr($key,0,7).' '.substr($key,-3,2).'.'.substr($key,-1,1);
	elseif(strpos($key, 'apache') === 0) $key_txt = substr($key,0,6).' '.substr($key,-2,1).'.'.substr($key,-1,1);
	if(array_key_exists($key, $wamp_versions_here)) {
		$used = true;
		$version_used = $wamp_versions_here[$key];
		if(version_compare($wamp_versions[$key], $version_used, '>')){
			$update_available = true;
			$message .= str_pad('- '.$key_txt.':',20).str_pad($version_used,12)." updated to ".color('blue').color('bold',$last_version."\n");
		}
	}
	if(!$used) $your_versions .= str_pad($key_txt.':',15).$version_used.' - Last version: '.$last_version."\n";
}
if($update_available) {
	$message_final .= color('blue').color('bold',">>>   Update(s) available\n\n");
	$message_final .= $message;
	$message_final .= "\nVersions not installed on your Wampserver:\n".$your_versions;
	$message_final .= "\n>>>   Update or new addons versions are available on\n https://wampserver.aviatechno.net/\n https://sourceforge.net/projects/wampserver/files/WampServer%203/WampServer%203.0.0/\n";
	$width = -1;
	$copyClip = true;
	$message_question .= "\n--- Do you want to copy the results into Clipboard?\n--- Press the Y key to confirm - ";
}
else {
	$message_final = color('red').color('bold',">>>   No update available\n");
	$message_final .= "\nVersions not installed on your Wampserver:\n".$your_versions;
	$width = 55;
	$copyClip = false;
	$message_question = "\n";
}

$message_question .= "Press ENTER to continue...";
echo 'exit(0)';
Command_Windows($message_final.$message_question,$width,-1,0,'Check Wampserver updates');
$confirm = trim(fgetc(STDIN));
$confirm = strtolower(trim($confirm ,'\''));
if($copyClip && $confirm == 'y') {
	write_file("temp.txt",color('clean',$message_final), true);
}
exit(0);

?>