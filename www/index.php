<?php
// 3.2.9 - Alias view modified (PhpMyAdmin not compat)

// Page created by Shepard [Fabian Pijcke] <Shepard8@laposte.net>
// Arno Esterhuizen <arno.esterhuizen@gmail.com>
// and Romain Bourdon <rromain@romainbourdon.com>
// and Hervé Leclerc <herve.leclerc@alterway.fr>
// Icons by Mark James <http://www.famfamfam.com/lab/icons/silk/>
// Version 2.5 -> 3.2.9 by Dominique Ottello alias Otomatic

$server_dir = "../";

require $server_dir.'scripts/config.inc.php';
require $server_dir.'scripts/wampserver.lib.php';

//**** Scroll lists parameters ****
//** Based on an idea by Panagiotis E. Papazoglou
//General scrolling (on or off) is controlled by Wampserver parameter 'ScrollListsHomePage'
// via Right-Click -> Wamp Settings -> Allow scrolling of lists on home page
//To allow or not the individual scrolling of the lists Projects, Alias and VirtualHost
//   'scroll' true or false to do the scroll or not
//   'lines'  minimum number of lines to do the scroll
// Do not change anything other than the values assigned to 'scroll' and 'lines'
$Scroll_List = array(
	'projects' => array('scroll' => true,'lines' => 16,'name' => 'ProjectsListScroller','nbname' => 'nbProjectsLines'),
	'alias'    => array('scroll' => true,'lines' => 16,'name' => 'AliasListScroller',   'nbname' => 'nbAliasLines'),
	'vhosts'   => array('scroll' => true,'lines' => 16,'name' => 'VhostsListScroller',  'nbname' => 'nbVirtualHostLines'),
);
foreach($Scroll_List as $key => $value) {
	${$value['name']} = '';
	${$value['nbname']} = 0;
}
$nbAlias = $nbVirtualHost = $nbProjects = 0;

//path to alias files
$aliasDir = $server_dir.'alias/';

//Works if you have ServerSignature On and ServerTokens Full in httpd.conf
$server_software = $_SERVER['SERVER_SOFTWARE'];
$error_content = '';

// we get the versions of the applications
$phpVersion = $wampConf['phpVersion'];
$apacheVersion = $wampConf['apacheVersion'];
$doca_version = 'doca'.substr($apacheVersion,0,3);
$mysqlVersion = $wampConf['mysqlVersion'];
// All php versions
$phpVersionList = listDir($c_phpVersionDir,'checkPhpConf','php',true);
$PhpAllVersions = implode(' - ',$phpVersionList);

//We get the value of VirtualHostMenu
$VirtualHostMenu = $wampConf['VirtualHostSubMenu'];

//we get the value of apachePortUsed
$port = $wampConf['apachePortUsed'];
$UrlPort = $port !== "80" ? ":".$port : '';
//We get the value(s) of the listening ports in Apache
$ListenPorts = implode(' - ',listen_ports($c_apacheConfFile));
//We get the value of mysqlPortUsed
$Mysqlport = $wampConf['mysqlPortUsed'];

//Directories to ignore in projects
$projectsListIgnore = array ('.','..','wampthemes','wamplangues');

//Search for available themes
$styleswitcher = '<select id="themes">'."\n";
$themes = glob('wampthemes/*', GLOB_ONLYDIR);
foreach ($themes as $theme) {
    if(file_exists($theme.'/style.css')) {
        $theme = str_replace('wampthemes/', '', $theme);
        $styleswitcher .= '<option id="'.$theme.'">'.$theme.'</option>'."\n";
    }
}
$styleswitcher .= '</select>'."\n";

//Displaying phpinfo
if(isset($_GET['phpinfo'])) {
	$type_info = intval(trim($_GET['phpinfo']));
	if($type_info < -1 || $type_info > 64)
		$type_info = -1;
	phpinfo($type_info);
	exit();
}

//Displaying xdebug_info();
$xdebug_info = '';
if(function_exists('xdebug_info')) {
	if(isset($_GET['xdebuginfo'])) {
		xdebug_info();
		exit();
	}
	$xdebug_info = '<li><a href="?xdebuginfo">xdebug_info()</a></li>';
}

