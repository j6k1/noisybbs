<?php
	chdir("../");
	require_once("inclueds.php");
	$baseurl = Util::getBaseUrl();

	if(file_exists("admin/adminpass.cgi"))
	{
		$genuser = false;
	}
	else
	{
		$genuser = true;
	}
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/login.css" charset="shift_jis" type="text/css">
<title>���O�C��(�Ǘ��҃��[�U)</title>
</head>
<body>
<center>
<h1>���O�C��(�Ǘ��҃��[�U)</h1>
<div id="container">
	<div id="form">
		<div>
			<form name="loginform" method="post" action="<?php echo $baseurl; ?>/admin/loginctrl.php">
				<?php if($genuser == false) { ?>
				<div class="head"><b>�p�X���[�h</b></div>
				<div id="password"><input type="password" name="pass" value="" /></div>
				<input type="hidden" name="mode" value="login" />
				<div id="submit"><input type="submit" value="���O�C��" /></div>
				<?php } else { ?>
				<div class="head"><b>�p�X���[�h</b></div>
				<div id="password"><input type="password" name="pass1" value="" /></div>
				<div class="head"><b>�ē���</b></div>
				<div id="password"><input type="password" name="pass2" value="" /></div>
				<input type="hidden" name="mode" value="setpass" />
				<div id="submit"><input type="submit" value="�p�X���[�h�ݒ�" /></div>
				<?php } ?>
				
			</form>
		</div>
	</div>
	<div id="caplogin"><a href="<?php echo $baseurl; ?>/admin/caplogin.php">�L���b�v�p���O�C���y�[�W��</a></div>
</div>
</center>
</body>
</html>

