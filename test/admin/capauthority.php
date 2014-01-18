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
	
	if($page < 0)
	{
		$page = 0;
	}

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
	
	$authoritys = CapInfo::getInstance()->getAuthoritys();
	$authorityset = false;

	$form = new FormUtil();
	$form->addElement("authority", $authoritys);
	
	if(isset($_POST["mode"]))
	{
		if($_POST["mode"] == "edit")
		{
			if(!isset($_POST["authority"]) || !is_array($_POST["authority"]))
			{
				$authoritys = array();
			}
			else
			{
				$authoritys = $_POST["authority"];
			}
			
			foreach($_POST["authority"] as $auth)
			{
				if(!in_array($auth, $authoritylist))
				{
					AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
					exit;
				}
			}
			
			$ret = CapList::getInstance()->UpdateAuthority($cappass, $authoritys);
			
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
			$form->setElementValue("authority", $authoritys);
			$authorityset = true;
		}
	}
	
	$authoritylist = array(
			"PASS_TATESUGI",
			"PASS_POSTEDLIMIT",
			"EDIT_BBS_SETTING",
			"EDIT_THREADS",
			"EDIT_RES",
			"EDIT_REGULATION",
			"EDIT_PLUGINS",
			"EDIT_NGWORD",
		);
	$count = count($authoritylist);
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/capctrl.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �z�X�g�K���Ǘ�</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></div>
	<div id="back">
		<a href="<?php echo "{$baseurl}/admin/capctrl.php/{$cappass}/1"; ?>">
			�L���b�v�Ǘ�(<?php echo CapInfo::getInstance()->getName(); ?>) ��
		</a>
	</div>
	<div class="form">
		<div class="head">�������X�g</div>
		<div class="line"></div>
		<div id="authoritys">
			<div class="box">
				<form name="authoritys" method="post" action="">
					<div class="space"></div>
					<?php $i = 0; ?>
					<?php foreach($authoritylist as $val) : ?>
					<?php if($i < (($page - 1) * 20)) { continue; } ?>
					<?php if(($i >= $count) || ($i >= $page * 20)){ break; } $i++; ?>				
					<div class="authority">
						<?php echo $val; echo $form->CheckBox("authority", $val) ?>
					</div>
					<div class="line"></div>
					<input type="hidden" name="mode" value="edit" />
					<?php endforeach ; ?>
					<div><input type="submit" value="�����ݒ�" /></div>
					<div class="msg">���`�F�b�N����������L�������܂��B</div>
				</form>
				<?php if($authorityset) { echo "<div class='msg'>������ݒ肵�܂����B</div>";} ?>
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
	</div>
	<div id="reload">
		<a href="<?php echo "{$baseurl}/admin/capauthority.php/{$cappass}/{$page}"; ?>">�����[�h</a>
	</div>
</div>
</center>
</body>
</html>