// Language
$langue = $wampConf['language'];
$i_langues = glob('wamplangues/index_*.php');
$languages = array();
foreach($i_langues as $value) {
  $languages[] = str_replace(array('wamplangues/index_','.php'), '', $value);
}
$langueget = (!empty($_GET['lang']) ? strip_tags(trim($_GET['lang'])) : '');
if(in_array($langueget,$languages))
	$langue = $langueget;

// Search for available languages
$langueswitcher = '<form method="get" style="display:inline-block;"><select name="lang" id="langues" onchange="this.form.submit();">'."\n";
$selected = false;
foreach($languages as $i_langue) {
  $langueswitcher .= '<option value="'.$i_langue.'"';
  if(!$selected && $langue == $i_langue) {
  	$langueswitcher .= ' selected ';
  	$selected = true;
  }
  $langueswitcher .= '>'.$i_langue.'</option>'."\n";
}
$langueswitcher .= '</select></form>';

include 'wamplangues/index_english.php';
if(file_exists('wamplangues/index_'.$langue.'.php')) {
	$langue_temp = $langues;
	include 'wamplangues/index_'.$langue.'.php';
	$langues = array_merge($langue_temp, $langues);
}
include 'wamplangues/help_english.php';
if(file_exists('wamplangues/help_'.$langue.'.php')) {
	$langue_temp = $langues;
	include 'wamplangues/help_'.$langue.'.php';
	$langues = array_merge($langue_temp, $langues);
}

$PhpAllVersionsNotFcgi = '';
if(!isset($c_ApacheDefine['PHPROOT'])) {
	$PhpAllVersionsNotFcgi = <<< EOF
		<dt>&nbsp;</dt>
		   <dd><small style='color:red;'>[FCGI]&nbsp;{$langues['fcgi_not_loaded']}</small></dd>
EOF;
}

// MySQL retrieval if supported
$nbDBMS = 0;
$MySQLdb = '';
if(isset($wampConf['SupportMySQL']) && $wampConf['SupportMySQL'] =='on') {
	$nbDBMS++;
	$defaultDBMSMySQL = ($wampConf['mysqlPortUsed'] == '3306') ? "&nbsp;-&nbsp;".$langues['defaultDBMS'] : "";
	$MySQLdb = <<< EOF
<dt>{$langues['versm']}</dt>
	<dd>${mysqlVersion}&nbsp;-&nbsp;{$langues['mysqlportUsed']}{$Mysqlport}{$defaultDBMSMySQL}&nbsp;-&nbsp; <a href='http://{$langues['docm']}'>{$langues['documentation-of']} MySQL</a></dd>
EOF;
}

// MariaDB retrieval if supported
$MariaDB = '';
if(isset($wampConf['SupportMariaDB']) && $wampConf['SupportMariaDB'] =='on') {
	$nbDBMS++;
	$defaultDBMSMaria = ($wampConf['mariaPortUsed'] == '3306') ? "&nbsp;-&nbsp;".$langues['defaultDBMS'] : "";
	$MariaDB = <<< EOF
<dt>{$langues['versmaria']}</dt>
  <dd>${c_mariadbVersion}&nbsp;-&nbsp;{$langues['mariaportUsed']}{$wampConf['mariaPortUsed']}{$defaultDBMSMaria}&nbsp;-&nbsp; <a href='http://{$langues['docmaria']}'>{$langues['documentation-of']} MariaDB</a></dd>
EOF;
}

//**** Modal Dialogs *****
//Get PHP loaded extensions
$message['phpLoadedExtensions'] = color('clean',GetPhpLoadedExtensions($c_phpVersion,6));
//Dialog modal PHP extensions
$divPhpExt = <<< EOF
<div id="phpextloaded" class="modalOto">
	<div>
		<div class='modalOtoBar'><input type='button' value='Copy' class='js-copy' data-target='#tocopy'>
			<a href="#closeOto" title="Close" class="closeOto">X</a>
		</div>
		<div id="tocopy">{$message['phpLoadedExtensions']}</div>
	</div>
