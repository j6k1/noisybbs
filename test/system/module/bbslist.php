<?php
	class BBSList
	{
		var $data;
		var $datapath;
		var $file_islocked;
		
		function BBSList()
		{
			$this->file_islocked = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("BBSList");
		}
		
		function Init()
		{
			$this->datapath = "../_service/bbslist.txt";
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
				$this->data = $data;				
			}
			
			return true;
		}
		
		function getRows()
		{
			return $this->data;
		}
		
		function Append($val)
		{
			array_push($this->data, $val);
			
			return true;
		}
		
		function Delete($bbs)
		{
			$count = count($this->data);
			for($i=0 ; $i < $count ; $i++)
			{
				if($this->data[$i] == $bbs)
				{
					array_splice($this->data, $i, 1);
				}
			}
			
			return true;
		}
		
		function Save()
		{
			$output = new BBSOutPutStream();
			
			$data = implode("\n", $this->data);
			
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
	}
?>
