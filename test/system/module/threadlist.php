<?php
	class ThreadList
	{
		function ThreadList()
		{
			
		}

		function Init($pathinfo)
		{
			BBSList::getInstance()->Init();
			$setting = SettingInfo::getInstance();			

			if(count($pathinfo) < 2)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbskey_null, ErrMsgID::get()->URLFORMAT);
			}
			
			$bbs =  $pathinfo[1];

			if(!in_array($bbs, Util::getBBSList()))
			{
				 return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					$errmsg->bbs_notfound, ErrMsgID::get()->URLFORMAT);
			}

			$ret = SubjectText::getInstance()->Init();

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			$ret = SubjectText::getInstance()->ReadData($setting);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			return true;
		}
		
		function DispThreadList($pathinfo)
		{
			$setting = SettingInfo::getInstance();
			$bbs = $setting->bbs;
			$baseurl = Util::getBaseUrl();
				
			$data = SubjectText::getInstance()->getRows();
			$threcnt = count($data);
			
			$page = (isset($pathinfo[2])) ? $pathinfo[2] : 1;
			
			if($page < 1)
			{
				$page = 1;
			}
			
			$start = ($page  - 1) * 10;
			$end = $page * 10;
			
			$output = new BBSOutPutStream();
			$BBS_TITLE = htmlspecialchars($setting->BBS_TITLE, ENT_QUOTES);
			$BBS_SUBJECT_COLOR = Util::valid_css_color_val($setting->BBS_SUBJECT_COLOR);
			
			$output->PrintStr(<<<EOM
<html><head><title>{$BBS_TITLE}</title>
<meta http-equiv=Content-Type content="text/html;charset=Shift-JIS">
</head><!--nobanner-->
<font color={$BBS_SUBJECT_COLOR} size=+1>{$BBS_TITLE}</font>
<dl><br><br>

EOM
			);
			
			$i = 0;
			foreach($data as $key => $val)
			{
				if($i < $start)
				{
					$i++;
					continue;
				}
				
				if(($i >= $threcnt) || ($i >= $end))
				{
					break;
				}
				$i++;
			
				list(, $title) = explode("<>", $val);
				$output->PrintStr(<<<EOM
<a href="{$baseurl}/mread.php/{$bbs}/{$key}/|10">{$title}</a><br>

EOM
				);
			}
			if($page > 1)
			{
				$past = $page - 1;
				$output->PrintStr(<<<EOM
<a href="{$baseurl}/threlist.php/{$bbs}/{$past}">‘O</a> 
EOM
				);
			}

			if(($page * 10) < $threcnt)
			{
				$next = $page + 1;
				$output->PrintStr(<<<EOM
<a href="{$baseurl}/threlist.php/{$bbs}/{$next}">ŽŸ</a>

EOM
				);
			}
			
			$output->PrintStr(<<<EOM
</dl><hr>
</body></html>

EOM
			);
			
			$output->Flush();
			return true;
		}		
	}
?>