</div>
EOF;
$popupPHPExtLink = "<a href='#phpextloaded'><small style='color:#777;'>".$langues['phpExtensions']."</small></a>";
$ModalDialogs = $divPhpExt;
//Get PHP versions usage
$message['phpVersionsUsage'] = GetPhpVersionsUsage();
//Dialog modal PHP versions usage
$divPhpUse = <<< EOF
<div id="phpversionsuse" class="modalOto">
	<div>
		<div class='modalOtoBar'><input type='button' value='Copy' class='js-copya' data-target='#tocopya'>
			<a href="#closeOto" title="Close" class="closeOto">X</a>
		</div>
		<div id="tocopya">{$message['phpVersionsUsage']}</div>
	</div>
</div>
EOF;
$popupPHPExtLink .= "&nbsp;-&nbsp;<a href='#phpversionsuse'><small style='color:#777;'>".$langues['phpVersionsUse']."</small></a>";
$ModalDialogs .= $divPhpUse;
//PHP FCGI help
$message = str_replace('  ','&nbsp;&nbsp;',$langues['fcgi_mode_help']);
$message = nl2br($message);
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
$ModalDialogs .= $divHelpFCGI;
$PhpAllVersions .= "&nbsp;-&nbsp;<a href='#helpfcgi'><small style='color:#777;'>".$langues['fcgi_mode_link']."</small></a>";
//Dialog modal MySQL - MariaDB
$popupMySQLMariaDBLink = '';
if($nbDBMS > 1) {
	$divMySQLMariaDB = <<< EOF
<div id="mysqlmariadb" class="modalOto">
	<div>
		<div class='modalOtoBar'><input type='button' value='' class='js-copyc' data-target='none'>
			<a href="#closeOto" title="Close" class="closeOto">X</a>
		</div>
		{$langues['HelpMySQLMariaDB']}
	</div>
</div>
EOF;
	$popupMySQLMariaDBLink = "&nbsp;-&nbsp;<a href='#mysqlmariadb'><small style='color:#777;'>MySQL - MariaDB</small></a>";
	$ModalDialogs .= $divMySQLMariaDB;
}
//**** End of Modal Dialogs *****

//Default DBMS in first position
if(empty($defaultDBMSMySQL))
	$DBMSTypes = $MariaDB.str_replace('</dd>',$popupMySQLMariaDBLink.'</dd>',$MySQLdb);
else
	$DBMSTypes = $MySQLdb.str_replace('</dd>',$popupMySQLMariaDBLink.'</dd>',$MariaDB);

// No Database Mysql System
$noDBMS = (empty($MySQLdb) && empty($MariaDB)) ? true : false;

