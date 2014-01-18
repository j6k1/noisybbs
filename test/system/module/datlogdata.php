<?php
	class DatLogData
	{
		var $file_islocked;
		var $datname;
		var $data;

		function DatLogData()
		{
			$this->file_islocked = false;
			$this->data = null;
		}
		
		function ReadData($threkey, $lock = false)
		{
			$setting = SettingInfo::getInstance();

			$this->file_islocked = false;

			if(!isset($threkey))
			{
				$threkey = $setting->thread_key;
			}
			
			$this->datname = "../{$setting->bbs}/system/data/datlog/{$threkey}.log";
			$data = FileReader::Read($this->datname);

			if(ErrInfo::IsErr($data))
			{
				return $data;
			}

			$this->data = explode("\n", $data);
			array_pop($this->data);
			
			return true;
		}

		function appendLog(&$hostinfo, $threcre = false, $threkey = null)
		{
			$setting = SettingInfo::getInstance();

			if(!isset($threkey))
			{
				$threkey = $setting->thread_key;
			}
			
			$this->datname = "../{$setting->bbs}/system/data/datlog/{$threkey}.log";
			$hostid = $hostinfo->gethostid();

			if(($hostinfo->carrier != null) || (isset($hostinfo->p2userid)))
			{
				$hostid .= "::hostname=({$hostinfo->hostname})";
			}
			$row = <<<EOM
{$hostinfo->ipaddr}<>{$hostid}<>{$hostinfo->useragent}

EOM;
			
			$output = new BBSOutPutStream();
			
			$output->PrintStr($row);
			
			if($threcre)
			{
				fclose(fopen($this->datname, "w"));
				$result = $output->FlushToFile($this->datname, $this->file_islocked, 0666);
			}
			else
			{
				$result = $output->FlushToFileA($this->datname, $this->file_islocked);
			}
			
			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			return true;
		}
	}
?>
