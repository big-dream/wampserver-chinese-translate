<?php

// Page created by Shepard [Fabian Pijcke] <Shepard8@laposte.net>
// Arno Esterhuizen <arno.esterhuizen@gmail.com>
// and Romain Bourdon <rromain@romainbourdon.com>
// and Hervé Leclerc <herve.leclerc@alterway.fr>
// Icons by Mark James <http://www.famfamfam.com/lab/icons/silk/>
// Version 2.5 -> 3.0.0 by Dominique Ottello aka Otomatic
// 3.1.9 - Support VirtualHost IDNA ServerName
//
//
//

$server_dir = "../";

require $server_dir.'scripts/config.inc.php';
require $server_dir.'scripts/wampserver.lib.php';

//chemin jusqu'aux fichiers alias
$aliasDir = $server_dir.'alias/';

//Fonctionne à condition d'avoir ServerSignature On et ServerTokens Full dans httpd.conf
$server_software = $_SERVER['SERVER_SOFTWARE'];
$error_content = '';

// on récupère les versions des applis
$phpVersion = $wampConf['phpVersion'];
$apacheVersion = $wampConf['apacheVersion'];
$doca_version = 'doca'.substr($apacheVersion,0,3);
$mysqlVersion = $wampConf['mysqlVersion'];

//On récupère la valeur de VirtualHostMenu
$VirtualHostMenu = $wampConf['VirtualHostSubMenu'];

//on récupère la valeur de apachePortUsed
$port = $wampConf['apachePortUsed'];
$UrlPort = $port !== "80" ? ":".$port : '';
//On récupère le ou les valeurs des ports en écoute dans Apache
$ListenPorts = implode(' - ',listen_ports());
//on récupère la valeur de mysqlPortUsed
$Mysqlport = $wampConf['mysqlPortUsed'];


// répertoires à ignorer dans les projets
$projectsListIgnore = array ('.','..','wampthemes','wamplangues');

// Recherche des différents thèmes disponibles
$styleswitcher = '<select id="themes">'."\n";
$themes = glob('wampthemes/*', GLOB_ONLYDIR);
foreach ($themes as $theme) {
    if (file_exists($theme.'/style.css')) {
        $theme = str_replace('wampthemes/', '', $theme);
        $styleswitcher .= '<option id="'.$theme.'">'.$theme.'</option>'."\n";
    }
}
$styleswitcher .= '</select>'."\n";

//affichage du phpinfo
if (isset($_GET['phpinfo'])) {
	$type_info = intval(trim($_GET['phpinfo']));
	if($type_info < -1 || $type_info > 64)
		$type_info = -1;
	phpinfo($type_info);
	exit();
}

// Language
$langue = $wampConf['language'];
$i_langues = glob('wamplangues/index_*.php');
$languages = array();
foreach ($i_langues as $value) {
  $languages[] = str_replace(array('wamplangues/index_','.php'), '', $value);
}
$langueget = (!empty($_GET['lang']) ? strip_tags(trim($_GET['lang'])) : '');
if(in_array($langueget,$languages))
	$langue = $langueget;

// Recherche des différentes langues disponibles
$langueswitcher = '<form method="get" style="display:inline-block;"><select name="lang" id="langues" onchange="this.form.submit();">'."\n";
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

include 'wamplangues/index_english.php';
if(file_exists('wamplangues/index_'.$langue.'.php')) {
	$langue_temp = $langues;
	include 'wamplangues/index_'.$langue.'.php';
	$langues = array_merge($langue_temp, $langues);
}

//initialisation

// Récupération MySQL si supporté
$nbDBMS = 0;
$MySQLdb = '';
if(isset($wampConf['SupportMySQL']) && $wampConf['SupportMySQL'] =='on') {
	$nbDBMS++;
	$defaultDBMSMySQL = ($wampConf['mysqlPortUsed'] == '3306') ? "&nbsp;-&nbsp;".$langues['defaultDBMS'] : "";
	$MySQLdb = <<< EOF
<dt>{$langues['versm']}</dt>
	<dd>${mysqlVersion}&nbsp;-&nbsp;{$langues['mysqlportUsed']}{$Mysqlport}{$defaultDBMSMySQL}&nbsp;-&nbsp; <a href='http://{$langues['docm']}'>{$langues['documentation']} MySQL</a></dd>
EOF;
}

// Récupération MariaDB si supporté
$MariaDB = '';
if(isset($wampConf['SupportMariaDB']) && $wampConf['SupportMariaDB'] =='on') {
	$nbDBMS++;
	$defaultDBMSMaria = ($wampConf['mariaPortUsed'] == '3306') ? "&nbsp;-&nbsp;".$langues['defaultDBMS'] : "";
	$MariaDB = <<< EOF
<dt>{$langues['versmaria']}</dt>
  <dd>${c_mariadbVersion}&nbsp;-&nbsp;{$langues['mariaportUsed']}{$wampConf['mariaPortUsed']}{$defaultDBMSMaria}&nbsp;-&nbsp; <a href='http://{$langues['docmaria']}'>{$langues['documentation']} MariaDB</a></dd>
EOF;
}

