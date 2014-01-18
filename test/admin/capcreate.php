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
	
	$form = new FormUtil();
	$form->addElement("capname", "", CheckPattern::get()->notnullstr);
	$form->addElement("cappass", "", '/^([0-9a-zA-Z_-]{6,16})\z/');
	$form->addElement("pass1", "", '/^([0-9a-zA-Z_-]{8,16})\z/');
	$form->addElement("pass2", "", null);	
	
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if( (!isset($_POST["capname"])) || (!isset($_POST["cappass"])) ||
			(!isset($_POST["pass1"])) || (!isset($_POST["pass2"])) )
		{
			AdminUtil::OutPutErrHtml("フォームデータが不正です。", "{$baseurl}/admin/login.php");
			exit;
		}

		$form->setElementValue("capname", $_POST["capname"]);
		$form->setElementValue("cappass", $_POST["cappass"]);
		$form->setElementValue("pass1", $_POST["pass1"]);
		$form->setElementValue("pass2", $_POST["pass2"]);

		$form->ValidateAll();
		
		if($_POST["pass1"] != $_POST["pass2"])
		{
			$passcmperr = true;
		}
		else
		{
			$passcmperr = false;
		}
		
		if(($form->HasError() == false) && ($passcmperr == false))
		{
			$capdata = new CapData($_POST["capname"], $_POST["cappass"], 
				$_POST["pass1"], array(), array());
			CapList::getInstance()->Add($capdata);
			$ret = CapList::getInstance()->Save();
				
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
				exit;
			}
			
			$form->setElementValue("capname", "");
			$form->setElementValue("cappass", "");
			$form->setElementValue("pass1", "");
			$form->setElementValue("pass2", "");
		}
	}
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/capcreate.css" charset="shift_jis" type="text/css">
<title>キャップ新規作成</title>
</head>
<body>
<center>
<h1>キャップ新規作成</h1>
<div id="container">
	<div id="back">
		<span id="index"><a href="<?php echo $baseurl; ?>/admin/capmng.php">キャップ管理へ</a></span>
		<span id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></span>
	</div>
	<div id="form">
		<div>
			<form name="loginform" method="post" action="">
				<div class="head"><b>キャップ名称</b></div>
				<div class="text"><?php echo $form->Text("capname"); ?></div>
				<?php echo $form->ErrMessage("capname", "<div class='errmsg'>名称が入力されていません。</div>"); ?>
				<div class="head"><b>キャップパスワード</b></div>
				<div class="text"><?php echo $form->Text("cappass"); ?></div>
				<?php echo $form->ErrMessage("cappass", "<div class='errmsg'>0-9a-zA-Z_-のいずれかの文字のみで、6～16文字で設定してください。</div>"); ?>
				<div class="head"><b>管理パスワード</b></div>
				<div class="text"><?php echo $form->Password("pass1"); ?></div>
				<?php echo $form->ErrMessage("pass1", "<div class='errmsg'>0-9a-zA-Z_-のいずれかの文字のみで、8～16文字で設定してください。</div>"); ?>
				<div class="head"><b>管理パスワード(再入力)</b></div>
				<div class="text""><?php echo $form->Password("pass2"); ?></div>
				<?php if($passcmperr) { echo "<div class='errmsg'>パスワードが正しく再入力されていません。</div>"; } ?> 
				<div id="submit"><input type="submit" value="作成" /></div>
				
			</form>
		</div>
	</div>
</div>
</center>
</body>
</html>

