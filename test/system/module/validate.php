<?php
	class Validate
	{
		var $required_prms;
		var $nanashing = false;
		var $nullmsgng = false;
	
		function Validate()
		{
			$this->required_prms = array(
				"FROM",
				"mail",
				"MESSAGE"
				);
		}
		
		function Isset_Required_Params($postdata)
		{
			$validate = Validate::getInstance();
			
			foreach($validate->required_prms as $prm)
			{
				if(isset($postdata[$prm]) == false)
				{
					return $prm;
				}
			}
			
			return true;
		}

		function Init()
		{
			$setting = SettingInfo::getInstance();
			
			if($setting->NANASHI_NG == "1")
			{
				$this->nanashing = true;
			}
			
			if($setting->NULLMSG_NG == "checked")
			{
				$this->nullmsgng = true;
			}
			
			return true;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("Validate");
		}
			
		function IsBBSKey_Invalid($bbsid)
		{
			if((preg_match('/\.\./', $bbsid)) || (!preg_match('/^[\.a-zA-Z0-9\-_]+$/', $bbsid)))
			{
				return true;
			}
			
			return false;
		}
		
		function chkall(&$writeinfo, $threcre = false)
		{
			if($threcre)
			{
				$ret = Validate::chksubject($writeinfo->subject);
				if(ErrInfo::IsErr($ret))
				{
					return $ret;
				}
			}

			$ret = Validate::chkmail($writeinfo->mail);
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}

			$ret = Validate::chkname($writeinfo->FROM);
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}

			$ret = Validate::chkmsg($writeinfo->MESSAGE);
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			return true;
		}
		
		function chksubject($subject)
		{
			$setting = SettingInfo::getInstance();
			$errmsg = ErrMessage::getInstance();
			$validate = Validate::getInstance();

			if(!isset($subject) || ($subject == ""))
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->nosubject, ErrMsgID::get()->NOSUBJECT);
			}
			
			if(Util::isoneline($subject) == false)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->invalidprm);
			}
			
			if(strlen($subject) > $setting->TITLE_MAX_STRLEN)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->subject_size_over, ErrMsgID::get()->SUBJECTSIZE);
			}
			
			return true;
		}

		function chkname($name)
		{
			$setting = SettingInfo::getInstance();
			$errmsg = ErrMessage::getInstance();
			$validate = Validate::getInstance();

			if($this->nanashing == true)
			{
				if(($name == "") && (!isset(CapInfo::getInstance()->capdata)))
				{
					return Logging::generrinfo($validate,
						__FUNCTION__ , __LINE__ , 
						$errmsg->noname, ErrMsgID::get()->NULLNAME);
				}
			}
			
			if(Util::isoneline($name) == false)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->invalidprm);
			}
			
			if(strlen($name) > $setting->NAME_MAX_STRLEN)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->name_size_over, ErrMsgID::get()->NAMESIZE);
			}
			
			return true;
		}

		function chkmail($mail)
		{
			$setting = SettingInfo::getInstance();
			$errmsg = ErrMessage::getInstance();
			$validate = Validate::getInstance();

			if(Util::isoneline($mail) == false)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->invalidprm);
			}
			
			if(strlen($mail) > $setting->MAIL_MAX_STRLEN)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->mail_size_over, ErrMsgID::get()->MAILSIZE);
			}
			
			return true;
		}
		
		function chkmsg($msg)
		{
			$setting = SettingInfo::getInstance();
			$errmsg = ErrMessage::getInstance();
			$validate = Validate::getInstance();

			if($this->nullmsgng == true)
			{
				if($msg == "")
				{
					return Logging::generrinfo($validate,
						__FUNCTION__ , __LINE__ , 
						$errmsg->nullmsg, ErrMsgID::get()->NULLMSG);
				}
			}
			
			if( ($setting->RES_MAX_STRLEN !== "") && 
			    (strlen($msg) > $setting->RES_MAX_STRLEN) )
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->name_size_over, ErrMsgID::get()->MSGSIZE);
			}
			
			$lines = explode("\n", $msg);
			$linecnt = count($lines);
			
			if($linecnt > $setting->LINE_MAX_COUNT)
			{
				return Logging::generrinfo($validate,
					__FUNCTION__ , __LINE__ , 
					$errmsg->res_linecnt_over, ErrMsgID::get()->LFCOUNT);
			}
			
			if(($setting->BBS_COLUMN_NUMBER !== null))
			{
				$cnt = count($lines);
				
				for($i=0; $i < $cnt ; $i++)
				{
					if(strlen($lines[$i]) >= $setting->BBS_COLUMN_NUMBER)
					{
						return Logging::generrinfo($validate,
							__FUNCTION__ , __LINE__ , 
							$errmsg->res_linelen_over, ErrMsgID::get()->LINESIZE);
					}
				}
			}
			
			if(NGWord::Check($msg) == true)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->ngword_found, ErrMsgID::get()->NGWORD);
			}
			
			return true;
		}
	}
?>