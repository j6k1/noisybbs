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
	
	if( isset($_POST["mode"]) && ($_POST["mode"] == "select") )
	{
		if(!isset($_POST['cap']))
		{
			AdminUtil::OutPutErrHtml("フォームデータが不正です。", "{$baseurl}/admin/login.php");
			exit;
		}
		
		Util::Redirect("{$baseurl}/admin/capctrl.php/{$_POST['cap']}/1");
	}
	else
	{
		if(!isset($_SERVER["PATH_INFO"]))
		{
			AdminUtil::OutPutErrHtml("URLの形式が不正です。", "{$baseurl}/admin/login.php");
			exit;
		}
			
		$pathinfo = explode("/", $_SERVER["PATH_INFO"]);
		
		if(count($pathinfo) < 3)
		{
			AdminUtil::OutPutErrHtml("URLの形式が不正です。", "{$baseurl}/admin/login.php");
			exit;
		}
		
		$cappass = $pathinfo[1];
		$page = $pathinfo[2];
	}
	
	if($page < 0)
	{
		$page = 0;
	}

	$setting = SettingInfo::getInstance();
	
	$ret = CapList::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	if(CapInfo::getInstance()->InitCaseAdmin($cappass) == false)
	{
		AdminUtil::OutPutErrHtml("指定されたキャップは存在しません。", "{$baseurl}/admin/capmng.php");
		exit;
	}
	
	if(LoginInfo::getInstance()->aclmode != "admin")
	{
		AdminUtil::OutPutErrHtml("操作権限がありません。", "{$baseurl}/admin/login.php");
		exit;
	}
	
	CapInfo::getInstance()->InitCaseAdmin(LoginInfo::getInstance()->cappass);
	
	$bbsnames = Util::getBBSNameList(Util::getBBSList());
	
	$count = count($bbsnames);

	$authoritys = CapInfo::getInstance()->getAuthoritys();

	$form = array();
	$form["edit"] = new FormUtil();

	$form["pass"] = new FormUtil();
	$form["pass"]->addElement("pass1", "", '/^([0-9a-zA-Z_-]{8,16})\z/');
	$form["pass"]->addElement("pass2", "", null);	
	
	$form["bbs"] = new FormUtil();
	$form["bbs"]->addElement("bbs", CapInfo::getHasBBSList(), null);
	
	$paschgcomp = false;
	$bbsselectcomp = false;
	
	if(isset($_POST["mode"]))
	{
		if($_POST["mode"] == "passchg")
		{
			if(!isset($_POST["pass1"]) || !isset($_POST["pass2"]))
			{
				AdminUtil::OutPutErrHtml("フォームデータが不正です。", "{$baseurl}/admin/login.php");
				exit;
			}
			
			$form["pass"]->setElementValue("pass1", $_POST["pass1"]);
			$form["pass"]->setElementValue("pass2", $_POST["pass2"]);
	
			$form["pass"]->ValidateAll();
			
			if($_POST["pass1"] != $_POST["pass2"])
			{
				$passcmperr = true;
			}
			else
			{
				$passcmperr = false;
			}
		
			if(($form["pass"]->HasError() == false) && ($passcmperr == false))
			{
				$ret = CapList::getInstance()->UpdatePass($cappass, $_POST["pass1"]);
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
					exit;
				}
			
				$ret = CapList::getInstance()->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
					exit;
				}
			}
			
			$paschgcomp = true;
		}
		else if($_POST["mode"] == "bbsselect")
		{
			if(!isset($_POST["bbs"]) || !is_array($_POST["bbs"]))
			{
				$bbslist = array();
			}
			else
			{
				$bbslist = $_POST["bbs"];
			}
			
			CapList::getInstance()->UpdateBBS($cappass, $bbslist);
			$ret = CapList::getInstance()->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
				exit;
			}
			$form["bbs"]->setElementValue("bbs", $bbslist);
			$bbsselectcomp = true;
		}
	}
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/capctrl.css" charset="shift_jis" type="text/css">
<title>管理画面 - キャップ管理(<?php echo CapInfo::getInstance()->getName(); ?>)</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/capmng.php"; ?>">キャップ管理へ</a></div>
	<div class="form">
		<div class="head">パスワード再設定</div>
		<div class="line"></div>
		<div id="passchg">
			<div class="box">
				<form name="passchg" method="post" action="">
					<div class="head"><b>パスワード</b></div>
					<div class="text"><?php echo $form["pass"]->Password("pass1"); ?></div>
					<?php echo $form["pass"]->ErrMessage("pass1", "<div class='errmsg'>0-9a-zA-Z_-のいずれかの文字のみで、8～16文字で設定してください。</div>"); ?>
					<div class="head"><b>パスワード(再入力)</b></div>
					<div class="text""><?php echo $form["pass"]->Password("pass2"); ?></div>
					<?php if(isset($passcmperr) && $passcmperr) { echo "<div class='errmsg'>パスワードが正しく再入力されていません。</div>"; } ?> 
					<input type="hidden" name="mode" value="passchg" />
					<div id="submit"><input type="submit" value="再設定" /></div>
				</form>
			<?php if(isset($passchgcomp) && $passchgcomp) { echo "<div class='msg'>パスワードを再設定しました。</div>";} ?>
			</div>
		</div>
		<div class="space"></div>
		<div class="head">板リスト</div>
		<div class="line"></div>
		<div id="bbs">
			<div class="box">
				<form name="bbs" method="post" action="">
					<div class="space"></div>
					<?php $i = 0; ?>
					<?php foreach($bbsnames as $bbskey => $val) : ?>
					<?php if($i < (($page - 1) * 20)) { continue; } ?>
					<?php if(($i >= $count) || ($i >= $page * 20)){ break; } $i++; ?>				
					<div class="authority">
						<?php echo $val; echo $form["bbs"]->CheckBox("bbs", $bbskey) ?>
					</div>
					<div class="line"></div>
					<input type="hidden" name="mode" value="bbsselect" />
					<?php endforeach ; ?>
					<div><input type="submit" value="チェックした板でキャップを有効化する" /></div>
				</form>
			<?php if($bbsselectcomp) { echo "<div class='msg'>選択した板でキャップを有効化しました。</div>";} ?>
			</div>
		</div>
		<div class="space"></div>
		<div id="authoritys">
			<div class="box">
				<a href="<?php echo "{$baseurl}/admin/capauthority.php/{$cappass}/1"; ?>">
					キャップ権限設定
				</a>
			</div>
		</div>
		<div id="pagelink">
			<?php if($page > 1) { ?>
			<a href="<?php echo $baseurl; ?>/admin/capctrl.php/<?php echo $cappass; ?>/<?php echo $page - 1; ?>">
				&lt;&lt;前へ
			</a>
			<?php } else { ?>
					&lt;&lt;前へ
			<?php } ?>
			
			<?php if($count > ($page * 20)) { ?>
				<a href="<?php echo $baseurl; ?>/admin/capctrl.php/<?php echo $cappass; ?>/<?php echo $page + 1; ?>">
					次へ&gt;&gt;
				</a>
			<?php } else { ?>
					次へ&gt;&gt;
			<?php } ?>
		</div>
	</div>
	<div id="reload">
		<a href="<?php echo "{$baseurl}/admin/capctrl.php/{$cappass}/{$page}"; ?>">リロード</a>
	</div>
</div>
</center>
</body>
</html>
