<?php
	session_start();
	chdir("../");
	
	require_once("inclueds.php");
	$baseurl = Util::getBaseUrl();
		
	class BBSSetting
	{
		function BBSSetting()
		{
			$this->bbs = $bbs;
		}
		
		function setValues($keys, &$form, $postdata)
		{
			$baseurl = Util::getBaseUrl();
			foreach($keys as $key)
			{
				$val = isset($postdata[$key]) ? $postdata[$key] : "";
				
				if(is_array($val))
				{
					AdminUtil::OutPutErrHtml("�t�H�[���f�[�^���s���ł��B",
						 "{$baseurl}/admin/setting.php");
					exit;
				}
				
				$form->setElementValue($key, $val);
			}
		}
		
		function Update($values)
		{
			$setting = SettingInfo::getInstance();
			$baseurl = Util::getBaseUrl();
		
			foreach($values as $key => $val)
			{
				if($setting->Update($key, 
					mb_convert_encoding($val, "UTF-8", "SJIS")) == false)
				{
					$bbs = SettingInfo::getInstance()->bbs;
					AdminUtil::OutPutErrHtml("{$key}�̍X�V�Ɏ��s���܂����B", 
						"{$baseurl}/admin/setting.php/{$bbs}");
					exit;
				}
			}
			
			return true;
		}
		
		function Save()
		{
			$setting = SettingInfo::getInstance();
			$baseurl = Util::getBaseUrl();
			
			$ret = $setting->Save();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, 
					"{$baseurl}/admin/setting.php/{$_POST['bbs']}");
				exit;
			}
			
			return true;
		}
	}
	
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
	
	CapInfo::getInstance()->InitCaseAdmin(LoginInfo::getInstance()->cappass);
	
	if(!isset($_POST["bbs"]))
	{
		if(!isset($_SERVER["PATH_INFO"]))
		{
			AdminUtil::OutPutErrHtml("���w�肳��Ă��܂���B", "{$baseurl}/admin/index.php");
			exit;
		}
		
		$pathinfo = explode("/", $_SERVER["PATH_INFO"]);
		
		if(!isset($pathinfo[1]))
		{
			AdminUtil::OutPutErrHtml("���w�肳��Ă��܂���B", "{$baseurl}/admin/index.php");
			exit;
		}
		$bbs = $pathinfo[1];
	}
	else
	{
		$bbs = $_POST["bbs"];
	}
		
	if(LoginInfo::getInstance()->aclmode == "admin")
	{
		if(in_array($bbs, Util::getBBSList()) == false)
		{
			AdminUtil::OutPutErrHtml("���݂��Ȃ��L�[���w�肳��܂����B", "{$baseurl}/admin/index.php");
			exit;
		}
	}
	else
	{
		if(CapInfo::hasBBSAuthority($bbs) == false)
		{
			AdminUtil::OutPutErrHtml("���̔̑��쌠��������܂���B", "{$baseurl}/admin/index.php");
			exit;
		}
	}
	
	$ret = SettingInfo::getInstance()->Init($bbs);

	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$setting = SettingInfo::getInstance();
	
	$ret = SubjectText::getInstance()->Init();
			
	if(ErrInfo::IsErr($ret))
	{
		AdminUtil::OutPutErrHtml($ret->sysmsg, "{$baseurl}/admin/login.php");
		exit;
	}
	
	$form = array();
	$form["system"] = new FormUtil();
	$form["design"] = new FormUtil();
	$form["respost"] = new FormUtil();
	$form["threcreate"] = new FormUtil();
	$form["head"] = new FormUtil();
	$form["analysis"] = new FormUtil();
	$form["tailres"] = new FormUtil();

	$form["system"]->addElement("BBS_READ_SCRIPT", $setting->BBS_READ_SCRIPT,
		 	array('/^js\z/', '/^php\z/'), 
			array("js" => "js", "php" => "php"));
	$form["system"]->addElement("BBS_THREAD_CACHE", $setting->BBS_THREAD_CACHE,
		array(CheckPattern::get()->nullstring, '/^1\z/'), null);
	$form["system"]->addElement("BBS_TITLE", $setting->BBS_TITLE, null);
	
	$form["system"]->addElement("RES_MAX", $setting->RES_MAX, 
		CheckPattern::get()->number, null);
	$form["system"]->addElement("BBS_DISP_IP", $setting->BBS_DISP_IP, 
		array(CheckPattern::get()->nullstring,
			  CheckPattern::get()->checked), null);
	$form["system"]->addElement("AIRPHONEIP_CHK", $setting->AIRPHONEIP_CHK, 
		array(CheckPattern::get()->nullstring, '/^1\z/'), null);
	$form["system"]->addElement("RES_INTERVAL", $setting->RES_INTERVAL, 
		CheckPattern::get()->number, null);
	$form["system"]->addElement("SAMBACOUNT", $setting->SAMBACOUNT, 
		CheckPattern::get()->number, null);
	$form["system"]->addElement("SAMBATIME", $setting->SAMBATIME, 
		CheckPattern::get()->number, null);
	$form["system"]->addElement("POSTEDLIMIT_TYPE", $setting->POSTEDLIMIT_TYPE, 
	 	array('/^intervalonly\z/', '/^sambalike\z/'), 
		array("intervalonly" => "intervalonly", "sambalike" => "sambalike"));
		
	$form["design"]->addElement("BBS_TITLE_COLOR", $setting->BBS_TITLE_COLOR, Util::valid_css_color_regexp());
	$form["design"]->addElement("BBS_TITLE_PICTURE", $setting->BBS_TITLE_PICTURE, null);
	$form["design"]->addElement("BBS_BG_COLOR", $setting->BBS_BG_COLOR, Util::valid_css_color_regexp());
	$form["design"]->addElement("BBS_BG_PICTURE", $setting->BBS_BG_PICTURE, null);
	$form["design"]->addElement("BBS_TEXT_COLOR", $setting->BBS_TEXT_COLOR, Util::valid_css_color_regexp());
	$form["design"]->addElement("BBS_LINK_COLOR", $setting->BBS_LINK_COLOR, Util::valid_css_color_regexp());
	$form["design"]->addElement("BBS_ALINK_COLOR", $setting->BBS_ALINK_COLOR, Util::valid_css_color_regexp());
	$form["design"]->addElement("BBS_VLINK_COLOR", $setting->BBS_VLINK_COLOR, Util::valid_css_color_regexp());
	
	$form["respost"]->addElement("BBS_READONLY", $setting->BBS_READONLY, array(
		CheckPattern::get()->nullstring, 
		'/^1\z/'), null);
	$form["respost"]->addElement("BBS_NONAME_NAME", $setting->BBS_NONAME_NAME, null);
	$form["respost"]->addElement("THRE_STOP_NAME", $setting->THRE_STOP_NAME, null);
	$form["respost"]->addElement("NULLMSG_NG", $setting->NULLMSG_NG, 
		array(CheckPattern::get()->nullstring,
			  CheckPattern::get()->checked), null);
	
	$form["respost"]->addElement("BBS_LINE_NUMBER", $setting->BBS_LINE_NUMBER, 
		CheckPattern::get()->number, null);
	$form["respost"]->addElement("BBS_SUBJECT_COUNT", $setting->BBS_SUBJECT_COUNT, 
		CheckPattern::get()->number, null);
	$form["respost"]->addElement("BBS_NAME_COUNT", $setting->BBS_NAME_COUNT, 
		CheckPattern::get()->number, null);
	$form["respost"]->addElement("BBS_MAIL_COUNT", $setting->BBS_MAIL_COUNT, 
		CheckPattern::get()->number, null);
	$form["respost"]->addElement("BBS_MESSAGE_COUNT", $setting->BBS_MESSAGE_COUNT, array(
		CheckPattern::get()->number,
		CheckPattern::get()->nullstring),
		 null);
	$form["respost"]->addElement("NANASHI_CHECK", $setting->NANASHI_CHECK, array(
		CheckPattern::get()->nullstring, 
		'/^1\z/'), null);
	$form["respost"]->addElement("BBS_SLIP", $setting->BBS_SLIP, 
		array(CheckPattern::get()->nullstring,
			  CheckPattern::get()->checked), null);
	
	$form["threcreate"]->addElement("BBS_THREAD_TATESUGI", 
		$setting->BBS_THREAD_TATESUGI, 
		CheckPattern::get()->number, null);
	$form["threcreate"]->addElement("THRECRE_MAX", 
		$setting->THRECRE_MAX, 
		CheckPattern::get()->number, null);
	
	$form["head"]->addElement("headtxt", file_get_contents("../{$bbs}/head.txt"), null);
	
	if(!file_exists("../{$bbs}/analysis.txt"))
	{
		fclose(fopen("../{$bbs}/analysis.txt", "w"));
		chmod("../{$bbs}/analysis.txt", 0666);
	}
	
	$form["analysis"]->addElement("analysistxt", file_get_contents("../{$bbs}/analysis.txt"), null);

	$form["tailres"]->addElement("tailres",  $setting->maxres_msg, null);
	
	$indexupdate = false;
	
	if(isset($_POST["mode"]))
	{
		switch($_POST["mode"])
		{
			case "system":
				BBSSetting::setValues($form["system"]->getElementKeys(),
					$form["system"], $_POST);
				$form["system"]->ValidateAll();
				
				if($form["system"]->HasError() == false)
				{
					BBSSetting::Update($form["system"]->getAllElementValue());
					BBSSetting::Save();
				}
				
			break;

			case "design":
				BBSSetting::setValues($form["design"]->getElementKeys(),
					$form["design"], $_POST);
				$form["design"]->ValidateAll();

				if($form["design"]->HasError() == false)
				{
					BBSSetting::Update($form["design"]->getAllElementValue());
					BBSSetting::Save();
				}
				
			break;
			
			case "respost":
				BBSSetting::setValues($form["respost"]->getElementKeys(),
					$form["respost"], $_POST);
				$form["respost"]->ValidateAll();

				if($form["respost"]->HasError() == false)
				{
					BBSSetting::Update($form["respost"]->getAllElementValue());
					BBSSetting::Save();
				}
				
			break;
			
			case "threcreate":
				BBSSetting::setValues($form["threcreate"]->getElementKeys(),
					$form["threcreate"], $_POST);
				$form["threcreate"]->ValidateAll();

				if($form["threcreate"]->HasError() == false)
				{
					BBSSetting::Update($form["threcreate"]->getAllElementValue());
					BBSSetting::Save();
				}
				
			break;
			
			case "header":
				$txt = isset($_POST["headtxt"]) ? $_POST["headtxt"] : "";
				$islocked = false;
				$output = new BBSOutPutStream();
				$output->PrintStr($txt);
				$ret = $output->FlushToFile("../{$bbs}/head.txt", $islocked);

				if(ErrInfo::IsErr($ret))
				{
					if($islocked)
					{
						Util::file_unlock("../{$bbs}/head.txt");
					}
					AdminUtil::OutPutErrHtml($ret->sysmsg, 
						"{$baseurl}/admin/setting.php/{$bbs}");
				}
				$form["head"]->setElementValue("headtxt", $txt);
			break;
			
			case "analysis":
				$txt = isset($_POST["analysistxt"]) ? $_POST["analysistxt"] : "";
				$islocked = false;
				$output = new BBSOutPutStream();
				$output->PrintStr($txt);
				$ret = $output->FlushToFile("../{$bbs}/analysis.txt", $islocked);

				if(ErrInfo::IsErr($ret))
				{
					if($islocked)
					{
						Util::file_unlock("../{$bbs}/analysis.txt");
					}
					AdminUtil::OutPutErrHtml($ret->sysmsg, 
						"{$baseurl}/admin/setting.php/{$bbs}");
				}
				$form["head"]->setElementValue("analysistxt", $txt);
			break;
			
			case "tailres":
				$txt = isset($_POST["tailres"]) ? $_POST["tailres"] : "";
				$islocked = false;
				$output = new BBSOutPutStream();
				$output->PrintStr($txt);
				if(!file_exists("../{$bbs}/1001.txt"))
				{
					fclose(fopen("../{$bbs}/1001.txt", "w"));
					chmod("../{$bbs}/1001.txt", 0666);
				}
				$ret = $output->FlushToFile("../{$bbs}/1001.txt", $islocked);

				if(ErrInfo::IsErr($ret))
				{
					if($islocked)
					{
						Util::file_unlock("../{$bbs}/1001.txt");
					}
					AdminUtil::OutPutErrHtml($ret->sysmsg,
						"{$baseurl}/admin/setting.php/{$bbs}");
				}
				$form["tailres"]->setElementValue("tailres", $txt);
			break;
			
			case "indexhtml":
			$ret = IndexHtml::getInstance()->WriteData();

			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, 
					"{$baseurl}/admin/setting.php/{$bbs}");
			}
			
			$ret = SubbackHtml::getInstance()->WriteData();
			
			if(ErrInfo::IsErr($ret))
			{
				AdminUtil::OutPutErrHtml($ret->sysmsg, 
					"{$baseurl}/admin/setting.php/{$bbs}");
			}
			
			$indexupdate = true;
			
			break;
			default:
			
		}
	}
