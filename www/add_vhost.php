<?php
// 3.2.8 - Support for Apache fcgi module

$server_dir = "../";
session_start();

require $server_dir.'scripts/config.inc.php';
require $server_dir.'scripts/wampserver.lib.php';
$c_PortToUse = $c_UsedPort;

$styleOpaNoClickMod = $styleNoDisplay = $serialModify = '';
$VhostToMod = $AliasToMod = array();
$VhostToModify = $AliasToModify = false;

//** Based on an idea by Panagiotis E. Papazoglou
//General scrolling (on or off) is controlled by Wampserver parameter 'ScrollListsHomePage'
// via Right-Click -> Wamp Settings -> Allow scrolling of lists on home page
//To allow or not the individual scrolling of the lists Alias and VirtualHost
//   'scroll' true or false to do the scroll or not
//   'lines'  minimum number of lines to do the scroll
// Do not change anything other than the values assigned to 'scroll' and 'lines'
$Scroll_List = array(
	'alias'    => array('scroll' => true,'lines' => 15,'name' => 'AliasListScroller',   'nbname' => 'nbAliasLines'),
	'vhosts'   => array('scroll' => true,'lines' => 15,'name' => 'VhostsListScroller',  'nbname' => 'nbVhostLines'),
);
foreach($Scroll_List as $key => $value) {
	${$value['name']} = '';
	${$value['nbname']} = 0;
}

//***** Language *****
$langue = $wampConf['language'];
$i_langues = glob('wamplangues/add_vhost_*.php');
$languages = array();
foreach ($i_langues as $value) {
  $languages[] = str_replace(array('wamplangues/add_vhost_','.php'), '', $value);
}
$langueget = (!empty($_GET['lang']) ? strip_tags(trim($_GET['lang'])) : '');
if(in_array($langueget,$languages))
	$langue = $langueget;
// Search for the different languages available
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
include 'wamplangues/help_english.php';
if(file_exists('wamplangues/help_'.$langue.'.php')) {
	$langue_temp = $langues;
	include 'wamplangues/help_'.$langue.'.php';
	$langues = array_merge($langue_temp, $langues);
}

//***** End of languages *****

// Automatic error correction?
$automatique = (isset($_POST['correct']) ? true : false);

$message_ok = '';
$message = array();
$errors = $errors_auto = $vhost_created = $alias_created = false;
$sub_menu_on = true;

//***** Alias values *****
$noDBMS = false; //Only to avoid error
$aliasContents = $AliasModify = '';
// Get Alias, PhpMyAdmin, Adminer & PhpSysInfo versions and parameters
GetAliasVersions();
// Create alias menu
if(is_dir($aliasDir)) {
	$PMyAdNotSeen = true;
	$handle=opendir($aliasDir);
	while (false !== ($file = readdir($handle))) {
	  if(is_file($aliasDir.$file) && strstr($file, '.conf')) {
			$href = $file = str_replace('.conf','',$file);
			if($Alias_Contents[$file]['OK']) {
				$file_sup = '';
				if($Alias_Contents[$file]['fcgid'] && $Alias_Contents[$file]['fcgidPHPOK']) {
					$file_sup .= "<p style='margin:-9px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$Alias_Contents[$file]['fcgidPHP']."</small></p>";
					$nbAliasLines++;
				}
			$aliasContents .= "<li><i>Alias : </i><span style='color:blue;'>".$file."</span>".$file_sup.'</li>';
			$nbAliasLines++;
			$AliasModify .= "<li><i>Alias : </i><input type='radio' name='alias_modify' value='".$file."'/> ".$file.$file_sup."</li>\n";
			}
	  }
	}
	closedir($handle);
}
if(empty($aliasContents))
	$aliasContents = "<li class='phpmynot'>".$langues['txtNoAlias']."</li>\n";
//***** End of Alias *****

//***** VirtualHost values and parameters *****
$VirtualHostMenu = !empty($wampConf['VirtualHostSubMenu']) ? $wampConf['VirtualHostSubMenu'] : "off";
if($VirtualHostMenu !== "on") {
	$message[] = '<p class="warning">'.$langues['VirtualSubMenuOn'].'</p>';
	$errors = true;
	$sub_menu_on = false;
}

/* Some tests about httpd-vhosts.conf file */
$virtualHost = check_virtualhost();
//***** End for VirtualHost

//***** Items for form *****
//***** VirtualHost port form
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
	$w_VirtualPortForm = <<< EOF
<input class='portfcgi' type='checkbox' name='vh_port_on' value='on'>
<label>{$langues['VirtualHostPort']}<code class="option"><i>{$langues['Optional']}</i></code></label><br>
<select class='fcgiport' name='vh_port'>
EOF;
	foreach($authorizedPorts as $authPort) {
  	$w_VirtualPortForm .= "<option value='".$authPort."'>Listen Port&nbsp;:&nbsp;".$authPort."&nbsp;&nbsp;</option>";
	}
	$w_VirtualPortForm .= "</select><br>";

}
else {
	$w_VirtualPortForm = <<< EOF
		<label>{$langues['VirtualHostPortNone']}<code class="option"><i>{$langues['Optional']}</i></code></label><br><br>
EOF;
}
//***** End VirtualHost port form
//***** VirtualHost IP form
$w_VirtualIPForm = <<< EOF
		<label>{$langues['VirtualHostIP']}<code class="option"><i>{$langues['Optional']}</i></code></label><br>
			<input class='optional' type="text" name="vh_ip"/><br>
