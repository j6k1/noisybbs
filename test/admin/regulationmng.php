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
	
	$bbs  = $pathinfo[1];
	$page = $pathinfo[2];
	
	if($page < 0)
	{
		$page = 0;
	}
	$ret = SettingInfo::getInstance()->Init($bbs);

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}

	$setting = SettingInfo::getInstance();
	
	$ret = CapList::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	CapInfo::getInstance()->InitCaseAdmin(LoginInfo::getInstance()->cappass);
	
	if( (LoginInfo::getInstance()->aclmode == "cap") && 
		((CapInfo::hasAuthority("EDIT_REGULATION") == false) ||
		(CapInfo::hasBBSAuthority($bbs) == false)) )
	{
		AdminUtil::OutPutErrHtml("操作権限がありません。", "{$baseurl}/admin/login.php");
		exit;
	}
	
	$ret = Regulation::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$form = array();
	$form["edit"] = new FormUtil();
	$form["edit"]->addElement("mode", "update", null, array(
			"update" => "更新する", 
			"delete" => "削除する"));
	$form["edit"]->addElement("regulationval", Regulation::getInstance()->getRows(), 
		CheckPattern::get()->ptn_valid);
	
	$checkboxval = array();
	$start = ($page - 1) * 20; $end = $page * 20;
	
	$form["edit"]->addElement("regulation", $checkboxval, null);
	
	$form["add"] = new FormUtil();
	$form["add"]->addElement("regulationval", "", CheckPattern::get()->ptn_valid);
	
	$editmode = "update";
		
	if(isset($_POST["mode"]))
	{
		$editmode = $_POST["mode"];
		$form["edit"]->setElementValue("mode", $editmode);

		if($editmode == "update")
		{
			$regulation = isset($_POST["regulation"]) ? $_POST["regulation"] : array();
			$form["edit"]->setElementValue("regulation", $regulation);
			
			if(isset($_POST["regulation"]) && is_array($_POST["regulation"]))
			{
				if( (!isset($_POST["regulationval"])) || (!is_array($_POST["regulationval"])) )
				{
					AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
					exit;
				}
				
				foreach($_POST["regulationval"] as $line)
				{
					if(preg_match('/[\r\n]/', $line))
					{
						AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
						exit;
					}
				}
				
				$regulationval = $_POST["regulationval"];
			
				foreach($regulation as $index)
				{
					$form["edit"]->setElementValue("regulationval", $regulationval[$index], $index);
				}
				
				$form["edit"]->ValidateAll();
				
				if($form["edit"]->HasError() === false)
				{
					foreach($regulation as $index)
					{
						Regulation::getInstance()->Update($index, $regulationval[$index]);
					}
					
					$ret = Regulation::getInstance()->Save();
					
					if(ErrInfo::IsErr($ret))
					{
						AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/regulationmng.php/{$bbs}");
						exit;
					}
				}
			}
		}
		else if($editmode == "delete")
		{
			if(isset($_POST["regulation"]) && is_array($_POST["regulation"]))
			{					
				$regulation = $_POST["regulation"];		
				
				foreach($regulation as $index)
				{
					Regulation::getInstance()->Delete($index);
				}
				
				$ret = Regulation::getInstance()->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/regulationmng.php/{$bbs}");
					exit;
				}
			}
		}
		else if($editmode == "add")
		{
			if( (!isset($_POST["regulationval"])) || 
				(preg_match('/[\r\n]/', $_POST["regulationval"])) )
			{
				AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
				exit;
			}
			
			$form["add"]->setElementValue("regulationval", $_POST["regulationval"]);
			$form["add"]->ValidateAll();
			if($form["add"]->HasError() === false)
			{
				Regulation::getInstance()->Add($_POST["regulationval"]);
				$ret = Regulation::getInstance()->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/regulationmng.php/{$bbs}");
					exit;
				}
				
				$form["add"]->setElementValue("regulationval", "");
			}
			
		}
		else
		{
			AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
			exit;
		}
		$form["edit"]->setElementValue("regulationval", Regulation::getInstance()->getRows());
	}
	$count = Regulation::getInstance()->Count();
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/regulationmng.css" charset="shift_jis" type="text/css">
<title>管理画面 - ホスト規制管理</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/setting.php/{$bbs}"; ?>">板設定編集へ戻る</a></div>
	<div class="form">
		<div class="head">規制リスト</div>
		<div class="line"></div>
		<div id="regulations">
			<div class="box">
				<form name="regulations" method="post" action="">
					<div class="space"></div>
					<?php $find = false; ?>
					<?php for($i=$start; $i < $end ; $i++) { ?>
					<?php if($i >= $count) { break; } $find = true; ?>				
					<div class="regulation">
						<div>
							<span class="checkbox"><?php echo $form["edit"]->CheckBox("regulation", "{$i}") ?></span>
							<span class="text"><?php echo $form["edit"]->Text("regulationval", null, $i); ?></span>
						</div>
					</div>
					<?php echo $form["edit"]->ErrMessage("regulationval", "<div class='errmsg'>有効な正規表現ではありません。</div>", $i); ?> 
					<div class="line"></div>
					<?php } if($find) { ?>
					<div>チェックした定義を
						<?php echo $form["edit"]->SelectBox("mode", 5); ?>
					</div>
					<div><input type="submit" value="実行" /></div>
					<?php } ?>
				</form>
				<div class="space"></div>
				<div class="line"></div>
				<div>定義追加(正規表現形式で指定してください。)</div>
				<form name="regulation" method="post" action="">
					<div class="regulation">
						<span class="text"><?php echo $form["add"]->Text("regulationval"); ?></span>
					</div>
					<?php echo $form["add"]->ErrMessage("regulationval", "<div class='errmsg'>有効な正規表現ではありません。</div>"); ?> 
					<input type="hidden" name="mode" value="add" />
					<div><input type="submit" value="実行" /></div>
				</form>
				<div id="pagelink">
					<?php if($page > 1) { ?>
					<a href="<?php echo $baseurl; ?>/admin/regulationmng.php/<?php echo $bbs; ?>/<?php echo $page - 1; ?>">
						&lt;&lt;前へ
					</a>
					<?php } else { ?>
							&lt;&lt;前へ
					<?php } ?>
					
					<?php if($count > ($page * 20)) { ?>
						<a href="<?php echo $baseurl; ?>/admin/regulationmng.php/<?php echo $bbs; ?>/<?php echo $page + 1; ?>">
							次へ&gt;&gt;
						</a>
					<?php } else { ?>
							次へ&gt;&gt;
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div id="reload">
		<a href="<?php echo "{$baseurl}/admin/regulationmng.php/{$bbs}/{$page}"; ?>">リロード</a>
	</div>
</div>
</center>
</body>
</html>
