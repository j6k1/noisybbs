<?php
	require_once("inclueds.php");
	
	class MobileRead
	{
		var $reader;
		var $pathinfo;
		
		function MobileRead()
		{
			$this->reader = null;
			$this->pathinfo = null;		
		}
		
		function Init()
		{
			$this->pathinfo = explode("/", $_SERVER["PATH_INFO"]);

			if(count($this->pathinfo) < 3)
			{
				 return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbskey_null, ErrMsgID::get()->URLFORMAT);
			}
			
			$bbs = $this->pathinfo[1];
			$key = $this->pathinfo[2];

			BBSList::getInstance()->Init();
			if(!in_array($bbs, Util::getBBSList()))
			{
				 return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbs_notfound, ErrMsgID::get()->URLFORMAT);
			}
			
			$ret = SettingInfo::getInstance()->Init($bbs, $key);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			if(!file_exists("../{$bbs}/dat/{$key}.dat"))
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->threadkey_invalid, 
					ErrMsgID::get()->THREAD_NOT_FOUND );
			}			
			
			$this->reader = new ResReader();

			$hostinfo = HostInfo::getInstance();
			$hostinfo->Init($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
			
			if($hostinfo->carrier === null)
			{
				Util::Redirect("{$this->reader->baseurl}/navigate.html");
			}
			
			$ret = $this->reader->Init($key);
			
			if($ret !== true)
			{
				return $ret;
			}
		}
		
		function Show()
		{
			$ret = $this->reader->MobileDisp($this->pathinfo);

			if($ret !== true)
			{
				return $ret;
			}
			
			return true;
		}
		
		function ShowOneRes()
		{
			if(count($this->pathinfo) < 4)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->resno_null, ErrMsgID::get()->URLFORMAT);
			}
		
			$ret = $this->reader->MobileDispOne($this->pathinfo[2], $this->pathinfo[3]);

			if($ret !== true)
			{
				return $ret;
			}
			
			return true;
		}
		
		function ShowResForm()
		{
			$ret = $this->reader->MobileResFormDisp();

			if($ret !== true)
			{
				return $ret;
			}
			
			return true;
		}
		
		function OutPutErrHtml(&$errinfo)
		{
			$setting = SettingInfo::getInstance();
			
			$sysmsg = "";
			if($errinfo->usrmsgid == ErrMsgID::get()->SYSERR)
			{
				$sysmsg = "{$errinfo->sysmsg}<br>";
			}
			
			$html = <<<EOM
<html><head><title>‚d‚q‚q‚n‚qII</title>
<meta http-equiv=Content-Type content="text/html;charset=Shift-JIS">
</head><!--nobanner-->
<html><body>
<b>{$errinfo->usrmsg}</b>{$sysmsg}
</body></html>
				
EOM;
			echo $html;
		}
	}	
?>
