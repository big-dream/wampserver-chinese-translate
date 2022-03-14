<?php
// 3.2.6 - Support for Apache Graceful Restart

$server_dir = "../";
session_start();

require $server_dir.'scripts/config.inc.php';
require $server_dir.'scripts/wampserver.lib.php';
$c_PortToUse = $c_UsedPort;

// Language
$langue = $wampConf['language'];
$i_langues = glob('wamplangues/add_vhost_*.php');
$languages = array();
foreach ($i_langues as $value) {
  $languages[] = str_replace(array('wamplangues/add_vhost_','.php'), '', $value);
}
$langueget = (!empty($_GET['lang']) ? strip_tags(trim($_GET['lang'])) : '');
if(in_array($langueget,$languages))
	$langue = $langueget;

// Recherche des différentes langues disponibles
$langueswitcher = '<form method="get" style="display:inline-block;margin-left:10px;"><select name="lang" id="langues" onchange="this.form.submit();">'."\n";
$selected = false;
foreach ($languages as $i_langue) {
  $langueswitcher .= '<option value="'.$i_langue.'"';
  if(!$selected && $langue == $i_langue) {
  	$langueswitcher .= ' selected ';
  	$selected = true;
  }
  $langueswitcher .= '>'.$i_langue.'</option>'."\n";
}
$langueswitcher .= '</select></form>';

include 'wamplangues/add_vhost_english.php';
if(file_exists('wamplangues/add_vhost_'.$langue.'.php')) {
	$langue_temp = $langues;
	include 'wamplangues/add_vhost_'.$langue.'.php';
	$langues = array_merge($langue_temp, $langues);
}

// Automatic error correction?
$automatique = (isset($_POST['correct']) ? true : false);

$message_ok = '';
$message = array();
$errors = false;
$errors_auto = false;
$vhost_created = false;
$sub_menu_on = true;

//We get the value of VirtualHostMenu
$VirtualHostMenu = !empty($wampConf['VirtualHostSubMenu']) ? $wampConf['VirtualHostSubMenu'] : "off";
if($VirtualHostMenu !== "on") {
	$message[] = '<p class="warning">'.$langues['VirtualSubMenuOn'].'</p>';
	$errors = true;
	$sub_menu_on = false;
}
//To not see form to suppress VirtualHost
$seeVhostDelete = (isset($_POST['seedelete']) && strip_tags(trim($_POST['seedelete'])) == 'afficher') ? true : false;

/* Some tests about httpd-vhosts.conf file */
$virtualHost = check_virtualhost();
$listenPort = listen_ports($c_apacheConfFile);
$w_VirtualPortForm = '';
$authorizedPorts = array();
if(count($listenPort) > 1) {
	$c_listenPort = '';
	foreach($listenPort as $value) {
		if($value != '80' && $value != $c_UsedPort) {
			$c_listenPort .= $value." ";
			$authorizedPorts[] = $value;
		}
	}
	$portsAccepted = sprintf($langues['VirtualHostPort'],$c_listenPort);
//	$w_VirtualPortForm = <<< EOF
//		<label>{$portsAccepted}<code class="option"><i>{$langues['Optional']}</i></code></label><br>
//			<input class="optional" type="text" name="vh_port"/><br>
//EOF;

	$w_VirtualPortForm = <<< EOF
		<label>{$portsAccepted}<code class="option"><i>{$langues['Optional']}</i></code></label><br>
			<input style='width:20px;height:20px;margin:2px 0 5px 45px;' type='checkbox' name='vh_port_on' value='on'><select style='margin:5px 0 5px 10px;' name='vh_port'>
EOF;
	for($i=0;$i < count($authorizedPorts);$i++) {
  	$w_VirtualPortForm .= "<option value='".$authorizedPorts[$i]."'>Listen Port&nbsp;:&nbsp;".$authorizedPorts[$i]."&nbsp;&nbsp;</option>";
	}
	$w_VirtualPortForm .= "</select><br>";

}
else {
	$w_VirtualPortForm = <<< EOF
		<label>{$langues['VirtualHostPortNone']}<code class="option"><i>{$langues['Optional']}</i></code></label><br><br>
EOF;
}

