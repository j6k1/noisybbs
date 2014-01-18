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

	$setting = SettingInfo::getInstance();
	
	$ret = KakoLogList::getInstance()->Init();

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
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
		AdminUtil::OutPutErrHtml("���쌠��������܂���B", "{$baseurl}/admin/index.php");
		exit;
	}
	
	$mode = "tohtml";
	
	if(isset($_POST["thread"]) && is_array($_POST["thread"]))
	{
		if(!isset($_POST["mode"]))
		{
			AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
	
		$mode = $_POST["mode"];
	
		if($mode == "tohtml")
		{
			$threads = $_POST["thread"];
			
			foreach($threads as $key)
			{
				$dir1 = substr($key, 0, 4);
				$dir2 = substr($key, 0, 5);
				
				$path = "../{$setting->bbs}/kako/{$dir1}/{$dir2}/{$key}.dat";
				
				if(!file_exists($path))
				{
					continue;
				}
				
				$data = file_get_contents($path);
				$data = explode("\n", $data);
				array_pop($data);
				
				$rescnt = count($data);
				$kakologlist = KakoLogList::getInstance()->getRows();
				
				$title = $kakologlist[$key];
				
				$ret = HtmlList::getInstance()->genHtml($bbs, $key);
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/kakologmng.php/{$bbs}");
					exit;
				}
				
				HtmlList::getInstance()->Append($key, "{$title}");
			}
			
			$ret = HtmlList::getInstance()->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/kakologmng.php/{$bbs}");
				exit;
			}
		}
		else if($mode == "delete")
		{
			$threads = $_POST["thread"];
			
			foreach($threads as $key)
			{
				$dir1 = substr($key, 0, 4);
				$dir2 = substr($key, 0, 5);
				
				$path = "../{$setting->bbs}/kako/{$dir1}/{$dir2}/{$key}.dat";
				
				if(!file_exists($path))
				{
					continue;
				}
				
				unlink($path);
				KakoLogList::getInstance()->Delete($key);
			}
			
			$ret = KakoLogList::getInstance()->Save();

			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/kakologmng.php/{$bbs}");
				exit;
			}	
		}
		else
		{
			AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
	}

	$threads = KakoLogList::getInstance()->getRows();
	$form = new FormUtil();
	$form->addElement("mode", "kakolog", null, array(
			"delete" => "�폜����", 
			"tohtml" => "HTML������"));
	$form->setElementValue("mode", $mode);

?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/threadmng.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �ߋ����O�Ǘ�</title>
</head>
<body>
<center>
<div id="container">
	<div id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></div>
	<div id="back"><a href="<?php echo "{$baseurl}/admin/setting.php/{$bbs}"; ?>">�ݒ�ҏW�֖߂�</a></div>
	<div class="form">
	<div class="head">�X���b�h�ꗗ</div>
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
							<?php if(HtmlList::getInstance()->Contain($key)) { ?>
							<div class="comphtml">html���ς݂̃X���b�h�ł��B</div>
							<?php } ?>
						</div>
					</div>
					<div class="line"></div>
					<?php if($i >= $page * 20) break; ?>
					<?php endforeach ; ?>
					<div>�`�F�b�N�����X����
						<?php echo $form->SelectBox("mode", 5); ?>
					</div>
					<div><input type="submit" value="���s" /></div>
				</form>
				<div id="pagelink">
					<?php if($page > 1) { ?>
					<a href="<?php echo $baseurl; ?>/admin/kakologmng.php/<?php echo $bbs; ?>/<?php echo $page - 1; ?>">
						&lt;&lt;�O��
					</a>
					<?php } else { ?>
							&lt;&lt;�O��
					<?php } ?>
					
					<?php if(count($threads) > ($page * 20)) { ?>
						<a href="<?php echo $baseurl; ?>/admin/kakologmng.php/<?php echo $bbs; ?>/<?php echo $page + 1; ?>">
							����&gt;&gt;
						</a>
					<?php } else { ?>
							����&gt;&gt;
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div id="kakomng">
		<span id="kakolog">
			<a href="<?php echo "{$baseurl}/admin/threadmng.php/{$bbs}/1"; ?>">
				�X���b�h�ꗗ��
			</a>
		</span>
		<span id="kakohtml">
			<a href="<?php echo "{$baseurl}/admin/htmlmng.php/{$bbs}/1"; ?>">
				�ߋ����OHTML�Ǘ���
			</a>
		</span>
	</div>
</div>
</center>
</body>
</html>
