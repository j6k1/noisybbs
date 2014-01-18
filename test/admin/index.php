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
	
	$ret = CapList::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	if(LoginInfo::getInstance()->aclmode == "admin")
	{
		$bbslist = Util::getBBSList();
	}
	else
	{
		CapInfo::getInstance()->InitCaseAdmin(LoginInfo::getInstance()->cappass);
		$bbslist = CapInfo::getHasBBSList();
	}
	
	if( (LoginInfo::getInstance()->aclmode == "cap") && (count($bbslist) == 0) )
	{
		$none_authority = true;
	}
	else
	{
	
		$none_authority = false;
	}
	
	$form = array();
	$form["bbslist"] = new FormUtil();
	$form["bbslist"]->addElement("bbs", null, null, Util::getBBSNameList($bbslist));
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/index.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - TOP�y�[�W</title>
</head>
<body>
<center>
<div id="container">
	<?php if($none_authority) { ?>
	<div id="message">���̃L���b�v�ɂ́A�������ݒ肳��Ă��܂���B</div>
	<div><a href="<?php echo $baseurl; ?>/admin/login.php">�߂�</a></div>
	<?php exit; } ?>
	<?php if(LoginInfo::getInstance()->aclmode == "admin") { ?>
	<div id="passchg"><a href="<?php echo $baseurl; ?>/admin/adminpasschg.php">�Ǘ��҃p�X���[�h�ύX</a></div>
	<?php } ?>
	<div class="form">
	<div id="bbsselect">
		<div class="head">���ꗗ</div>
			<form name="bbsselect" method="post" action="<?php echo $baseurl; ?>/admin/setting.php">
				<?php echo $form["bbslist"]->SelectBox("bbs", 4); ?>
				<input type="hidden" name="mode" value="bbsselect" />
				<div style="text-align: right;"><input type="submit" value="�I��" /></div>
			</form>
		</div>
		<?php if(LoginInfo::getInstance()->aclmode == "admin") { ?>
		<div class="line"></div>
		<div id="bbsmng"><a href="<?php echo $baseurl; ?>/admin/bbsmng.php">�̍쐬/�폜</a></div>
		<div class="line"></div>
		<div id="capmng"><a href="<?php echo $baseurl; ?>/admin/capmng.php">�L���b�v�Ǘ�</a></div>
		<?php } ?>
	</div>
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></div>
</div>
</center>
</body>
</html>

