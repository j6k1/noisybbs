<?php
	session_start();
	chdir("../");
	require_once("inclueds.php");

	$baseurl = Util::getBaseUrl();

	if(LoginInfo::getInstance()->Init() == false)
	{
		Util::Redirect("{$baseurl}/admin/login.php");
	}	
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/login.css" charset="shift_jis" type="text/css">
<title>�Ǘ��p�X���[�h�ύX</title>
</head>
<body>
<center>
<h1>�Ǘ��p�X���[�h�ύX</h1>
<div id="container">
	<div id="form">
		<div>
			<form name="loginform" method="post" action="<?php echo $baseurl; ?>/admin/loginctrl.php">
				<div class="head"><b>�p�X���[�h</b></div>
				<div id="password"><input type="password" name="pass1" value="" /></div>
				<div class="head"><b>�ē���</b></div>
				<div id="password"><input type="password" name="pass2" value="" /></div>
				<input type="hidden" name="mode" value="setpass" />
				<div id="submit"><input type="submit" value="�p�X���[�h�ݒ�" /></div>
				
			</form>
		</div>
	</div>
	<div id="caplogin"><a href="<?php echo $baseurl; ?>/admin/login.php">���O�C���y�[�W��</a></div>
</div>
</center>
</body>
</html>

