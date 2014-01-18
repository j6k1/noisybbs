<?php
	chdir("../");
	session_start();
	require_once("inclueds.php");
	$baseurl = Util::getBaseUrl();

	LoginInfo::LogOut();
	
	Util::Redirect("{$baseurl}/admin/login.php");
?>
