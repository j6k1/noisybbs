<?php
	chdir("../");
	require_once("inclueds.php");
	$baseurl = Util::getBaseUrl();
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/login.css" charset="shift_jis" type="text/css">
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/caplogin.css" charset="shift_jis" type="text/css">
<title>���O�C��(�L���b�v)</title>
</head>
<body>
<center>
<h1>���O�C��(�L���b�v)</h1>
<div id="container">
	<div id="form">
		<div>
			<form name="loginform" method="post" action="<?php echo $baseurl; ?>/admin/caploginctrl.php">
				<div class="head"><b>�L���b�v�p�X���[�h</b></div>
				<div id="cappass"><input type="text" name="cappass" value="" /></div>
				<div class="head"><b>�Ǘ��p�X���[�h</b></div>
				<div id="password"><input type="password" name="pass" value="" /></div>
				<input type="hidden" name="mode" value="login" />
				<div id="submit"><input type="submit" value="���O�C��" /></div>
				
			</form>
		</div>
	</div>
	<div id="login"><a href="<?php echo $baseurl; ?>/admin/login.php">�Ǘ��җp���O�C���y�[�W��</a></div>
</div>
</center>
</body>
</html>

