<?php
	session_start();
	chdir("../");
	require_once("inclueds.php");
	$baseurl = Util::getBaseUrl();

	$instance = new AdminLogin("{$baseurl}/admin/caplogin.php");
	$instance->ExecuteCap();
?>
