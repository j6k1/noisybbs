<?php
	class SubjectText
	{
		var $data;
		var $file_islocked;
		
		function SubjectText()
		{
			$this->file_islocked = false;
			$this->data = null;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("SubjectText");
		}
		
		function RemoveInstance()
		{
			Singleton::RemoveInstance("SubjectText");
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();			
			$subjecttxt = SubjectText::getInstance();
			
			$subjecttxt->data = null;
			
			$ret = $subjecttxt->ReadData($setting);
			
			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}
			
			return true;
		}
		
		function ReadData(&$setting, $lock = false)
		{
			$this->file_islocked = false;
			$this->data = array();

			if(file_exists($setting->subjecttxt) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$setting->subjecttxt}が見つかりません。");
			}
			
			$buff = FileReader::Read($setting->subjecttxt);
			
			if(ErrInfo::IsErr($buff))
			{
				return $buff;
			}
			
			$data = explode("\n", $buff);
			
			foreach($data as $row)
			{
				if($row == "")
				{
					continue;
				}
				
				preg_match('/^(\d+)\.dat/', $row, $match);
				$key = $match[1];
				$this->data[$key] = $row;
			}
			
			return true;
		}
		
		function &getRows()
		{
			return $this->data;
		}
		
		function getRow($key)
		{
			if(!isset($this->data[$key]))
			{
				return null;
			}
			
			return $this->data[$key];
		}
		
		function AddSubject($opekey, $title)
		{
			$subjecttxt = SubjectText::getInstance();
			
			$subjecttxt->Add($opekey, $title);
		}
		
		function UpdateTitle($opekey, $newtitle)
		{
			$data = $this->data[$opekey];
			
			list(,$row) = explode("<>", $data);
			preg_match('/(?:\((\d+)\))$/', $row, $match);
			
			$count = $match[1];
			
			$updaterow = "{$opekey}.dat<>{$newtitle} ({$count})";
			$this->data[$opekey] = $updaterow;
		}
		
		function UpdateSubject($opekey, $sage = false)
		{
			$subjecttxt = SubjectText::getInstance();
			
			$subjecttxt->Update($opekey, $sage);
		}
		
		function Add($opekey, $title)
		{
			$addrow = "{$opekey}.dat<>{$title} (1)";
			
			$data = array();
			
			$data[$opekey] = $addrow;
			
			foreach($this->data as $key => $row)
			{
				$data[$key] = $row;
			}
			
			$this->data = $data;
		}
		
		function Delete($key)
		{
			unset($this->data[$key]);
			
			return true;
		}
		
		function Update($opekey, $sage = false)
		{
			list(,$row) = explode("<>", $this->data[$opekey]);
			preg_match('/(\(\d+\))$/', $row, $match);
			$rescnt = $match[1];
			$rescnt = preg_match('/\d+/', $rescnt, $match);
			$rescnt = $match[0];
			$rescnt++;
			$newrow = preg_replace('/(\(\d+\))$/', '('. $rescnt .')', $this->data[$opekey]);
			
			if($sage == false)
			{	 
				unset($this->data[$opekey]);
				$data = array();
				
				$data[$opekey] = $newrow;
				
				foreach($this->data as $key => $row)
				{
					$data[$key] = $row;
				}

				$this->data = $data;
			}
			else
			{
				$this->data[$opekey] = $newrow;
			}
		}
		
		function WriteData()
		{
			$setting = SettingInfo::getInstance();			
			$subjecttxt = SubjectText::getInstance();

			$output = new BBSOutPutStream();
			
			$data = implode("\n", $subjecttxt->data);

			if($data != ""){$data .= "\n";}
			$data = preg_replace('/^\n/', '', $data);
			
			$output->PrintStr($data);
			
			if(file_exists($setting->subjecttxt) == false)
			{
				fclose(fopen($setting->subjecttxt, "w"));
				chmod($setting->subjecttxt, 0666);
			}
			else
			{
				$result = $output->FlushToFile($setting->subjecttxt, $subjecttxt->file_islocked);
			}
			
			if(ErrInfo::IsErr($result))
			{
				return $result;
			}			
		
			return true;
		}
	}
?>