//Alias
$aliasContents = '';
// alias retrieval
// Get PhpMyAdmin versions and parameters
GetAliasVersions();
// Create alias menu
if(is_dir($aliasDir)) {
	$PMyAdNotSeen = true;
	$handle=opendir($aliasDir);
	while (false !== ($file = readdir($handle))) {
	  if(is_file($aliasDir.$file) && strstr($file, '.conf')) {
			$href = $file = str_replace('.conf','',$file);
	  	if(stripos($file,'phpmyadmin') !== false) {
	  		if(!$PMyAdNotSeen || $noDBMS) continue;
				$PMyAdNotSeen = false;
				foreach($Alias_Contents['PMyAdVer'] as $key => $none) {
					$value = $Alias_Contents['PMyAd'][$key];
					$href = $value;
					$file = 'PhpMyAdmin '.$Alias_Contents[$value]['version'];
					$file_sup ='';
					if($Alias_Contents[$value]['fcgid'] && $Alias_Contents[$value]['fcgidPHPOK']) {
						$file_sup .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$Alias_Contents[$value]['fcgidPHP']."</small></p>";
						$nbAliasLines++;
					}
					$aliasContents .= '<li><a href="'.$href.'/">'.$file.'</a>'.$file_sup.'</li>';
					$nbAlias++;
					$nbAliasLines++;
					if($Alias_Contents[$value]['compat'] !== true) {
						$aliasContents .= '<li class="phpmynot">'.$Alias_Contents[$value]['notcompat'].'</li>';
						$nbAliasLines++;
					}
				}
			}
			elseif(stripos($file,'adminer') !== false) {
				if($noDBMS) continue;
				$file_sup = '';
				if($Alias_Contents['adminer']['fcgid'] && $Alias_Contents['adminer']['fcgidPHPOK']) {
					$file_sup .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$Alias_Contents['adminer']['fcgidPHP']."</small></p>";
				}
   			$aliasContents .= '<li><a href="'.$href.'/">'.$file.' '.$Alias_Contents['adminer']['version'].'</a>'.$file_sup.'</li>';
   			$nbAlias++;
   			$nbAliasLines++;
			}
			//Do not show phpsysinfo in alias column
			elseif(stripos($file,'phpsysinfo') === false){
				$file_sup = '';
				if($Alias_Contents[$file]['fcgid'] && $Alias_Contents[$file]['fcgidPHPOK']) {
					$file_sup .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$Alias_Contents[$file]['fcgidPHP']."</small></p>";
					$nbAliasLines++;
				}
 	    	$aliasContents .= '<li><a href="'.$href.'/">'.$file.'</a>'.$file_sup.'</li>';
 	    	$nbAlias++;
 	    	$nbAliasLines++;
	  	}
	  }
	}
	closedir($handle);
}
if(empty($aliasContents))
	$aliasContents = "<li class='phpmynot'>".$langues['txtNoAlias']."</li>\n";

// Get PhpSysInfo version and parameters
$phpsysinfo = '';
if($Alias_Contents['phpsysinfo']['OK']) {
	$file_sup = '';
	if($Alias_Contents['phpsysinfo']['fcgid'] && $Alias_Contents['phpsysinfo']['fcgidPHPOK']) {
		$file_sup .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$Alias_Contents['phpsysinfo']['fcgidPHP']."</small></p>";
	}
	$phpsysinfo = '<li><a href="phpsysinfo">PhpSysInfo '.$Alias_Contents['phpsysinfo']['version'].'</a>'.$file_sup.'</li>';
}