EOF;
//***** End of VirtualHost IP form
//********************************

//********************************
//***** See or not see forms *****
//To not see form to suppress VirtualHost
$seeVhostDelete = (isset($_POST['seedelete']) && strip_tags(trim($_POST['seedelete'])) == 'afficher') ? true : false;
//To not see form to modify VirtualHost
$seeVhostModify = (isset($_POST['seemodify']) && strip_tags(trim($_POST['seemodify'])) == 'afficher') ? true : false;
//To not see form to modify Alias
$seeAliasModify = (isset($_POST['seealiasmod']) && strip_tags(trim($_POST['seealiasmod'])) == 'afficher') ? true : false;
$styleOpaNoClick = ($seeVhostDelete || $seeVhostModify || $seeAliasModify) ? " style='opacity:0.45;pointer-events:none;'" : '';
$aliasDisplay = ($seeVhostDelete || $seeVhostModify || $seeAliasModify) ? " style='display:none;'" : '';

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
			if(in_array($value, $virtualHost['ServerName'])) {
				//Extract <VirtualHost... </VirtualHost>
				$mask = "{
				<VirtualHost                         # beginning of VirtualHost
				[^<]*(?:<(?!/VirtualHost)[^<]*)*     # avoid premature end
				\n\s*ServerName\s+${p_value}\s*\n    # Test server name
				.*?                                  # we stop as soon as possible
				</VirtualHost>\s*\n                  # end of VirtualHost
				}isx";
				if(preg_match($mask,$myVhostsContents,$matches) === 1) {
					$myVhostsContents = str_replace($matches[0],'',$myVhostsContents, $count);
					if($count > 0) {
						$replaceVhosts = true;
					}
				}
				if($replaceVhosts) {
					//Suppress ServerName into hosts file
					$count = $count1 = 0;
					$myHostsContents = preg_replace("~^([0-9\.:]+\s+".$p_value."\r?\n?)~mi",'',$myHostsContents,-1, $count);
					$myHostsContents = str_ireplace($value,'',$myHostsContents,$count1);
					if($count > 0 || $count1 > 0 ) {
						$replaceHosts = true;
					}
				}
			}
			else {
				$message[] = '<p class="warning">ServerName '.$value.' doesn\'t exist</p>';
				$errors = true;
			}

		} //End for b1

		if($replaceVhosts) {
			//Cleaning of httpd-vhosts.conf file
			$myVhostsContents = clean_file_contents($myVhostsContents."\r\n#\r\n",array(1,0),false,true,true,$c_apacheVhostConfFile);
		}
		if($replaceHosts) {
			//error_log("replaceHosts = true");
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

/* If form modify VirtualHost submitted */
if(isset($_POST['vhostmodify'])
	&& isset ($_SESSION['passmodify'])
	&& isset($_POST['checkmodify'])
	&& strip_tags(trim($_POST['checkmodify'])) == $_SESSION['passmodify']) {
	if(isset($_POST['virtual_modify'])) {
		$VhostToMod['name'] = strip_tags(trim($_POST['virtual_modify']));
		$myVhostsContents = file_get_contents($c_apacheVhostConfFile);
		$VhostToModify = false;
		if(in_array($VhostToMod['name'], $virtualHost['ServerName'])) {
			//Extract <VirtualHost... </VirtualHost>
			$p_value = preg_quote($VhostToMod['name']);
			$mask = "{
				<VirtualHost                         # beginning of VirtualHost
				[^<]*(?:<(?!/VirtualHost)[^<]*)*     # avoid premature end
				\n\s*ServerName\s+${p_value}\s*\n    # Test server name
				.*?                                  # we stop as soon as possible
				</VirtualHost>\s*\n                  # end of VirtualHost
				}isx";
			if(preg_match($mask,$myVhostsContents,$matches) === 1){
				//$matches[0] = complete <VirtualHost ...</VirtulaHost> structure
				$VhostToModify = true;
				$VhostToMod['original'] = $matches[0];
				//Does this VirtualHost use FCGI mode?
				$VhostToMod['fcgi'] = '';
				if($virtualHost['ServerNameFcgid'][$VhostToMod['name']] && $virtualHost['ServerNameFcgidPHPOK'][$VhostToMod['name']]) {
					$VhostToMod['fcgi'] = $virtualHost['ServerNameFcgidPHP'][$VhostToMod['name']];
				}
				//What is the directory of this VirtualHost?
				//<Directory "G:/www/faq-fra/">
				$VhostToMod['directory'] = '';
				if(preg_match('~^[ \t]*<Directory.*"(.*)">.*\r?$~mi',$VhostToMod['original'],$matches) === 1) {
					$VhostToMod['directory'] = $matches[1];
				}
				$serialModify = json_encode($VhostToMod);
			}
		}
	}
}