/* Help MySQL - MariaDB popup */
$popupLink = '';
if($nbDBMS > 1) {
	$popupLink = <<< EOF
 - <a class='popup'>MySQL - MariaDB<span>{$langues['HelpMySQLMariaDB']}</span></a>
EOF;
}
//Default DBMS in first position
if(empty($defaultDBMSMySQL))
	$DBMSTypes = $MariaDB.str_replace('</dd>',$popupLink.'</dd>',$MySQLdb);
else
	$DBMSTypes = $MySQLdb.str_replace('</dd>',$popupLink.'</dd>',$MariaDB);

// No Database Mysql System
$noDBMS = (empty($MySQLdb) && empty($MariaDB)) ? true : false;
$phpmyadminTool = $noDBMS ? '' : '<li><a href="phpmyadmin/">phpmyadmin</a></li>';

$aliasContents = '';

// récupération des alias
if (is_dir($aliasDir))
{
    $handle=opendir($aliasDir);
    while (($file = readdir($handle))!==false)
    {
	    if (is_file($aliasDir.$file) && strstr($file, '.conf'))
	    {
	    	if(!($noDBMS && ($file == 'phpmyadmin.conf' || $file == 'adminer.conf'))) {
		    	$msg = '';
		    	$aliasContents .= '<li><a href="'.str_replace('.conf','',$file).'/">'.str_replace('.conf','',$file).'</a></li>';
		  	}
	    }
    }
    closedir($handle);
}
if (empty($aliasContents))
	$aliasContents = "<li>".$langues['txtNoAlias']."</li>\n";


