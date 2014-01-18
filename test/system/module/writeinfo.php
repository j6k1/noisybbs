<?php
	class WriteInfo
	{
		var $hostinfo;
		var $FROM;
		var $mail;
		var $MESSAGE;
		var $subject;
		var $datetime;
		var $id;
		var $idsuffix;
		var $hostname;

		var $israndfile_locked;
		
		function WriteInfo($postdata, &$hostinfo)
		{
			$this->hostinfo = $hostinfo;
			$this->israndfile_locked = false;
			
			$this->subject    = $this->getvalue($postdata, "subject");
			$this->FROM = $this->getvalue($postdata, "FROM");
			$this->mail = $this->getvalue($postdata, "mail");
			$this->MESSAGE    = $this->getvalue($postdata, "MESSAGE");
			$this->datetime = null;
			$this->id = null;
			$this->idsuffix = null;
			$this->hostname = null;
		}
		
		function getvalue($postdata, $key)
		{
			if(isset($postdata[$key]))
			{
				return $postdata[$key];
			}
			else
			{
				return null;
			}
		}
		
		function genresdata()
		{
			$setting = SettingInfo::getInstance();

			$this->FROM = Util::bbshtmlspecialchars($this->FROM);
			$this->mail = Util::bbshtmlspecialchars($this->mail);
			$this->MESSAGE = Util::msgbody_escape($this->MESSAGE);
			
			$capinfo = CapInfo::getInstance();			
			
			if( (!isset($capinfo->capdata)) && 
			  ((isset($this->FROM) && ($this->FROM == ""))) )
			{
				$this->FROM = $setting->BBS_NONAME_NAME;
			}
			
			$this->FROM = str_replace("š", "™", $this->FROM);
			$this->FROM = str_replace("Ÿ", "ž", $this->FROM);
			
			$trip_key = strstr($this->FROM, '#');

			if(($trip_key != "") && ($trip_key != "#")){
				$this->FROM = substr($this->FROM, 0, strpos($this->FROM, '#'));
				$this->FROM .= 'Ÿ';
				$this->FROM .= Util::gen_trip(substr($trip_key, 1));
			}
			
			if(isset($capinfo->capdata))
			{
				if($this->FROM != "")
				{
					$this->FROM .= "—";
				}
				
				$this->FROM = "{$this->FROM}{$capinfo->capdata->name}š";
			}
			
			if(isset($this->subject))
			{
				$this->subject = Util::bbshtmlspecialchars($this->subject);
			}
			
			list($usec, $time) = explode(" ", microtime());
			$this->datetime = Util::cnv_bbs_date($time, $usec);
			$this->id = $this->genid();
			
			if(($this->hostinfo->carrier === null) || ($setting->AIRPHONEIP_CHK == ""))
			{
				if( isset($setting->p2ipaddrs) && 
					(in_array($this->hostinfo->ipaddr, $setting->p2ipaddrs)) )
				{
					$this->idsuffix = "P";
				}
				else if($this->hostinfo->ismona)
				{
					$this->idsuffix = "o";
				}
				else
				{
					$this->idsuffix = "0";
				}
			}
			else if($this->hostinfo->carrier == "SI")
			{
				$this->idsuffix = "{$this->hostinfo->carrier}";
			}
			else
			{
				$this->idsuffix = "{$this->hostinfo->carrier}O";
			}
			
			if($setting->BBS_SLIP == "checked")
			{
				$this->id = "{$this->id}{$this->idsuffix}";
			}
		}
		
		function genid()
		{
			$setting = SettingInfo::getInstance();
			
			if(($setting->BBS_DISP_IP !== null) && ($setting->BBS_DISP_IP == "checked"))
			{
				$this->hostname = $this->hostinfo->hostname;
			}
			
			$result = true;
			
			if(file_exists($setting->randseed_file) == false)
			{
				$randkey = $this->writerandseed();
			}
			else
			{
				$randseed = file_get_contents($setting->randseed_file);
				list($date, $randkey) = explode("<>", $randseed);
				
				if($date != date("Ymd"))
				{
					$randkey = $this->writerandseed();
				}
			}
			
			$seed = $this->hostinfo->getidseed();
			
			$seed = md5($seed);
			$seed = substr($seed, -8);
			$seed .= $setting->bbs;
			$seed .= date("d");
			$seed .= $randkey;
			
			$id = Util::gen_id($seed);
			
			return $id;
		}
		
		function writerandseed()
		{
			$setting = SettingInfo::getInstance();
			$output = new BBSOutPutStream();

			$mtime = microtime();
			$output->PrintStr(date("Ymd"));
			$output->PrintStr("<>");
			$randkey = substr($mtime, 2, (strpos($mtime, " ") - 2));
			$output->PrintStr($randkey);
			
			if(file_exists($setting->randseed_file) == false)
			{
				fclose(fopen($setting->randseed_file, "w"));
				chmod($setting->randseed_file, 0666);
			}
			
			$result = $output->FlushToFile($setting->randseed_file, $this->israndfile_locked);
			
			if(ErrInfo::IsErr($result))
			{
				Logging::writelog($regulation, __FUNCTION__ , __LINE__ , 
					$result->sysmsg);
			}

			return $randkey;
		}
	}
?>
