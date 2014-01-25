<?php
	class IndexHtml
	{
		var $file_islocked;
		
		function IndexHtml()
		{
			$this->file_islocked = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("IndexHtml");
		}
		
		function RemoveInstance()
		{
			Singleton::RemoveInstance("IndexHtml");
		}
		
		function WriteData()
		{
			$setting = SettingInfo::getInstance();			
			$subjecttxt = SubjectText::getInstance();
			$indexhtml = IndexHtml::getInstance();
			$baseurl = Util::getBaseUrl();
			$headtxt = file_get_contents($setting->bbsheader_file);
			$analysistxt = "";
			
			if(file_exists("../{$setting->bbs}/analysis.txt"))
			{
				$analysistxt = file_get_contents("../{$setting->bbs}/analysis.txt");
			}
			
			$php_mode = (($setting->BBS_READ_SCRIPT != null) && 
				$setting->BBS_READ_SCRIPT == "php") ? true : false;
			if($php_mode) { $ext = "cgi"; } else { $ext = "html"; }

			$output = new BBSOutPutStream();
			
			$BBS_BG_PICTURE = htmlspecialchars($setting->BBS_BG_PICTURE, ENT_QUOTES);
			$BBS_TITLE_PICTURE = htmlspecialchars($setting->BBS_TITLE_PICTURE, ENT_QUOTES);
			$BBS_TITLE = htmlspecialchars($setting->BBS_TITLE, ENT_QUOTES);
			$BBS_BG_COLOR = Util::valid_css_color_val($setting->BBS_BG_COLOR);
			$BBS_TEXT_COLOR = Util::valid_css_color_val($setting->BBS_TEXT_COLOR);
			$BBS_TITLE_COLOR = Util::valid_css_color_val($setting->BBS_TITLE_COLOR);
			$BBS_LINK_COLOR = Util::valid_css_color_val($setting->BBS_LINK_COLOR);
			$BBS_VLINK_COLOR = Util::valid_css_color_val($setting->BBS_VLINK_COLOR);
			$BBS_ALINK_COLOR = Util::valid_css_color_val($setting->BBS_ALINK_COLOR);
			
			$output->PrintStr(<<<EOM
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
  "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
<meta http-equiv="Content-Style-type" content="text/css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="{$baseurl}/read.{$ext}/{$setting->bbs}/">
<style>
body,td,a,p,.h{
	font-family:arial,sans-serif;
	background-color: {$BBS_BG_COLOR};
}
body{
	background-image: url('{$BBS_BG_PICTURE}');
	color: {$BBS_TEXT_COLOR};
}
#title{
	font-size: 32px;
	padding-bottom: 20px;
	background-image: url('{$BBS_TITLE_PICTURE}');
	color: {$BBS_TITLE_COLOR}
}
#container {
	width: 640px;
}
#summary{
	padding: 4px;
	text-align: left;
	border-color: black;
	border-style: double;
}
#threlist{
	padding-top: 2px;
	padding-bottom: 4px;
	padding-left: 2px;
	padding-right: 2px;
	border-top-style: solid;
	border-bottom-style: solid;
	border-left-style: none;
	border-right-style: none;
	border-width: 1px;
	border-color: black;
}
#listhead{
	margin-top: 20px;
	margin-bottom: 20px;
	font-weight: bold;
}
#listhtml{
	margin-top: 12px;
	text-align: right;
	font-size: 14px;
}
#mobile{
	margin-top: 12px;
	text-align: right;
	font-size: 14px;
}
#navigate{
	margin-top: 20px;
}
#post-title{
	width: 400px;
}
#message {
	width: 600px;
}
#post-title{
    	width: 380px;
}
#e-mail {
	width: 200px;
}
#name {
	width: 260px;
}
div.thread{
	text-align: center;
	padding-top: 2px;
	padding-bottom: 0px;
	padding-left: 0px;
	padding-right: 0px;
}
a:link {
	color: {$BBS_LINK_COLOR};
}
a:visited {
	color: {$BBS_VLINK_COLOR};
}
a:hover {
	color: {$BBS_ALINK_COLOR};
}
#form {
	text-align: left;
}
#copyright {
	font-size: 12px;
}
@media screen and (max-width: 320px) {
    body {
    	width: 300px;
		font-size: 14px;
	}
    #container {
    	width: 300px;
	}
	#listhtml{
		font-size: 12px;
	}
	#mobile{
		font-size: 12px;
	}
	#title{
		font-size: 14px;
	}
	#form {
		text-align: left;
		font-size: 12px;
	}
	#post-title{
		margin-left: 3px;
    	width: 230px;
	}
	#e-mail {
		width: 75px;
	}
	#name {
		width: 90px;
	}
	#message {
		width: 300px;
	}
}
</style>
<title>{$BBS_TITLE}</title>
{$analysistxt}
</head>
<body>
<center>
	<div id="container">
		<div id="title">{$BBS_TITLE}</div>
		<div id="summary">{$headtxt}</div>
		
		<div id="listhead">上位スレッド一覧({$setting->BBS_THREAD_NUMBER}件まで)</div>
		<div id="threlist">

EOM
			);
			$cnt = 0;
			foreach($subjecttxt->data as $row)
			{
				$cnt++;
				list($key, $title) = explode('<>', $row);
				$key = preg_replace('/\.dat/', '', $key);

				$output->PrintStr(<<<EOM
			<div class="thread"><a href="{$key}/|50">{$title}</a></div>

EOM
				);

				if($cnt >= $setting->BBS_THREAD_NUMBER)
				{
					break;
				}
			}
			
			$output->PrintStr(<<<EOM
		</div>
		<div id="navigate"><a href="{$baseurl}/navigate.html">２ちゃんねるブラウザを使いましょう</a></div>
		<div id="listhtml"><a href="{$baseurl}/../{$setting->bbs}/subback.html">全スレッド一覧</a></div>
		<div id="mobile"><a href="{$baseurl}/threlist.php/{$setting->bbs}">携帯用</a></div>
		<div id="form">
			<form method="post" action="{$baseurl}/bbs.cgi?guid=On" utn="utn">
			タイトル：<input type="text" name="subject" id="post-title" value="" /><br>
	 		名前：<input type="text" name="FROM" id="name" value="">
	 		E-mail(<font size="1">省略可</font>)：<input type="text" name="mail" id="e-mail" value=""><br>
	 		 <input type="submit" value="スレッド作成" /><br>
	 		<textarea name="MESSAGE" id="message" rows="6" erap="off"></textarea><br>
	 		<input type="hidden" name="bbs" value="{$setting->bbs}">
	 		<input type="hidden" name="time" value="">
	 		<input type="hidden" name="suka" value="">
	 		</form><br>
		</div>
	</div>
	<span id="copyright">&copy; 2010 will-co21.net</span>
</center>
</body>
</html>

EOM
				);
			
			if(file_exists($setting->indexhtml) == false)
			{
				fclose(fopen($setting->indexhtml, "w"));
				chmod($setting->indexhtml, 0666);
			}
			
			$result = $output->FlushToFile($setting->indexhtml, $indexhtml->file_islocked);

			if(ErrInfo::IsErr($result))
			{
				return $result;
			}			
		
			return true;
		}
	}
?>
