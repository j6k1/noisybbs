<?php
	require_once("inclueds.php");
	
	class BBS_Main
	{
		var $hostinfo;
		var $writeinfo;
		var $datdata;
		var $datlogdata;
		
		function BBS_Main()
		{

		}
		
		function Execute()
		{
			$errmsg = ErrMessage::getInstance();
			
			if(isset($_POST['subject']))
			{
				$key = time();
			}
			else
			{
				if(!isset($_POST['key']))
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"スレッドキーが未指定です。");
				}
				
				$key = $_POST['key'];
			}
			
			
			BBSList::getInstance()->Init();
			if( !in_array($_POST["bbs"], Util::getBBSList()) )
			{
				 return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbskey_notfound);
			}
			
			$ret = SettingInfo::getInstance()->Init($_POST['bbs'], $key);
			ErrMsgGetter::getInstance();

			if($ret !== true)
			{
				return $ret;
			}
			
			$setting = SettingInfo::getInstance();
			
			$ret = ThreadStateList::getInstance()->Init();
			
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			
			if(ThreadStateList::getInstance()->hasState($key, "THREADSTOP"))
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->threadstop, 
					ErrMsgID::get()->THREADSTOP );
			}
			
			$datdata = new DatData();
			$datdata->ReadData($key);
			
			if($datdata->getRowCount() >= $setting->RES_MAX)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->res_max, 
					ErrMsgID::get()->RESMAX );
			}
			
			$ret = NGWord::getInstance()->Load();
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = CapList::getInstance()->Init();

			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = CapInfo::getInstance()->Init($_POST["mail"], $setting->bbs);
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = Logging::getInstance()->Init();
			
			if($ret !== true)
			{
				return $ret;
			}
			
			if(!isset($_POST['subject']))
			{
				if(!file_exists("../{$_POST['bbs']}/dat/{$key}.dat"))
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						$errmsg->threadkey_invalid, 
						ErrMsgID::get()->THREAD_NOT_FOUND );
				}
			}
			
			$ret = PluginManager::getInstance()->IncludePlugins();

			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = PluginManager::getInstance()->LoadPlugins();
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$pluginmng = PluginManager::getInstance();
			$plugincnt = count($pluginmng->plugins);
			
			for($i=0; $i < $plugincnt ; $i++)
			{
				$ret = $pluginmng->plugins[$i]->ExecBeforeProc();
				if($ret !== true)
				{
					return $ret;
				}
			}
			
			$readonly = $setting->BBS_READONLY;
			
			if( ($readonly == "1") || 
				( ($readonly == "caps") && (!isset(CapInfo::getInstance()->capdata)) )
			  )
			{
			
				return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"読み取り専用モードです。", ErrMsgID::get()->READONLY);
			}
			
			$this->hostinfo = HostInfo::getInstance();
			$this->hostinfo->Init($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);

			if(isset($this->hostinfo->carrier))
			{
				if( (!isset($this->hostinfo->uniqno)) && 
					(!isset($this->hostinfo->modelid)) )
				{
					return Logging::generrinfo($this,
							__FUNCTION__ , __LINE__ , 
							"端末固有番号が送信されていません。", ErrMsgID::get()->NOUNIQNO);
				}
			}
			
			for($i=0; $i < $plugincnt ; $i++)
			{
				$ret = $pluginmng->plugins[$i]->ExecHostInfo($this->hostinfo);
				if($ret !== true)
				{
					return $ret;
				}
			}
			
			for($i=0; $i < $plugincnt ; $i++)
			{
				$ret = $pluginmng->plugins[$i]->ExecPostData($_POST);
				if($ret !== true)
				{
					return $ret;
				}
			}
			
			$this->writeinfo = new WriteInfo($_POST, $this->hostinfo);

			for($i=0; $i < $plugincnt ; $i++)
			{
				$ret = $pluginmng->plugins[$i]->ExecWriteBefore($this->writeinfo);
				if($ret !== true)
				{
					return $ret;
				}
			}
			
			$ret = $this->ChkAllowWrite();
			
			if($ret !== true)
			{
				if(Tatesugi::getInstance()->file_islocked)
				{
					Util::file_unlock($setting->threcrecnt_file);
				}
				
				return $ret;
			}
			
			$ret = $this->ResWrite();
			
			if($ret !== true)
			{
				if(SubjectText::getInstance()->file_islocked)
				{
					Util::file_unlock(SettingInfo::getInstance()->subjecttxt);
				}
				
				if(SubbackHtml::getInstance()->file_islocked)
				{
					Util::file_unlock(SettingInfo::getInstance()->subbackhtml);
				}
				
				if(IndexHtml::getInstance()->file_islocked)
				{
					Util::file_unlock(SettingInfo::getInstance()->indexhtml);
				}
				
				return $ret;
			}
			
			return true;
		}
		
		function ChkAllowWrite()
		{
			$errmsg = ErrMessage::getInstance();
			$ret = true;
			$setting = SettingInfo::getInstance();
			$key = $setting->thread_key;
			
			if(isset($_POST['subject'])) 
			{
				if( (isset($_POST['subject'])) && 
					(file_exists("../{$_POST['bbs']}/dat/{$key}.dat")) )
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						"既に同一スレッドキーでスレが立てられています。",
						ErrMsgID::get()->THREADKEY_EXISTS);
				}
				
				if(!CapInfo::hasAuthority("PASS_TATESUGI"))
				{
					$tatesugi = Tatesugi::getInstance();
					$ret = $tatesugi->Init();
	
					if(ErrInfo::IsErr($ret))
					{
						return $ret;
					}
					
					$ret = $tatesugi->Find($this->hostinfo->ipaddr);
					
					if(ErrInfo::IsErr($ret))
					{
						return $ret;
					}

					fclose(fopen("../{$setting->bbs}/dat/{$key}.dat", "w"));

					$tatesugi->Update($this->hostinfo->ipaddr);
					$ret = $tatesugi->Save();
					
					if(ErrInfo::IsErr($ret))
					{
						return $ret;
					}
				}
				else
				{
					fclose(fopen("../{$setting->bbs}/dat/{$key}.dat", "w"));
				}
			}

			$ret = Validate::getInstance()->Init();
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = Validate::getInstance()->Isset_Required_Params($_POST);
			
			if($ret !== true)
			{
				return Logging::generrinfo(Validate::getInstance(),
							__FUNCTION__ , __LINE__ , 
							$errmsg->invalidprm, ErrMsgID::get()->SYSERR);
			}
			
			$ret = Validate::getInstance()->chkall($this->writeinfo, isset($_POST['subject']));
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = PostedLimit::getInstance()->Init();
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = PostedLimit::getInstance()->rentou_check($this->hostinfo, time());
			
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			$ret = Regulation::getInstance()->Init();
			
			if($ret !== true)
			{
				return $ret;
			}
			
			if(Regulation::getInstance()->HostChk($this->hostinfo->gethostid()) == false)
			{
				return Logging::generrinfo(Regulation::getInstance(),
						__FUNCTION__ , __LINE__ , 
						$errmsg->denyhost, ErrMsgID::getInstance()->DENYHOST);
			}
			
			return true;
		}
		
		function ResWrite()
		{
			$errmsg = ErrMessage::getInstance();
			$this->datdata = new DatData();
			$this->datlogdata = new DatLogData();
			$this->writeinfo->genresdata();
			
			$pluginmng = PluginManager::getInstance();
			$plugincnt = count($pluginmng->plugins);

			for($i=0; $i < $plugincnt ; $i++)
			{
				$ret = $pluginmng->plugins[$i]->ExecWriteAfter($this->writeinfo);
				if($ret !== true)
				{
					return $ret;
				}
			}
			
			$key = (isset($_POST['key'])) ? $_POST['key'] : null;
			$ret = $this->datdata->appendRes($this->writeinfo,  $key);
			
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			if(isset($_POST["FROM"])) setcookie("FROM", $_POST["FROM"], time()+3600*24*30);
			if(isset($_POST["mail"])) setcookie("mail", $_POST["mail"], time()+3600*24*30);
			
			if( ($this->hostinfo->is_cookie_id) && (!isset($_COOKIE['uniqid'])) )
			{
				if( (isset($this->hostinfo->uniqno)) && 
					($this->hostinfo->uniqno != "") )
				{
					$year = date("Y") + 1;
					$expire = strtotime(sprintf("%04d", $year). date("-m-d"));
					setcookie('uniqid', $this->hostinfo->uniqno, $expire);
				}
			}
			
			$setting = SettingInfo::getInstance();
			if($ret >= $setting->RES_MAX)
			{
				$maxres_msg = Util::msgbody_escape($setting->maxres_msg);
				$tailrow = <<<EOM
{$setting->THRE_STOP_NAME}<><>Over {$setting->RES_MAX} Thread<>{$maxres_msg}"

EOM;
				$ret = $this->datdata->appendRow($tailrow,  $key);
			}
			
			$ret = $this->datlogdata->appendLog($this->hostinfo, isset($_POST['subject']), $key);
			
			if($ret !== true)
			{
				return $ret;
			}
			
			$ret = SubjectText::getInstance()->Init();
			
			if($ret !== true)
			{
				return $ret;
			}

			if(isset($this->writeinfo->subject))
			{
				SubjectText::getInstance()->AddSubject(SettingInfo::getInstance()->thread_key, $this->writeinfo->subject);
			}
			else
			{
				SubjectText::getInstance()->UpdateSubject($_POST['key'], preg_match('/sage/', $this->writeinfo->mail));
			}
			
			$ret = SubjectText::getInstance()->WriteData();
			
			if($ret !== true)
			{
				return $ret;
			}

			$ret = SubbackHtml::getInstance()->WriteData();
			
			if($ret !== true)
			{
				return $ret;
			}

			$ret = IndexHtml::getInstance()->WriteData();

			if($ret !== true)
			{
				return $ret;
			}

			for($i=0; $i < $plugincnt ; $i++)
			{
				$ret = $pluginmng->plugins[$i]->ExecAfterProc();
				if($ret !== true)
				{
					return $ret;
				}
			}
			
			return true;
		}
		
		function OutPutErrHtml(&$errinfo)
		{
			$setting = SettingInfo::getInstance();
			$rooturl = Util::getRootUrl();
			
			$sysmsg = "";
			if($errinfo->usrmsgid == ErrMsgID::get()->SYSERR)
			{
				$sysmsg = "{$errinfo->sysmsg}<br>";
			}
			
			header("Content-type: text/html; charset=\"shift-jis\"");
			if($this->hostinfo->carrier === null)
			{
				$html = <<<EOM

<html>
<head><title>ＥＲＲＯＲ！</title>
<meta http-equiv=Content-Type content=text/html; charset=Shift_JIS>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head><body bgcolor=#FFFFFF>
<!-- 2ch_X:error --><!--nobanner-->
<font size=+1 color=#FF0000><b>ＥＲＲＯＲ：{$errinfo->errhead}</b>
</font><ul><br>ホスト<b>{$this->hostinfo->hostname}</b><br>
<b> </b><br>名前： {$this->writeinfo->name}<br>E-mail： {$this->writeinfo->mail}<br>内容：{$errinfo->errhead}<br>
{$errinfo->usrmsg}<br>{$sysmsg}</ul><small>こちらでリロードしてください。
<a href="{$rooturl}/{$setting->bbs}/"> GO! </a><hr>
<div align=right></div></small></body></html>
		
EOM;
			} else {
				$html = <<<EOM

<html><head><title>ＥＲＲＯＲ！</title></head><!--nobanner-->
<body><font color=red>ERROR:{$errinfo->errhead}</font><hr>
{$errinfo->usrmsg}<br>{$sysmsg}<hr>携帯の「戻るボタンで」戻ってください</body></html>
				
EOM;
			}
			echo $html;
		}

		function OutPutJumpHtml()
		{
			$setting = SettingInfo::getInstance();
			$rooturl = Util::getRootUrl();

			header("Content-type: text/html; charset=\"shift-jis\"");
			if($this->hostinfo->carrier === null)
			{
			
				$html = <<<EOM
<html><head><title>書きこみました。</title><!--nobanner-->
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta content=5;URL={$rooturl}/{$setting->bbs}/ http-equiv=refresh></head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<body>書きこみが終わりました。
<br><br>画面を切り替えるまでしばらくお待ち下さい。
<br><br><br><br><br><hr>
</body>
</html>

EOM;
			} else {
				$html = <<<EOM
<!--nobanner--><html><body>書き込み完了です<br>
携帯の「戻る」ボタンで戻ってください。
</body>
</html>

EOM;
			}

			echo $html;
		}

	}

	if(!isset($_POST['bbs'])) $_POST['bbs'] = null; 
	$bbs = new BBS_Main();
	$ret = $bbs->Execute();
	
	if(ErrInfo::IsErr($ret))
	{
		$bbs->OutPutErrHtml($ret);
	}	
	else
	{
		$bbs->OutPutJumpHtml();	
	}
?>

