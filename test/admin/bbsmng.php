<?php
	session_start();
	chdir("../");
	umask(0);
	
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
	
	if(LoginInfo::getInstance()->aclmode != "admin")
	{
		AdminUtil::OutPutErrHtml("���쌠��������܂���B", "{$baseurl}/admin/login.php");
		exit;
	}
	
	$bbslist = Util::getBBSList();
	
	$form = array();
	$form["create"] = new FormUtil();
	$form["create"]->addElement("bbskey", "", '/^[\.a-zA-Z0-9\-_]+\z/');
	$form["create"]->addElement("bbsname", "", CheckPattern::get()->notnullstr);

	$form["delete"] = new FormUtil();
	$form["delete"]->addElement("bbs", null, null, Util::getBBSNameList($bbslist));
	
	if(isset($_POST["mode"]) && ($_POST["mode"] == "delete"))
	{
		if(!isset($_POST["bbs"]))
		{
			AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
		
		$bbs = $_POST["bbs"];
		
		BBSList::getInstance()->Delete($bbs);
		$ret = BBSList::getInstance()->Save();
		
		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
		
		CapList::getInstance()->DeleteBBS($bbs);
		
		$ret = CapList::getInstance()->Save();
		
		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
		
		$ret = Util::RemoveFiles("../{$bbs}");
		
		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
		
		$ret = rmdir("../{$bbs}");
		
		if($ret == false)
		{
			AdminUtil::OutPutErrHtml("../{$bbs}�̍폜�Ɏ��s���܂����B", "{$baseurl}/admin/login.php");
			exit;
		}
		$bbslist = Util::getBBSList();
		$form["delete"]->addElement("bbs", null, null, Util::getBBSNameList($bbslist));
	}
	else if(isset($_POST["mode"]) && ($_POST["mode"] == "create"))
	{
		if(!isset($_POST["bbskey"]) || !isset($_POST["bbsname"]))
		{
			AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
		
		$bbs = $_POST["bbskey"];
		$bbsname = $_POST["bbsname"];
		
		if(preg_match('/(^test$)|(^_service$)/', $bbs))
		{
			AdminUtil::OutPutErrHtml("�L�[�Ɂutest�v����т́u_service�v�͎g�p�ł��܂���B", "{$baseurl}/admin/login.php");
			exit;
		}
		
		if(in_array($bbs, $bbslist))
		{
			AdminUtil::OutPutErrHtml("�w�肵���L�[�͊��Ɏg���Ă��܂��B", "{$baseurl}/admin/login.php");
			exit;
		}
		
		$form["create"]->setElementValue("bbskey", $bbs);
		$form["create"]->setElementValue("bbsname", $bbsname);
		
		$form["create"]->ValidateAll();
		
		if($form["create"]->HasError() == false)
		{
			if(mkdir("../{$bbs}", 0755) == false)
			{
				AdminUtil::OutPutErrHtml("../{$bbs}�̍쐬�Ɏ��s���܂����B", "{$baseurl}/admin/login.php");
				exit;
			}
			chmod("../{$bbs}", 0777);
			BBSList::getInstance()->Append($bbs);
			$ret = BBSList::getInstance()->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
				exit;
			}
			
			$ret = Util::ExtractFiles("system/package", "../{$bbs}");

			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
				exit;
			}
			
			system("copy -R system/package/* ../{$bbs}/");
			chmod("../{$bbs}/1001.txt", 0666);
			chmod("../{$bbs}/head.txt", 0666);
			chmod("../{$bbs}/analysis.txt", 0666);
			chmod("../{$bbs}/SETTING.TXT", 0666);
			chmod("../{$bbs}/subject.txt", 0666);
			
			chmod("../{$bbs}/system/sysdata/denyhosts.cgi", 0666);
			chmod("../{$bbs}/system/sysdata/envlist.txt", 0666);
			chmod("../{$bbs}/system/sysdata/ngword.txt", 0666);
			chmod("../{$bbs}/system/sysdata/pluginlist.cgi", 0666);

			chmod("../{$bbs}/system/sysdata/logs/rentouchk.cgi", 0666);
			
			$ret = FileReader::Read("../{$bbs}/SETTING.TXT");
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
				exit;
			}
			
			$settingtxt = mb_convert_encoding($ret, "UTF-8", "SJIS");

			$bbsid = Util::gen_id($bbs);
			$settingtxt = "{$bbs}@{$bbsid}\n" . $settingtxt;
			
			Util::updateenv("BBS_TITLE", 
				mb_convert_encoding($bbsname, "UTF-8", "SJIS"), $settingtxt);
			
			$data = mb_convert_encoding($settingtxt, "SJIS", "UTF-8");
			
			$output = new BBSOutPutStream();
			
			$output->PrintStr($data);
			
			$islocked = false;
			$ret = $output->FlushToFile("../{$bbs}/SETTING.TXT", $islocked);

			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
				exit;
			}

			$bbslist = Util::getBBSList();
			$form["delete"]->addElement("bbs", null, null, Util::getBBSNameList($bbslist));
		}
	}
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/bbsmng.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �̍쐬/�폜</title>
</head>
<body>
<center>
<div id="container">
	<div id="back">
		<span id="index"><a href="<?php echo $baseurl; ?>/admin/index.php">�Ǘ����TOP��</a></span>
		<span id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></span>
	</div>
	<div class="form">
		<div id="bbscreate">
			<div class="head">�쐬</div>
			<div class="space"></div>
			<div class="line"></div>
			<form name="bbscreate" method="post" action="">
				<div class="head"><b>����</b></div>
				<div class="text"><?php echo $form["create"]->Text("bbsname"); ?></div>
				<?php echo $form["create"]->ErrMessage("bbsname", "<div class='errmsg'>���̂����͂���Ă��܂���B</div>"); ?>
				<div class="space"></div>
				<div class="head"><b>�L�[</b></div>
				<div class="text"><?php echo $form["create"]->Text("bbskey"); ?></div>
				<?php echo $form["create"]->ErrMessage("bbskey", "<div class='errmsg'>0-9a-zA-Z_-�̂����ꂩ�̕����݂̂��g���܂��B</div>"); ?>
				<input type="hidden" name="mode" value="create" />
				<div style="text-align: right;">
					<input type="submit" value="�쐬" />
				</div>
			</form>
			<div class="line"></div>
		</div>
		<div id="bbsdelete">
			<div class="head">�폜</div>
			<form name="capselect" method="post" action="">
				<?php echo $form["delete"]->SelectBox("bbs", 4); ?>
				<input type="hidden" name="mode" value="delete" />
				<div style="text-align: right;">
					<input type="submit" value="�I���������폜" onClick='return confirm("�{���ɍ폜���܂����H");'/>
				</div>
			</form>
		</div>
	</div>
	<div id="reload">
		<a href="<?php echo "{$baseurl}/admin/bbsmng.php"; ?>">�����[�h</a>
	</div>
</div>
</center>
</body>
</html>

