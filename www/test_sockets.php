<?php
if (extension_loaded('sockets')) {
	//创建 IPv4 socket
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) ;
	if ($socket === false) {
		$errorcode = socket_last_error() ;
		$errormsg = socket_strerror($errorcode);
		echo "<p>IPv4 Socket 错误: ".$errormsg."</p>\n" ;
	} else {
		echo "<p>IPv4 Socket: 支持</p>\n" ;
		socket_close($socket);
	}

	//创建 IPv6 socket
	$socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP) ;
	if ($socket === false) {
		$errorcode = socket_last_error() ;
		$errormsg = socket_strerror($errorcode);
		echo "<p>IPv6 Socket 错误: ".$errormsg."</p>\n" ;
	} else {
		echo "<p>IPv6 Socket: 支持</p>\n" ;
		socket_close($socket);
	}
} else {
	echo "<p>PHP扩展(sockets)未启用</p>\n" ;
}
