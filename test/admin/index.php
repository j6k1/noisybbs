<?php
	session_start();
	chdir("../");
	
	require_once("inclueds.php");
	$baseurl = Util::getBaseUrl();
	
	BBSList::getInstance()->Init();
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
	
	if(LoginInfo::getInstance()->aclmode == "admin")
	{
		$bbslist = Util::getBBSList();
	}
	else
	{
		CapInfo::getInstance()->InitCaseAdmin(LoginInfo::getInstance()->cappass);
		$bbslist = CapInfo::getHasBBSList();
	}
	
	if( (LoginInfo::getInstance()->aclmode == "cap") && (count($bbslist) == 0) )
	{
		$none_authority = true;
	}
	else
	{
	
		$none_authority = false;
	}
	
	$form = array();
	$form["bbslist"] = new FormUtil();
	$form["bbslist"]->addElement("bbs", null, null, Util::getBBSNameList($bbslist));
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/index.css" charset="shift_jis" type="text/css">
<title>管理画面 - TOPページ</title>
</head>
<body>
<center>
<div id="container">
	<?php if($none_authority) { ?>
	<div id="message">このキャップには、権限が設定されていません。</div>
	<div><a href="<?php echo $baseurl; ?>/admin/login.php">戻る</a></div>
	<?php exit; } ?>
	<?php if(LoginInfo::getInstance()->aclmode == "admin") { ?>
	<div id="passchg"><a href="<?php echo $baseurl; ?>/admin/adminpasschg.php">管理者パスワード変更</a></div>
	<?php } ?>
	<div class="form">
	<div id="bbsselect">
		<div class="head">板名一覧</div>
			<form name="bbsselect" method="post" action="<?php echo $baseurl; ?>/admin/setting.php">
				<?php echo $form["bbslist"]->SelectBox("bbs", 4); ?>
				<input type="hidden" name="mode" value="bbsselect" />
				<div style="text-align: right;"><input type="submit" value="板選択" /></div>
			</form>
		</div>
		<?php if(LoginInfo::getInstance()->aclmode == "admin") { ?>
		<div class="line"></div>
		<div id="bbsmng"><a href="<?php echo $baseurl; ?>/admin/bbsmng.php">板の作成/削除</a></div>
		<div class="line"></div>
		<div id="capmng"><a href="<?php echo $baseurl; ?>/admin/capmng.php">キャップ管理</a></div>
		<?php } ?>
	</div>
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></div>
</div>
</center>
</body>
</html>