/* If form modify Alias submitted */
if(isset($_POST['aliasmodify'])
	&& isset ($_SESSION['aliaspassmodify'])
	&& isset($_POST['aliascheckmodify'])
	&& strip_tags(trim($_POST['aliascheckmodify'])) == $_SESSION['aliaspassmodify']) {
	if(isset($_POST['alias_modify'])) {
		$AliasToMod['name'] = strip_tags(trim($_POST['alias_modify']));
		if(in_array($AliasToMod['name'],$Alias_Contents['alias'])){
			$AliasToMod['path'] = $aliasDir.$AliasToMod['name'].'.conf';
			$AliasToMod['directory'] = $aliasDir.$AliasToMod['name'].'.conf';
			$AliasToMod['original'] = file_get_contents($AliasToMod['directory']);
			$AliasToModify = true;
			//Does this Alias use FCGI mode?
			$AliasToMod['fcgi'] = '';
			if($Alias_Contents[$AliasToMod['name']]['fcgid'] && $Alias_Contents[$AliasToMod['name']]['fcgidPHPOK']) {
					$AliasToMod['fcgi'] = $Alias_Contents[$AliasToMod['name']]['fcgidPHP'];
			}
			//What is the directory of this Alias?
			//<Directory "G:/www/faq-fra/">
			$AliasToMod['directory'] = '';
			if(preg_match('~^[ \t]*<Directory.*"(.*)">.*\r?$~mi',$AliasToMod['original'],$matches) === 1) {
				$AliasToMod['directory'] = $matches[1];
			}
			$serialModify = json_encode($AliasToMod);
		}
	}
}

