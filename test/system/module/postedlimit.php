<?php
	class PostedLimit
	{
		var $file_islocked;
		var $loglist;

		function PostedLimit()
		{
			$this->file_islocked = false;
			$this->loglist = null;
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();

			if(file_exists($setting->rentou_chk_file) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$setting->rentou_chk_file}が見つかりません。");
			}
			
			$this->loglist = file_get_contents($setting->rentou_chk_file);
			
			return true;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("PostedLimit");
		}
		
		function findhostlog(&$hostinfo)
		{
			$setting = SettingInfo::getInstance();
			$postedlimit = PostedLimit::getInstance();

			if(isset($postedlimit->loglist) === false)
			{
				return Logging::generrinfo($postedlimit,
					__FUNCTION__ , __LINE__ , 
					"{get_class($postedlimit)}が初期化されていません。");
			}
			
			$bbs = $setting->bbs;
		
			$host = $hostinfo->getidseed();
			$host = Util::cnvpreghostid($host);		
			
			if(preg_match('/'.$host.'<>([0-9]+)<>([0-9]+)\n/m', $postedlimit->loglist, $match) > 0)
			{
				return $match;
			}
			else
			{
				return false;
			}			
		}
		
		function rentou_check(&$hostinfo, $time)
		{	
			$setting = SettingInfo::getInstance();
			$postedlimit = PostedLimit::getInstance();
			$result = PostedLimit::findhostlog($hostinfo);
			
			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			if(CapInfo::hasAuthority("PASS_POSTEDLIMIT"))
			{
				return true;
			}
			
			if($result === false)
			{
				PostedLimit::rentoulist_update($hostinfo, $time);
				return true;
			}
			
			if($setting->POSTEDLIMIT_TYPE == "sambalike")
			{
				$cnt = (int)$result[2];
				
				$msgargs = array();
				if($cnt >= $setting->SAMBACOUNT)
				{
					if( ($setting->SAMBATIME * 60) <= ($time - (int)$result[1]))
					{
						PostedLimit::rentoulist_update($hostinfo, $time, true);
						return true;
					}
					else
					{
						PostedLimit::rentoulist_update($hostinfo, $time, false, $cnt);
						
						$msgargs["wait"] = $setting->SAMBATIME - ($time - $result[1]);
						return Logging::generrinfo($postedlimit,
							__FUNCTION__ , __LINE__ , 
							$errmsg->postedlimit, ErrMsgID::get()->SAMBANOW, $msgargs);
					}
				}
								
				if(($time - $result[1]) < $setting->RES_INTERVAL)
				{
					$cnt++;
					if($cnt < ($setting->SAMBACOUNT - 1))
					{
						$errid = ErrMsgID::get()->SAMBA1;
						$msgargs["wait"] = $setting->RES_INTERVAL;
						$msgargs["time"] = $time - $result[1];
					}
					else if($cnt == ($setting->SAMBACOUNT - 1))
					{
						$errid = ErrMsgID::get()->SAMBA2;
					}
					else
					{
						$errid = ErrMsgID::get()->SAMBA3;
						$msgargs["resettime"] = $setting->SAMBATIME;
					}
					
					PostedLimit::rentoulist_update($hostinfo, $time, false, $cnt);
					return Logging::generrinfo($postedlimit,
						__FUNCTION__ , __LINE__ , 
						$errmsg->postedlimit, $errid, $msgargs);
				}
				
				PostedLimit::rentoulist_update($hostinfo, $time, false, $cnt);
				return true;
			}
			else if($setting->POSTEDLIMIT_TYPE == "intervalonly")
			{
				if(($time - $result[1]) < $setting->RES_INTERVAL)
				{
					return Logging::generrinfo($postedlimit,
						__FUNCTION__ , __LINE__ , 
						$errmsg->postedlimit, ErrMsgID::get()->POSTEDLIMIT, 
						array("wait" => $setting->RES_INTERVAL));
				}
				else
				{
					PostedLimit::rentoulist_update($hostinfo, $time);
					return true;
				}
			}
			else
			{
				return Logging::generrinfo($postedlimit,
					__FUNCTION__ , __LINE__ , 
					"連投規制のモードが未定義の値です。" );
			}
		}
		
		function rentoulist_update(&$hostinfo, $time, $delete = false, $cnt = 0)
		{	
			$setting = SettingInfo::getInstance();
			$postedlimit = PostedLimit::getInstance();

			$host = $hostinfo->getidseed();
			$preg_host = Util::cnvpreghostid($host);
			
			$host = Util::cnvloghostid($host);
			
			if($delete == true)
			{
				$rentouchk_rec = "";
			}
			else
			{
				$rentouchk_rec = "{$host}<>{$time}<>{$cnt}\n";
			}
			
			$result = PostedLimit::findhostlog($hostinfo);
			
			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			if($result !== false)
			{
				$postedlimit->loglist = preg_replace('/^'.$preg_host.'<>\d+<>\d+\n/m', $rentouchk_rec, $postedlimit->loglist);
			}else{
				$postedlimit->loglist .= $rentouchk_rec;
			}
			
			$output = new BBSOutPutStream();
			
			$output->PrintStr($postedlimit->loglist);			
			$result = $output->FlushToFile($setting->rentou_chk_file, $postedlimit->file_islocked);
			
			if(ErrInfo::IsErr($result))
			{
				Logging::writelog($regulation, __FUNCTION__ , __LINE__ , 
					$result->sysmsg);
			}
			
			return true;
		}
	}
?>
