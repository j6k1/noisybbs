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

	$ret = SubjectText::getInstance()->Init();
			
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
	
	$ret = ThreadStateList::getInstance()->Init();
	
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}

	$mode = "kakolog";
	
	if(isset($_POST["thread"]) && is_array($_POST["thread"]))
	{
		if(!isset($_POST["mode"]))
		{
			AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
	
		$mode = $_POST["mode"];
	
		if($mode == "kakolog")
		{
			$delthreads = $_POST["thread"];
			
			foreach($delthreads as $key)
			{
				if(!file_exists("../{$setting->bbs}/dat/{$key}.dat"))
				{
					continue;
				}
				
				$dir1 = substr($key, 0, 4);
				$dir2 = substr($key, 0, 5);
				
				if(!file_exists("../{$setting->bbs}/kako/{$dir1}"))
				{
					mkdir("../{$setting->bbs}/kako/{$dir1}", 0777);
				}
	
				if(!file_exists("../{$setting->bbs}/kako/{$dir1}/{$dir2}"))
				{
					mkdir("../{$setting->bbs}/kako/{$dir1}/{$dir2}", 0777);
				}
				
				copy( "../{$setting->bbs}/dat/{$key}.dat" ,
					"../{$setting->bbs}/kako/{$dir1}/{$dir2}/{$key}.dat");
				unlink("../{$setting->bbs}/dat/{$key}.dat");
				chmod("../{$setting->bbs}/kako/{$dir1}/{$dir2}/{$key}.dat", 0666);
				
				$title = SubjectText::getInstance()->getRow($key);
				SubjectText::getInstance()->Delete($key);
				list(, $title) = explode("<>", $title);
				
				KakoLogList::getInstance()->Append($key, $title);
			}
			
			$ret = SubjectText::WriteData();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				exit;
			}
			
			$ret = SubbackHtml::getInstance()->WriteData();
			
			if($ret !== true)
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				exit;
			}

			$ret = IndexHtml::getInstance()->WriteData();

			if($ret !== true)
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				exit;
			}

			$ret = KakoLogList::getInstance()->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				exit;
			}
		}
		else if($mode == "threadstop")
		{
			$stopthreads = $_POST["thread"];
			
			foreach($stopthreads as $key)
			{
				ThreadStateList::getInstance()->SetState($key, "THREADSTOP");
			}
			
			$ret = ThreadStateList::getInstance()->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				exit;
			}
		}
		else if($mode == "threadstart")
		{
			$startthreads = $_POST["thread"];
			
			foreach($startthreads as $key)
			{
				ThreadStateList::getInstance()->ReleaseState($key, "THREADSTOP");
			}
			
			$ret = ThreadStateList::getInstance()->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				exit;
			}
		}
		else if($mode == "titledelete")
		{
			$threads = $_POST["thread"];
			
			foreach($threads as $key)
			{
				$title = SubjectText::getInstance()->getRow($key);
				SubjectText::getInstance()->UpdateTitle($key, "���ځ`��I");

				$datdata = new DatData();
				$datdata->ReadData($key);
				
				$fields = explode("<>", $datdata->data[0]);
				
				if(count($fields) < 4)
				{
					for($i=0; $i < 4; $i++)
					{
						$fields[$i] = '�������Ă܂�';
					}
				}
				
				list($from, $mail, $dateid, $body) = $fields;
				
				$row = "{$from}<>{$mail}<>{$dateid}<>{$body}<>���ځ`��I";
				
				$datdata->UpdateRow(1, $row);
				
				$ret = $datdata->Save();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
				}
				
				$ret = SubjectText::WriteData();
				
				if(ErrInfo::IsErr($ret))
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
					exit;
				}

				$ret = SubbackHtml::getInstance()->WriteData();
				
				if($ret !== true)
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
					exit;
				}
	
				$ret = IndexHtml::getInstance()->WriteData();
	
				if($ret !== true)
				{
					AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/threadmng.php/{$bbs}");
					exit;
				}
			}
		}
		else
		{
			AdminUtil::OutPutErrHtml("�t�H�[����񂪕s���ł��B", "{$baseurl}/admin/login.php");
			exit;
		}
	}

	$threads = SubjectText::getInstance()->getRows();
	$form = new FormUtil();
	$form->addElement("mode", "kakolog", null, array(
			"kakolog" => "�ߋ����O������", 
			"threadstop" => "�X���X�g����", 
			"threadstart" => "�X���X�g��������",
			"titledelete" => "�X���^�C�폜"));
	$form->setElementValue("mode", $mode);

?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/threadmng.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �X���b�h�ꗗ</title>
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
							<?php list( , $title) = explode("<>", $val); ?>
							<a href="<?php echo "{$baseurl}/admin/resedit.php/{$bbs}/{$key}/-50"; ?>">
								<?php echo $title; $i++; ?>
							</a>
							<?php if(ThreadStateList::getInstance()->hasState($key, "THREADSTOP")) { ?>
							<div class="threadstop">�X���b�h�X�g�b�v����Ă��܂��B</div>
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
					<a href="<?php echo $baseurl; ?>/admin/threadmng.php/<?php echo $bbs; ?>/<?php echo $page - 1; ?>">
						&lt;&lt;�O��
					</a>
					<?php } else { ?>
							&lt;&lt;�O��
					<?php } ?>
					
					<?php if(count($threads) > ($page * 20)) { ?>
						<a href="<?php echo $baseurl; ?>/admin/threadmng.php/<?php echo $bbs; ?>/<?php echo $page + 1; ?>">
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
			<a href="<?php echo "{$baseurl}/admin/kakologmng.php/{$bbs}/1"; ?>">
				�ߋ����O�Ǘ���
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