/* If form suppress VirtualHost submitted */
if(isset($_POST['vhostdelete'])
	&& isset ($_SESSION['passdel'])
	&& isset($_POST['checkdelete'])
	&& strip_tags(trim($_POST['checkdelete'])) == $_SESSION['passdel']) {
	if(isset($_POST['virtual_del'])) {
		$myVhostsContents = file_get_contents($c_apacheVhostConfFile);
		$myHostsContents = file_get_contents($c_hostsFile);
		$nb = count($_POST['virtual_del']);
		$replaceVhosts = $replaceHosts = false;
		for($i = 0; $i < $nb ;$i++) { //for b1
			$value = strip_tags(trim($_POST['virtual_del'][$i]));
			if(!in_array($value, $virtualHost) || $value == 'localhost') {
				$value = '';
				break;
			}
			$p_value = preg_quote($value);
			//Check a port number
			$ApacheVar = '';
			$value_url = $port = '';
			if(strpos($value, ':') !== false) {
				$value_url = strstr($value,':',true);
				$port = substr(strstr($value,':'),1);
				if(in_array($port,$c_ApacheDefine) || ($wampConf['apacheUseOtherPort'] == 'on' && $port == $c_UsedPort)) {
					$ApacheVar = array_search($port,$c_ApacheDefine);
					$p_value = preg_quote($value_url);
				}
				else
					error_log("Value ".$port." does not exist in array \$c_ApacheDefine. It is not a value of an Apache Variable");
			}
			if(in_array($value, $virtualHost['ServerName'])) {
				//Extract <VirtualHost... </VirtualHost>
				$mask = "{
					<VirtualHost                         # beginning of VirtualHost
					[^<]*(?:<(?!/VirtualHost)[^<]*)*     # avoid premature end
					\n\s*ServerName\s+${p_value}\s*\n    # Test server name
					.*?                                  # we stop as soon as possible
					</VirtualHost>\s*\n                  # end of VirtualHost
					}isx";
				$countName = 0;
				$countName = preg_match_all($mask,$myVhostsContents,$matches);
				$found = false;
				if($countName > 0) {
				for($j = 0 ; $j < $countName; $j++) {
					if(empty($ApacheVar)) {
						if(strpos($matches[0][$j],'MYPORT') === false) {
							$found = $j;
							break;
						}
					}
					else {
						if(strpos($matches[0][$j],$ApacheVar) !== false) {
							$found = $j;
							break;
						}
					}
				}
}
				if($found !== false) {
					$myVhostsContents = str_replace($matches[0][$found],'',$myVhostsContents, $count);
					if($count > 0) {
						$replaceVhosts = true;
					}
				}

				if($countName == 1) {
					//Suppress ServerName into hosts file
					$count = $count1 = 0;
					$myHostsContents = preg_replace("~^([0-9\.:]+\s+".$p_value."\r?\n?)~mi",'',$myHostsContents,-1, $count);
					$myHostsContents = str_ireplace($value,'',$myHostsContents,$count1);
					if($count > 0 || $count1 > 0 )
						$replaceHosts = true;
				}
			}
			else {
				$message[] = '<p class="warning">ServerName '.$value.' doesn\'t exist</p>';
				$errors = true;
			}

		} //End for b1

		if($replaceVhosts) {
			//Cleaning of httpd-vhosts.conf file
			$myVhostsContents = clean_file_contents($myVhostsContents);
			$fp = fopen($c_apacheVhostConfFile, 'wb');
			fwrite($fp, $myVhostsContents);
			fclose($fp);
		}
		if($replaceHosts) {
			if($wampConf['BackupHosts'] == 'on') {
				@copy($c_hostsFile,$c_hostsFile."_wampsave.".$next_hosts_save);
				$next_hosts_save++;
			}
			//Cleaning of hosts file
			$myHostsContents = clean_file_contents($myHostsContents,array(2,0),true);
			$fp = fopen($c_hostsFile, 'r+b');
			if(flock($fp, LOCK_EX)) { // acquire an exclusive lock
    		ftruncate($fp, 0);      // truncate file
    		fwrite($fp, $myHostsContents);
    		fflush($fp);            // flush output before releasing the lock
    		flock($fp, LOCK_UN);    // release the lock
			}
			else {
				$message[] = '<p class="warning">Unable to write to '.$c_hostsFile.' file</p>';
				$errors = true;
			}
			fclose($fp);
		}
		$virtualHost = check_virtualhost();
	}
}

