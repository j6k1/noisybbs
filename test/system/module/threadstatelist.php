<?php
	class ThreadStateList
	{
		var $data;
				
		function ThreadStateList()
		{
			$this->data = null;
		}
	
		function &getInstance()
		{
			return Singleton::getInstance("ThreadStateList");
		}
			
		function Init()
		{		
			$setting = SettingInfo::getInstance();
			$errmsg = ErrMessage::getInstance();

			$bbs = $setting->bbs;
			$path = "../{$bbs}/{$setting->thread_state_file}";
			
			if(!file_exists($path))
			{
				fclose(fopen($path, "w"));
				chmod($path, 0666);
			}
			
			$data = FileReader::Read($path);
			
			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			if($data == "")
			{
				$data = array();
			}
			else
			{
				$data = explode("\n", $data);
				array_pop($data);
			}
			
			foreach($data as $val)
			{
				list($key, $state) = explode("<>", $val);
				if($state == "")
				{
					$this->data[$key] = array();
				}
				else
				{
					$this->data[$key] = explode("&", $state);
				}
			}
			
			return true;
		}
		
		function SetState($threadkey, $val)
		{
			if(!isset($this->data[$threadkey]))
			{
				$this->data[$threadkey] = array();
			}
			
			$key = array_search($val, $this->data[$threadkey]);
			
			if($key == true)
			{
				return true;
			}
			
			array_push($this->data[$threadkey], $val);
			
			return true;
		}
		
		function ReleaseState($threadkey, $val)
		{
			if(!isset($this->data[$threadkey]))
			{
				return true;
			}
			
			$key = array_search($val, $this->data[$threadkey]);
			
			if($key === false)
			{
				return true;
			}
			
			unset($this->data[$threadkey][$key]);
			
			return true;
		}
		
		function Delete($key)
		{
			unset($this->data[$key]);
			
			return true;
		}
		
		function hasState($key, $state)
		{
			if(!isset($this->data[$key]))
			{
				return false;
			}
			
			return in_array($state, $this->data[$key]);
		}
		
		function Save()
		{
			$setting = SettingInfo::getInstance();
			$path = "../{$setting->bbs}/{$setting->thread_state_file}";
			$output = new BBSOutPutStream();
			
			$data = array();
			
			foreach($this->data as $key => $values)
			{
				$val = implode("&", $values);
				array_push($data, "{$key}<>{$val}");
			}
			
			$data = implode("\n", $data);

			if($data != "")
			{
				$data .= "\n";
			}

			$output->PrintStr($data);
			$islocked = false;
			$ret = $output->FlushToFile($path, $islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($islocked)
				{
					Util::file_unlock($this->datapath);
				}
				
				return $ret;
			}			
		
			return true;
		}
	}
?>