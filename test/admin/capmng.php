<?php
	session_start();
	chdir("../");
	
	require_once("inclueds.php");
	
	BBSList::getInstance()->Init();
	$baseurl = Util::getBaseUrl();
	
	if(LoginInfo::getInstance()->Init() == false)
	{
		Util::Redirect("{$baseurl}/admin/login.php");
	}
	
	$ret = CapList::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	if(LoginInfo::getInstance()->aclmode != "admin")
	{
		AdminUtil::OutPutErrHtml("操作権限がありません。", "{$baseurl}/admin/login.php");
		exit;
	}
	
	if(isset($_POST["mode"]) && ($_POST["mode"] == "delete"))
	{
		CapList::getInstance()->Delete($_POST["cap"]);
		$ret = CapList::getInstance()->Save();
		
		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
	}

	$form = array();
	$form["select"] = new FormUtil();
	$form["select"]->addElement("cap", null, null, CapList::getInstance()->getCapNames());

	$form["delete"] = new FormUtil();
	$form["delete"]->addElement("cap", null, null, CapList::getInstance()->getCapNames());
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/capmng.css" charset="shift_jis" type="text/css">
<title>管理画面 - キャップ管理</title>
</head>
<body>
<center>
<div id="container">
	<div id="back">
		<span id="index"><a href="<?php echo $baseurl; ?>/admin/index.php">管理画面TOPへ</a></span>
		<span id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></span>
	</div>
	<div class="form">
		<div id="capcreate">
			<div class="head">キャップ管理</div>
			<div class="space"></div>
			<div><a href="<?php echo $baseurl; ?>/admin/capcreate.php">キャップ作成ページへ</a></div>
			<div class="line"></div>
		</div>
		<div id="capselect">
		<div class="head">キャップ選択</div>
			<form name="capselect" method="post" action="<?php echo $baseurl; ?>/admin/capctrl.php">
				<?php echo $form["select"]->SelectBox("cap", 4); ?>
				<input type="hidden" name="mode" value="select" />
				<div style="text-align: right;"><input type="submit" value="選択" /></div>
			</form>
		</div>
		<div class="line"></div>
		<div id="capdelete">
			<div class="head">キャップ削除</div>
			<form name="capdelete" method="post" action="">
				<?php echo $form["delete"]->SelectBox("cap", 4); ?>
				<input type="hidden" name="mode" value="delete" />
				<div style="text-align: right;">
					<input type="submit" value="選択したキャップを削除" onClick='return confirm("本当に削除しますか？");'/>
				</div>
			</form>
		</div>
	</div>
</div>
</center>
</body>
</html>

