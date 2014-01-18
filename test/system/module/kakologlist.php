<?php
	class KakoLogList
	{
		var $data;
		var $datapath;
		var $file_islocked;
		
		function KakoLogList()
		{
			$this->file_islocked = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("KakoLogList");
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();
			
			$this->datapath = "../{$setting->bbs}/kako/kakologlist.txt";
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
		
		function getRows()
		{
			return $this->data;
		}
	}
?>