//***** Is form add or modify Vhost or Alias submitted ? ******
if(isset($_POST['submit'])
	&& !$errors
	&& isset($_SESSION['passadd'])
	&& isset($_POST['checkadd'])
	&& strip_tags(trim($_POST['checkadd'])) == $_SESSION['passadd']
	&& isset($_POST['addmodify'])) {
	if(strip_tags(trim($_POST['addmodify'])) == 'modify') { //Modify VirtualHost
		//Modify VirtualHost - add FCGI mode - change FCGI PHP version - Suppress FCGI mode
		$VhostModified = false;
		$vh_name = strip_tags(trim($_POST['vh_name']));
		$vh_fcgi_on = false;
		$vh_fcgi_php = '';
		if(isset($_POST['vh_fcgi_on']) && strip_tags(trim($_POST['vh_fcgi_on'])) == 'on') {
			$vh_fcgi_on = true;
			$vh_fcgi_php = strip_tags(trim($_POST['vh_fcgi_php']));
		}
		$VhostToMod = json_decode($_POST['modifycontent'],true);
		//error_log("vh_name=".$vh_name." - fcgi=".$vh_fcgi_php);
		//error_log("VhostToMod=\n".print_r($VhostToMod,true));
		if(!empty($vh_fcgi_php)) {
			//Asked for FCGI mode
			if(!empty($VhostToMod['fcgi'])) {
				//Is alredy FCGI
				if($VhostToMod['fcgi'] != $vh_fcgi_php) {
					//Change FCGI PHP version
					//Get line Define FCGIPHPVERSION "x.y.z"
					if(preg_match('~^\s*Define\s+FCGIPHPVERSION\s+"([0-9.]+)"\r?$~mi',$VhostToMod['original'],$matches) === 1){
						if($matches[1] == $VhostToMod['fcgi']) {
							$count = $counts = 0;
							$VhostToMod['newphp'] = str_replace($VhostToMod['fcgi'],$vh_fcgi_php,$matches[0],$count);
							$counts += $count;
							$VhostToMod['new'] = str_replace($matches[0],$VhostToMod['newphp'],$VhostToMod['original'],$count);
							$counts += $count;
							if($counts == 2) {
								$VhostToMod['new'] = clean_string_var($VhostToMod['new']);
								$VhostModified = true;
							}
						}
					}
				}
			}
			else {
				//Is not FCGI - Mode FCGI to add
				if(preg_match('~^\s*\</VirtualHost\>\r?$~mi',$VhostToMod['original'],$matches) === 1) {
					$httpd_vhosts_fcgi = <<< EOFFCGIPHP
  <IfModule fcgid_module>
    Define FCGIPHPVERSION "${vh_fcgi_php}"
    FcgidInitialEnv PHPRC \${PHPROOT}\${FCGIPHPVERSION}
    <Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "\${PHPROOT}\${FCGIPHPVERSION}/php-cgi.exe" .php
    </Files>
  </IfModule>

EOFFCGIPHP;
					$VhostToMod['new'] = str_replace($matches[0],$httpd_vhosts_fcgi.$matches[0],$VhostToMod['original'],$count);
					if($count > 0) {
						$VhostToMod['new'] = clean_string_var($VhostToMod['new']);
						$VhostModified = true;
					}
				}
			}
		}
		else {
			//FCGI mode not wanted
			if(!empty($VhostToMod['fcgi'])) {
				//To suppress FCGI mode
				if(preg_match('~^[ \t]*\<IfModule fcgid_module\>.*\</IfModule\>\r?$~ism',$VhostToMod['original'],$matches) === 1) {
					$VhostToMod['new'] = str_replace($matches[0],'',$VhostToMod['original'],$count);
					if($count > 0 ) {
						$VhostToMod['new'] = clean_string_var($VhostToMod['new']);
						$VhostModified = true;
					}
				}
			}
		}
		if($VhostModified) {
			$httpVhostsContents = file_get_contents($c_apacheVhostConfFile);
			$httpVhostsContents = str_replace($VhostToMod['original'],$VhostToMod['new'],$httpVhostsContents,$count);
			if($count > 0) {
				if(write_file($c_apacheVhostConfFile,$httpVhostsContents)) {
					$message_ok = '<p class="ok">'.sprintf($langues['VirtualCreated'],$VhostToMod['name']).'</p>';
					$message_ok .= '<p class="ok_plus">'.$langues['However'].'</p>';
					$vhost_created = true;
				}
			}
		}
		else {
			$error = true;
			$message[] = '<p class="warning">'.$langues['NoModify'].'</p>';
		}
	}//End Vhost modify procedure
	elseif(strip_tags(trim($_POST['addmodify'])) == 'modifyalias') { // Modify Alias
		$AliasModified = false;
		$vh_name = strip_tags(trim($_POST['vh_name']));
		$vh_fcgi_on = false;
		$vh_fcgi_php = '';
		if(isset($_POST['vh_fcgi_on']) && strip_tags(trim($_POST['vh_fcgi_on'])) == 'on') {
			$vh_fcgi_on = true;
			$vh_fcgi_php = strip_tags(trim($_POST['vh_fcgi_php']));
		}
		$AliasToMod = json_decode($_POST['modifycontent'],true);
		if(!empty($vh_fcgi_php)) {
			//Asked for FCGI mode
			if(!empty($AliasToMod['fcgi'])) {
				//Is alredy FCGI
				if($AliasToMod['fcgi'] != $vh_fcgi_php) {
					//Change FCGI PHP version
					//Get line Define FCGIPHPVERSION "x.y.z"
					if(preg_match('~^[ \t]*Define FCGIPHPVERSION "([0-9.]+)"\r$~mi',$AliasToMod['original'],$matches) === 1){
						if($matches[1] == $AliasToMod['fcgi']) {
							$count = $counts = 0;
							$AliasToMod['newphp'] = str_replace($AliasToMod['fcgi'],$vh_fcgi_php,$matches[0],$count);
							$counts += $count;
							$AliasToMod['new'] = str_replace($matches[0],$AliasToMod['newphp'],$AliasToMod['original'],$count);
							$counts += $count;
							if($counts == 2) {
								$AliasToMod['new'] = clean_string_var($AliasToMod['new']);
								$AliasModified = true;
							}
						}
					}

				}//End change FCGI PHP version
			}
			else {
				//Is not FCGI - Mode FCGI to add
				$firstPart = <<< EOF
<IfModule fcgid_module>
  Define FCGIPHPVERSION "{$vh_fcgi_php}"
  FcgidInitialEnv PHPRC \${PHPROOT}\${FCGIPHPVERSION}
</IfModule>

EOF;
				$secondPart = <<< EOF
  <IfModule fcgid_module>
    <Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "\${PHPROOT}\${FCGIPHPVERSION}/php-cgi.exe" .php
    </Files>
  </IfModule>

EOF;
			$counts = '0';
			$pattern = array('~^[ \t]*\<Directory.*"(.*)">.*\r?$~mi','~^[ \t]*\</Directory\>.*\r?$~mi');
			$replace = array($firstPart.'${0}',$secondPart.'${0}');
			$AliasToMod['new'] = preg_replace($pattern,$replace,$AliasToMod['original'],-1,$count);
			if(!is_null($AliasToMod['new']) && $count == 2) $AliasModified = true;
			}//End add FCGI mode
		}
		else {
			//FCGI mode not wanted
			if(!empty($AliasToMod['fcgi'])) {
				//To suppress FCGI mode
				$count = 0;
				//Search for first <IfModule...
				$p_value = preg_quote($AliasToMod['fcgi']);
				$mask = "{
				\<IfModule\s+fcgid_module            # beginning
				[^<]*(?:<(?!/IfModule)[^<]*)*        # avoid premature end
				\n\s*Define.*${p_value}\s*\n         # Test Define
				.*?                                  # we stop as soon as possible
				\</IfModule\>\s*\n                   # end
				}isx";
				if(preg_match($mask,$AliasToMod['original'],$matches) === 1) {
					//error_log("matches1=\n".print_r($matches,true));
					$AliasToMod['new'] = str_ireplace($matches[0],'',$AliasToMod['original'],$count);
					if($count > 0) {
						$count =0;
						//Search for second <IfModule...
						$mask = "~^[\t ]*\<IfModule\s+fcgid_module.*FcgidWrapper.*\</IfModule\>\r?$~mis";
						if(preg_match($mask,$AliasToMod['new'],$matches) === 1) {
							//error_log("matches2=\n".print_r($matches,true));
							$AliasToMod['new'] = str_ireplace($matches[0],'',$AliasToMod['new'],$count);
							if($count > 0) $AliasModified = true;
						}
					}
				}
			}//End of suppress FCGI
		}
		if($AliasModified) {
			//Clean new Alias and write it
			$AliasToMod['new'] = clean_file_contents($AliasToMod['new']."\r\n#\r\n",array(1,0),false, true);
			if(write_file($AliasToMod['path'],$AliasToMod['new'])) {
				$message_ok = '<p class="ok">'.sprintf($langues['ModifiedAlias'],$AliasToMod['name']).'</p>';
				$message_ok .= '<p class="ok_plus">'.$langues['HoweverAlias'].'</p>';
				$alias_created = true;
			}
		}
		else {
			$errors = true;
			$message[] = '<p class="warning">'.$langues['NoModifyAlias'].'</p>';
		}
	}//End Alias modify procedure
	elseif(strip_tags(trim($_POST['addmodify'])) == 'add') { //Add VirtualHost
		//***** Create Vhost submitted
		// Escape any backslashes used in the path to the file
		//$c_apacheVhostConfFile = str_replace('\\', '\\\\', $c_apacheVhostConfFile);
		$vh_name = strip_tags(trim($_POST['vh_name']));
		$vh_ip = (isset($_POST['vh_ip'])) ? strip_tags(trim($_POST['vh_ip'])) : '';
		$vh_port = '';
		if(isset($_POST['vh_port_on']) && strip_tags(trim($_POST['vh_port_on'])) == 'on') {
			$vh_port = strip_tags(trim($_POST['vh_port']));
		}
		$vh_fcgi_on = false;
		$vh_fcgi_php = '';
		if(isset($_POST['vh_fcgi_on']) && strip_tags(trim($_POST['vh_fcgi_on'])) == 'on') {
			$vh_fcgi_on = true;
			$vh_fcgi_php = strip_tags(trim($_POST['vh_fcgi_php']));
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
		$regexIDNA = '#^([\w-]+://?|www[\.])?xn--[a-z0-9]+[a-z0-9\-\.]*[a-z0-9]+(\.[a-z]{2,7})?$#';
		// Not IDNA  /^[A-Za-z]+([-.](?![-.])|[A-Za-z0-9]){1,60}[A-Za-z0-9]$/
		$regexServerName = 	'/^
			(?=.*[A-Za-z]) # at least one letter somewhere
			[A-Za-z0-9]+ 	 # letter or number in first place
			([-.](?![-.])	 #  a . or - not followed by . or -
					|					 #   or
			[A-Za-z0-9]		 #  a letter or a number
			){0,60}				 # this, repeated from 0 to 60 times - at least two characters
			[A-Za-z0-9]		 # letter or number at the end
			$/x';
		if(preg_match($regexIDNA,$vh_name,$matchesIDNA) == 0
			&& preg_match($regexServerName,$vh_name) == 0) {
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
			elseif(check_IP_local($vh_ip) === false) {
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
			$httpd_vhosts_fcgi = '';
			if($vh_fcgi_on) {
				$httpd_vhosts_fcgi = <<< EOFFCGIPHP

  <IfModule fcgid_module>
    Define FCGIPHPVERSION "${vh_fcgi_php}"
    FcgidInitialEnv PHPRC \${PHPROOT}\${FCGIPHPVERSION}
    <Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "\${PHPROOT}\${FCGIPHPVERSION}/php-cgi.exe" .php
    </Files>
  </IfModule>
EOFFCGIPHP;
			}
			$httpd_vhosts_add = <<< EOFNEWVHOST

#
<VirtualHost {$c_UsedIp}:{$c_PortToUse}>
	ServerName {$vh_name}
	DocumentRoot "{$vh_folder}"
	<Directory  "{$vh_folder}/">
		Options +Indexes +Includes +FollowSymLinks +MultiViews
		AllowOverride All
		Require local
	</Directory>{$httpd_vhosts_fcgi}
</VirtualHost>
#

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
			//Clean httpd-vhosts.conf file
			$myVhostsContents = file_get_contents($c_apacheVhostConfFile);
			$myVhostsContents = clean_file_contents($myVhostsContents."\r\n#\r\n",array(1,0),false, true, true, $c_apacheVhostConfFile);

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
				$errors = true;
				$message[] = '<p class="warning">'.$langues['NoModify'].'</p>';
			}
		}
	}//End VirtulHost create procedure
}//End forms modify and create

