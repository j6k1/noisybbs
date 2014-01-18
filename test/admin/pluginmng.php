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
		AdminUtil::OutPutErrHtml("URL�̌`�����s���ł��B", "{$baseurl}/admin/login.php");
		exit;
	}
	
	$pathinfo = explode("/", $_SERVER["PATH_INFO"]);
	
	if(count($pathinfo) < 3)
	{
		AdminUtil::OutPutErrHtml("URL�̌`�����s���ł��B", "{$baseurl}/admin/login.php");
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
		((CapInfo::hasAuthority("EDIT_PLUGINS") == false) ||
		(CapInfo::hasBBSAuthority($bbs) == false)) )
	{
		AdminUtil::OutPutErrHtml("���쌠��������܂���B", "{$baseurl}/admin/login.php");
		exit;
	}
	
	$ret = PluginManager::getInstance()->InitPluginMaster();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$enableplugins = PluginManager::getInstance()->getEnablePlugins();
	
	if(ErrInfo::IsErr($enableplugins))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$form = new FormUtil();
	$form->addElement("plugin", $enableplugins, null);
	$pluginlist = PluginManager::getInstance()->getMasterData();
	
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		if(!isset($_POST["plugin"]) || !is_array($_POST["plugin"]))
		{
			$plugins = array();
		}
		else
		{
			$plugins = $_POST["plugin"];
		}
		if(file_exists($setting->pluginlist) == false)
		{
			AdminUtil::OutPutErrHtml("�v���O�C����`�t�@�C��{$setting->pluginlist}�͑��݂��܂���B",
				"{$baseurl}/admin/login.php");
		}
		
		$output = new BBSOutPutStream();
		
		foreach($plugins as $classname)
		{
			if(!array_key_exists($classname, $pluginlist))
			{
				AdminUtil::OutPutErrHtml("����`�̃v���O�C���N���X��{$classname}���w�肳��܂����B", "{$baseurl}/admin/login.php");
				exit;
			}
			$output->PrintStr("{$classname}\n");
		}
		
		$islocked = false;
		$ret = $output->FlushToFile($setting->pluginlist, $islocked);
		
		if(ErrInfo::IsErr($ret))
		{
			if($islocked)
			{
				Util::file_unlock($setting->pluginlist);
			}
			AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
			exit;
		}
		$form->setElementValue("plugin", $plugins);
	}
	
	$count = count($pluginlist);
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/pluginmng.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �v���O�C���Ǘ�</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/setting.php/{$bbs}"; ?>">�ݒ�ҏW�֖߂�</a></div>
	<div class="form">
		<div class="head">�v���O�C�����X�g</div>
		<div class="line"></div>
		<div id="plugins">
			<div class="box">
				<form name="plugins" method="post" action="">
					<div class="space"></div>
					<?php $i = 0; ?>
					<?php foreach($pluginlist as $classname => $name) : ?>
					<?php if($i < (($page - 1) * 20)) { continue; } ?>
					<?php if(($i >= $count) || ($i >= $page * 20)){ break; } $i++; ?>				
					<div class="plugin">
						<?php echo $name; echo $form->CheckBox("plugin", $classname) ?>
					</div>
					<div class="line"></div>
					<?php endforeach ; ?>
					<div><input type="submit" value="�ݒ�" /></div>
					<div class="msg">���`�F�b�N�����v���O�C����L�������܂��B</div>
				</form>
				<div id="pagelink">
					<?php if($page > 1) { ?>
					<a href="<?php echo $baseurl; ?>/admin/pluginmng.php/<?php echo $bbs; ?>/<?php echo $page - 1; ?>">
						&lt;&lt;�O��
					</a>
					<?php } else { ?>
							&lt;&lt;�O��
					<?php } ?>
					
					<?php if($count > ($page * 20)) { ?>
						<a href="<?php echo $baseurl; ?>/admin/pluginmng.php/<?php echo $bbs; ?>/<?php echo $page + 1; ?>">
							����&gt;&gt;
						</a>
					<?php } else { ?>
							����&gt;&gt;
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div id="reload">
		<a href="<?php echo "{$baseurl}/admin/pluginmng.php/{$bbs}/{$page}"; ?>">�����[�h</a>
	</div>
</div>
</center>
</body>
</html>
