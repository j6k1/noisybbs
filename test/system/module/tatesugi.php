<?php
	class TateSugi
	{
		var $data;
		var $file_islocked;
		
		function TateSugi()
		{
			$this->file_islocked = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("TateSugi");
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();
			
			if(file_exists("{$setting->threcrecnt_file}") == false)
			{
				fclose(fopen("{$setting->threcrecnt_file}", "w"));
				chmod("{$setting->threcrecnt_file}", 0666);
			}
			
			$data = FileReader::Read("{$setting->threcrecnt_file}");

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
					list($ipgroup, $cnt) = explode("<>", $val);
					$this->data[$ipgroup] = $cnt;
				}
			}
			
			return true;
		}
		
		function Find($ip)
		{
			$ip = explode(".", $ip);
			$ipgroup = ( ( ((int)$ip[0]) & 0x4 ) << 8 ) + $ip[1];
			
			$result = true;
			
			$setting = SettingInfo::getInstance();

			if( (isset($this->data[$ipgroup])) && 
			    ($this->data[$ipgroup] >= $setting->THRECRE_MAX) )
			{
			    return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"TATESUGI‹K§‚Å‚·B", 
					ErrMsgID::get()->TATESUGI);			
			}
			
			return true;
		}
		function Update($ip)
		{
			$setting = SettingInfo::getInstance();

			$ip = explode(".", $ip);
			$ipgroup = ( ( ((int)$ip[0]) & 0x4 ) << 8 ) + $ip[1];

			if(isset($this->data[$ipgroup]))
			{
				$this->data[$ipgroup]++;
			}
			else
			{
				$this->data[$ipgroup] = 1;
			}
			
			while(count($this->data) > $setting->BBS_THREAD_TATESUGI)
			{
				array_pop($this->data);
			}
			
			return true;
		}
		
		function Save()
		{
			$setting = SettingInfo::getInstance();

			$output = new BBSOutPutStream();
			
			$data = array();
			
			foreach($this->data as $ipgroup => $cnt)
			{
				array_push($data, "{$ipgroup}<>{$cnt}");
			}
			$data = implode("\n", $data);

			if($data != ""){$data .= "\n";}

			$output->PrintStr($data);
			$ret = $output->FlushToFile($setting->threcrecnt_file, $this->file_islocked);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}			
		
			return true;
		}
	}
?>