//***** FCGI select PHP version form *****
//*****  Modal Dialog FCGI mode help *****
$w_FcgiPhpVersionForm = $ModalDialogs = $ModalFCGILink = '';
	//**** Modal Dialogs *****
	//See mode FCGI
	$message = str_replace('  ','&nbsp;&nbsp;',$langues['fcgi_mode_help']);
	$message = str_replace('<code>',"<code class='normal'>",$message);
	$message = nl2br($message);
	//Dialog modal help FCGI
	$divHelpFCGI = <<< EOF
<div id="helpfcgi" class="modalOtoArial">
	<div>
		<div class='modalOtoBar'><input type='button' value='Copy' class='js-copyb' data-target='#tocopyb'>
			<a href="#closeOto" title="Close" class="closeOto">X</a>
		</div>
		<div id="tocopyb">{$message}</div>
	</div>
</div>
EOF;
	$ModalFCGILink .= "<a href='#helpfcgi'><small style='color:#777;'>".$langues['fcgi_mode_link']."</small></a>";
	$ModalDialogs .= $divHelpFCGI;
	//***** End of Modal Dialogs *****
$StyleNoFcgi = $FcgiModNotLoaded = '';
if(!isset($c_ApacheDefine['PHPROOT'])) {
	$StyleNoFcgi = " style='opacity:0.6;pointer-events:none;'";
	$FcgiModNotLoaded = <<< EOF
<label><span style='color:red;'>[FCGI]&nbsp;{$langues['fcgi_not_loaded']}</span>&nbsp;-&nbsp;{$ModalFCGILink}</label><br>
EOF;
}
$checked = (!empty($VhostToMod['fcgi']) || !empty($AliasToMod['fcgi'])) ? " checked='checked'" : '';
$w_FcgiPhpVersionForm = <<< EOF
<input class='portfcgi' type='checkbox' name='vh_fcgi_on' value='on' {$checked}{$StyleNoFcgi}>
<label{$StyleNoFcgi}>{$langues['VirtualHostPhpFCGI']}<code class="option"><i>{$langues['Optional']}</i></code> {$ModalFCGILink}</label><br>
<select class='fcgiport' name='vh_fcgi_php'{$StyleNoFcgi}>
EOF;
foreach($phpVersionList as $php_FCGI) {
	$selected = ((!empty($VhostToMod['fcgi']) && ($VhostToMod['fcgi'] == $php_FCGI))
						|| (!empty($AliasToMod['fcgi']) && ($AliasToMod['fcgi'] == $php_FCGI))) ? " selected" : '';
 	$w_FcgiPhpVersionForm .= "<option style='line_height:0.8em;' value='".$php_FCGI."' ".$selected.">PHP&nbsp;:&nbsp;".$php_FCGI."&nbsp;&nbsp;</option>";
}
$w_FcgiPhpVersionForm .= "</select><br>";
$w_FcgiPhpVersionForm .= $FcgiModNotLoaded;
//***** End FCGI select PHP form

