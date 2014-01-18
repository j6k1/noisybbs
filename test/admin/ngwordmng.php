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
		((CapInfo::hasAuthority("EDIT_NGWORD") == false) ||
		(CapInfo::hasBBSAuthority($bbs) == false)) )
	{
		AdminUtil::OutPutErrHtml("操作権限がありません。", "{$baseurl}/admin/login.php");
		exit;
	}
	
	$ret = NGWord::getInstance()->Load();
	
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
	$form["edit"]->addElement("ngwordval", NGWord::getInstance()->getRows(), 
		CheckPattern::get()->ptn_valid);
	
	$checkboxval = array();
	$start = ($page - 1) * 10; $end = $page * 10;
	
	$form["edit"]->addElement("ngword", $checkboxval, null);
	
	$form["add"] = new FormUtil();
	$form["add"]->addElement("ngwordval", "", CheckPattern::get()->ptn_valid);
	
	$editmode = "update";
		
	if(isset($_POST["mode"]))
	{
		$editmode = $_POST["mode"];
		$form["edit"]->setElementValue("mode", $editmode);

		if($editmode == "update")
		{
			$ngword = $_POST["ngword"];		
			$form["edit"]->addElement("ngword", $_POST["ngword"]);
			
			$form["edit"]->setElementValue("ngword", $_POST["ngword"]);
			
			if(isset($_POST["ngword"]) && is_array($_POST["ngword"]))
			{
				if( (!isset($_POST["ngwordval"])) || (!is_array($_POST["ngwordval"])) )
				{
					AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
					exit;
				}
				
				$ngwordval = $_POST["ngwordval"];
			
				foreach($ngword as $index)
				{
					$ngwordval[$index] = strtr($ngwordval[$index], array("\r\n" => "\n", "\r" => "\n"));
			
					$form["edit"]->setElementValue("ngwordval", $ngwordval[$index], $index);
				}
				
				$form["edit"]->ValidateAll();
				
				if($form["edit"]->HasError() === false)
				{
					foreach($ngword as $index)
					{
						NGWord::getInstance()->Update($index, $ngwordval[$index]);
					}
					
					$ret = NGWord::getInstance()->Save();
					
					if(ErrInfo::IsErr($ret))
					{
						AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/ngwordmng.php/{$bbs}");
						exit;
					}
				}
			}
		}
		else if($editmode == "delete")
		{
			if(isset($_POST["ngword"]) && is_array($_POST["ngword"]))
			{					
				$ngword = $_POST["ngword"];		
				
				foreach($ngword as $index)
				{
					NGWord::getInstance()->Delete($index);
				}
				
				$ret = NGWord::getInstance()->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/ngwordmng.php/{$bbs}");
					exit;
				}
			}
		}
		else if($editmode == "add")
		{
			if(!isset($_POST["ngwordval"]))
			{
				AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
				exit;
			}
			
			$_POST["ngwordval"] = strtr($_POST["ngwordval"], array("\r\n" => "\n", "\r" => "\n"));
			
			$form["add"]->setElementValue("ngwordval", $_POST["ngwordval"]);
			$form["add"]->ValidateAll();
			if($form["add"]->HasError() === false)
			{
				NGWord::getInstance()->Add($_POST["ngwordval"]);
				$ret = NGWord::getInstance()->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/ngwordmng.php/{$bbs}");
					exit;
				}
				
				$form["add"]->setElementValue("ngwordval", "");
			}
			
		}
		else
		{
			AdminUtil::OutPutErrHtml("フォーム情報が不正です。", "{$baseurl}/admin/login.php");
			exit;
		}
		$form["edit"]->setElementValue("ngwordval", NGWord::getInstance()->getRows());
	}
	$count = NGWord::getInstance()->Count();
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/ngwordmng.css" charset="shift_jis" type="text/css">
<title>管理画面 - NGワード管理</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/setting.php/{$bbs}"; ?>">板設定編集へ戻る</a></div>
	<div class="form">
		<div class="head">NGワード</div>
		<div class="line"></div>
		<div id="ngwords">
			<div class="box">
				<form name="ngwords" method="post" action="">
					<div class="space"></div>
					<?php $find = false; ?>
					<?php for($i=$start; $i < $end ; $i++) { ?>
					<?php if($i >= $count) { break; } $find = true; ?>				
					<div class="ngword">
						<div>
							<span><?php echo $form["edit"]->CheckBox("ngword", "{$i}") ?></span>
							<span><?php echo $form["edit"]->TextArea("ngwordval", 7, null, $i); ?></span>
						</div>
					</div>
					<?php echo $form["edit"]->ErrMessage("ngwordval", "<div class='errmsg'>有効な正規表現ではありません。</div>", $i); ?> 
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
				<div>定義追加(正規表現形式で、改行で区切って指定してください。)</div>
				<form name="ngword" method="post" action="">
					<div class="ngword">
						<?php echo $form["add"]->TextArea("ngwordval", 6); ?>
					</div>
					<?php echo $form["add"]->ErrMessage("ngwordval", "<div class='errmsg'>有効な正規表現ではありません。</div>"); ?> 
					<input type="hidden" name="mode" value="add" />
					<div><input type="submit" value="実行" /></div>
				</form>
				<div id="pagelink">
					<?php if($page > 1) { ?>
					<a href="<?php echo $baseurl; ?>/admin/ngwordmng.php/<?php echo $bbs; ?>/<?php echo $page - 1; ?>">
						&lt;&lt;前へ
					</a>
					<?php } else { ?>
							&lt;&lt;前へ
					<?php } ?>
					
					<?php if($count > ($page * 10)) { ?>
						<a href="<?php echo $baseurl; ?>/admin/ngwordmng.php/<?php echo $bbs; ?>/<?php echo $page + 1; ?>">
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
		<a href="<?php echo "{$baseurl}/admin/ngwordmng.php/{$bbs}/{$page}"; ?>">リロード</a>
	</div>
</div>
</center>
</body>
</html>