//Retrieving ServerName from httpd-vhosts.conf
$addVhost = "<li><a href='add_vhost.php?lang=".$langue."'>".$langues['txtAddVhost']."</a></li>";
if($VirtualHostMenu == "on") {
	$vhostError = false;
	$vhostErrorCorrected = true;
	$error_message = array();
  $allToolsClass = "four-columns";
	$virtualHost = check_virtualhost();
	$nbVirtualHost = $nbVirtualHostLines = $virtualHost['nb_Server'];
	$vhostsContents = '';
	if($virtualHost['include_vhosts'] === false) {
		$vhostsContents = "<li><i style='color:red;'>Error Include Apache</i></li>";
		$vhostError = true;
		$error_message[] = sprintf($langues['txtNoIncVhost'],$wampConf['apacheVersion']);
	}
	else {
		if($virtualHost['vhosts_exist'] === false) {
			$vhostsContents = "<li><i style='color:red;'>No vhosts file</i></li>";
			$vhostError = true;
			$error_message[] = sprintf($langues['txtNoVhostFile'],$virtualHost['vhosts_file']);
		}
		else {
				if($virtualHost['nb_Server'] > 0) {
				$port_number = true;
				$nb_Server = $virtualHost['nb_Server'];
				$nb_Virtual = $virtualHost['nb_Virtual'];
				$nb_Document = $virtualHost['nb_Document'];
				$nb_Directory = $virtualHost['nb_Directory'];
				$nb_End_Directory = $virtualHost['nb_End_Directory'];

				foreach($virtualHost['ServerName'] as $key => $value) {
					if($virtualHost['ServerNameValid'][$value] === false) {
						$vhostError = true;
						$vhostErrorCorrected = false;
						$vhostsContents .= '<li>'.$value.' - <i style="color:red;">syntax error</i></li>';
						$error_message[] = sprintf($langues['txtServerName'],"<span style='color:black;'>".$value."</span>",$virtualHost['vhosts_file']);
					}
					elseif($virtualHost['ServerNameValid'][$value] === true) {
						$UrlPortVH = ($virtualHost['ServerNamePort'][$value] != '80') ? ':'.$virtualHost['ServerNamePort'][$value] : '';
						if(!$virtualHost['port_listen'] && $virtualHost['ServerNamePortListen'][$value] !== true || $virtualHost['ServerNamePortApacheVar'][$value] !== true) {
							$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
							$vhostsContents .= '<li>'.$value_url.$UrlPortVH.' - <i style="color:red;">Not a Listen port</i></li>';
							if($virtualHost['ServerNamePortListen'][$value] !== true)
								$msg_error = ' not an Apache Listen port';
							elseif($virtualHost['ServerNamePortApacheVar'][$value] !== true)
								$msg_error = ' not an Apache define variable';
							if(!$vhostError) {
								$vhostError = true;
								$vhostErrorCorrected = false;
								$error_message[] = "Port ".$UrlPortVH." used for the VirtualHost is ".$msg_error;
							}
						}
						elseif($virtualHost['DocRootNotwww'][$value] === false) {
							$vhostError = true;
							$vhostErrorCorrected = false;
							$vhostsContents .= '<li>'.$value.' - <i style="color:red;">DocumentRoot error</i></li>';
							$error_message[] = sprintf($langues['txtDocRoot'],"<span style='color:black;'>".$value."</span>","<span style='color:black;'>".$wwwDir."</span>");
						}
						elseif($virtualHost['ServerNameDev'][$value] === true) {
							$vhostError = true;
							$vhostErrorCorrected = false;
							$vhostsContents .= '<li>'.$value.' - <i style="color:red;">TLD error</i></li>';
							$error_message[] = sprintf($langues['txtTLDdev'],"<span style='color:black;'>".$value."</span>","<span style='color:black;'>.dev</span>");
						}
						elseif($virtualHost['ServerNameIntoHosts'][$value] === false) {
							$vhostError = true;
							$vhostErrorCorrected = false;
							$vhostsContents .= '<li>'.$value.' - <i style="color:red;">hosts file error</i></li>';
							$error_message[] = sprintf($langues['txtNoHosts'],"<span style='color:black;'>".$value."</span>");
						}
						else {
							$value_aff = $vh_ip = '';
							$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
							$value_link = $value;
							if($virtualHost['ServerNameIp'][$value] !== false) {
								$vh_ip = $virtualHost['ServerNameIp'][$value];
								$value_url = $value_link = $vh_ip;
								$value_aff .= ' <i>('.$value.')</i>';
								if($virtualHost['ServerNameIpValid'][$value] === false) {
									$vhostError = true;
									$vhostErrorCorrected = false;
									$value_aff .=  ' <i style="color:red;">IP not valid</i>';
									$error_message[] = sprintf($langues['txtServerNameIp'],"<span style='color:black;'>".$vh_ip."</span>","<span style='color:black;'>".$value."</span>",$virtualHost['vhosts_file']);
								}
							}
							if($virtualHost['ServerNameIDNA'][$value] === true){
								$value_aff .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>IDNA-> ".$virtualHost['ServerNameUTF8'][$value]."</small></p>";
								$nbVirtualHostLines++;
							}
							if(isset($c_ApacheDefine['PHPROOT']) && $virtualHost['ServerNameFcgid'][$value] === true){
								$value_aff .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$virtualHost['ServerNameFcgidPHP'][$value]."</small></p>";
								$nbVirtualHostLines++;
							}
							if($virtualHost['ServerNameFcgid'][$value] === true && $virtualHost['ServerNameFcgidPHPOK'][$value] !== true) {
								$value_aff .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>FCGI -> PHP ".$virtualHost['ServerNameFcgidPHP'][$value]." - <span style='color:red;'>".$langues['phpNotExists']."</span></small></p>";
								$vhostError = true;
								$vhostErrorCorrected = false;
								$error_message[] = '<b>Error</b> --- VirtualHost '.$value.' - Fast CGI PHP '.$virtualHost['ServerNameFcgidPHP'][$value].' - '.$langues['phpNotExists'];
							}
							if(in_array($value,$virtualHost['ServerNameHttps'])) {
								$value_aff .= "<p style='margin:-11px 0 -2px 25px;color:green;'><small>HTTPS </small></p>";
								$nbVirtualHostLines++;
							}
							$vhostsContents .= '<li><a href="http://'.$value_url.$UrlPortVH.'">'.$value_link.'</a>'.$value_aff.'</li>';
						}
					}
					else {
						$vhostError = true;
						$error_message[] = sprintf($langues['txtVhostNotClean'],$virtualHost['vhosts_file']);
					}
				}
				//Check number of <Directory equals </Directory
				if($nb_End_Directory != $nb_Directory) {
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = sprintf($langues['txtNbNotEqual'],"&lt;Directory ....&gt;","&lt;/Directory&gt;",$virtualHost['vhosts_file']);
				}
				//Check number of DocumentRoot equals to number of ServerName
				if($nb_Document != $nb_Server) {
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = sprintf($langues['txtNbNotEqual'],"DocumentRoot","ServerName",$virtualHost['vhosts_file']);
				}
				//Check validity of DocumentRoot
				if($virtualHost['document'] === false) {
					foreach($virtualHost['documentPath'] as $value) {
						if($virtualHost['documentPathValid'][$value] === false) {
							$documentPathError = $value;
							$vhostError = true;
							$vhostErrorCorrected = false;
							$error_message[] = sprintf($langues['txtNoPath'],"<span style='color:black;'>".$value."</span>", "DocumentRoot", $virtualHost['vhosts_file']);
							break;
						}
					}
				}
				//Check validity of Directory Path
				if($virtualHost['directory'] === false) {
					foreach($virtualHost['directoryPath'] as $value) {
						if($virtualHost['directoryPathValid'][$value] === false) {
							$documentPathError = $value;
							$vhostError = true;
							$vhostErrorCorrected = false;
							$error_message[] = sprintf($langues['txtNoPath'],"<span style='color:black;'>".$value."</span>", "&lt;Directory ...", $virtualHost['vhosts_file']);
							break;
						}
					}
				}
				//Check number of <VirtualHost equals or > to number of ServerName
				if($nb_Server != $nb_Virtual && $wampConf['NotCheckDuplicate'] == 'off') {
					$port_number = false;
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = sprintf($langues['txtNbNotEqual'],"&lt;VirtualHost","ServerName",$virtualHost['vhosts_file']);
				}
				//Check number of port definition of <VirtualHost *:xx> equals to number of ServerName
				if($virtualHost['nb_Virtual_Port'] != $nb_Virtual && $wampConf['NotCheckDuplicate'] == 'off') {
					$port_number = false;
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = sprintf($langues['txtNbNotEqual'],"port definition of &lt;VirtualHost *:xx&gt;","ServerName",$virtualHost['vhosts_file']);
				}
				//Check validity of port number
				if($port_number && $virtualHost['port_number'] === false) {
					$port_number = false;
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = sprintf($langues['txtPortNumber'],"&lt;VirtualHost *:port&gt;",$virtualHost['vhosts_file']);
				}
				//Check if duplicate ServerName
				if($virtualHost['nb_duplicate'] > 0) {
					$DuplicateNames = '';
					foreach($virtualHost['duplicate'] as $NameValue)
						$DuplicateNames .= " ".$NameValue;
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = "Duplicate ServerName <span style='color:blue;'>".$DuplicateNames."</span> into ".$virtualHost['vhosts_file'];
				}
				//Check if duplicate Server IP
				if($virtualHost['nb_duplicateIp'] > 0) {
					$DuplicateNames = '';
					foreach($virtualHost['duplicateIp'] as $NameValue)
						$DuplicateNames .= " ".$NameValue;
					$vhostError = true;
					$vhostErrorCorrected = false;
					$error_message[] = "Duplicate IP <span style='color:blue;'>".$DuplicateNames."</span> into ".$virtualHost['vhosts_file'];
				}
			}
		}
	}
	if(empty($vhostsContents)) {
		$vhostsContents = "<li><i style='color:red:'>No VirtualHost</i></li>";
		$vhostError = true;
		$error_message[] = sprintf($langues['txtNoVhost'],$wampConf['apacheVersion']);
	}
	if(!$c_hostsFile_writable){
		$vhostError = true;
		$error_message[] = sprintf($langues['txtNotWritable'],$c_hostsFile)."<br>".nl2br($WarningMsg);
	}
	if($vhostError) {
		$vhostsContents .= "<li><i style='color:red;'>Error(s)</i> See below</li>";
		$error_content .= "<p style='color:red;'>";
		foreach($error_message as $value) {
			$error_content .= $value."<br />";
		}
		$error_content .= "</p>\n";
		if($vhostErrorCorrected)
			$addVhost = "<li><a href='add_vhost.php?lang=".$langue."'>".$langues['txtAddVhost']."</a> <span style='font-size:0.72em;color:red;'>".$langues['txtCorrected']."</span></li>";
	}
}
else {
    $allToolsClass = "three-columns";
}
//End retrieving ServerName from httpd-vhosts.conf

