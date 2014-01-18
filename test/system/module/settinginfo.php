<?php
	class SettingInfo
	{
		var $overload;
		
		var $systemdir = "system";
		var $datadir = "sysdata";
		var $plugindir = "plugin";
		var $logdir = "logs";

		var $errmsgfile = "errmsg.cgi";
		var $denyhosts_file = "denyhosts.cgi";
		var $randseed_file = "randseed.cgi";
		var $pluginlist    = "pluginlist.cgi";
		var $pluginmaster  = "pluginmaster.cgi";
		var $caplist = "caplist.cgi";
		var $ngrulefile = "ngword.txt";
		
		var $thread_state_file = 'threadstate.cgi';
		var $rentou_chk_file = 'rentouchk.cgi';
		var $threcrecnt_file = 'threcrecnt.cgi';
		var $air_phone_ips_file = 'air_phone_iplist.txt';
		var $envtext_file = 'envlist.txt';
		
		var $maxres_msg_file = '1001.txt';
		var $bbsheader_file = "head.txt";
		var $bbsfooter_file = "foot.txt";		
		
		var $subjecttxt = 'subject.txt';
		var $indexhtml = 'index.html';
		var $subbackhtml = 'subback.html';
		
		var $values;
		var $setting_default;
		var $env_default;
		var $bbs_values;
		var $air_phone_iplist;
		var $required_prms;
		var $p2ipaddrs;
		
		var $maxres_msg;
	
		var $bbs;
		var $thread_key;
		var $settingtxt;
		var $envtxt;
		
		function SettingInfo()
		{
			$this->overload = true;
			
			if(preg_match('/^4\./', phpversion()))
			{
				if(function_exists("overload") == false)
				{
					$this->overload = false;
				}
				else
				{
					overload(get_class($this));
				}
			}
			
			$this->values = array();
			
			$this->required_prms = array(
				"BBS_TITLE",
				"BBS_TITLE_PICTURE",
				"BBS_TITLE_COLOR",
				"BBS_TITLE_LINK",
				"BBS_BG_COLOR",
				"BBS_BG_PICTURE",
				"BBS_NONAME_NAME",
				"BBS_MAKETHREAD_COLOR",
				"BBS_MENU_COLOR",
				"BBS_THREAD_COLOR",
				"BBS_TEXT_COLOR",
				"BBS_NAME_COLOR",
				"BBS_LINK_COLOR",
				"BBS_ALINK_COLOR",
				"BBS_VLINK_COLOR",
				"BBS_CONTENTS_NUMBER",
				"BBS_SUBJECT_COLOR",
				"BBS_PASSWORD_CHECK",
				"BBS_UNICODE",
				"BBS_NAMECOOKIE_CHECK",
				"BBS_MAILCOOKIE_CHECK",
				"BBS_SLIP",
				"BBS_FORCE_ID",
				"BBS_NO_ID",
				"BBS_THREAD_TATESUGI",
				"BBS_MESSAGE_COUNT",
				"BBS_NAME_COUNT",
				"BBS_MAIL_COUNT",
				"BBS_LINE_NUMBER",
				"NANASHI_CHECK",
				"BBS_SUBJECT_COUNT",
				"BBS_MAX_MENU_THREAD",
				"THRE_STOP_NAME",
				"AIRPHONEIP_CHK",
				"NULLMSG_NG",
				"RES_MAX",
				"POSTEDLIMIT_TYPE",
				"RES_INTERVAL",
				"SAMBACOUNT",
				"SAMBATIME",
				"THRECRE_MAX",
				);

			$this->errmsgfile = "{$this->systemdir}/{$this->datadir}/{$this->errmsgfile}";
			$this->air_phone_ips_file = "{$this->systemdir}/{$this->datadir}/{$this->air_phone_ips_file}";
			$this->air_phone_iplist = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("SettingInfo");
		}			
		
		function RemoveInstance()
		{
			Singleton::RemoveInstance("SettingInfo");
		}
		
		function __get($name)
		{
			if(isset($this->values[$name]) == false)
			{
				return null;
			}
			else
			{
				return $this->values[$name];
			}
		}
		
		function __set($name, $value)
		{
			$this->values[$name] = $value;
		}
		
		function invtervalstrtotime($src)
		{
			$time = 0;
			if(preg_match('/^(\d+day )?(([0-1]\d)|(2[0-3])):([0-5]\d):([0-5]\d)$/', 
				"{$src}", $match) > 0)
			{
				$time = $match[4] + ($match[3] * 60) + ($match[2] * 60 * 24);
				
				if(isset($match[1]))
				{
					$time += ($match[1] * 24 * 60 * 60);
				}
				return $time;
			}
			else
			{
				return false;
			}
		}
		
		function Init($bbsid, $key = null)
		{
			$errmsg = ErrMessage::getInstance();
	
			if(!isset($bbsid))
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbskey_null);
			}
		
			if($bbsid == "")
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbskey_null);
			}
			
			if(Validate::IsBBSKey_Invalid($bbsid))
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbskey_invalid);
			}

			$this->bbs = $bbsid;
			
			$this->subjecttxt = "../{$this->bbs}/{$this->subjecttxt}";
			$this->indexhtml = "../{$this->bbs}/{$this->indexhtml}";
			$this->subbackhtml = "../{$this->bbs}/{$this->subbackhtml}";
			
			$this->datadir = "../{$this->bbs}/{$this->systemdir}/{$this->datadir}";
			$this->plugindir = "{$this->systemdir}/{$this->plugindir}";
			$this->logdir = "{$this->datadir}/{$this->logdir}";
			$this->ngrulefile = "{$this->datadir}/{$this->ngrulefile}";
			$this->denyhosts_file = "{$this->datadir}/{$this->denyhosts_file}";
			$this->randseed_file = "{$this->datadir}/{$this->randseed_file}";
			$this->pluginlist = "{$this->datadir}/{$this->pluginlist}";

			$this->thread_state_file = "{$this->datadir}/{$this->thread_state_file}";
			$this->rentou_chk_file = "{$this->logdir}/{$this->rentou_chk_file}";
			$this->threcrecnt_file = "{$this->logdir}/{$this->threcrecnt_file}";
			$this->maxres_msg_file = "../{$this->bbs}/{$this->maxres_msg_file}";
			$this->bbsheader_file = "../{$this->bbs}/{$this->bbsheader_file}";
			$this->bbsfooter_file = "../{$this->bbs}/{$this->bbsfooter_file}";
			
			if(file_exists($this->air_phone_ips_file) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$this->air_phone_ips_file}が見つかりません。");
			}
			
			if(file_exists("system/sysdata/p2ip.txt") == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイルsystem/sysdata/p2ip.txtが見つかりません。");
			}
			
			if( (!file_exists("../{$this->bbs}")) || (!is_dir("../{$this->bbs}")) )
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ディレクトリ../{$this->bbs}が見つかりません。", 
					ErrMsgID::get()->BBS_NOT_FOUND);
			}
			
			if(file_exists("../{$this->bbs}/SETTING.TXT") == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル../{$this->bbs}/SETTING.TXTが見つかりません。");
			}

			if(file_exists("{$this->datadir}/{$this->envtext_file}") == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$this->datadir}/{$this->envtext_file}が見つかりません。");
			}
			
			$this->envtxt = file_get_contents("{$this->datadir}/{$this->envtext_file}");
			$this->envtxt = mb_convert_encoding($this->envtxt, 'UTF-8', 'SJIS');

			$this->settingtxt = file_get_contents("../{$this->bbs}/SETTING.TXT");
			$this->settingtxt = mb_convert_encoding($this->settingtxt, 'UTF-8', 'SJIS');

			$setting_default = file_get_contents("system/package/SETTING.TXT");
			$setting_default = mb_convert_encoding($setting_default, 'UTF-8', 'SJIS');
			
			$env_default = file_get_contents("system/package/system/sysdata/envlist.txt");
			$env_default = mb_convert_encoding($env_default, 'UTF-8', 'SJIS');

			Util::readenvs($setting_default, 'SJIS', $this->setting_default);
			Util::readenvs($env_default, 'SJIS', $this->env_default);
			
			Util::readenvs($this->envtxt, 'SJIS', $values);
			Util::readenvs($this->settingtxt, 'SJIS', $values);
			
			$this->bbs_values = $values;
			
			$this->values = array_merge(
				$this->setting_default, $this->env_default, $values);
			
			$this->p2ipaddrs = file_get_contents("system/sysdata/p2ip.txt");
			$this->p2ipaddrs = explode("\n", $this->p2ipaddrs);
			array_pop($this->p2ipaddrs);
			
			$this->RES_MAX_STRLEN =   $this->BBS_MESSAGE_COUNT;
			$this->NAME_MAX_STRLEN =  $this->BBS_NAME_COUNT;
			$this->MAIL_MAX_STRLEN =  $this->BBS_MAIL_COUNT;

			$this->LINE_DISP_MAX  = $this->BBS_LINE_NUMBER;
			$this->LINE_MAX_COUNT =   $this->BBS_LINE_NUMBER * 2;

			$this->NANASHI_NG =       $this->NANASHI_CHECK;
			$this->TITLE_MAX_STRLEN = $this->BBS_SUBJECT_COUNT;
						
			$this->DISP_THREAD_MAX =  $this->BBS_MAX_MENU_THREAD;
		
			if(($this->SAMBACOUNT == null) || ($this->SAMBACOUNT == ""))
			{
				$this->SAMBACOUNT = 5;
			}
			
			if(($this->SAMBATIME == null) || ($this->SAMBATIME == ""))
			{
				$this->SAMBACOUNT = 60;
			}
			
			$issetprms = $this->Isset_Required_Params();
			
			if($issetprms !== true)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"パラメータ{$issetprms}を読み込めませんでした。");
			}
			
			if(file_exists($this->maxres_msg_file) == true)
			{
				$this->maxres_msg = file_get_contents($this->maxres_msg_file);
			}
			else
			{
				$this->maxres_msg = "このスレッドは1000を超えました。もう書けないので、新しいスレッドを立ててくださいです・・・";
			}		

			if(($this->air_phone_iplist = @file($this->air_phone_ips_file)) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$this->air_phone_ips_file}の読み込みに失敗しました。");
			}
			
			if(isset($key))
			{
				if(!preg_match('/^[0-9]+$/', $key))
				{
					return Logging::generrinfo($this,
						__FUNCTION__ , __LINE__ , 
						$errmsg->threadkey_invalid);
				}
				else
				{
					$this->thread_key = $key;
				}
			}
			
			return true;
		}
		
		function getValues()
		{
			return $this->values;
		}
		
		function Isset_Required_Params()
		{
			foreach($this->required_prms as $prm)
			{
				if(isset($this->values[$prm]) == false)
				{
					return $prm;
				}
			}
			
			return true;
		}
		
		function Update($key, $val)
		{
			if(isset($this->bbs_values[$key]) == false)
			{
				if(isset($this->setting_default[$key]))
				{
					Util::addenv($key, $val, $this->settingtxt);
				}
				else if(isset($this->env_default[$key]))
				{
					Util::addenv($key, $val, $this->envtxt);
				}
				
				return true;
			}
			
			$ret = Util::updateenv($key, $val, $this->settingtxt);
			
			if($ret == true)
			{
				return true;
			}
			
			$ret = Util::updateenv($key, $val, $this->envtxt);
			
			return $ret;
		}
		
		function Save()
		{
			$output = new BBSOutPutStream();
			$output->PrintStr(mb_convert_encoding($this->settingtxt, "SJIS", "UTF-8"));

			$islocked = false;
			
			$ret = $output->FlushToFile("../{$this->bbs}/SETTING.TXT", $islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($islocked)
				{
					Util::file_unlock("../{$this->bbs}/SETTING.TXT");
				}
				return $ret;
			}			
		
			$output = new BBSOutPutStream();
			$output->PrintStr(mb_convert_encoding($this->envtxt, "SJIS", "UTF-8"));

			$ret = $output->FlushToFile("{$this->datadir}/{$this->envtext_file}", $islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($islocked)
				{
					Util::file_unlock("{$this->datadir}/{$this->envtext_file}");
				}
				return $ret;
			}
			
			return true;			
		}
	}
?>
