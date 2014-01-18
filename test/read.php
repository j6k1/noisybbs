<?php
	require_once("inclueds.php");
	
	class ResReadMain
	{
		function ResReadMain()
		{

		}
		
		function ExecutePHPMODE($bbs, $key, $option)
		{
			$errmsg = ErrMessage::getInstance();
			$ret = BBSList::getInstance()->Init();
			$setting = SettingInfo::getInstance();
			
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}

			if(!in_array($bbs, Util::getBBSList()))
			{
				 return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbs_notfound, ErrMsgID::get()->URLFORMAT);
			}
			
			if(!file_exists("../{$bbs}/dat/{$key}.dat"))
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->threadkey_invalid, 
					ErrMsgID::get()->THREAD_NOT_FOUND );
			}			
			
			if($setting->BBS_THREAD_CACHE == "1")
			{
				if( (function_exists("apache_request_headers")) ||
					(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) )
				{
					if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					{
						$ifmodsince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
					}
					else 
					{
						$request_headers = apache_request_headers();
						if(isset($request_headers["If-Modified-Since"]))
						{
							$ifmodsince = $request_headers["If-Modified-Since"];
						}
					}
					
					$last_modified = filemtime("../{$bbs}/dat/{$key}.dat");
				
					if(isset($ifmodsince))
					{
						if($last_modified <= strtotime($ifmodsince))
						{
							header('HTTP/1.1 304 Not Modified');
							header('Pragma: cache');
							header('Cache-Control: max-age=0');
							return true;
						}
					}
				
					header("Last-Modified: " . gmstrftime('%a, %d %b %Y %H:%M:%S GMT', $last_modified));
					header('Pragma: cache');
					header('Cache-Control: max-age=0');
				}
			}
			
			$hostinfo = HostInfo::getInstance();
			$hostinfo->Init($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
			
			$reader = new ResReader();
			
			$ret = $reader->Init($key);
			
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			$c_name = $_COOKIE["FROM"];
			$c_mail = $_COOKIE["mail"];
			
			$c_name = @mb_convert_encoding($c_name, "SJIS", "UTF-8");
			$c_mail = @mb_convert_encoding($c_mail, "SJIS", "UTF-8");
		
			$reslines = $reader->getResLines($option);
			
			if(ErrInfo::IsErr($reslines))
			{
				return $reslines;
			}

			$fields = explode("<>", $reader->datdata->data[0]);
			
			if(count($fields) < 5)
			{
				$subject = 'スレタイ不明';
			}
			else
			{
				list(,,,,$subject) = $fields;
			}
			
			$result = ResReader::ParseOption($option);

			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			$optinfo = $result;

			$pastnums = ResReader::getPastNums($optinfo["st"], $optinfo["end"]);
			$nextnums = ResReader::getNextNums($optinfo["st"], $optinfo["end"]);
			
			$past = (($optinfo["st"] - 100) > 1) ? ($optinfo["st"] - 100) : 1;
			$pastend = $past + 100;
			$nextend = $optinfo["st"] + 100;
			$count = $reader->datdata->getRowCount();
			$speed = $count / ((int)(time() / 60) - (int)($key / 60)) * 60 * 24; 
			$size = strlen(file_get_contents("../{$bbs}/dat/{$key}.dat"));
			
			$html = <<<EOM
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
	<meta name="Author" content="">
	<meta content="no-cache" http-equiv="Pragma">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>{$subject}</title>
	<script type="text/javascript" src="../../../ajax/ajaxlib.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../ajax/read.css" media="screen">

</head>
<body>
<script type="text/javascript">
//<!--
	window.onload = function() {
		var since = {$key};
		var count = {$count};
		var date = new Date();
		var speed = count / (Math.floor(date.getTime() / 60000) - Math.floor(since / 60)) * 60 * 24;
		document.getElementById("speednum").innerHTML = speed;

		var message = document.getElementById("message");
		standardize(message);
		var shiftkey = false;
		
		message.addEventListener("keydown", function (evt) {
			if(!evt)
			{
				var evt = event;
			}
			
			if(evt.keyCode == 16){ shiftkey = true; } 
			if((evt.keyCode == 13)  && (shiftkey == true))
			{ 
				evt.preventDefault();
				document.getElementById("resform").submit.click();
				return false; 
			}
		}, true);
		message.addEventListener("keyup", function (evt) { 
			if(!evt)
			{
				var evt = event;
			}
			if(evt.keyCode == 16) {shiftkey = false; } 
		}, true);
	}
//-->
</script>
<div id="header">
<span class="headlist">
<a href="">【BBS2ch</a>】</span>
<span style='float:left;'><a href="#footer">↓</a>
<a href="../../../../{$bbs}/index.html">掲示板に戻る</a> <a href="../{$key}/">全部</a> <a href="../{$key}/|50">最新50</a>
</span>
<br style='clear:both;'>
</div>
<h1 id="subject">{$subject}</h1>
<dl id=resbodys>
{$reslines}
</dl>


<font color="red" face="Arial"><b id="size">{$size}</b></font> <span id="speed" title="勢いは一日あたりのレス数の目安です。(勢い=レス数÷スレが立ってからの分数×60×24)" style="font-family:Arial;color:green;"><strong>Speed:<span id="speednum">{$speed}</span></strong></span>&nbsp;&nbsp;

<div id="footer">
<hr class="footer"><a href="#header">↑</a> <a href="../../../../{$bbs}/index.html">掲示板に戻る</a> <a href="../{$key}/">全部</a> <a href="../{$key}/{$past}-{$pastend}">前100</a> <a href="../{$key}/{$optinfo["st"]}-{$nextend}">次100</a> <a href="../{$key}/|50">最新50</a>
<a href="">新着レスを表示</a>
<hr size="1" class="footer">
</div>
<form action="../../../../test/bbs.php?guid=On" method="post" id="resform" utn="utn">
 名前：<input type="text" name="FROM" id="name" value="{$c_name}"> 
 E-mail(<font size="1">省略可</font>)：<input type="text" name="mail" id="e-mail" value="{$c_mail}"><br>
 <textarea name="MESSAGE" id="message" rows="6" erap="off"></textarea><br>
 <input type="hidden" name="key" value="{$key}">
 <input type="hidden" name="bbs" value="{$bbs}">
 <input type="hidden" name="time" value="">
 <input type="hidden" name="suka" value="">
 <input type="submit" id="submit" value="書き込む【Shift+Enter】"><br>
 </form><br>
</span><br style='clear:both;'>
</body>
</html>
EOM;
			 echo $html;
			 
			 return true;
		 }
		 
		 function Execute()
		 {
			 $pathinfo = explode("/", $_SERVER["PATH_INFO"]);
			 $errmsg = ErrMessage::getInstance();
			 ErrMsgGetter::getInstance();

			 if(count($pathinfo) < 3)
			 {
				 return Logging::generrinfo($this,
					 __FUNCTION__ , __LINE__ , 
					 $errmsg->bbskey_null, ErrMsgID::get()->URLFORMAT);
			 }
			 
			 $bbs = $pathinfo[1];
			 $key = $pathinfo[2];

			 $ret = SettingInfo::getInstance()->Init($bbs, $key);

			 if($ret !== true)
			 {
				 return $ret;
			 }
			 
			 $setting = SettingInfo::getInstance();
			 
			 $php_mode = (($setting->BBS_READ_SCRIPT != null) && 
				 $setting->BBS_READ_SCRIPT == "php") ? true : false;
			 
			 if((count($pathinfo) <= 3) && ($php_mode))
			 {
				  return Logging::generrinfo($this,
					 __FUNCTION__ , __LINE__ , 
					 $errmsg->urlformat, ErrMsgID::get()->URLFORMAT);
			 }
			 
			 $ret = Logging::getInstance()->Init();
			 
			 if(ErrInfo::IsErr($ret))
			 {
				 return $ret;
			 }
			 
			 $option = $pathinfo[3] ;

			 if($php_mode)
			 {
				 return $this->ExecutePHPMODE($bbs, $key, $option);
			 }
			 
			 $hostinfo = HostInfo::getInstance();
			 $hostinfo->Init($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
			 $baseurl = Util::getBaseUrl();
			 
			 if($hostinfo->carrier === null)
			 {
				 Util::Redirect("{$baseurl}/navigate.html");
			 }
			 else
			 {
				 $link = "{$baseurl}/mread.php/{$bbs}/{$key}/|10";
				 $html = <<<EOM
<html><head><title>{$subject}</title>
<meta http-equiv=Content-Type content="text/html;charset=Shift-JIS">
</head><!--nobanner-->
<html><body>
<a href="{$link}">{$link}</a>
</body></html>
				 
EOM;
				 echo $html;
			 }
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
<html><head><title>{$subject}</title>
<meta http-equiv=Content-Type content="text/html;charset=Shift-JIS">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head><!--nobanner-->
<html><body>
<b>{$errinfo->usrmsg}</b>{$sysmsg}
</body></html>
				 
EOM;
			 echo $html;
		 }
	 }
	 
	 $reader = new ResReadMain();
	 
	 $ret = $reader->Execute();

	 if(ErrInfo::IsErr($ret))
	 {
		 ResReadMain::OutPutErrHtml($ret);
	 }
?>
