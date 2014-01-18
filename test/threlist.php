<?php
	require_once("inclueds.php");
	
	class ThreListMain
	{
		var $lsprinter;
		var $pathinfo;
		
		function ThreListMain()
		{
			$this->lsprinter = null;
			$this->pathinfo = null;		
		}
		
		
		function Init()
		{
			$this->pathinfo = explode("/", $_SERVER["PATH_INFO"]);
			$bbs = $this->pathinfo[1];
			$baseurl = Util::getBaseUrl();

			BBSList::getInstance()->Init();
			if(!in_array($bbs, BBSList::getInstance()->getRows()))
			{
				 return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbs_notfound, ErrMsgID::get()->URLFORMAT);
			}
			
			$ret = SettingInfo::getInstance()->Init($bbs);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			$hostinfo = HostInfo::getInstance();
			$hostinfo->Init($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
			
			if($hostinfo->carrier === null)
			{
				Util::Redirect("{$baseurl}/navigate.html");
			}
			
			$this->lsprinter = new ThreadList();

			$ret = $this->lsprinter->Init($this->pathinfo);

			if($ret !== true)
			{
				$this->OutPutErrHtml($ret);
				return false;
			}
			
			return true;
		}

		function Show()
		{
			$ret = $this->Init();

			if($ret !== true)
			{
				$this->OutPutErrHtml($ret);
				return false;
			}
			
			$ret = $this->lsprinter->DispThreadList($this->pathinfo);

			if($ret !== true)
			{
				$this->OutPutErrHtml($ret);
				return false;
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
	$threlist = new ThreListMain();
	
	$threlist->Show();
?>
