<?php
//3.2.8 - New file
//3.3.0 - Modification of lines FcgidInitialEnv

$langues['fcgi_mode_link'] = 'FCGI mode help';
$langues['fcgi_not_loaded'] = 'PHP cannot be used in FCGI mode because the Apache module fcgid_module is not loaded';
$langues['fcgi_mode_help'] = <<< 'FCGIEOT'
<h4>How to use PHP in Fast CGI mode with Wampserver</h4>
The CGI (Common Gateway Interface) defines a way for a web server to interact with external content-generating programs, which are often referred to as CGI programs or CGI scripts. It is a simple way to put dynamic content on your web site, using whatever programming language you're most familiar with.

<h5>- Only one PHP version as Apache module</h5>
Since the beginning, Wampserver loads PHP as an Apache module:
  <code>LoadModule php_module "${INSTALL_DIR}/bin/php/php8.1.1/php8apache2_4.dll"</code>
which makes all VirtualHost, Alias and Projects use the same PHP version.
If you change the PHP version via the PHP menu of Wampmanager, this new version will be used everywhere.

<h5>- Several PHP versions with FCGI mode</h5>
Since Wampserver 3.2.8, it is possible to use PHP in CGI mode, i.e. you can define a different PHP version, whose addons have been previously installed, for each VirtualHost. This means that the VirtualHost are not obliged to use the same PHP version anymore.

The Apache fcgid_module (mod_fcgid.so) simplifies the implementation of CGI
The documentation is here: <a href='https://httpd.apache.org/mod_fcgid/mod/mod_fcgid.html'>mod_fcgid</a>

<h5>- Prerequisites</h5>
- 1 Presence of the mod_fcgid.so file in the Apache modules folder.
- 2 Presence of the module loading line in the httpd.conf file
  <code>LoadModule fcgid_module modules/mod_fcgid.so</code> (Not commented - No # at the beginning))
- 3 Presence of the common configuration directives of the module fcgid_module in the file httpd.conf
<code>
&lt;IfModule fcgid_module>
  FcgidMaxProcessesPerClass 300
  FcgidConnectTimeout 10
  FcgidProcessLifeTime 1800
  FcgidMaxRequestsPerProcess 0
  FcgidMinProcessesPerClass 0
  FcgidFixPathinfo 0
  FcgidZombieScanInterval 20
  FcgidMaxRequestLen 536870912
  FcgidBusyTimeout 120
  FcgidIOTimeout 120
  FcgidTimeScore 3
  FcgidPassHeader Authorization
  Define PHPROOT ${INSTALL_DIR}/bin/php/php
&lt;/IfModule>
</code>
These three points 1, 2 and 3 are done automatically with the Wampserver 3.2.8 update

<h5>- Creating a FCGI VirtualHost</h5>
- After the Wampserver 3.2.8 update, the 'http://localhost/add_vhost.php' page allows the addition of a FCGI VirtualHost in all simplicity.
The choice of the version of PHP to use is limited to the versions of the PHP addons installed in your Wampserver what avoids an error of version PHP.
Indeed, to declare, in a VirtualHost, a non-existent PHP version in Wampserver will generate an Apache error and a "crash" of this one.

- If you want to modify an existing VirtualHost to add the FCGI mode with an existing PHP version already in the Wampserver PHP addons, you just have to go on the page http://localhost/add_vhost.php and launch the VirtualHost modification form to be able, in three clicks, to add the FCGI mode to the VirtualHost, to change the PHP version or to remove the FCGI mode.
It will be necessary to refresh Wampserver for that to be taken into account.
This same page http://localhost/add_vhost.php also allows, via the Alias modification form, to add the FCGI mode to an Alias, to change the PHP version or to remove the FCGI mode, always in three clicks.

<h5>- Some details</h5>
To add FCGI mode to an existing VirtualHost, simply add the following directives just before the &lt;/VirtualHost> end of that VirtualHost:
<code>
  &lt;IfModule fcgid_module>
    Define FCGIPHPVERSION "7.4.27"
    FcgidInitialEnv PHPRC "${PHPROOT}${FCGIPHPVERSION}/php.ini"
    &lt;Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe" .php
    &lt;/Files>
  &lt;/IfModule>
</code>
The PHP version must exist as a PHP addon in your Wampserver and can be modified.
Conversely removing these lines causes the VirtualHost to revert to the PHP version used as an Apache module.

For Alias, it's a little less simple, you need to add the previous lines in two parts, the first part:
<code>
&lt;IfModule fcgid_module>
  Define FCGIPHPVERSION "7.4.27"
  FcgidCmdOptions ${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe \
  InitialEnv PHPRC=${PHPROOT}${FCGIPHPVERSION}/php.ini
&lt;/IfModule>
</code>
just before the &lt;Directory... directive.
The second part:
<code>
&lt;IfModule fcgid_module>
  &lt;Files ~ "\.php$">
    Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
    AddHandler fcgid-script .php
    FcgidWrapper "${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe" .php
  &lt;/Files>
&lt;/IfModule>
</code>
inside the &lt;Directory...>..&lt;/Directory> context so as to obtain, for example for any Alias, the following structure:
<code>
Alias /myalias "g:/www/mydir/"
&lt;IfModule fcgid_module>
  Define FCGIPHPVERSION "7.4.27"
  FcgidCmdOptions ${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe \
  InitialEnv PHPRC=${PHPROOT}${FCGIPHPVERSION}/php.ini
&lt;/IfModule>
&lt;Directory "g:/www/mydir/">
  Options Indexes FollowSymLinks
  AllowOverride all
  Require local
  &lt;IfModule fcgid_module>
    &lt;Files ~ "\.php$">
      Options +Indexes +Includes +FollowSymLinks +MultiViews +ExecCGI
      AddHandler fcgid-script .php
      FcgidWrapper "${PHPROOT}${FCGIPHPVERSION}/php-cgi.exe" .php
    &lt;/Files>
  &lt;/IfModule>
&lt;/Directory>
</code>

FCGIEOT;

?>