//***** Show VirtualHost list *****
$VhostDefine = $VhostDelete = $VhostModify = "";
if($virtualHost['nb_Server'] > 0) {
	$i = 0;
	foreach($virtualHost['ServerName'] as $value) {
		$ip ='';
		if(!empty($virtualHost['virtual_ip'][$i]))
			$ip = " - VirtualHost ip = <span style='color:blue;'>".$virtualHost['virtual_ip'][$i].'</span>';
		$UrlPortVH = ($virtualHost['ServerNamePort'][$value] != '80') ? " - Port = <span style='color:red;'>".$virtualHost['ServerNamePort'][$value]."</span>" : "";
		$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
		$value_aff = $value_fcgi = '';
		$new_line = false;
		if($virtualHost['ServerNameIDNA'][$value] === true) {
			$value_aff .= "<br>";
			$new_line = true;
			$value_aff .= "<span class='greenpad'><small>IDNA-> ".$virtualHost['ServerNameUTF8'][$value].'</small></span>';
			$nbVhostLines++;
		}
		if(isset($c_ApacheDefine['PHPROOT']) && $virtualHost['ServerNameFcgid'][$value] === true){
			if(!$new_line) {
				$value_aff .= "<br>";
				$nbVhostLines++;
			}
			$value_aff .= "<span class='greenpad'><small>FCGI -> PHP ".$virtualHost['ServerNameFcgidPHP'][$value].'</small></span>';
			$value_fcgi .= "<br><span class='greenpad'><small>FCGI -> PHP ".$virtualHost['ServerNameFcgidPHP'][$value].'</small></span>';
		}
		if($virtualHost['ServerNameFcgid'][$value] === true && $virtualHost['ServerNameFcgidPHPOK'][$value] !== true) {
			if(!$new_line) $value_aff .= "<br>";
			$value_aff .= "<span style='color:red;padding-left:1em;'><small>FCGI -> PHP ".$virtualHost['ServerNameFcgidPHP'][$value].' - '.$langues['phpNotExists'].'</small></span>';
		}
		if($virtualHost['ServerNameValid'][$value] === false)
			$VhostDefine .= "<li><i>ServerName : </i><span style='color:red;'>".$value." - ServerName syntax error</span></li>\n";
		else {
			$VhostDefine .= "<li><i>ServerName : </i><span style='color:blue;'>".$value.$value_aff."</span> - <i>Directory : </i>".$virtualHost['documentPath'][$i].$UrlPortVH.$ip."</li>\n";
			$nbVhostLines++;
			}
		if($value != 'localhost') {
			$VhostDelete .= "<li><i>ServerName : </i><input type='checkbox' name='virtual_del[]' value='".$value."'/> <span style='color:blue;'>".$value."</span></li>\n";
			$VhostModify .= "<li><i>ServerName : </i><input type='radio' name='virtual_modify' value='".$value."'/> <span style='color:blue;'>".$value.$value_fcgi."</span></li>\n";
		}
		$i++;
	}
}
//***** End Show VirtualHost list *****

//***** Is Include conf/extra/httpd-vhosts.conf not commented # *****
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
//***** Does conf/extra/httpd-vhosts.conf file exist ?
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
//***** Is conf/extra/httpd-vhosts.conf file clean ?
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
//***** Does VirtualHost localhost exist ?
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

// To scroll Alias and VirtualHost list display
if($wampConf['ScrollListsHomePage'] == 'on') {
	foreach($Scroll_List as $value) {
		if($value['scroll'] && ${$value['nbname']} > $value['lines']) {
			${$value['name']} = " style='height:18rem;overflow-y:scroll;padding-right:5px;'";
		}
	}
}

