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
		AdminUtil::OutPutErrHtml("���쌠��������܂���B", "{$baseurl}/admin/login.php");
		exit;
	}
	
	if(isset($_POST["mode"]) && ($_POST["mode"] == "delete"))
	{
		CapList::getInstance()->Delete($_POST["cap"]);
		$ret = CapList::getInstance()->Save();
		
		if(ErrInfo::IsErr($ret))
		{
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
	}

	$form = array();
	$form["select"] = new FormUtil();
	$form["select"]->addElement("cap", null, null, CapList::getInstance()->getCapNames());

	$form["delete"] = new FormUtil();
	$form["delete"]->addElement("cap", null, null, CapList::getInstance()->getCapNames());
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/capmng.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �L���b�v�Ǘ�</title>
</head>
<body>
<center>
<div id="container">
	<div id="back">
		<span id="index"><a href="<?php echo $baseurl; ?>/admin/index.php">�Ǘ����TOP��</a></span>
		<span id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></span>
	</div>
	<div class="form">
		<div id="capcreate">
			<div class="head">�L���b�v�Ǘ�</div>
			<div class="space"></div>
			<div><a href="<?php echo $baseurl; ?>/admin/capcreate.php">�L���b�v�쐬�y�[�W��</a></div>
			<div class="line"></div>
		</div>
		<div id="capselect">
		<div class="head">�L���b�v�I��</div>
			<form name="capselect" method="post" action="<?php echo $baseurl; ?>/admin/capctrl.php">
				<?php echo $form["select"]->SelectBox("cap", 4); ?>
				<input type="hidden" name="mode" value="select" />
				<div style="text-align: right;"><input type="submit" value="�I��" /></div>
			</form>
		</div>
		<div class="line"></div>
		<div id="capdelete">
			<div class="head">�L���b�v�폜</div>
			<form name="capdelete" method="post" action="">
				<?php echo $form["delete"]->SelectBox("cap", 4); ?>
				<input type="hidden" name="mode" value="delete" />
				<div style="text-align: right;">
					<input type="submit" value="�I�������L���b�v���폜" onClick='return confirm("�{���ɍ폜���܂����H");'/>
				</div>
			</form>
		</div>
	</div>
</div>
</center>
</body>
</html>