?>
<html>
<head>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<link rel="stylesheet" href="<?php echo $baseurl; ?>/admin/css/setting.css" charset="shift_jis" type="text/css">
<title>�Ǘ���� - �̐ݒ�</title>
</head>
<body>
<center>
<div id="container">
	<div id="back">
		<span id="index"><a href="<?php echo $baseurl; ?>/admin/index.php">�Ǘ����TOP��</a></span>
		<span id="logout"><a href="<?php echo $baseurl; ?>/admin/logout.php">���O�A�E�g</a></span>
	</div>
	<div id="form">
		<?php if( (LoginInfo::getInstance()->aclmode == "admin") || 
			(CapInfo::hasAuthority("EDIT_BBS_SETTING")) ) { ?>
		<div class="space"></div>
		<div class="head">�V�X�e���ݒ�</div>
		<div class="line"></div>
		<div id="system">
			<div class="box">
				<form name="system" method="post" action="">
					<div class="setting-key-name"><div class="setting-key-label">BBS_TITLE</div><div class="setting-key-summary"><div>(�̖��O)</div></div></div>
					<div class="input"><?php echo $form["system"]->Text("BBS_TITLE"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">RES_MAX</div><div class="setting-key-summary"><div>(�X���b�h�̃��X�ő吔)</div></div></div>
					<div class="input"><?php echo $form["system"]->Text("RES_MAX"); ?></div>
					<?php echo $form["system"]->ErrMessage("RES_MAX", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">RES_INTERVAL</div><div class="setting-key-summary"><div>(�ŏ����e�Ԋu �����ꖢ�����ƋK������܂�)</div></div></div>
					<div class="input"><?php echo $form["system"]->Text("RES_INTERVAL"); ?></div>
					<?php echo $form["system"]->ErrMessage("RES_INTERVAL", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">SAMBACOUNT</div><div class="setting-key-summary"><div>(�K�������܂ł̉� ��POSTEDLIMIT_TYPE��sambalike�̂Ƃ��ARES_INTERVAL�����̊Ԋu�ł̓��e�����̉񐔌J��Ԃ����ƋK������܂��BSAMBATIME���Ԍo�Ɖ񐔂̓��Z�b�g����܂��B)</div></div></div>
					<div class="input"><?php echo $form["system"]->Text("SAMBACOUNT"); ?></div>
					<?php echo $form["system"]->ErrMessage("SAMBACOUNT", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-summary">SAMBATIME</div><div class="setting-key-summary"><div>(�������݋K������������܂ł̃J�E���^���N���A�����܂ł̎��� ���P�ʂ͎�)</div></div></div>
					<div class="input"><?php echo $form["system"]->Text("SAMBATIME"); ?></div>
					<?php echo $form["system"]->ErrMessage("SAMBATIME", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-summary">POSTEDLIMIT_TYPE</div><div class="setting-key-summary"><div>(�������݋K������ ��intervalonly���O�񏑂����݂���RES_INTERVAL�b�����ŏ������܂��Ə������߂Ȃ�������ȏ�Ȃ��x�������݋K�����������Ă��������߂܂��Bsambalike�Ȃ�J�E���^�Ɉ��񐔈��������������莞�ԋK������܂��B)</div></div></div>
					<div class="select">
					<?php echo $form["system"]->SelectBox("POSTEDLIMIT_TYPE", 5); ?>
					</div>
					<?php echo $form["system"]->ErrMessage("POSTEDLIMIT_TYPE", "<div class='errmsg'>����`�̒l�ł��B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_DISP_IP</div><div class="setting-key-summary"><div>(IP��\�����邩�ۂ�)</div></div></div>
					<div class="checkbox"><?php echo $form["system"]->CheckBox("BBS_DISP_IP", "checked"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">AIRPHONEIP_CHK</div><div class="setting-key-summary"><div>(air-phone��IP�ш�Ŕ��ʂ��邩�ǂ���)</div></div></div>
					<div class="checkbox"><?php echo $form["system"]->CheckBox("AIRPHONEIP_CHK", "1"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_READ_SCRIPT</div><div class="setting-key-summary"><div>(�X���b�h��ǂނ��߂̃X�N���v�g�Ƃ���php�𗘗p���邩js�𗘗p���邩)</div></div></div>
					<div class="select">
					<?php echo $form["system"]->SelectBox("BBS_READ_SCRIPT", 5); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_THREAD_CACHE</div><div class="setting-key-summary"><div>(php�ŃX���b�h��ǂޏꍇ�ɃL���b�V���@�\��L���ɂ��邩 ���u���E�U�̒ʐM���ׂ��y���Ȃ�)</div></div></div>
					<div class="checkbox"><?php echo $form["system"]->CheckBox("BBS_THREAD_CACHE", "1"); ?></div>
					<div class="line"></div>
					<input type="hidden" name="mode" value="system">
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
		</div>
		<div class="space"></div>
		<div class="head">�f�U�C���ݒ�</div>
		<div class="line"></div>
		<div id="design">
			<div class="box">
				<form name="design" method="post" action="">
					<div class="setting-key-name"><div class="setting-key-label">BBS_TITLE_COLOR</div><div class="setting-key-summary"><div>(�f�����̕����F)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_TITLE_COLOR"); ?></div>
					<?php echo $form["design"]->ErrMessage("BBS_TITLE_COLOR", "<div class='errmsg'>�F�̌`�����s���ł��B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_TITLE_PICTURE</div><div class="setting-key-summary"><div>(�f���̃o�i�[�摜��URL)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_TITLE_PICTURE"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_BG_COLOR</div><div class="setting-key-summary"><div>(�f���̔w�i�F)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_BG_COLOR"); ?></div>
					<?php echo $form["design"]->ErrMessage("BBS_BG_COLOR", "<div class='errmsg'>�F�̌`�����s���ł��B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_BG_PICTURE</div><div class="setting-key-summary"><div>(�f���̔w�i�摜)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_BG_PICTURE"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_TEXT_COLOR</div><div class="setting-key-summary"><div>(�f���̕����F)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_TEXT_COLOR"); ?></div>
					<?php echo $form["design"]->ErrMessage("BBS_TEXT_COLOR", "<div class='errmsg'>�F�̌`�����s���ł��B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_LINK_COLOR</div><div class="setting-key-summary"><div>(�f���̃����N������̐F)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_LINK_COLOR"); ?></div>
					<?php echo $form["design"]->ErrMessage("BBS_LINK_COLOR", "<div class='errmsg'>�F�̌`�����s���ł��B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_ALINK_COLOR</div><div class="setting-key-summary"><div>(�f���̑I�𒆂̃����N�̐F)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_ALINK_COLOR"); ?></div>
					<?php echo $form["design"]->ErrMessage("BBS_ALINK_COLOR", "<div class='errmsg'>�F�̌`�����s���ł��B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_VLINK_COLOR</div><div class="setting-key-summary"><div>(�f���̕\���ς݂̃����N�̐F)</div></div></div>
					<div class="input"><?php echo $form["design"]->Text("BBS_VLINK_COLOR"); ?></div>
					<?php echo $form["design"]->ErrMessage("BBS_VLINK_COLOR", "<div class='errmsg'>�F�̌`�����s���ł��B</div>"); ?>
					<div class="line"></div>
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="design">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
			<div class="space"></div>
			<div class="box">
				<div>TOP�y�[�W�w�b�_����</div>
				<div>���f����TOP�̈ē��̃e�L�X�g�ł��B</div>
				<div class="line"></div>
				<form name="header" method="post" action="">
					<div class="textarea">
						<?php echo $form["head"]->TextArea("headtxt", 6); ?>
					</div>
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="header">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
			<div class="box">
				<div>�A�N�Z�X��̓X�N���v�g�u���b�N</div>
				<div>��Google Analytics�̃g���b�L���O�R�[�h�Ȃǂ�&lt;/head&gt�^�O�̑O�ɖ��ߍ��߂܂��B</div>
				<div class="line"></div>
				<form name="header" method="post" action="">
					<div class="textarea">
						<?php echo $form["analysis"]->TextArea("analysistxt", 6); ?>
					</div>
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="analysis">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
		</div>
		<div class="space"></div>
		<div id="tailres">
			<div class="box">
				<div>1001.txt</div>
				<div class="line"></div>
				<form name="tailres" method="post" action="">
					<div class="textarea">
						<?php echo $form["tailres"]->TextArea("tailres", 6); ?>
					</div>
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="tailres">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
		</div>
		<div class="space"></div>
		<div id="indexhtml">
			<div class="box">
				<form name="indexhtml" method="post" action="">
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="indexhtml">
					<div class="submit"><input type="submit" value="index.html�X�V" /></div>
				</form>
				<?php if($indexupdate) { ?>
				<div class="msg">index.html���X�V���܂����B</div>
				<?php } ?>
			</div>
		</div>
		<div class="space"></div>
		<div class="head">���e�ݒ�</div>
		<div class="line"></div>
		<div id="respost">
			<div class="box">
				<form name="respost" method="post" action="">
					<div class="setting-key-name"><div class="setting-key-label">BBS_READONLY</div><div class="setting-key-summary"><div>(��ǂݎ���p�ɂ��邩�ۂ� �����ꂪ�L�����Ə������݂܂���B)</div></div></div>
					<div class="checkbox"><?php echo $form["respost"]->CheckBox("BBS_READONLY", "1"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_NONAME_NAME</div><div class="setting-key-summary"><div>(���O����Ń��X���������񂾂Ƃ��̖��O���̕�����)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("BBS_NONAME_NAME"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">THRE_STOP_NAME</div><div class="setting-key-summary"><div>(���X�ő厞�ɍŌ�ɒǉ�����鏑�����݂̖��O���̕�����)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("THRE_STOP_NAME"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_LINE_NUMBER</div><div class="setting-key-summary"><div>(index.html�̈�s�ӂ�̍s������уX���b�h�{���y�[�W�̉��s�� ���������2�{�̒l�ɂȂ�)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("BBS_LINE_NUMBER"); ?></div>
					<?php echo $form["respost"]->ErrMessage("BBS_LINE_NUMBER", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_SUBJECT_COUNT</div><div class="setting-key-summary"><div>(�X���b�h�^�C�g���̍ő�o�C�g�� ��SJIS)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("BBS_SUBJECT_COUNT"); ?></div>
					<?php echo $form["respost"]->ErrMessage("BBS_SUBJECT_COUNT", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_NAME_COUNT</div><div class="setting-key-summary"><div>(���O���̍ő�o�C�g�� ��SJIS)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("BBS_NAME_COUNT"); ?></div>
					<?php echo $form["respost"]->ErrMessage("BBS_NAME_COUNT", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_MAIL_COUNT</div><div class="setting-key-summary"><div>(���[���A�h���X���̍ő�o�C�g��)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("BBS_MAIL_COUNT"); ?></div>
					<?php echo $form["respost"]->ErrMessage("BBS_MAIL_COUNT", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_MESSAGE_COUNT</div><div class="setting-key-summary"><div>(���̓��e���X�̍ő�o�C�g�� ��SJIS)</div></div></div>
					<div class="input"><?php echo $form["respost"]->Text("BBS_MESSAGE_COUNT"); ?></div>
					<?php echo $form["respost"]->ErrMessage("BBS_MESSAGE_COUNT", "<div class='errmsg'>���l����͂��邩�A�����͂ɂ��Ă��������B</div>"); ?>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">NANASHI_CHECK</div><div class="setting-key-summary"><div>(���O����������邩�ǂ���)</div></div></div>
					<div class="checkbox"><?php echo $form["respost"]->CheckBox("NANASHI_CHECK", "1"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">NULLMSG_NG</div><div class="setting-key-summary"><div>(�{��������ɂł��邩�ۂ�)</div></div></div>
					<div class="checkbox"><?php echo $form["respost"]->CheckBox("NULLMSG_NG", "checked"); ?></div>
					<div class="line"></div>
					<div class="setting-key-name"><div class="setting-key-label">BBS_SLIP</div><div class="setting-key-summary"><div>(�g�т�PC�̎��ʋL���\���̗L��)</div></div></div>
					<div class="checkbox"><?php echo $form["respost"]->CheckBox("BBS_SLIP", "checked"); ?></div>
					<div class="line"></div>
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="respost">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
		</div>
		<div class="space"></div>
		<div class="head">�X�����Đ���</div>
		<div class="line"></div>
		<div id="threcreate">
			<div class="box">
				<form name="threcreate" method="post" action="">
					<div class="setting-key-name"><div class="setting-key-label">BBS_THREAD_TATESUGI</div><div class="setting-key-summary"><div>(����IP�ш悲�Ƃ̃X�����Đ��ő�L�^���R�[�h��)</div></div></div>
					<div class="input"><?php echo $form["threcreate"]->Text("BBS_THREAD_TATESUGI"); ?></div>
					<?php echo $form["threcreate"]->ErrMessage("BBS_THREAD_TATESUGI", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<div class="setting-key-name"><div class="setting-key-label">THRECRE_MAX</div><div class="setting-key-summary"><div>(���X�g�Ɋ��ɂ��铯��IP�ш悩��X���b�h�𗧂Ă���ő吔)</div></div></div>
					<div class="input"><?php echo $form["threcreate"]->Text("THRECRE_MAX"); ?></div>
					<?php echo $form["threcreate"]->ErrMessage("THRECRE_MAX", "<div class='errmsg'>���l����͂��Ă��������B</div>"); ?>
					<input type="hidden" name="bbs" value="<?php echo $bbs; ?>">
					<input type="hidden" name="mode" value="threcreate">
					<div class="submit"><input type="submit" value="�ݒ�X�V" /></div>
				</form>
			</div>
		</div>
		<div class="space"></div>
		<?php } ?>
		<div id="threadmng">
			<div class="head">�X���b�h�Ǘ�</div>
			<div class="line"></div>
			<a href="<?php echo $baseurl; ?>/admin/threadmng.php/<?php echo $bbs; ?>/1">�X���b�h�ꗗ��</a>
			<div class="space"></div>
		</div>
		<div id="ngword">
			<div class="head">NG���[�h�ݒ�</div>
			<div class="line"></div>
			<a href="<?php echo $baseurl; ?>/admin/ngwordmng.php/<?php echo $bbs; ?>/1">NG���[�h�ꗗ��</a>
			<div class="space"></div>
		</div>
		<div id="regulation">
			<div class="head">�K�����X�g�Ǘ�</div>
			<div class="line"></div>
			<a href="<?php echo $baseurl; ?>/admin/regulationmng.php/<?php echo $bbs; ?>/1">�K�����X�g�ꗗ��</a>
			<div class="space"></div>
		</div>
		<div id="plugins">
			<div class="head">�v���O�C���Ǘ�</div>
			<div class="line"></div>
			<a href="<?php echo $baseurl; ?>/admin/pluginmng.php/<?php echo $bbs; ?>/1">�v���O�C���ꗗ��</a>
			<div class="space"></div>
		</div>
	</div>
	<div id="reload"><a href="<?php echo "{$baseurl}/admin/setting.php/{$bbs}"; ?>">�����[�h</a></div>
</div>
</center>
</body>
</html>
