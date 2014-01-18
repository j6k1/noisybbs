<?php
	class NGWord
	{
		var $data;
		var $file_islocked;
		
		function NGWord()
		{
			$this->data = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("NGWord");
		}			

		function Load()
		{
			$setting = SettingInfo::getInstance();

			if(!file_exists($setting->ngrulefile))
			{
				return true;
			}
			
			$data = FileReader::Read($setting->ngrulefile);
			
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
				$this->data = explode("\n\n", $data);
			}
			
			return true;
		}
		
		function getRow($index)
		{
			return $this->data[$index];
		}
		
		function getRows()
		{
			return $this->data;
		}
		
		function Add($ngword)
		{
			array_push($this->data, $ngword);
			
			return true;
		}
		
		function Update($index, $ngword)
		{
			$this->data[$index] = $ngword;
			
			return true;
		}
		
		function Save()
		{
			$setting = SettingInfo::getInstance();

			$data = implode("\n\n", $this->data);
			$output = new BBSOutPutStream();
			
			$output->PrintStr($data);
			
			$islocked = false;
			$ret = $output->FlushToFile($setting->ngrulefile, $islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($islocked)
				{
					Util::file_unlock($setting->ngrulefile);
				}
				return $ret;
			}			
		
			if($data == "")
			{
				$this->data = array();
			}
			else
			{
				$this->data = explode("\n\n", $data);
			}
			
			return true;
		}
		
		function Delete($ruleno)
		{
			unset($this->data[$ruleno]);
			
			return true;
		}
		
		function Count()
		{
			return count($this->data);
		}
		
		function CheckOneRule($utf8text, $rules)
		{
			if($rules == "")
			{
				return false;
			}
			
			$rules = explode("\n", $rules);
			
			$ng = true;
			
			foreach($rules as $val)
			{
				$val = mb_convert_encoding($val, "UTF-8", "SJIS");
				
				if(@preg_match($val . 'u', $utf8text) === 0)
				{
					$ng = false;
				}
			}
			return $ng;
		}
		
		function Check($text)
		{
			$text = mb_convert_encoding($text, "UTF-8", "SJIS");
			
			$ngword = NGWord::getInstance();
			
			if(!isset($ngword->data))
			{
				return false;
			}
			
			$data = $ngword->data;
			
			foreach($data as $rule)
			{
				if(NGWord::CheckOneRule($text, $rule) == true)
				{
					return true;
				}
			}

			return false;
		}
	}
?>
