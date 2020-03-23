<?php
/*
* 如果您给root用户设置了密码，请更改 $password 的值(内容)
* 如果您更改了数据库端口，请更改 $port 的值为您设定的端口号
*
*/
$user = 'root'; // 数据库用户名
$password = ''; // 数据库密码，如果您修改了root用户的密码，则这里需要进行修改
$database = ''; // 数据库名称，要连接的数据库，该数据库必须存在
$port = NULL; // 数据库端口，如果您更改了数据库端口，则这里需要修改为您设定的端口号
$mysqli = new mysqli('127.0.0.1', $user, $password, $database, $port);

if ($mysqli->connect_error) {
    die('连接错误 (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
echo '<p>连接正常</p>';
echo '<p>连接类型: '. $mysqli->host_info.'</p>';
echo '<p>MySQL 版本: '.$mysqli->server_info.'</p>';
$mysqli->close();