// Project recovery
$list_projects = array();
$handle=opendir(".");
while (false !== ($file = readdir($handle))) {
	if(is_dir($file) && !in_array($file,$projectsListIgnore))
		$list_projects[] = $file;
}
closedir($handle);
$projectContents = '';
if(count($list_projects) > 0) {
	if($wampConf['LinksOnProjectsHomePage'] == 'on') {
		$projectContents .= "<li class='projectsdir'>http://localhost/project/</li>\n";
	}
	foreach($list_projects as $file) {
		$projectContents .= ($wampConf['LinksOnProjectsHomePage'] == 'on') ? "<li><a href='http://localhost/".$file."/'>".$file."</a></li>" : '<li>'.$file.'</li>';
		$nbProjects++;
		$nbProjectsLines++;
	}
	if($wampConf['LinksOnProjectsHomeByIp'] == 'on') {
		$projectContents = str_replace('localhost',$c_local_ip,$projectContents);
	}
}

if(empty($projectContents))
	$projectContents = "<li class='projectsdir'>".$langues['txtNoProjet']."</li>\n";
else {
	if($wampConf['LinksOnProjectsHomePage'] == 'off') {
		$projectContents .= "<li class='projectsdir'>".sprintf($langues['txtProjects'].'.<br>'.$langues['txtProjectsLink'],$wwwDir)."</li>";
		if(strpos($projectContents,"http://localhost/") !== false) {
			$projectContents .= "<li><i style='color:blue;'>Warning:</i> See below</li>";
			if(!isset($error_content))
				$error_content = '';
			$error_content .= "<p style='color:blue;'>".sprintf($langues['nolocalhost'],$wampConf['apacheVersion'])."</p>";
		}
	}
}