$VhostDefine = $VhostDelete = "";
if($virtualHost['nb_Server'] > 0) {
	$i = 0;
	foreach($virtualHost['ServerName'] as $value) {
		$ip ='';
		if(!empty($virtualHost['virtual_ip'][$i]))
			$ip = " - VirtualHost ip = <span style='color:blue;'>".$virtualHost['virtual_ip'][$i].'</span>';
		$UrlPortVH = ($virtualHost['ServerNamePort'][$value] != '80') ? "<span style='color:red;'>:".$virtualHost['ServerNamePort'][$value]."</span>" : "";
		$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
		$value_aff = ($virtualHost['ServerNameIDNA'][$value] === true) ? $value." <span style='color:green;'><small>IDNA-> ".$virtualHost['ServerNameUTF8'][$value].'</small></span>' : $value_url;
		if($virtualHost['ServerNameValid'][$value] === false)
			$VhostDefine .= "<li><i>ServerName : </i><span style='color:red;'>".$value_aff." - ServerName syntax error</span></li>\n";
		else
			$VhostDefine .= "<li><i>ServerName : </i><span style='color:blue;'>".$value_aff."</span>".$UrlPortVH." - <i>Directory : </i>".$virtualHost['documentPath'][$i].$ip."</li>\n";
		if($value != 'localhost')
			$VhostDelete .= "<li><i>ServerName : </i><input type='checkbox' name='virtual_del[]' value='".$value."'/> <span style='color:blue;'>".$value."</span></li>";
		$i++;
	}
}
if($virtualHost['include_vhosts'] === false && !$errors) {
	if($automatique) {
		$httpConfFileContents = file_get_contents($c_apacheConfFile);
		$httpConfFileContents = preg_replace("~^[ \t]*#[ \t]*(Include[ \t]*conf/extra/httpd-vhosts.conf.*)$~m","$1",$httpConfFileContents,1);
		$fp = fopen($c_apacheConfFile,'wb');
		fwrite($fp,$httpConfFileContents);
		fclose($fp);
		$virtualHost = check_virtualhost();
	}
	else {
		$message[] = '<p class="warning_auto">'.sprintf($langues['UncommentInclude'],$c_apacheConfFile).'</p>';
		$errors = true;
		$errors_auto = true;
	}
}
if($virtualHost['vhosts_exist'] === false && !$errors) {
	if($automatique) {
		$fp = fopen($c_apacheVhostConfFile,'wb');
		fclose($fp);
		$virtualHost = check_virtualhost();
	}
	else {
		$message[] = '<p class="warning_auto">'.sprintf($langues['FileNotExists'],$c_apacheVhostConfFile).'</p>';
		$errors = true;
		$errors_auto = true;
	}
}
if(in_array("dummy", $virtualHost['ServerNameValid'], true) !== false && !$errors) {
	if($automatique) {
		$fp = fopen($c_apacheVhostConfFile,'wb');
		fclose($fp);
		$virtualHost = check_virtualhost();
	}
	else {
		$message[] = '<p class="warning_auto">'.sprintf($langues['NotCleaned'],$c_apacheVhostConfFile).'</p>';
		$errors = true;
		$errors_auto = true;
	}
}
if(empty($virtualHost['FirstServerName']) && !$errors) {
	if($automatique) {
		$virtual_localhost = <<< EOFLOCAL

#
<VirtualHost *:{$c_PortToUse}>
	ServerName localhost
	DocumentRoot "{$wwwDir}"
	<Directory  "{$wwwDir}/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
</VirtualHost>

EOFLOCAL;

		$fp = fopen($c_apacheVhostConfFile,'wb');
		fwrite($fp,$virtual_localhost);
		fclose($fp);
		$virtualHost = check_virtualhost();

	}
	else {
		$message[] = '<p class="warning_auto">'.sprintf($langues['NoVirtualHost'],$c_apacheVhostConfFile).'</p>';
		$errors = true;
		$errors_auto = true;
	}
}

