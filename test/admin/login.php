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
<title>ログイン(管理者ユーザ)</title>
</head>
<body>
<center>
<h1>ログイン(管理者ユーザ)</h1>
<div id="container">
	<div id="form">
		<div>
			<form name="loginform" method="post" action="<?php echo $baseurl; ?>/admin/loginctrl.php">
				<?php if($genuser == false) { ?>
				<div class="head"><b>パスワード</b></div>
				<div id="password"><input type="password" name="pass" value="" /></div>
				<input type="hidden" name="mode" value="login" />
				<div id="submit"><input type="submit" value="ログイン" /></div>
				<?php } else { ?>
				<div class="head"><b>パスワード</b></div>
				<div id="password"><input type="password" name="pass1" value="" /></div>
				<div class="head"><b>再入力</b></div>
				<div id="password"><input type="password" name="pass2" value="" /></div>
				<input type="hidden" name="mode" value="setpass" />
				<div id="submit"><input type="submit" value="パスワード設定" /></div>
				<?php } ?>
				
			</form>
		</div>
	</div>
	<div id="caplogin"><a href="<?php echo $baseurl; ?>/admin/caplogin.php">キャップ用ログインページへ</a></div>
</div>
</center>
</body>
</html>