// To scroll Projects, Alias and VirtualHost list display
if($wampConf['ScrollListsHomePage'] == 'on') {
	foreach($Scroll_List as $value) {
		if($value['scroll'] && ${$value['nbname']} > $value['lines']) {
			${$value['name']} = " style='height:21rem;overflow-y:scroll;padding-right:5px;'";
		}
	}
}

//Miscellaneous checks - Which php.ini is loaded?
$phpini = strtolower(trim(str_replace("\\","/",php_ini_loaded_file())));
$c_phpConfFileOri = strtolower($c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$phpConfFileForApache);
$c_phpCliConf = strtolower($c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampConf['phpConfFile']);
if($phpini != strtolower($c_phpConfFile) && $phpini != $c_phpConfFileOri) {
	$error_content .= "<p style='color:red;'>*** ERROR *** The PHP configuration loaded file is: ".$phpini." - should be: ".$c_phpConfFile." or ".$c_phpConfFileOri;
	$error_content .= "<br>You must perform: <span style='color:green;'>Right-click icon Wampmanager -> Refresh</span><br>";
	if($phpini == $c_phpCliConf || $phpini == $c_phpCliConfFile)
		$error_content .= " - This file is only for PHP in Command Line.";
	$error_content .= "</p>";
}
if($filelist = php_ini_scanned_files()) {
	if(strlen($filelist) > 0) {
		$error_content .= "<p style='color:red;'>*** ERROR *** There are too many php.ini files</p>";
		$files = explode(',', $filelist);
		foreach ($files as $file) {
			$error_content .= "<p style='color:red;'>*** ERROR *** There are other php.ini files: ".trim(str_replace("\\","/",$file))."</p>";
		}
	}
}

