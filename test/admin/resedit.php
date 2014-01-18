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
	$thread = $pathinfo[2];
	
	$option = isset($pathinfo[3]) ? $pathinfo[3] : "";
	
	$ret = SettingInfo::getInstance()->Init($bbs, $thread);

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
		((CapInfo::hasAuthority("EDIT_RES") == false) ||
		 (CapInfo::hasBBSAuthority($bbs) == false)) )
	{
		AdminUtil::OutPutErrHtml("操作権限がありません。", "{$baseurl}/admin/login.php");
		exit;
	}
	
	$reader = new ResReader();
	$ret = $reader->Init($thread);
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$ret = $reader->ParseOption($option);

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}

	$first = false;
	$start = $ret["st"];
	$end = $ret["end"];
	
	$past = ResReader::getPastNums($start, $end);
	$next = ResReader::getNextNums($start, $end);
	
	$datlogdata = new DatLogData();
	$ret = $datlogdata->ReadData($thread);

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	if( isset($_POST["res"]) && (is_array($_POST["res"])) )
	{
		$reslist = $_POST["res"];
		
		foreach($reslist as $res)
		{
			if( ((int)$res) == 1 )
			{
				$title = $reader->datdata->getTitle();
				$row = "あぼ～ん！<>あぼ～ん！<>あぼ～ん！<>あぼ～ん！<>{$title}";
			}
			else
			{
				$row = "あぼ～ん！<>あぼ～ん！<>あぼ～ん！<>あぼ～ん！";
			}
			$reader->datdata->UpdateRow($res, $row);
		}
		
		$ret = $reader->datdata->Save();
		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
	
	}
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/resedit.css" charset="shift_jis" type="text/css">
<title>管理画面 - レス編集</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">ログアウト</a></div>
	<div id="back">
		<a href="<?php echo "{$baseurl}/admin/threadmng.php/{$bbs}"; ?>/1">
		スレッド一覧へ戻る
		</a>
	</div>
	<div class="form">
		<form name="resedit" method="post" action="">
			<div id="pagelink">
				<?php if($start > 1) { ?>
				<a href="<?php echo $baseurl; ?>/admin/resedit.php/<?php echo "{$bbs}/{$thread}/{$past['from']}-{$past['to']}"; ?>">
					&lt;&lt;前へ
				</a>
				<?php } else { ?>
						&lt;&lt;前へ
				<?php } ?>
				&nbsp;
				<?php if($reader->datdata->getRowCount() > $end) { ?>
					<a href="<?php echo $baseurl; ?>/admin/resedit.php/<?php echo "{$bbs}/{$thread}/{$next['from']}-{$next['to']}"; ?>">
						次へ&gt;&gt;
					</a>
				<?php } else { ?>
						次へ&gt;&gt;
				<?php } ?>
			</div>
			<div class="head"><?php echo $reader->datdata->getTitle(); ?></div>
			<div class="space"></div>
			<div class="box">
				<div class="submit">
					<input type="submit" name="submit" value="チェックしたレスをあぼ～ん" />
				</div>
				<?php for($i=($start - 1); $i < $end ; $i++) { ?>
				<input type="checkbox" name="res[<?php echo $i + 1; ?>]" value="<?php echo $i + 1; ?>" />
				<?php echo $reader->getAdminResHtml(3, $i + 1, $datlogdata->data); ?>
				<div class="line"></div>
				<?php } ?>
			</div>
			<div id="pagelink">
				<?php if($start > 1) { ?>
				<a href="<?php echo $baseurl; ?>/admin/resedit.php/<?php echo "{$bbs}/{$thread}/{$past['from']}-{$past['to']}"; ?>">
					&lt;&lt;前へ
				</a>
				<?php } else { ?>
						&lt;&lt;前へ
				<?php } ?>
				&nbsp;
				<?php if($reader->datdata->getRowCount() > $end) { ?>
					<a href="<?php echo $baseurl; ?>/admin/resedit.php/<?php echo "{$bbs}/{$thread}/{$next['from']}-{$next['to']}"; ?>">
						次へ&gt;&gt;
					</a>
				<?php } else { ?>
						次へ&gt;&gt;
				<?php } ?>
			</div>
		</form>
	</div>
</div>
</center>
</body>
</html>