//Récupération des ServerName de httpd-vhosts.conf
$addVhost = "<li><a href='add_vhost.php?lang=".$langue."'>".$langues['txtAddVhost']."</a></li>";
if($VirtualHostMenu == "on") {
	$vhostError = false;
	$vhostErrorCorrected = true;
	$error_message = array();
    $allToolsClass = "four-columns";
	$virtualHost = check_virtualhost();
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
						elseif($virtualHost['ServerNameIp'][$value] !== false) {
							$vh_ip = $virtualHost['ServerNameIp'][$value];
							if($virtualHost['ServerNameIpValid'][$value] !== false) {
								$vhostsContents .= '<li><a href="http://'.$vh_ip.$UrlPortVH.'">'.$vh_ip.'</a> <i>('.$value.')</i></li>';
							}
							else {
								$vhostError = true;
								$vhostErrorCorrected = false;
								$vhostsContents .= '<li>'.$vh_ip.' for '.$value.' - <i style="color:red;">IP not valid</i></li>';
								$error_message[] = sprintf($langues['txtServerNameIp'],"<span style='color:black;'>".$vh_ip."</span>","<span style='color:black;'>".$value."</span>",$virtualHost['vhosts_file']);
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
						else {
							$value_url = ((strpos($value, ':') !== false) ? strstr($value,':',true) : $value);
							$valueaff = ($virtualHost['ServerNameIDNA'][$value] === true) ? "<p style='margin:-8px 0 -8px 25px;'><small>IDNA-> ".$virtualHost['ServerNameUTF8'][$value]."</small></p>" : '';
							$vhostsContents .= '<li><a href="http://'.$value_url.$UrlPortVH.'">'.$value.'</a>'.$valueaff.'</li>';
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

//Fin Récupération ServerName

// récupération des projets
$handle=opendir(".");
$projectContents = '';
while (($file = readdir($handle))!==false)
{
	if (is_dir($file) && !in_array($file,$projectsListIgnore))
	{
		$projectContents .= '<li>'.$file.'</li>';
	}
}
closedir($handle);
if (empty($projectContents))
	$projectContents = "<li class='projectsdir'>".$langues['txtNoProjet']."</li>\n";
else {
	if(strpos($projectContents,"http://localhost/") !== false) {
		$projectContents .= "<li><i style='color:blue;'>Warning:</i> See below</li>";
		if(!isset($error_content))
			$error_content = '';
		$error_content .= "<p style='color:blue;'>".sprintf($langues['nolocalhost'],$wampConf['apacheVersion'])."</p>";
	}
	else {
		$projectContents .= "<li class='projectsdir'>".sprintf($langues['txtProjects'],$wwwDir)."</li>";
	}
}

//initialisation
$phpExtContents = '';

// récupération des extensions PHP
$loaded_extensions = get_loaded_extensions();
// classement alphabétique des extensions
setlocale(LC_ALL,"{$langues['locale']}");
sort($loaded_extensions,SORT_LOCALE_STRING);
foreach ($loaded_extensions as $extension)
	$phpExtContents .= "<li>${extension}</li>";

//vérifications diverses - Quel php.ini est chargé ?
$phpini = strtolower(trim(str_replace("\\","/",php_ini_loaded_file())));
$c_phpConfFileOri = strtolower($c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$phpConfFileForApache);
$c_phpCliConf = strtolower($c_phpVersionDir.'/php'.$wampConf['phpVersion'].'/'.$wampConf['phpConfFile']);

if($phpini != strtolower($c_phpConfFile) && $phpini != $c_phpConfFileOri) {
	$error_content .= "<p style='color:red;'>*** ERROR *** The PHP configuration loaded file is: ".$phpini." - should be: ".$c_phpConfFile." or ".$c_phpConfFileOri;
	$error_content .= "<br>You must perform: <span style='color:green;'>Right-click icon Wampmanager -> Refresh</span><br>";
	if($phpini == $c_phpCliConf || $phpini == $c_phpCliConfFile)
		$error_content .= " - This file is only for PHP in Command Line - Maybe you've added 'PHPIniDir' in the 'httpd.conf' file. Delete or comment this line.";
	$error_content .= "</p>";
}
if($filelist = php_ini_scanned_files()) {
	if (strlen($filelist) > 0) {
		$error_content .= "<p style='color:red;'>*** ERROR *** There are too much php.ini files</p>";
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
	<link rel="shortcut icon" href="favicon.ico" type="image/ico" />
</head>

<body>
  <div id="head">
    <div class="innerhead">
	    <h1><abbr title="Windows">W</abbr><abbr title="Apache">a</abbr><abbr title="MySQL/MariaDB">m</abbr><abbr title="PHP">p</abbr><abbr title="server WEB local">server</abbr></h1>
		   <ul>
			   <li>Apache 2.4</li><li>-</li><li>MySQL 5 &amp; 8</li><li>-</li><li>MariaDB 10</li><li>-</li><li>PHP 5 &amp; 7</li>
		   </ul>
     </div>
		<ul class="utility">
		  <li>Version ${c_wampVersion} - ${c_wampMode}</li>
      <li>${langueswitcher}${styleswitcher}</li>
	  </ul>
	</div>

	<div class="config">
	    <div class="innerconfig">

	        <h2> {$langues['titreConf']} </h2>

	        <dl class="content">
		        <dt>{$langues['versa']}</dt>
		            <dd>${apacheVersion}&nbsp;&nbsp;-&nbsp;<a href='http://{$langues[$doca_version]}'>{$langues['documentation']}</a></dd>
		        <dt>{$langues['server']}</dt>
		            <dd>${server_software}&nbsp;-&nbsp;{$langues['portUsed']}{$ListenPorts}</dd>
		        <dt>{$langues['versp']}</dt>
		            <dd>${phpVersion}&nbsp;&nbsp;-&nbsp;<a href='http://{$langues['docp']}'>{$langues['documentation']}</a></dd>
		        <dt>{$langues['phpExt']}</dt>
		            <dd>
			            <ul>
			                ${phpExtContents}
			            </ul>
		            </dd>
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
		            {$phpmyadminTool}
		            {$addVhost}
	            </ul>
	        </div>
	        		<div class="column">
	            <h2>{$langues['txtProjet']}</h2>
	            <ul class="projects">
	                ${projectContents}
	            </ul>
	        </div>
	        	<div class="column">
	            <h2>{$langues['txtAlias']}</h2>
	            <ul class="aliases">
	                ${aliasContents}
	            </ul>
	        </div>
EOPAGE;
if($VirtualHostMenu == "on") {
$pageContents .= <<< EOPAGEA
	        <div class="column">
	            <h2>{$langues['txtVhost']}</h2>
	            <ul class="vhost">
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

<script>
var select = document.getElementById("themes");
if (select.addEventListener) {
    /* Only for modern browser and IE > 9 */
    var stylecall = document.getElementById("stylecall");
    /* looking for stored style name */
    var wampStyle = localStorage.getItem("wampStyle");
    if (wampStyle !== null) {
        stylecall.setAttribute("href", "wampthemes/" + wampStyle + "/style.css");
        selectedOption = document.getElementById(wampStyle);
        selectedOption.setAttribute("selected", "selected");
    }
    else {
        localStorage.setItem("wampStyle","classic");
        selectedOption = document.getElementById("classic");
        selectedOption.setAttribute("selected", "selected");
    }
    /* Changing style when select change */

    select.addEventListener("change", function(){
        var styleName = this.value;
        stylecall.setAttribute("href", "wampthemes/" + styleName + "/style.css");
        localStorage.setItem("wampStyle", styleName);
    })
}
</script>
</body>
</html>
EOPAGEC;

echo $pageContents;

?>
