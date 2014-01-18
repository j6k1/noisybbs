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
	
	if($page < 1)
	{
		$page = 1;
	}
	$ret = SettingInfo::getInstance()->Init($bbs);

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}

	$ret = KakoLogList::getInstance()->Init();

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}

	$setting = SettingInfo::getInstance();
	
	$ret = HtmlList::getInstance()->Init();

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$ret = CapList::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	CapInfo::getInstance()->InitCaseAdmin(LoginInfo::getInstance()->cappass);
	
	if( (LoginInfo::getInstance()->aclmode == "cap") && 
		((CapInfo::hasAuthority("EDIT_THREADS") == false) ||
		(CapInfo::hasBBSAuthority($bbs) == false)) )
	{
		AdminUtil::OutPutErrHtml("操作権限がありません。", "{$baseurl}/admin/index.php");
		exit;
	}
	
	if(isset($_POST["thread"]) && is_array($_POST["thread"]))
	{
		$threads = $_POST["thread"];
		
		foreach($threads as $key)
		{
			$dir1 = substr($key, 0, 4);
			$dir2 = substr($key, 0, 5);
			
			$path = "../{$setting->bbs}/kako/{$dir1}/{$dir2}/{$key}.html";
			
			if(!file_exists($path))
			{
				continue;
			}
			
			unlink($path);
			HtmlList::getInstance()->Delete($key);
		}
		
		$ret = HtmlList::getInstance()->Save();

		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/htmlmng.php/{$bbs}");
			exit;
		}	
	}

	$threads = HtmlList::getInstance()->getRows();
	$form = new FormUtil();

?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/threadmng.css" charset="shift_jis" type="text/css">
<title>管理画面 - HTML化済みスレッド管理</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/setting.php/{$bbs}"; ?>">板設定編集へ戻る</a></div>
	<div class="form">
	<div class="head">スレッド一覧</div>
	<div class="line"></div>
		<div id="threads">
			<div class="box">
				<form name="threads" method="post" action="">
					<?php $i=0; ?>
					<?php foreach($threads as $key => $val) : ?>
					<?php if($i < ($page - 1) * 20) { $i++; continue; } ?>
					
					<div class="thread">
						<div>
							<input type="checkbox" name="thread[]" value="<?php echo $key; ?>" />
							<?php $title = $val; ?>
							<?php echo $title; $i++; ?>
						</div>
					</div>
					<div class="line"></div>
					<?php if($i >= $page * 20) break; ?>
					<?php endforeach ; ?>
					<div><input type="submit" value="チェックしたHTMLを削除" /></div>
				</form>
				<div id="pagelink">
					<?php if($page > 1) { ?>
					<a href="<?php echo $baseurl; ?>/admin/htmlmng.php/<?php echo $bbs; ?>/<?php echo $page - 1; ?>">
						&lt;&lt;前へ
					</a>
					<?php } else { ?>
							&lt;&lt;前へ
					<?php } ?>
					
					<?php if(count($threads) > ($page * 20)) { ?>
						<a href="<?php echo $baseurl; ?>/admin/htmlmng.php/<?php echo $bbs; ?>/<?php echo $page + 1; ?>">
							次へ&gt;&gt;
						</a>
					<?php } else { ?>
							次へ&gt;&gt;
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div id="kakomng">
		<span id="kakolog">
			<a href="<?php echo "{$baseurl}/admin/threadmng.php/{$bbs}/1"; ?>">
				スレッド一覧へ
			</a>
		</span>
		<span id="kakohtml">
			<a href="<?php echo "{$baseurl}/admin/kakologmng.php/{$bbs}/1"; ?>">
				過去ログ管理へ
			</a>
		</span>
	</div>
</div>
</center>
</body>
</html>
