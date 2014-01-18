<?php
	class Regulation
	{
		var $denyhosts;
		var $file_islocked;
		var $need_updatefile;
		
		function Regulation()
		{
			$this->denyhosts = null;
			$this->file_islocked = false;
			$this->need_updatefile = false;
		}
		
		function &getInstance()
		{
			return Singleton::getInstance("Regulation");
		}
		
		function RemoveInstance()
		{
			Singleton::RemoveInstance("Regulation");
		}
		
		function Init()
		{
			$setting = SettingInfo::getInstance();
			$denyhostsfile = $setting->denyhosts_file;
			
			if(isset($setting->bbs) == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"{get_class($setting)}が初期化されていません。");
			}
			
			if(file_exists("{$denyhostsfile}") == false)
			{
				return Logging::generrinfo($this,
					__FUNCTION__ , __LINE__ , 
					"ファイル{$denyhostsfile}が見つかりません。");
			}
			
			$data = FileReader::Read($denyhostsfile);
			
			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			if($data == "")
			{
				$this->denyhosts = array();
			}
			else
			{
				$this->denyhosts = explode("\n", $data);
				array_pop($this->denyhosts);
			}
			
			return true;
		}
		
		function HostChk($hostname)
		{
			$regulation = Regulation::getInstance();
			$hostcount = count($regulation->denyhosts);
			$result = null;
						
			for($i=0; $i < $hostcount; $i++)
			{
				if($regulation->denyhosts[$i] == "")
				{
					continue;
				}
				
				$denyhost = $regulation->denyhosts[$i];

				if(preg_match('/^#[^\n]*;$/', $denyhost))
				{
					continue;
				}
				
				$result = @preg_match($denyhost, $hostname);
				
				if($result === false)
				{
					$regulation->need_updatefile = true;

					$datetime = date("Y/m/d H:i:s");
					$tmp = $regulation->denyhosts[i];
					
					$regulation->Update($i,"#{$datetime} pattern err : {$tmp};");
					
					Logging::writelog($regulation, __FUNCTION__ , __LINE__ , 
						"err \"preg_match\" : (reg pattern = {$denyhost})");
					continue;
				}

				if($regulation->need_updatefile == true)
				{
					$ret = $regulation->Save();
					if(ErrInfo::IsErr($ret))
					{
						Logging::writelog($regulation, __FUNCTION__ , __LINE__ , 
							$ret->sysmsg);
					}
				}
				
				if($result > 0)
				{
					return false;//規制中のホスト
				}
			}
			
			return true;
		}
		
		function getRow($index)
		{
			return $this->denyhosts[$index];
		}
		
		function getRows()
		{
			return $this->denyhosts;
		}
		
		function Add($hostpattern)
		{
			array_push($this->denyhosts, $hostpattern);
			
			return true;
		}
		
		function Update($index, $hostpattern)
		{
			$this->denyhosts[$index] = $hostpattern;
			
			return true;
		}
		
		function Delete($index)
		{
			unset($this->denyhosts[$index]);
			
			return true;
		}
		
		function Count()
		{
			return count($this->denyhosts);
		}
		
		function Save()
		{
			$setting = SettingInfo::getInstance();
			$errmsg = ErrMessage::getInstance();
			$regulation = Regulation::getInstance();
			$denyhostsfile = $setting->denyhosts_file;
			
			if(isset($this->denyhosts) == false)
			{
				return Logging::generrinfo($regulation,
					__FUNCTION__ , __LINE__ , 
					$errmsg->$denyhosts_notinit);
			}
			
			$denyhostsstr = implode("\n", $regulation->denyhosts);
			if($denyhostsstr != ""){$denyhostsstr .= "\n";}
			
			$output = new BBSOutPutStream();
			
			$output->PrintStr($denyhostsstr);			
			$ret = $output->FlushToFile($denyhostsfile, $this->file_islocked);
			
			if(ErrInfo::IsErr($ret))
			{
				if($this->file_islocked)
				{
					Util::file_unlock($denyhostsfile);
				}
				return $ret;
			}
			
			$data = $denyhostsstr;
			if($data == "")
			{
				$this->denyhosts = array();
			}
			else
			{
				$this->denyhosts = explode("\n", $data);
				array_pop($this->denyhosts);
			}
			
			return $ret;
		}
	}
?>