$pageContents = <<< EOPAGE
<!DOCTYPE html>
<html>
<head>
	<title>{$langues['titreHtml']}</title>
	<meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width">
	<link id="stylecall" rel="stylesheet" href="wampthemes/classic/style.css" />
	<link id="stylecall" rel="stylesheet" href="wampthemes/popupmodal.css" />
	<link rel="shortcut icon" href="favicon.ico" type="image/ico" />
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
      <li>${langueswitcher}${styleswitcher}</li>
	  </ul>
	</div>

	<div class="config">
	    <div class="innerconfig">
        <h2>{$langues['titreConf']}</h2>
	        <dl class="content">
		        <dt>{$langues['versa']}</dt>
		            <dd>${apacheVersion}&nbsp;&nbsp;-&nbsp;<a href='http://{$langues[$doca_version]}'>{$langues['documentation-of']} Apache</a></dd>
		        <dt>{$langues['server']}</dt>
		            <dd>${server_software}&nbsp;-&nbsp;{$langues['portUsed']}{$ListenPorts}</dd>
		        <dt>{$langues['versp']}</dt>
		            <dd><small style='color:blue;'>[Apache module]&nbsp;</small>&nbsp;${phpVersion}&nbsp;-&nbsp;<a href='http://{$langues['docp']}'>{$langues['documentation-of']} PHP</a>&nbsp;-&nbsp;{$popupPHPExtLink}</dd>
		        ${PhpAllVersionsNotFcgi}
		        <dt>&nbsp;</dt>
		        		<dd><small style='color:green;'>[FCGI]</small>&nbsp;${PhpAllVersions}</dd>
						${DBMSTypes}
	        </dl>
      </div>
  </div>
   <div class="divider1">&nbsp;</div>
   <div class="alltools ${allToolsClass}">
	    <div class="inneralltools">
	        <div class="column">
	            <h2>{$langues['titrePage']}</h2>
	            <ul class="tools">
		            <li><a href="?phpinfo=-1">phpinfo()</a></li>
		            {$xdebug_info}
		            {$phpsysinfo}
		            {$addVhost}
	            </ul>
	        </div>
	        		<div class="column">
	            <h2>{$langues['txtProjet']}&nbsp;<span style='font-size:60%;'>({$nbProjects})</span></h2>
	            <ul class="projects"{$ProjectsListScroller}>
	                ${projectContents}
	            </ul>
	        </div>
	        	<div class="column">
	            <h2>{$langues['txtAlias']}&nbsp;<span style='font-size:60%;'>({$nbAlias})</span></h2>
	            <ul class="aliases"{$AliasListScroller}>
	                ${aliasContents}
	            </ul>
	        </div>
EOPAGE;
if($VirtualHostMenu == "on") {
$pageContents .= <<< EOPAGEA
	        <div class="column">
	            <h2>{$langues['txtVhost']}&nbsp;<span style='font-size:60%;'>({$nbVirtualHost})</span></h2>
	            <ul class="vhost"{$VhostsListScroller}>
	                ${vhostsContents}
	            </ul>
	        </div>
EOPAGEA;
}
if(!empty($error_content)) {
$pageContents .= <<< EOPAGEB
	<div id="error" style="clear:both;"></div>
	${error_content}
EOPAGEB;
}
$pageContents .= <<< EOPAGEC
      </div>
    </div>
	<div class="divider2">&nbsp;</div>
	<ul id="foot">
		<li><a href="{$langues['forumLink']}">{$langues['forum']}</a></li>
	</ul>
{$ModalDialogs}

EOPAGEC;
include 'wampthemes/select_themes.php';
include 'wampthemes/copy_modal.php';
$pageContents .= <<< EOPAGED
</body>
</html>
EOPAGED;

echo $pageContents;

?>