/* If form submitted */
if(isset($_POST['submit'])
	&& !$errors
	&& isset($_SESSION['passadd'])
	&& isset($_POST['checkadd'])
	&& strip_tags(trim($_POST['checkadd'])) == $_SESSION['passadd']) {
	// Escape any backslashes used in the path to the file
	//$c_apacheVhostConfFile = str_replace('\\', '\\\\', $c_apacheVhostConfFile);
	$vh_name = strip_tags(trim($_POST['vh_name']));
	$vh_ip = strip_tags(trim($_POST['vh_ip']));
	$vh_port = '';
	if(isset($_POST['vh_port_on']) && strip_tags(trim($_POST['vh_port_on'])) == 'on') {
		$vh_port = strip_tags(trim($_POST['vh_port']));
	}
	$vh_folder = str_replace(array('\\','//'), '/',strip_tags(trim($_POST['vh_folder'])));
	if(substr($vh_folder,-1) == "/")
		$vh_folder = substr($vh_folder,0,-1);
	$vh_folder = strtolower($vh_folder);
	//3.0.6 - Check / at first character
	if(substr($vh_folder,0,1) == "/" && substr($vh_folder,0,2) != "//")
		$vh_folder = "/".$vh_folder;

	if($virtualHost['FirstServerName'] !== "localhost" && !$errors) {
		$message[] = '<p class="warning">'.sprintf($langues['NoFirst'],$c_apacheVhostConfFile).'</p>';
		$errors = true;
	}
	/* Validity of the domain name */
	clearstatcache(); // added for update 3.1.4
	//Check if IDN is needed
	$vh_nameIDN = idn_to_ascii($vh_name,IDNA_DEFAULT,INTL_IDNA_VARIANT_UTS46);
	if($vh_nameIDN !== $vh_name)
		$vh_name = $vh_nameIDN;
	// IDNA (Punycode) 3.2.3 - improve regex
	$regexIDNA = '#^([\w-]+://?|www[\.])?xn--[a-z0-9]+[a-z0-9\-\.]*[a-z0-9]+(\.[a-z]{2,7})?$#';
	// Not IDNA  /^[A-Za-z]+([-.](?![-.])|[A-Za-z0-9]){1,60}[A-Za-z0-9]$/
	if(preg_match($regexIDNA,$vh_name,$matchesIDNA) == 0
		&& preg_match('/^
		(?=.*[A-Za-z]) # at least one letter somewhere
		[A-Za-z0-9]+ 	 # letter or number in first place
		([-.](?![-.])	 #  a . or - not followed by . or -
				|					 #   or
		[A-Za-z0-9]		 #  a letter or a number
		){0,60}				 # this, repeated from 0 to 60 times - at least two characters
		[A-Za-z0-9]		 # letter or number at the end
		$/x',$vh_name) == 0) {
		$message[] = '<p class="warning">'.sprintf($langues['ServerNameInvalid'],$vh_name).'</p>';
		$errors = true;
	}
	elseif($wampConf['NotVerifyTLD'] == 'off' && substr($vh_name,-4) !== false && (strtolower(substr($vh_name,-4) == '.dev'))) {
		$message[] = '<p class="warning">'.sprintf($langues['txtTLDdev'],$vh_name,".dev").'</p>';
		$errors = true;
	}
	elseif((!file_exists($vh_folder) || !is_dir($vh_folder))) {
		$message[] = '<p class="warning">'.sprintf($langues['DirNotExists'],$vh_folder).'</p>';
		$errors = true;
	}
	elseif(strtolower($vh_folder) == strtolower($wwwDir)) {
		$message[] = '<p class="warning">'.sprintf($langues['NotwwwDir'],$vh_folder).'</p>';
		$errors = true;
	}
	elseif($c_hostsFile_writable !== true) {
		$message[] = '<p class="warning">'.sprintf($langues['FileNotWritable'],$c_hostsFile).'</p>';
		$errors = true;
	}
	elseif($wampConf['NotCheckDuplicate'] == 'off' && array_key_exists(strtolower($vh_name), array_change_key_case($virtualHost['ServerName'], CASE_LOWER))) {
		if(empty($vh_port) || !in_array($vh_port, $authorizedPorts)) {
			$message[] = '<p class="warning">'.sprintf($langues['VirtualAlreadyExist'],$vh_name).'</p>';
			$errors = true;
		}
	}
	$c_UsedIp = '*';
	$c_HostIp = '127.0.0.1';
	if(!$errors && !empty($vh_ip)) {
		if($vh_ip == '127.0.0.0' || $vh_ip == '127.0.0.1' ) {
		$message[] = '<p class="warning">'.sprintf($langues['VirtualIpAlreadyUsed'],$vh_ip).'</p>';
		$errors = true;
		}
		// Validité IP locale
		elseif(check_IP($vh_ip) === false) {
			$message[] = '<p class="warning">'.sprintf($langues['LocalIpInvalid'],$vh_ip).'</p>';
			$errors = true;
		}
		elseif(in_array($vh_ip, $virtualHost['virtual_ip']) && $wampConf['NotCheckDuplicate'] == 'off') {
			$message[] = '<p class="warning">'.sprintf($langues['VirtualIpAlreadyUsed'],$vh_ip).'</p>';
			$errors = true;
		}
		else
			$c_UsedIp = $c_HostIp = $vh_ip;
	}
	if(!$errors && !empty($vh_port)) {
		if($vh_port == '80' || $vh_port == $c_UsedPort) {
			$message[] = '<p class="warning">'.sprintf($langues['VirtualPortExist'],$vh_port).'</p>';
			$errors = true;
		}
		elseif(!in_array($vh_port, $authorizedPorts)) {
			$message[] = '<p class="warning">'.sprintf($langues['VirtualPortNotExist'],$vh_port).'</p>';
			$errors = true;
		}
		else {
			$key = array_search($vh_port, $c_ApacheDefine);
			$c_PortToUse = '${'.$key.'}';
		}
	}
	if($errors === false) {
		/* Preparation of files content */
		$httpd_vhosts_add = <<< EOFNEWVHOST


#
<VirtualHost {$c_UsedIp}:{$c_PortToUse}>
	ServerName {$vh_name}
	DocumentRoot "{$vh_folder}"
	<Directory  "{$vh_folder}/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>
</VirtualHost>

EOFNEWVHOST;
		$hosts_add = <<< EOFHOSTS

{$c_HostIp}	{$vh_name}
::1	{$vh_name}

EOFHOSTS;
		/* Opening the files to add the lines */
		if($wampConf['BackupHosts'] == 'on') {
			@copy($c_hostsFile,$c_hostsFile."_wampsave.".$next_hosts_save);
			$next_hosts_save++;
		}
		$fp1 = fopen($c_apacheVhostConfFile, 'a+b');
		$fp2 = fopen($c_hostsFile, 'a+b');
		$fp1w = $fpw2 = false;
		if(fwrite($fp1, $httpd_vhosts_add) !== false) $fp1w = true;
		if(fwrite($fp2, $hosts_add) !== false) $fp2w = true;
		fclose($fp1);
		fclose($fp2);
		if($fp1w === true && $fp2w === true) {
			if("Possible" === true) { // It is not possible — for the moment — to do that from php WEB
			// Restart DNS
			$command = 'CMD /D /C ipconfig /flushdns';
			$output = `$command`;
			// Apache Graceful Restart
			$command = 'CMD /D /C '.str_replace('/','\\',$c_apacheExe).' -n '.$c_apacheService.' -k restart';
			$output .= `$command`;
			$command = 'CMD /D /C START /D '.str_replace('/','\\',$c_installDir).'\scripts /WAIT /B '.str_replace('/','\\',$c_phpExe).' -f refresh.php';
			//error_log("command=\n".$command);
			$output = `$command`;
			//error_log("output=\n".$output);

			// Message to Refresh Wampmanager to update menu's
			$message_ok = '<p class="ok">'.sprintf($langues['VirtualCreated'],$vh_name).'</p>';
			$message_ok .= '<p class="ok_plus">'.$langues['HoweverWamp'].'</p>';
			$vhost_created = true;
			}
			else {
				$message_ok = '<p class="ok">'.sprintf($langues['VirtualCreated'],$vh_name).'</p>';
				$message_ok .= '<p class="ok_plus">'.$langues['However'].'</p>';
				$vhost_created = true;
			}
		}
		else {
			$message = '<p class="warning">'.$langues['NoModify'].'</p>';
		}
	}
}

$pageContents = <<< EOPAGE
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>${langues['addVirtual']}</title>
		<meta charset="UTF-8">
		<style>
			* {
				margin: 0;
				padding: 0;
			}

			html {
				background: #ddd;
			}
			body {
				margin: 1em 5%;
				padding: 1em 3em;
				font: 80%/1.4 tahoma, arial, helvetica, lucida sans, sans-serif;
				border: 1px solid #999;
				background: #eee;
				position: relative;
			}
			header {
				margin-bottom: 1.8em;
				margin-top: .5em;
				padding-bottom: 0em;
				border-bottom: 1px solid #999;
				height: 125px;
				background: url('img/gifLogo.gif') 0 0 no-repeat;
			}

			header h1 {
				padding-left: 130px;
				padding-top: 15px;
				font-size: 1.8em;
			}

			header h1 a:hover {color:blue;}

			h2 {
				margin: 0.8em 0 0 0;
			}

			p {
				padding: 1%;
			}

			.ok, .ok_plus, .warning, .warning_auto {
				text-align: center;
				font-size: 1.3em;
				text-shadow: 1px 1px 0 #000;
				background: #585858;
			}

			.ok {
				color: limegreen;
			}
			.ok_plus {
				text-align:justify;
				background: #777777;
			}

			.warning, .warning_auto, .ok_plus {
				color: orange;
			}
			.warning_auto {
				border: 3px solid #4FEF10;
			}
			label {
				padding-left: 22px;
				margin-left: 22px;
				background: url('img/pngWrench.png') 0 100% no-repeat;
			}

			input[type="text"] {
				width: 80%;
				margin: 0.2% 1% 1% 1%;
				padding: 0.3% 1%;
				border: 1px solid #999;
			}
			input.required {
				border:1px solid red;
			}
			input.optional {
				border:1px solid green;
			}
			input[type="submit"] {
				min-width: 50%;
				background: #DDD;
				border: 1px solid #999;
				margin: 1%;
				padding: 0.3% 1%;
			}

			input[type="checkbox"] {
				vertical-align: middle;
			}

			input[type="submit"]:hover {
				background: #FF0099;
				color: #FFF;
			}

			pre {
				width: 98%;
				overflow: auto;
				padding: 1%;
				border: #FF0099 1px solid;
				background: #585858;
			}

			a {
				color: #000;
				text-decoration: none;
			}

			code, code.option, code.requis {
				color: #FFF;
				text-shadow: 1px 1px 0 #000;
				padding: 0.1% 0.5%;
				border-radius: 3px;
				background: #585858;
				font-size: 1.2em;
			}
			code.option {
				background: green;
			}
			code.requis {
				background: red;
			}
			.utility {
				position: absolute;
				right: 4em;
				top: 122px;
				font-size: 0.85em;
			}
		</style>
	</head>
	<body>
	<header>
		<h1><a href="add_vhost.php?lang={$langue}">{$langues['addVirtual']}</a> - <a href="index.php?lang={$langue}">{$langues['backHome']}</a></h1>
		<ul class="utility">
		  <li>Version ${c_wampVersion} - ${c_wampMode}${langueswitcher}</li>
	  </ul>
	</header>
EOPAGE;

if($vhost_created)
	$pageContents .= $message_ok;
else {
	if($errors) {
		foreach($message as $value)
		 	$pageContents .= $value;
		}
	if($sub_menu_on === true) {
	$pageContents .= <<< EOPAGEB
		<p>Apache Virtual Hosts <code>{$c_apacheVhostConfFile}</code></p>
EOPAGEB;
	if(!empty($VhostDefine)) {
	$pageContents .= <<< EOPAGEB
		<p>{$langues['VirtualHostExists']}</p>
		<div style='width:70%;float:left;'>
			<ul style='list-style:none;'>{$VhostDefine}</ul>
		</div>
		<div id='vhostdelete' style='width:28%;float:right;'>
EOPAGEB;
		if(!empty($VhostDelete) && $wampConf['NotCheckDuplicate'] == "off" && $wampConf['NotCheckVirtualHost'] == 'off') {
			if($seeVhostDelete) {
			$_SESSION['passdel'] = mt_rand(100000001,mt_getrandmax());
			$pageContents .= <<< EOPAGEB
			<form id='deletevhost' method='post'>
				<ul style='list-style:none;'>{$VhostDelete}</ul>
				<input type='hidden' name='checkdelete' value='{$_SESSION['passdel']}' />
				<input type='submit' name='vhostdelete' value='{$langues['suppVhost']}' />
			</form>
EOPAGEB;
			}
			else {
			$pageContents .= <<< EOPAGEB
			<form id='seedelete' method='post' style='display:inline-block;'>
			<input type='hidden' name='seedelete' value='afficher'/>
			<input type='submit' value='{$langues['suppForm']}'/>
			</form>
EOPAGEB;
			}
		}
	}
	$pageContents .= <<< EOPAGEB
		</div>
		<div style='clear:both;'></div>
		<p>Windows hosts <code>{$c_hostsFile}</code></p>
EOPAGEB;
	$pageContents .= '<form method="post">';
	if($errors_auto) {
	$pageContents .= <<< EOPAGEB
	<p><label>{$langues['GreenErrors']}</label></p>
		<p style="text-align: right;"><input type="submit" name="correct" value="{$langues['Correct']}" /></p>

EOPAGEB;
	}
	else {
	$_SESSION['passadd'] = mt_rand(100000001,mt_getrandmax());
	$pageContents .= <<< EOPAGEB
		<p><label>{$langues['VirtualHostName']}<code class="requis"><i>{$langues['Required']}</i></code></label><br>
			<input class='required' type="text" name="vh_name" required="required" /><br>
		<label>{$langues['VirtualHostFolder']}<code class="requis"><i>{$langues['Required']}</i></code></label><br>
			<input class='required' type="text" name="vh_folder" required="required"/></p>
		{$w_VirtualPortForm}
		<label>{$langues['VirtualHostIP']}<code class="option"><i>{$langues['Optional']}</i></code></label><br>
			<input class='optional' type="text" name="vh_ip"/><br>
			<input type='hidden' name='checkadd' value='{$_SESSION['passadd']}' />
		<p style="text-align: right;"><input type="submit" name="submit" value="{$langues['Start']}" /></p>

EOPAGEB;
	}
	}
	$pageContents .= <<< EOPAGEB
	</form>
EOPAGEB;
}
$pageContents .= <<< EOPAGEB
</body>
</html>
EOPAGEB;
echo $pageContents;
?>