<?php
	class Logging
	{
		var $errlogfile = "bbserrlog.log";
		var $errloglevel = 4;
		
		function Logging()
		{
			$setting = SettingInfo::getInstance();			
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("Logging");
		}			
		
		function Init()
		{
			$setting = SettingInfo::getInstance();
			$errlogdir = "";
			
			if(isset($setting->bbs) == false)
			{
				$errlogdir = "{$setting->systemdir}/{$setting->datadir}/{$setting->logdir}";
			}
			else
			{
				$errlogdir = $setting->logdir;
			}
			
			$this->errlogfile = "{$errlogdir}/{$this->errlogfile}";
			
			return true;
		}
		
		function genlogtxt($class, $func, $line, $msg, $level = null)
		{
			$logging = Logging::getInstance();			
			if($level == null)
			{
				$level = $logging->errloglevel;
			}
			
			$classname = get_class($class);
			$date = date("Ymd H:i:s");
			switch($level)
			{
				case 1:
					$logtxt = "{$date} : {$msg}\n";
					break;
				case 2:
					$logtxt = "{$date} : {$classname} : {$msg}\n";
					break;
				case 3:
					$logtxt = "{$date} : {$classname}:{$func} : {$msg}\n";
					break;
				case 4:
					$logtxt = "{$date} : {$classname}:{$func}:Line={$line} : {$msg}\n";
					break;
				default:
					$logtxt = "エラーレベル{$level}は未定義の値です。";
			}
			
			return $logtxt;
		}
		
		function generrinfo($class, $func, $line, $msg, $msgid = null, $msgargs = null, $level = null)
		{
			$msg = Logging::genlogtxt($class, $func, $line, $msg, $level = null);
			$result = new ErrInfo($msg, $msgid, $msgargs);
			
			return $result;
		}

		function genusrerrinfo($msgid, $msgargs = null)
		{
			$msg = "";
			$result = new ErrInfo($msg, $msgid, $msgargs);
			
			return $result;
		}

		function writelog($class, $func, $line, $msg)
		{
			$logging = Logging::getInstance();			
			$logtxt = Logging::genlogtxt($class, $func, $line, $msg, 4);
			
			if(file_exists($logging->errlogfile) == false)
			{
				$fp = fopen($logging->errlogfile, "w");
				chmod($logging->errlogfile, 0666);
				fclose($fp);
			}

			$result = Util::appendfwrite($logging->errlogfile, $logtxt);
			
			return $result;
		}
	}
?>