$pageContents = <<< EOPAGE
<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>${langues['addVirtual']}</title>
		<meta charset="UTF-8">
	  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  	<meta name="viewport" content="width=device-width">
  	<link id="stylecall" rel="stylesheet" href="wampthemes/classic/style.css" />
  	<link id="stylecall" rel="stylesheet" href="wampthemes/popupmodal.css" />
		<link rel="shortcut icon" href="favicon.ico" type="image/ico" />
		<style>
			#divleft {
				width:69%;float:left;border-right:1px solid black;
			}
			#divright {
				width:30%;float:right;
			}
			p {padding: 1%;}
			.ok, .ok_plus, .warning, .warning_auto {
				text-align: center;
				font-size: 1.3em;
				text-shadow: 1px 1px 0 #000;
				background: #585858;
			}
			.ok {color: limegreen;}
			.ok_plus {
				text-align:justify;
				background: #777777;
			}
			.warning, .warning_auto, .ok_plus {color: orange;}
			.warning_auto {border: 3px solid #4FEF10;}
			form.lineblock {
				display:inline-block;
			}
			label {
				margin-left: 10px;
			}
			input.portfcgi {
				width:20px;
				height:20px;
				margin:2px 0 5px 10px;
			}
			input[type="text"] {
				width: 45%;
				margin: 0.2% 1% 1% 1%;
				padding: 0.3% 1%;
				border: 1px solid #999;
			}
			input.required {border:1px solid red;}
			input.optional {border:1px solid green;}
			input[type="submit"] {
				background: #ddd;
				border: 1px solid #999;
				margin: 0.2% 1% 1% 1%;;
				padding: 0.3% 1%;
			}
			input[type="checkbox"] {vertical-align: middle;}
			input[type="submit"]:hover {
				background: #FF0099;
				color: #FFF;
			}
			input.modify {
				width:280px;
			}
			select.fcgiport {
				margin:1px 0 5px 45px;
			}

			pre {
				width: 98%;
				overflow: auto;
				padding: 1%;
				border: #FF0099 1px solid;
				background: #585858;
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
			code.rien,code.normal {
				background:#eee;
				text-shadow:unset;
				border-radius:0;
			}
			code.normal {
				background:#fff;
				color:#000;
			}
			span.greenpad {
				color:green;
				padding-left:1em;
			}
			ul.nostyle {
				list-style:none;
			}
		</style>
	</head>
	<body>
  <div id="head">
    <div class="innerhead">
	    <h1><abbr title="Windows">W</abbr><abbr title="Apache">a</abbr><abbr title="MySQL/MariaDB">m</abbr><abbr title="PHP">p</abbr><abbr title="server WEB local">server</abbr></h1>
		   <ul>
			   <li>Apache 2.4</li><li>-</li><li>MySQL 5 &amp; 8</li><li>-</li><li>MariaDB 10</li><li>-</li><li>PHP 5, 7 &amp; 8</li>
		   </ul>
    </div>
		<ul class="utility">
		  <li>Version ${c_wampVersion} - ${c_wampMode}</li>
      <li>${langueswitcher}</li>
	  </ul>
	</div>
	<ul class='vhost' style='text-align:center;'><li><a href="add_vhost.php?lang={$langue}">{$langues['addVirtual']}</a> - <a href="index.php?lang={$langue}">{$langues['backHome']}</a></li></ul>

EOPAGE;

if($vhost_created || $alias_created)
	$pageContents .= $message_ok;
else {
	if($errors) {
		foreach($message as $value)
		 	$pageContents .= $value;
	}
	if($sub_menu_on) {
	$pageContents .= <<< EOPAGEB
		<p>Apache Virtual Hosts <code>{$c_apacheVhostConfFile}</code></p>
EOPAGEB;
	if(!empty($VhostDefine)) {
	$pageContents .= <<< EOPAGEB
	<div id='divleft'>
	<p><b><u>{$langues['VirtualHostExists']}</u></b></p>
	<ul class='nostyle' {$VhostsListScroller}>{$VhostDefine}</ul>
	<p>Windows hosts <code>{$c_hostsFile}</code></p>\n
EOPAGEB;
	$pageContents .= "<div{$styleOpaNoClick}>\n";
	$pageContents .= "<form method='post'>\n";
	if($errors_auto) {
	$pageContents .= <<< EOPAGEB
	<p><label>{$langues['GreenErrors']}</label></p>\n
	<p style="text-align: right;"><input type="submit" name="correct" value="{$langues['Correct']}" /></p>\n
EOPAGEB;
	}
	else {
	$_SESSION['passadd'] = mt_rand(100000001,mt_getrandmax());
	//Check if VirtualHost to modify
	$styleOpaNoClickMod = ($VhostToModify || $AliasToModify) ? " style='opacity:0.75;pointer-events:none;'" : '';
	$styleNoDisplay = ($VhostToModify || $AliasToModify) ? " style='display:none;pointer-events:none;'" : '';
	$valueHostName = '';
	$valueHostDir = '';
	$valueModify = 'add';
	$valueStart = $langues['Start'];
	$VirtualHostName = $langues['VirtualHostName'];
	$Required = $langues['Required'];
	$Directory = $langues['VirtualHostFolder'];
	$CodeClass = " class='requis'";
	if($VhostToModify) {
		$valueHostName = ' value="'.$VhostToMod['name'].'"';
		$valueHostDir = ' value="'.$VhostToMod['directory'].'"';
		$valueModify = 'modify';
		$VirtualHostName = 'ServerName';
		$Required = '';
		$Directory = 'Directory';
		$CodeClass = " class='rien'";
	}
	elseif($AliasToModify) {
		$valueStart = $langues['StartAlias'];
		$valueHostName = ' value="'.$AliasToMod['name'].'"';
		$valueHostDir = ' value="'.$AliasToMod['directory'].'"';
		$valueModify = 'modifyalias';
		$VirtualHostName = 'Alias';
		$Required = '';
		$Directory = 'Directory';
		$CodeClass = " class='rien'";
	}

	$pageContents .= <<< EOPAGEB
		<div{$styleOpaNoClickMod}>
		<label>{$VirtualHostName}<code {$CodeClass}><i>{$Required}</i></code></label><br>\n
			<input class='required' type="text" name="vh_name" required {$valueHostName}/><br>\n
		<label>{$Directory}<code {$CodeClass}><i>{$Required}</i></code></label><br>\n
			<input class='required' type="text" name="vh_folder" required {$valueHostDir}/><br>\n
		</div>
		{$w_FcgiPhpVersionForm}\n
		<div{$styleNoDisplay}>
			{$w_VirtualPortForm}\n
			{$w_VirtualIPForm}\n
		</div>
		<div>
			<input type='hidden' name='addmodify' value='{$valueModify}' />
			<input type='hidden' name='modifycontent' value='{$serialModify}' />
			<input type='hidden' name='checkadd' value='{$_SESSION['passadd']}' />
			<input type="submit" name="submit" value="{$valueStart}" />\n
		</div>

EOPAGEB;
	}
	}// End if !empty VhostDefine
	$pageContents .= "</form>\n";
	$pageContents .= "</div>\n";

	$pageContents .= <<< EOPAGEB
		</div>
		<div id='divright'>
		  <div${aliasDisplay}>
			<p><b><u>Alias</u></b></p>
			<ul class='nostyle' {$AliasListScroller}>${aliasContents}</ul>
		  </div>
		  <div id='vhostdelete' style='margin-top:2em;'>
EOPAGEB;
		if(!empty($VhostDelete) && $wampConf['NotCheckDuplicate'] == "off" && $wampConf['NotCheckVirtualHost'] == 'off') {
			if($seeVhostDelete) {
			$_SESSION['passdel'] = mt_rand(100000001,mt_getrandmax());
			$pageContents .= <<< EOPAGEB
			<form id='deletevhost' method='post'>
				<ul class='nostyle' {$VhostsListScroller}>{$VhostDelete}</ul>
				<input type='hidden' name='checkdelete' value='{$_SESSION['passdel']}' />
				<input type='submit' name='vhostdelete' value='{$langues['suppVhost']}' />
			</form>
EOPAGEB;
			}
			elseif(!$seeVhostModify  && !$seeAliasModify) {
			$pageContents .= <<< EOPAGEB
			<form id='seedelete' method='post' class='lineblock'>
			<input type='hidden' name='seedelete' value='afficher'/>
			<input class='modify' type='submit' value='{$langues['suppForm']}'/>
			</form>
EOPAGEB;
			}
		}
		$pageContents .= <<< EOPAGEB
			</div>
			<div id='vhostmodify' style='margin-top:0.5em;'>
EOPAGEB;
		if(!empty($VhostModify) && $wampConf['NotCheckDuplicate'] == "off" && $wampConf['NotCheckVirtualHost'] == 'off') {
			if($seeVhostModify) {
			$_SESSION['passmodify'] = mt_rand(100000001,mt_getrandmax());
			$pageContents .= <<< EOPAGEB
			<form id='modifyvhost' method='post'>\n
				<ul class='nostyle' {$VhostsListScroller}>{$VhostModify}</ul>\n
				<input type='hidden' name='checkmodify' value='{$_SESSION['passmodify']}' />\n
				<input type='submit' name='vhostmodify' value='{$langues['modifyVhost']}' />\n
			</form>\n
EOPAGEB;
			}
			elseif(!$seeVhostDelete && !$seeAliasModify) {
			$pageContents .= <<< EOPAGEB
			<form id='seemodify' method='post' class='lineblock'>\n
			<input type='hidden' name='seemodify' value='afficher'/>\n
			<input class='modify' type='submit' value='{$langues['modifyForm']}'/>\n
			</form>\n
EOPAGEB;
			}
		}
	} //End if sub_menu_on
	$pageContents .= <<< EOPAGEB
		</div>
			<div id='aliasmodify' style='margin-top:0.5em;'>
EOPAGEB;
		if(!empty($AliasModify)) {
			if($seeAliasModify) {
			$_SESSION['aliaspassmodify'] = mt_rand(100000001,mt_getrandmax());
			$pageContents .= <<< EOPAGEB
			<form id='modifyalias' method='post'>\n
				<ul class='nostyle' {$AliasListScroller}>{$AliasModify}</ul>\n
				<input type='hidden' name='aliascheckmodify' value='{$_SESSION['aliaspassmodify']}' />\n
				<input type='submit' name='aliasmodify' value='{$langues['modifyAlias']}' />\n
			</form>\n
EOPAGEB;
			}
			elseif(!$seeVhostDelete && !$seeVhostModify) {
			$pageContents .= <<< EOPAGEB
			<form id='seealiasmod' method='post' class='lineblock'>\n
			<input type='hidden' name='seealiasmod' value='afficher'/>\n
			<input class='modify' type='submit' value="{$langues['modAliasForm']}"/>\n
			</form>\n
EOPAGEB;
			}
		}
	$pageContents .= <<< EOPAGEB
		</div>
		</div> <!-- End div 30% right -->
		<div style='clear:both;'></div>\n
EOPAGEB;
}
$pageContents .= $ModalDialogs;
include 'wampthemes/copy_modal.php';
$pageContents .= "</body>\n</html>\n";

echo $pageContents;

?>