<?php
	class SubbackHtml
	{
		var $file_islocked;
		
		function SubbackHtml()
		{
			$this->file_islocked = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("SubbackHtml");
		}
		
		function RemoveInstance()
		{
			Singleton::RemoveInstance("SubbackHtml");
		}
		
		function WriteData()
		{
			$setting = SettingInfo::getInstance();			
			$subjecttxt = SubjectText::getInstance();
			$subbackhtml = SubbackHtml::getInstance();
			$baseurl = Util::getBaseUrl();

			$php_mode = (($setting->BBS_READ_SCRIPT != null) && 
				$setting->BBS_READ_SCRIPT == "php") ? true : false;
			if($php_mode) { $ext = "cgi"; } else { $ext = "html"; }
			$basepath = ($ext == "html") ?
				"read.html#!/{$setting->bbs}/" : "read.{$ext}/{$setting->bbs}/";
			
			$html = "";
			
			$html .= <<<EOM
<html lang="ja">
<head>
<title>
</title>
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="{$baseurl}/" target="block">
<style type="text/css"><!--
a { margin-right: 1em; }div.floated { border: 1px outset honeydew; float: left; height: 20em; line-height: 1em; margin: 0 0 .5em 0; padding: .5em; }div.floated, div.block { background-color: honeydew; }div.floated a, div.block a { display: block; margin-right: 0; text-decoration: none; white-space: nowrap; }div.floated a:hover, div.block a:hover { background-color: cyan; }div.floated a:active, div.block a:active { background-color: gold; }div.right { clear: left; text-align: right; }div.right a { margin-right: 0; }div.right a.js { background-color: dimgray; border: 1px outset dimgray; color: palegreen; text-decoration: none; }
-->
</style>
</head>
<body>
<div>
EOM;
			$cnt = 0;
			foreach($subjecttxt->data as $row)
			{
				$cnt++;
				list($key, $title) = explode('<>', $row);
				$key = preg_replace('/\.dat$/', '', $key);
				
				$html .= <<<EOM
<span style="font-size : 12px;"><a href="{$basepath}{$key}/|50">{$cnt}: {$title}</a></span>
EOM;
			}
			
			$html .= <<<EOM
<span style="font-size : 12px;"><b><a href="../../test/kako.php/{$setting->bbs}">過去ログ倉庫</a></b></span>
</div>
</body>
</html>

EOM;

			$output = new BBSOutPutStream();
			
			$output->PrintStr($html);

			if(file_exists($setting->subbackhtml) == false)
			{
				fclose(fopen($setting->subbackhtml, "w"));
				chmod($setting->subbackhtml, 0666);
			}
			
			$result = $output->FlushToFile($setting->subbackhtml, $subbackhtml->file_islocked);

			if(ErrInfo::IsErr($result))
			{
				return $result;
			}			
		
			return true;
		}
	}
?>
