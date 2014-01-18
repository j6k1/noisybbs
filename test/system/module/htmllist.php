<?php
	class HtmlList
	{
		var $data;
		var $datapath;
		var $file_islocked;
		
		function HtmlList()
		{
			$this->file_islocked = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("HtmlList");
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();
			
			$this->datapath = "../{$setting->bbs}/kako/htmllist.txt";
			if(file_exists($this->datapath) == false)
			{
				fclose(fopen($this->datapath, "w"));
				chmod($this->datapath, 0666);
			}
			
			$data = FileReader::Read($this->datapath);

			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			if($data == "")
			{
				$this->data = array();
			}
			else
			{
				$data = explode("\n", $data);
				array_pop($data);
				$this->data = array();
				
				foreach($data as $val)
				{
					list($threadkey, $title) = explode("<>", $val);
					$this->data[$threadkey] = $title;
				}
			}
			
			return true;
		}
		
		function Append($key, $val)
		{
			$this->data[$key] = $val;
			
			return true;
		}
		
		function Delete($key)
		{
			unset($this->data[$key]);
			
			return true;
		}
		
		function Save()
		{
			$output = new BBSOutPutStream();
			
			$data = array();
			
			foreach($this->data as $key => $val)
			{
				array_push($data, "{$key}<>{$val}");
			}
			$data = implode("\n", $data);
			
			if($data != "")
			{
				$data .= "\n";
			}
			
			$output->PrintStr($data);
			$ret = $output->FlushToFile($this->datapath, $this->file_islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($this->file_islocked)
				{
					Util::file_unlock($this->datapath);
				}
				
				return $ret;
			}			
		
			return true;
		}
		
		function Contain($key)
		{
			return isset($this->data[$key]);
		}
		
		function Ksort()
		{
			ksort($this->data, SORT_NUMERIC);
		}
		
		function Krsort()
		{
			krsort($this->data, SORT_NUMERIC);
		}
		
		function getRows()
		{
			return $this->data;
		}
		
		function genHtml($bbs, $key)
		{
			$dir1 = substr($key, 0, 4);
			$dir2 = substr($key, 0, 5);
			
			$path = "../{$bbs}/kako/{$dir1}/{$dir2}/{$key}.dat";
			$data = file_get_contents($path);
			$data = explode("\n", $data);
			array_pop($data);
			$last = count($data);
			$last50 = $last - 50 + 1;
			
			if($last50 < 1)
			{
				$last50 = 1;
			}
			
			$rooturl = Util::getRootUrl();
			$fields = explode("<>", $data[0]);
			
			if(count($fields) < 5)
			{
				$title = 'スレタイ不明';
			}
			else
			{
				list(,,,,$title) = $fields;
			}
			
			$output = new BBSOutPutStream();
			
			$output->PrintStr( <<<EOM
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis">
<meta name="Author" content="">
<meta content="no-cache" http-equiv="Pragma">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="../../../../test/ajax/read.css" media="screen">
<title><!-- <>TITLE<> -->{$title}<!-- <>TITLEND<> --></title>
</head>
<body>
<div id="header">
<span class="headlist"></span>
<span style='float:left;'><a href="#footer">↓</a>
<a href="{$rooturl}/{$bbs}/index.html">掲示板へ戻る</a>
<a href="{$key}.html">全部</a> <a href="#R1">1-</a>

EOM
				);
			for($i=101; $i < $last ; $i+=100)
			{
				$output->PrintStr(<<<EOM
 <a href="#R{$i}">{$i}-</a>
EOM
					);
			}
			$output->PrintStr(<<<EOM
 <a href="#R{$last50}">最新50</a>
</span>
<br style="clear:both;">
</div>
<h1 id="subject">{$title}</h1>
<dl class="thread">

EOM
				);			
			$i=1;
			foreach($data as $row)
			{
				$fields = explode("<>", $row);
				
				if(count($fields) < 4)
				{
					for($j=0; $j < 4; $j++)
					{
						$fields[$j] = 'ここ壊れてます';
					}
				}
				
				list($from, $mail, $dateid, $body) = $fields;
				$name = "<b>{$from}</b>";
				
				$output->PrintStr(<<<EOM
<!-- <>Res[{$i}]<> -->
<dt><a name="R{$i}">{$i}</a> 名前：<span class="name">{$name}</span>
<span class="info">[$mail]：{$dateid}</span><dd>{$body}</dd>
<!-- <>ResEnd[{$i}]<> -->

EOM
					);
					
				$i++;
			}
			
			$output->PrintStr(<<<EOM
</dl>
<div id="footer">
<hr class="footer"><a href="#header">↑</a> <a href="{$rooturl}/{$bbs}/index.html">掲示板へ戻る</a>
 <a href="{$key}.html">全部</a>
 <a href="#R{$last50}">最新50</a>
</div>
</body>
</html>
			
EOM
				);
				
			$islocked = false;
			$htmlpath = "../{$bbs}/kako/{$dir1}/{$dir2}/{$key}.html";
			
			fclose(fopen($htmlpath, "w"));
			
			$ret = $output->FlushToFile($htmlpath, $islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($islocked)
				{
					Util::file_unlock($htmlpath);
				}
				
				return $ret;
			}			
		}
	}
?>
