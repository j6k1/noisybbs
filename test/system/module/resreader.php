<?php
	class ResReader
	{
		var $datdata;
		var $baseurl;
		var $start;
		var $end;
		var $first;
		
		function ResReader()
		{
			$this->datdata = null;
			$this->baseurl = Util::getBaseUrl();
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("ResReader");
		}			
		
		function Init($key)
		{
			$this->datdata = new DatData();

			$ret = $this->datdata->ReadData($key);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			return true;
		}
		
		function getResLines($option)
		{
			$setting = SettingInfo::getInstance();			

			$result = ResReader::ParseOption($option);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			$optinfo = $result;

			$pastnums = ResReader::getPastNums($optinfo["st"], $optinfo["end"]);
			$nextnums = ResReader::getNextNums($optinfo["st"], $optinfo["end"]);
			
			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			$output = new BBSOutPutStream();

			list(,,,,$subject) = explode("<>", $this->datdata->data[0]);

			if($optinfo["st"] != 1 && $optinfo["first"] == true)
			{
				$output->PrintStr($this->getResHtml(1));
			}
			
			$st = $optinfo["st"];
			$end = $optinfo["end"];
			
			for($i=$st; $i <= $end ; $i++)
			{
				$output->PrintStr($this->getResHtml($i));
			}

			return $output->getBuffString();
		}
		
		function getResHtml($resno)
		{
			$setting = SettingInfo::getInstance();			
			$fields = explode("<>", $this->datdata->data[$resno-1]);
			
			if(count($fields) < 4)
			{
				for($i=0; $i < 4; $i++)
				{
					$fields[$i] = 'ここ壊れてます';
				}
			}
			
			list($from, $mail, $dateid, $body) = $fields;
			
			$body = Util::addLink($body);
			$body = $this->addAnchorLink($body);
			$resline = "";
		
			$resline .= "<dt id=\"a{$resno}\">{$resno} : ";
			$resline .= "<span class=\"name\"><b>{$from}</b></span> ";
			$resline .= "<span class=\"info\">[{$mail}] ";
			$resline .= "{$dateid}</span></dt>";
			$resline .= "<dd>{$body}</dd>";
			$resline .= "\n";
			return $resline;
		}

		function MobileHeadDisp(&$output, $subject, $optinfo)
		{
			$setting = SettingInfo::getInstance();			

			$output->PrintStr(<<<EOM
<html><head><title>{$subject}</title>
<meta http-equiv=Content-Type content="text/html;charset=Shift-JIS">
</head><!--nobanner-->

EOM
);
			return true;
		}
		
		function MobileMenuDisp(&$output, $key, $subject, $pastnums, $nextnums)
		{
			$setting = SettingInfo::getInstance();			
			$resreader = ResReader::getInstance();			

			$bbs = $setting->bbs;
			$baseurl = $resreader->baseurl;
			$linkbase = "{$baseurl}/mread.php/{$bbs}/{$key}";
			
			$output->PrintStr(<<<EOM
<a href="{$baseurl}/threlist.php/{$setting->bbs}">覧</a> 
<a href="{$linkbase}/1-10n">1-</a> 
<a href="{$linkbase}/{$pastnums["from"]}-{$pastnums["to"]}n">前</a> 
<a href="{$linkbase}/{$nextnums["from"]}-{$nextnums["to"]}n">次</a> 
<a href="{$linkbase}/|10">新</a> 
<a href="#res">ﾚｽ</a><br><br>
<dl><font color={$setting->BBS_SUBJECT_COLOR} size=+1>{$subject}</font><br><br>

EOM
);
			return true;
		}
		
		function MobileFootDisp(&$output, $key, $pastnums, $nextnums)
		{
			$setting = SettingInfo::getInstance();			
			$resreader = ResReader::getInstance();			

			$bbs = $setting->bbs;
			$baseurl = $resreader->baseurl;
			$linkbase = "{$baseurl}/mread.php/{$bbs}/{$key}";
			$formlink = "{$baseurl}/mresform.php/{$bbs}/{$key}";
			$output->PrintStr(<<<EOM
</dl><hr>
<a href="{$linkbase}/{$pastnums["from"]}-{$pastnums["to"]}n">前</a> 
<a href="{$linkbase}/{$nextnums["from"]}-{$nextnums["to"]}n">次</a>
<hr><a name=res></a>
<a href={$formlink}>書き込む</a>
</body></html>

EOM
);
			return true;
		}
		
		function MobileDisp($pathinfo)
		{
			$setting = SettingInfo::getInstance();			

			if(count($pathinfo) < 4)
			{
				$pathinfo[3] = "";
			}
			
			$result = ResReader::ParseOption($pathinfo[3]);
			$optinfo = $result;
			
			$pastnums = ResReader::getPastNumsMobile($optinfo["st"], $optinfo["end"]);
			$nextnums = ResReader::getNextNumsMobile($optinfo["st"], $optinfo["end"]);
			
			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			$output = new BBSOutPutStream();

			$fields = explode("<>", $this->datdata->data[0]);
			
			if(count($fields) < 5)
			{
				$subject = 'スレタイ不明';
			}
			else
			{
				list(,,,,$subject) = $fields;
			}
			
			ResReader::MobileHeadDisp($output, $subject, $result);
			ResReader::MobileMenuDisp($output, $pathinfo[2], $subject, $pastnums, $nextnums);

			if($optinfo["st"] != 1 && $optinfo["first"] == true)
			{
				$output->PrintStr($this->getTruncateRes(1));
			}
			
			$st = $optinfo["st"];
			$end = $optinfo["end"];
			
			for($i=$st; $i <= $end ; $i++)
			{
				$output->PrintStr($this->getTruncateRes($i));
			}
			
			ResReader::MobileFootDisp($output, $pathinfo[2], $pastnums, $nextnums);

			$output->Flush();
			
			return true;
			
		}
		
		function MobileDispOne($key, $resno)
		{
			$setting = SettingInfo::getInstance();			
			$resreader = ResReader::getInstance();			

			$bbs = $setting->bbs;
			$baseurl = $resreader->baseurl;
			$linkbase = "{$baseurl}/mread.php/{$bbs}/{$key}";
			$output = new BBSOutPutStream();

			$fields = explode("<>", $this->datdata->data[0]);
			
			if(count($fields) < 5)
			{
				$subject = 'スレタイ不明';
			}
			else
			{
				list(,,,,$subject) = $fields;
			}
			
			$this->MobileHeadDisp($output, $subject, $result);

			$output->PrintStr(<<<EOM
<dl><font color={$setting->BBS_SUBJECT_COLOR} size=+1>{$subject}</font><br><br>

EOM
);
			list($from, $mail, $dateid, $body) = explode("<>", $this->datdata->data[$resno-1]);
			$body = Util::ConvertMobileLink($body);

			$output->PrintStr("<hr>[{$resno}]{$from}</b>：{$dateid}<br>{$body}<br>");
			
			$output->PrintStr(<<<EOM
</dl><hr>
<a href="{$baseurl}/threlist.php/{$bbs}">覧</a> 
<a href="{$linkbase}/1-10n">1-</a> 
<a href="{$baseurl}/mresform.php/{$bbs}/{$key}">書き込む</a>
</body></html>
EOM
);

			$output->Flush();
			
			return true;
		}
		
		function MobileResFormDisp()
		{
			$setting = SettingInfo::getInstance();			
			$resreader = ResReader::getInstance();			
			
			$time = time();
			$output = new BBSOutPutStream();

			list(,,,,$subject) = explode("<>", $this->datdata->data[0]);

			ResReader::MobileHeadDisp($output, $subject, $result);
			
			$output->PrintStr(<<<EOM
<form method="POST" action="{$resreader->baseurl}/bbs.cgi?guid=On" utn>
<input type=hidden name=bbs value={$setting->bbs}>
<input type=hidden name=key value={$setting->thread_key}>
<input type=hidden name=time value={$time}>
名前<br><input type=text name="FROM"><br>
E-mail<br><input type=text name="mail"><br>
<textarea rows=1 wrap=off name="MESSAGE"></textarea>
<br><input type=submit value="書き込む"><br>
</form>
ブラウザの「戻る」ボタンで戻って下さい。
</body></html>

EOM
);
			$output->Flush();
			
			return true;
		}
		
		function getPastNums($st,$end)
		{
			($st - 50) > 1 ? $p1 = ($st - 50) : $p1 = 1;
			($st - 1) > 1 ? $p2 = ($st - 1) : $p2 = 1;
		
			if($p1 == 1) $p2 = 50;
			
			$nums = array();
			$nums["from"] = $p1;
			$nums["to"] = $p2;
			
			return $nums;
		}
		
		function getNextNums($st,$end)
		{
			$nums = array();
			$nums["from"] = $end + 1;
			$nums["to"]   = $end + 50;
			
			return $nums;
		}
				
		function getPastNumsMobile($st,$end)
		{
			($st - 10) > 1 ? $p1 = ($st - 10) : $p1 = 1;
			($st - 1) > 1 ? $p2 = ($st - 1) : $p2 = 1;
		
			if($p1 == 1) $p2 = 10;
			
			$nums = array();
			$nums["from"] = $p1;
			$nums["to"] = $p2;
			
			return $nums;
		}
		
		function getNextNumsMobile($st,$end)
		{
			$nums = array();
			$nums["from"] = $end + 1;
			$nums["to"]   = $end + 10;
			
			return $nums;
		}
		
		function getAdminResHtml($indent, $resno, $datlog)
		{
			$setting = SettingInfo::getInstance();			
			list($from, $mail, $dateid, $body) = explode("<>", $this->datdata->data[$resno-1]);
			list($ip, $hostname, $useragent) = explode("<>", $datlog[$resno-1]);
			
			$from = Util::adminResHtmlSpecialChars($from);
			$mail = Util::adminResHtmlSpecialChars($mail);
			$dateid = Util::adminResHtmlSpecialChars($dateid);
			$body = Util::adminResHtmlSpecialChars($body);
			
			$row = "<span class='resinfo'>[{$resno}]{$from} : mail [{$mail}] {$dateid}</span>\n";
			for($i=0; $i < $indent ; $i++) $row .= "\t";
			$row .= "<div class='hostinfo'>ip[{$ip}] : host[{$hostname}] : useragent[{$useragent}]</div>\n";
			for($i=0; $i < $indent ; $i++) $row .= "\t";
			$row .= "<div class='msgbody'>{$body}</div>\n";
			
			return $row;
		}
		
		function getTruncateRes($resno)
		{
			$setting = SettingInfo::getInstance();
			
			$fields = explode("<>", $this->datdata->data[$resno-1]);
			
			if(count($fields) < 4)
			{
				for($i=0; $i < 4; $i++)
				{
					$fields[$i] = 'ここ壊れてます';
				}
			}
			list($from, $mail, $dateid, $body) = $fields;
			$body = Util::ConvertMobileLink($body);

			$output = new BBSOutPutStream();
			$output->PrintStr("<hr>[{$resno}]{$from}</b>：{$dateid}<br>");
			
			$rows = explode("<br>", $body);
			$rowcnt = count($rows);
			
			$linemax = $setting->BBS_LINE_NUMBER;
			$maxchrlen = ((int)($linemax / 4)) * 20;
			
			$chrlen = 0;
			$output_pre_chrlen = 0;
			
			$link = <<<EOM
{$this->baseurl}/mresrd.php/{$setting->bbs}/{$setting->thread_key}/{$resno}
EOM;
			for($i=0 ; $i < $rowcnt ; $i++)
			{
				$chrlen += strlen($rows[$i]);
				
				if($i > $linemax)
				{
					$delline = $rowcnt - $i;
					
					$output->PrintStr("<a href='{$link}'>省{$delline}行</a><br>");
					
					break;
				}
				else if($chrlen > $maxchrlen)
				{
					$delline = $rowcnt - $i;

					$output->PrintStr(substr($rows[$i], 0, 
						$maxchrlen - $output_pre_chrlen) . "<br>");
					$output->PrintStr("<a href='{$link}'>省{$delline}行</a><br>");
					
					break;
				}
				else
				{
					$output->PrintStr("{$rows[$i]}<br>");
					$output_pre_chrlen += strlen($rows[$i]);
				}
			}
			
			return $output->getBuffString();
		}
		
		function ParseOption($opt)
		{
			$count = count($this->datdata->data);
			
			if(preg_match('/^$/', $opt))
			{
				$result["st"] = 1;
				$result["end"] = $count;
				$result["first"] = false;
			}
			else if(preg_match('/^(\d+)-(\d+)(n?)$/', $opt, $match))
			{
				$result["st"] = $match[1];
				$result["end"] = $match[2];
				(!empty($match[3])) ? $result["first"] = false : $result["first"] = true;
				
			}
			else if(preg_match('/^-(\d+)(n?)$/', $opt, $match))
			{
				$result["st"] = 1;
				$result["end"] = $match[1];
				$result["first"] = false;
			}
			else if(preg_match('/^(\d+)-(n?)$/', $opt, $match))
			{
				$result["st"] = $match[1];
				$result["end"] = $count;
				(!empty($match[2])) ? $result["first"] = false : $result["first"] = true;
			}
			else if(preg_match('/^\|(\d+)(n?)$/', $opt, $match))
			{
				if(($count - $match[1]) > 1)
				{
					$result["st"] = $count - $match[1] + 1;
				}
				else
				{
					$result["st"] = 1;
				}
				$result["end"] = $count;
				(!empty($match[2])) ? $result["first"] = false : $result["first"] = true;				
			}
			else if(preg_match('/^(\d+)(n?)$/', $opt, $match))
			{
				$result["st"] = $match[1];
				$result["end"] = $match[1];
				(!empty($match[2])) ? $result["first"] = false : $result["first"] = true;
			}
			else
			{
				$result["st"] = 1;
				$result["end"] = $count;
				$result["first"] = false;
			}
			
			if($result["st"] < 1)
			{
				$result["st"] = 1;
			}
			
			if($result["st"] > $count)
			{
				$result["st"] = $count;
			}

			if($result["end"] < 1)
			{
				$result["end"] = 1;
			}
			
			if($result["end"] > $count)
			{
				$result["end"] = $count;
			}
			
			$this->start = $result["st"];
			$this->end = $result["end"];
			$this->first = $result["first"];
			
			return $result;
		}
		
		function addAnchorLink($body)
		{
			$regexp = array();
			$regexp[] = '((?:\d+\-\d+)|(?:\-\d+)|(?:\d+\-))';
			$regexp[] = '(\d+)';
			
			$regexp = implode("|", $regexp);
			
			return preg_replace_callback("/(?:&gt;&gt;)(?:{$regexp})/", 
				array($this, 'addAnchorLinkInner'),
				$body);
		}

		function addAnchorLinkInner($match)
		{
			$setting = SettingInfo::getInstance();			
			$resreader = ResReader::getInstance();			

			$bbs = $setting->bbs;
			$key = $setting->thread_key;
			$baseurl = $resreader->baseurl;
			$linkbase = "{$baseurl}/read.cgi/{$bbs}/{$key}/";

			if(isset($match[1]) && ($match[1] != ""))
			{
				return "<a href=\"{$linkbase}{$match[1]}\">&gt;&gt;{$match[1]}</a>";
			}
			else if(isset($match[2]) && ($match[2] != ""))
			{
				$num = intval($match[2]);
				
				if( (($num >= $this->start) && 
					 ($num <= $this->end)) || 
					 (($num == 1) && ($this->first)) )
				{
					return "<a href=\"#a{$match[2]}\">&gt;&gt;{$match[2]}</a>";
				}
				else
				{
					return "<a href=\"{$linkbase}{$match[2]}\">&gt;&gt;{$match[2]}</a>";
				}
			}
			
			return $match[0];
		}
	}
?>
