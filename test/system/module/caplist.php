<?php
	class CapList
	{
		var $data;
		var $file_islocked;
		
		function CapList()
		{
			$this->data = null;
			$this->file_islocked = false;
		}
		
		function getCapNames()
		{
			$names = array();
			
			foreach($this->data as $cappass => $val)
			{
				$names[$cappass] = $val->name;
			}
			
			return $names;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("CapList");
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();
			
			$capdata = FileReader::Read("system/sysdata/{$setting->caplist}");

			if(ErrInfo::IsErr($capdata))
			{
				return $capdata;
			}
			
			if($capdata != "")
			{
				$capdata = explode("\n", $capdata);
				array_pop($capdata);
			}
			else
			{
				$capdata = array();
			}
			
			$this->data = array();

			foreach($capdata as $val)
			{
				list($cappass, $admpass, $name, $bbslist, $authority) = explode("<>", $val);
				
				if($bbslist == "")
				{
					$bbslist = array();
				}
				else
				{
					$bbslist = explode("&", $bbslist);
				}
				
				if($authority == "")
				{
					$authority = array();
				}
				else
				{
					$authority = explode("&", $authority);
				}
				
				$this->data[$cappass] = new CapData($name, $cappass, $admpass, $bbslist, $authority);
			}
			
			return true;
		}
		
		function Add($capdata)
		{
			array_push($this->data, $capdata);
			
			return true;
		}
		
		function Delete($cappass)
		{
			unset($this->data[$cappass]);
			
			return true;
		}
		
		function UpdateAuthority($cappass, $authoritys)
		{
			foreach($authoritys as $val)
			{
				if(preg_match('/<|>/', $val))
				{
					return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"パラメータ{$val}に不正な文字が含まれています。");

				}
			}
			
			$this->data[$cappass]->authority = $authoritys;
			
			return true;
		}
		
		function UpdateBBS($cappass, $bbslist)
		{
			$this->data[$cappass]->bbslist = $bbslist;
			
			foreach($bbslist as $val)
			{
				if(preg_match('/<|>/', $val))
				{
					return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"パラメータ{$val}に不正な文字が含まれています。");

				}
			}
			
			return true;
		}
		
		function UpdatePass($cappass, $admpass)
		{
			$this->data[$cappass]->admpass = md5($admpass);
			
			return true;
		}
		
		function Save()
		{
			$setting = SettingInfo::getInstance();

			$output = new BBSOutPutStream();

			foreach($this->data as $rec)
			{
				$row = "{$rec->cappass}<>{$rec->admpass}<>{$rec->name}<>";
				$row .= implode("&", $rec->bbslist);
				$row .= "<>";
				$row .= implode("&", $rec->authority);
				
				$output->PrintStr("{$row}\n");
			}

			$ret = $output->FlushToFile("system/sysdata/{$setting->caplist}", $this->file_islocked);

			if(ErrInfo::IsErr($ret))
			{
				if($this->file_islocked)
				{
					Util::file_unlock("system/sysdata/{$setting->caplist}");
				}
				
				return $ret;
			}			
		
			return true;
		}

		function DeleteBBS($bbs)
		{
			foreach($this->data as &$rec)
			{
				$count = count($rec->bbslist);
				for($i=0 ; $i < $count ; $i++)
				{
					if($rec->bbslist[$i] == $bbs)
					{
						array_splice($rec->bbslist, $i, 1);
					}
				}
			}
			
			return true;
		}
	}
?>
