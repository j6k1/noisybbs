<?php
	class DatData
	{
		var $file_islocked;
		var $datname;
		var $data;

		function DatData()
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
			
			$this->datname = "../{$setting->bbs}/dat/{$threkey}.dat";
			$data = FileReader::Read($this->datname);

			if(ErrInfo::IsErr($data))
			{
				return $data;
			}
			
			$this->data = explode("\n", $data);
			
			if(count($this->data) > 1)
			{
				array_pop($this->data);
			}
			
			return true;
		}

		function Save()
		{
			$setting = SettingInfo::getInstance();

			$output = new BBSOutPutStream();
			
			$data = implode("\n", $this->data);

			$output->PrintStr($data);

			if($data != "")
			{
				$output->PrintStr("\n");
			}

			$ret = $output->FlushToFile($this->datname, $this->file_islocked);

			if(ErrInfo::IsErr($ret))
			{
				return $ret;
			}			
		
			return true;
		}
		
		function appendRes(&$writeinfo, $threkey = null)
		{
			$setting = SettingInfo::getInstance();

			if(!isset($threkey))
			{
				$threkey = $setting->thread_key;
			}
			
			$this->datname = "../{$setting->bbs}/dat/{$threkey}.dat";
			
			$subject = "";
			
			if(isset($writeinfo->subject))
			{
				$subject = Util::datspecialchars($subject);
				$subject = "<>{$writeinfo->subject}";
			}
			else
			{
				$subject = "";
			}
			
			$writeinfo->FROM = Util::datspecialchars($writeinfo->FROM);
			$writeinfo->mail = Util::datspecialchars($writeinfo->mail);
			$writeinfo->datetime = Util::datspecialchars($writeinfo->datetime);
			$writeinfo->MESSAGE = Util::datspecialchars($writeinfo->MESSAGE);
			$writeinfo->id = Util::datspecialchars($writeinfo->id);
			
			if(isset(CapInfo::getInstance()->capdata))
			{
				$res = <<<EOM
{$writeinfo->FROM}<>{$writeinfo->mail}<>{$writeinfo->datetime}<>{$writeinfo->MESSAGE}{$subject}

EOM;
			}
			else if(!isset($writeinfo->hostname))
			{
				$writeinfo->hostname = Util::datspecialchars($writeinfo->hostname);
				$res = <<<EOM
{$writeinfo->FROM}<>{$writeinfo->mail}<>{$writeinfo->datetime} ID:{$writeinfo->id}<>{$writeinfo->MESSAGE}{$subject}

EOM;
			}
			else
			{
				$res = <<<EOM
{$writeinfo->FROM}<>{$writeinfo->mail}<>{$writeinfo->datetime} Host:{$writeinfo->hostname}<>{$writeinfo->MESSAGE}{$subject}

EOM;
			}
			
			$output = new BBSOutPutStream();
			
			$output->PrintStr($res);
			
			if(isset($writeinfo->subject))
			{
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
			
			$data = file_get_contents($this->datname);
			$data = explode("\n", $data);
			$count = count($data) - 1;
			
			return $count;
		}
		
		function appendRow($row, $threkey = null)
		{
			$setting = SettingInfo::getInstance();

			if(!isset($threkey))
			{
				$threkey = $setting->thread_key;
			}
			
			$this->datname = "../{$setting->bbs}/dat/{$threkey}.dat";

			$output = new BBSOutPutStream();
			
			$output->PrintStr($row);

			$result = $output->FlushToFileA($this->datname, $this->file_islocked);

			if(ErrInfo::IsErr($result))
			{
				return $result;
			}
			
			return true;
		}
		
		function UpdateRow($resno, $row)
		{
			$this->data[$resno - 1] = $row;
			
			return true;
		}
		
		function getTitle()
		{
			list(,,,,$title) = explode("<>", $this->data[0]);
			
			return $title;
		}
		
		function getRowCount()
		{
			return count($this->data);
		}
	}
?>
