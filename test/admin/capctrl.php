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
			AdminUtil::OutPutErrHtml("�t�H�[���f�[�^���s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
		
		Util::Redirect("{$baseurl}/admin/capctrl.php/{$_POST['cap']}/1");
	}
	else
	{
		if(!isset($_SERVER["PATH_INFO"]))
		{
			AdminUtil::OutPutErrHtml("URL�̌`�����s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
			
		$pathinfo = explode("/", $_SERVER["PATH_INFO"]);
		
		if(count($pathinfo) < 3)
		{
			AdminUtil::OutPutErrHtml("URL�̌`�����s���ł��B", "{$baseurl}/admin/login.php");
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
		AdminUtil::OutPutErrHtml("�w�肳�ꂽ�L���b�v�͑��݂��܂���B", "{$baseurl}/admin/capmng.php");
		exit;
	}
	
	if(LoginInfo::getInstance()->aclmode != "admin")
	{
		AdminUtil::OutPutErrHtml("���쌠��������܂���B", "{$baseurl}/admin/login.php");
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
				AdminUtil::OutPutErrHtml("�t�H�[���f�[�^���s���ł��B", "{$baseurl}/admin/login.php");
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
<title>�Ǘ���� - �L���b�v�Ǘ�(<?php echo CapInfo::getInstance()->getName(); ?>)</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/capmng.php"; ?>">�L���b�v�Ǘ���</a></div>
	<div class="form">
		<div class="head">�p�X���[�h�Đݒ�</div>
		<div class="line"></div>
		<div id="passchg">
			<div class="box">
				<form name="passchg" method="post" action="">
					<div class="head"><b>�p�X���[�h</b></div>
					<div class="text"><?php echo $form["pass"]->Password("pass1"); ?></div>
					<?php echo $form["pass"]->ErrMessage("pass1", "<div class='errmsg'>0-9a-zA-Z_-�̂����ꂩ�̕����݂̂ŁA8�`16�����Őݒ肵�Ă��������B</div>"); ?>
					<div class="head"><b>�p�X���[�h(�ē���)</b></div>
					<div class="text""><?php echo $form["pass"]->Password("pass2"); ?></div>
					<?php if(isset($passcmperr) && $passcmperr) { echo "<div class='errmsg'>�p�X���[�h���������ē��͂���Ă��܂���B</div>"; } ?> 
					<input type="hidden" name="mode" value="passchg" />
					<div id="submit"><input type="submit" value="�Đݒ�" /></div>
				</form>
			<?php if(isset($passchgcomp) && $passchgcomp) { echo "<div class='msg'>�p�X���[�h���Đݒ肵�܂����B</div>";} ?>
			</div>
		</div>
		<div class="space"></div>
		<div class="head">���X�g</div>
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
					<div><input type="submit" value="�`�F�b�N�����ŃL���b�v��L��������" /></div>
				</form>
			<?php if($bbsselectcomp) { echo "<div class='msg'>�I�������ŃL���b�v��L�������܂����B</div>";} ?>
			</div>
		</div>
		<div class="space"></div>
		<div id="authoritys">
			<div class="box">
				<a href="<?php echo "{$baseurl}/admin/capauthority.php/{$cappass}/1"; ?>">
					�L���b�v�����ݒ�
				</a>
			</div>
		</div>
		<div id="pagelink">
			<?php if($page > 1) { ?>
			<a href="<?php echo $baseurl; ?>/admin/capctrl.php/<?php echo $cappass; ?>/<?php echo $page - 1; ?>">
				&lt;&lt;�O��
			</a>
			<?php } else { ?>
					&lt;&lt;�O��
			<?php } ?>
			
			<?php if($count > ($page * 20)) { ?>
				<a href="<?php echo $baseurl; ?>/admin/capctrl.php/<?php echo $cappass; ?>/<?php echo $page + 1; ?>">
					����&gt;&gt;
				</a>
			<?php } else { ?>
					����&gt;&gt;
			<?php } ?>
		</div>
	</div>
	<div id="reload">
		<a href="<?php echo "{$baseurl}/admin/capctrl.php/{$cappass}/{$page}"; ?>">�����[�h</a>
	</div>
</div>
</center>
</body>
</html>
